<?php
namespace org\opencomb\oauth_userstate_adapter\aspect;

use org\opencomb\oauth_userstate_adapter\PullState;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\lang\aop\jointpoint\JointPointMethodDefine;

class ToListStateAspect
{
	/**
	 * @pointcut
	 */
	public function pointcutToListStateAspect()
	{
		return array(
			new JointPointMethodDefine('org\\opencomb\\userstate\\ListState','process') ,
		) ;
	}
	
	/**
	 * @advice around
	 * @for pointcutToListStateAspect
	 */
	private function process()
	{
		// 调用原始原始函数
		aop_call_origin() ;
		
		
		if($this->params['lastData'])
		{
		    $aLastData = json_decode($this->params['lastData'],true);
		    $oPullState = new \org\opencomb\oauth_userstate_adapter\PullState(array("lastData"=>$aLastData));
		    $oPullState->process();
		    
		}
	}
}
?>