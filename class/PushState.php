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

class PushState extends Controller
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
	    
	    
	    if(empty($aService) || empty($sTitle))
	    {
	        return;
	    }
	    
	    
	    $aId = IdManager::singleton()->currentId() ;
	    
	    $this->auser->loadSql('uid = @1',$aId->userId()) ;
	    
	    foreach($this->auser->childIterator() as $o)
	    {
	        if(in_array($o->service, $aService) )
	        {
	            try{
	                $aAdapter = AdapterManager::singleton()->createApiAdapter($o->service) ;
	                
	                $oFace = new \org\opencomb\userstate\FaceIcon();
	                $sTitle = $oFace->changeTag($sTitle, $o->service);
	                
	                $aRs = @$aAdapter->createPushMulti($o,$sTitle);
	            }catch(AuthAdapterException $e){
	                $this->createMessage(Message::error,$e->messageSentence(),$e->messageArgvs()) ;
	                $this->messageQueue()->display() ;
	                return ;
	            }
	        }
	    }
	    
	    $OAuthCommon = new OAuthCommon("",  "");
	    $aRsT = $OAuthCommon -> multi_exec();
	    
	    
	    $aIdList = array();
	    foreach($this->auser->childIterator() as $o)
	    {
	        if(in_array($o->service, $aService) )
	        {
	            $aAdapter = AdapterManager::singleton()->createApiAdapter($o->service) ;
	    
	            $aIdList[$o->service] = @$aAdapter->pushLastId($o,$aRsT[$o->service]);
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