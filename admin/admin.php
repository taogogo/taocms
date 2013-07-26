<?php
session_start();
include "../config.php";
include "../include/common.php";
$action=$_REQUEST['action'];
$ctrl=$_REQUEST['ctrl'];
$id=(array)$_REQUEST['id'];
//请登录
if(!Base::checkadmin()&&$ctrl!='login'&&$ctrl!='checkUser'){
	Base::showmessage('',"index.php?action=login",1);
}
$referInfo=parse_url($_SERVER['HTTP_REFERER']);
$referHost=isset($referInfo['port'])?"{$referInfo['host']}:{$referInfo['port']}":$referInfo['host'];
if($referHost !== $_SERVER['HTTP_HOST']){
    Base::showmessage('refer error','admin.php?action=frame&ctrl=logout');
}
if(Base::catauth($action)){
	if(class_exists($action)){
		$model=new $action($action,$id);
		if (method_exists($action,$ctrl)) {
			$model->$ctrl();
		}
	}
}

?>