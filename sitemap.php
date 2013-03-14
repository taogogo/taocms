<?php   
include "config.php";
//载入公共变量
include SYS_ROOT.INC."common.php";
//数据处理
$dbit=new Dbclass();
$dbitok=$dbit->connect(SYS_ROOT.DB_NAME);
function getcat($cats,$dbit){
	echo '<ul>';
	foreach($cats as $cat){

		echo '<li><a href="'.WEBURL.$cat['staticurl'].'" >'.$cat['name'].'</a></li>';
		$cattails=$dbit->getlist(TB."category","status=1 and fid=".intval($cat['id']),"id,name,staticurl,fid,nickname");
		if($cattails){
			getcat($cattails,$dbit);
		}
	}
	echo '</ul>';
}
getcat(array(0=>array('name'=>'首页')),$dbit);