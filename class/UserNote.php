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

class UserNote extends Controller
{
    private $minNextTime = 30;
    private $maxNextTime = 600;
    
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
            ) ,
		) ;
	    
	    return  $aOrm;
	}
	public function process()
	{
	    
	    if(!IdManager::singleton()->currentId() || empty($this->params['suid']) || empty($this->params['service']))
	    {
	        exit;
	    }
	    
	    $aId = IdManager::singleton()->currentId() ;
	    
	    $this->auser->loadSql('uid = @1 and token <> @2 and service= @3' , $aId->userId() , "", $this->params['service']) ;
	    
        if($this->auser->hasData('token') && $this->auser->hasData('token_secret') )
        {
            try{
                $aAdapter = AdapterManager::singleton()->createApiAdapter($this->auser->service) ;
                $aRs = @$aAdapter->getUserByNote($this->auser,$this->params['suid']);
            }catch(AuthAdapterException $e){
                $this->createMessage(Message::error,$e->messageSentence(),$e->messageArgvs()) ;
                $this->messageQueue()->display() ;
                return ;
            }
            if($aRs)
            {
                $this->auser->loadSql('suid = @1 and service= @3' , $this->params['suid'] , "", $this->params['service']) ;
                $this->auser->setData("note",$aRs);
                $this->auser->save();
            }
            
            echo $aRs;exit;
        }
	}
}

?>