<?php
namespace org\opencomb\oauth_userstate_adapter\aspect;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\aop\jointpoint\JointPointMethodDefine;

class UserStatePullStateAspect
{
	/**
	 * @pointcut
	 */
	public function pointcutUserStatePullStateAspect()
	{
		return array(
			new JointPointMethodDefine('org\\opencomb\\userstate\\NewStateNumber','process') ,
		) ;
	}
	
	/**
	 * @advice around
	 * @for pointcutUserStatePullStateAspect
	 */
	private function process()
	{
        $oAuth = new \org\opencomb\oauth_userstate_adapter\PullState();
        $oAuth->process();
		
		// 调用原始原始函数
		aop_call_origin() ;
	}
}
?>