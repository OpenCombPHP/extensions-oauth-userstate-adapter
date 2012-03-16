<?php
namespace org\opencomb\oauth_userstate_adapter ;

use net\daichen\oauth\OAuthCommon;

use net\daichen\oauth\Http;

use org\opencomb\userstate\CreateState;

use org\opencomb\oauth\adapter\AuthAdapterException;

use org\opencomb\platform\ext\Extension;
use org\opencomb\oauth\adapter\AdapterManager;

use org\jecat\framework\db\DB;
use org\jecat\framework\auth\IdManager;
use org\jecat\framework\message\Message;
use org\opencomb\coresystem\mvc\controller\Controller ;

class ForwardState extends Controller
{
	public function createBeanConfig()
	{
	    $aOrm = array(
		
	        /**
	         * 用来快速获取，判断认证信息
	         */
            'model:auser' => array(
                	'orm' => array(
                		'table' => 'oauth:user' ,
    		            'keys'=>array('uid','suid'),
                	) ,
                    'list' => true,
            ) ,
            'model:state' => array(
                	'orm' => array(
                		'table' => 'oauth:state' ,
    		            'keys'=>array('sid','service'),
                	) ,
            ) ,
	            
		) ;
	    
	    return  $aOrm;
	}
	public function process()
	{
	    
	    $aService = $this->params['service'];
	    $sTitle = $this->params['title'];
	    $forwardtid = $this->params['forwardtid'];
	    
	    if(empty($aService) || empty($sTitle) || empty($forwardtid))
	    {
	        return;
	    }
	    
	    
	    $aId = IdManager::singleton()->currentId() ;
	    
	    /**
	     * 克隆MODEL-Where，只用来获得用户KEY
	     * @var unknown_type
	     */
	    $auserModelWhere = clone $this->auser->prototype()->criteria()->where();
	    $auserModelWhere->eq('uid',$aId->userId());
	    $this->auser->load($auserModelWhere) ;
	    
	    foreach($this->auser->childIterator() as $o)
	    {
	        if(in_array($o->service, $aService) )
	        {
	            try{
	                $aAdapter = AdapterManager::singleton()->createApiAdapter($o->service) ;
	                $aRs = @$aAdapter->createPushForwardMulti($o,$forwardtid[$o->service],$sTitle);
	            }catch(AuthAdapterException $e){
	                $this->createMessage(Message::error,$e->messageSentence(),$e->messageArgvs()) ;
	                $this->messageQueue()->display() ;
	                return ;
	            }
	        }
	    }
	    
	    $OAuthCommon = new OAuthCommon("",  "");
	    $aRsT = $OAuthCommon -> multi_exec();
	    
	    echo "<pre>";print_r($aRsT);echo "</pre>";
	    $aIdList = array();
	    foreach($this->auser->childIterator() as $o)
	    {
	        if(in_array($o->service, $aService) )
	        {
	            $aAdapter = AdapterManager::singleton()->createApiAdapter($o->service) ;
	    
	            $aIdList[$o->service] = @$aAdapter->pushLastForwardId($o,$aRsT[$o->service]);
	        }
	    }
	    
	    foreach($aIdList as $k => $id)
	    {
	        $this->state->setData('stid',$this->params['stid']) ;
	        $this->state->setData('service',$k) ;
	        $this->state->setData('sid',$id) ;
	        $this->state->save();
	    }
	    
	}
}

?>