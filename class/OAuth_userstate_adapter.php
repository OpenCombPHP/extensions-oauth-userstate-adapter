<?php 
namespace org\opencomb\oauth_userstate_adapter ;

use org\jecat\framework\ui\xhtml\weave\Patch;

use org\jecat\framework\lang\aop\AOP;

use org\jecat\framework\ui\xhtml\weave\WeaveManager;

use org\opencomb\platform\ext\Extension ;

class OAuth_userstate_adapter extends Extension 
{
	/**
	 * 载入扩展
	 */
	public function load()
	{
		$aWeaveMgr = WeaveManager::singleton() ;
		//获取最新记录数然时候先拉取
		AOP::singleton()->register('org\\opencomb\\oauth_userstate_adapter\\aspect\\UserStatePullStateAspect') ;
		
		//发布消息同步到weibo
		AOP::singleton()->register('org\\opencomb\\oauth_userstate_adapter\\aspect\\UserStatePushStateAspect') ;
		$aWeaveMgr->registerTemplate( 'userstate:CreateState.html', "/form@0/div@0/div@0/div@0/div@1/div@0", 'oauth_userstate_adapter:aspect/pushState.html', Patch::appendAfter ) ;
		
		
		//转发
		$aWeaveMgr->registerTemplate( 'userstate:UserState.html', "/div@0/model:foreach@0/dl@0/dd@0/div@3/textarea@0", 'oauth_userstate_adapter:aspect/ForwardState.html', Patch::appendAfter ) ;
	}
}