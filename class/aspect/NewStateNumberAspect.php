<?php
namespace org\opencomb\oauth_userstate_adapter\aspect;



use org\jecat\framework\mvc\controller\Request;

use org\opencomb\oauth_userstate_adapter\PushState;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\aop\jointpoint\JointPointMethodDefine;

class NewStateNumberAspect
{
	/**
	 * @pointcut
	 */
	public function pointcutNewStateNumberAspect()
	{
		return array(
			new JointPointMethodDefine('org\\opencomb\\userstate\\UpdateForwardNumber','process') ,
		) ;
	}
	
	/**
	 * @advice around
	 * @for pointcutUserStateForwardStateAspect
	 */
	private function process()
	{
		
		// 调用原始原始函数
		$stid = aop_call_origin() ;
		
		$stid = $this->params["stid"];
	    $service = $this->params["service"];
	    
		if($service!="wownei.com" && \org\jecat\framework\auth\IdManager::singleton()->currentId())
		{
		    $aModelOauthState = \org\jecat\framework\bean\BeanFactory::singleton()->createBean( $conf=array(
		            'class' => 'model' ,
		            'orm' => array(
            			'table' => 'oauth:state' ,
	                    'keys'=>array('stid'),
                        'columns' => array("sid","service") ,  
		            ) ,
		    )) ;
		    if(empty($service))
		    {
		        $aModelOauthState->loadSql('stid = @1' , array($stid)) ;
		        if($aModelOauthState->isEmpty())
		        {
		            echo  "0";exit;
		        }
		        
		    }else{
		        $aModelOauthState->loadSql('stid = @1 and service = @2' , array($stid,$service)) ;
		    }
		    
		    $aModel = \org\jecat\framework\bean\BeanFactory::singleton()->createBean( $conf=array(
		            'class' => 'model' ,
		            'orm' => array(
		                    'table' => 'oauth:user' ,
		                    'keys'=>array('uid','suid'),
		            ) ,
		    )) ;
		    
		    
		    $aId = \org\jecat\framework\auth\IdManager::singleton()->currentId() ;
		    $aModel->loadSql('uid = @1 and service = @2' , array($aId->userId() , $aModelOauthState->service)) ;
	    
		    if($aModel)
		    {
		        try{
		            $aAdapter = \org\opencomb\oauth\adapter\AdapterManager::singleton()->createApiAdapter($aModelOauthState->service) ;
		            $aRs = @$aAdapter->getForwardNumber($aModel->token,$aModel->token_secret,$aModelOauthState->sid);
		            
		            if($aRs > 0)
		            {
		                $aModelState = \org\jecat\framework\bean\BeanFactory::singleton()->createBean( $conf=array(
		                        'class' => 'model' ,
		                        'orm' => array(
                        			'table' => 'oauth:state' ,
            	                    'keys'=>array('stid'),
                                    'columns' => array("sid","forwardcount") ,  
		                        ) ,
		                )) ;
		                
		                $aModelState->loadSql('stid = @1 and service = @2' , array($stid, $aModelOauthState->service)) ;
		                $aModelState->setData("forwardcount",$aRs);
		                $aModelState->save();
		            }
		            
		            echo $aRs;
		            
		        }catch(AuthAdapterException $e){
		            $this->createMessage(Message::error,$e->messageSentence(),$e->messageArgvs()) ;
		            $this->messageQueue()->display() ;
		            return ;
		        }
		    }
                
		}
	    exit;
		
	}
}
?>