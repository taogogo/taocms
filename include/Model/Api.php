<?php
class Api{
	function dig()
	{
		$db=new Dbclass(SYS_ROOT.DB_NAME);
		$id=Base::safeword($_REQUEST['id'],1);
		$ctrl=Base::safeword($_REQUEST['c'],1);
		$ajax=Base::safeword($_REQUEST['ajax'],1);
		$param=Base::safeword($_REQUEST['p']);
		$flag=$param=='up'?'+':'-';
		$word=$param=='up'?'顶':'踩';
		if($_COOKIE['tao_dig'.$id]<Base::getnowtime()){
			setcookie('tao_dig'.$id,Base::getnowtime()+3600);
		}else{
			Base::showmessage('本文一小时之内不能再'.$word.'啦','-1',null,$ajax);
		}
		$db->updatelist(TB.'cms','orders3=orders3'.$flag.'1',$id);
		if(MEMCACHE){
			$mem=new Memcached(MEMCACHE);
			$mem->delete($id.'_cms');
		}
		Base::showmessage($word.'成功',$ajax?'1':$_SERVER['HTTP_REFERER'],null,$ajax);
	}
}