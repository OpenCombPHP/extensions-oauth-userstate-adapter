<?php
namespace org\opencomb\oauth_userstate_adapter\aspect;

use org\opencomb\oauth_userstate_adapter\ForwardState;

use org\jecat\framework\mvc\controller\Request;

use org\opencomb\oauth_userstate_adapter\PushState;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\aop\jointpoint\JointPointMethodDefine;

class UserStatePushStateAspect
{
	/**
	 * @advice around
	 * @for pointcutUserStatePushStateAspect
	 */
	private function process()
	{
		
		// 调用原始原始函数
		$stid = aop_call_origin() ;
		
		
		/**
		 * push weibo
		 * @var unknown_type
		 */
		
	    if( Request::isUserRequest($this->params) )
	    {
	        //转发
	        if($this->params['forwardtid'])
	        {
	            if(is_array($this->params['pushweibo']))
	            {
	                $aWeibo = $this->params['pushweibo'];
	            }else{
	                $aWeibo = explode("|", $this->params['pushweibo']);
	            }
	            
	            
	            if($aWeibo)
	            {
	                $aModel = \org\jecat\framework\bean\BeanFactory::singleton()->createBean( $conf=array(
	                        'class' => 'model' ,
	                        'list'=>true,
	                        'orm' => array(
	                                'table' => 'oauth:state' ,
		                            'keys'=>array('sid','service'),
	                                'where' => array('stid = @1',$this->params['forwardtid']) ,
	                        ) ,
	                ), 'UserStatePushStateAspect' ) ;
	                $aModel->load() ;
	                
	                foreach($aModel->childIterator() as $o)
	                {
	                    $ostate[$o->service] = $o->sid ;
	                }
	                for($i = 0; $i < sizeof($aWeibo); $i++){
	                    if(!empty($ostate[$aWeibo[$i]]))
	                    {
	                        $aList1[] = $aWeibo[$i];
	                    }else{
	                        $aList2[] = $aWeibo[$i];
	                    }
	                    
	                }
	                
	                if(!empty($aList1))
	                {
	                    $aParams = array(
	                            'service'=>$aList1,
	                            'title'=>preg_replace("/<a .*?>(.*?)<\/a>/u", "$1", $this->params['body']),
	                            'forwardtid'=>$ostate,
	                            'stid'=>$stid,
	                    );
	                    $oOauthPush = new \org\opencomb\oauth_userstate_adapter\ForwardState($aParams);
	                    $oOauthPush->process();
	                }
	                
	                if(!empty($aList2))
	                {
	                    $aParams = array(
	                            'service'=>$aList2,
	                            'title'=>preg_replace("/<a .*?>(.*?)<\/a>/u", "$1", $this->params['body']),
	                            'stid'=>$stid,
	                    );
	                    $oOauthPush = new PushState($aParams);
	                    $oOauthPush->process();
	                }
	                
	                
	            }
	            
	        }else{
	            if(is_array($this->params['pushweibo']))
	            {
	                $aWeibo = $this->params['pushweibo'];
	            }else{
	                $aWeibo = explode(",", $this->params['pushweibo']);
	            }
	            
	            
	            if($aWeibo)
	            {
	                $aParams = array(
	                        'service'=>$aWeibo,
	                        'title'=>$this->params['body'],
	                        'picPath'=>$this->params['picPath'],
	                        'stid'=>$stid,
	                );
	                $oOauthPush = new PushState($aParams);
	                $oOauthPush->process();
	            }
	        }
	        
	    }
		
	}
}
?>