<?php
session_start();
include('config.php');
include(SYS_ROOT.INC.'common.php');
$ctrl=$_REQUEST['ctrl'];
$action=$_REQUEST['action'];
$m=ucfirst($action);
if(!in_array($m,array('Api','Comment')))die;
$model=new $m();
if (method_exists($m,$ctrl)) {
	$model->$ctrl();
}