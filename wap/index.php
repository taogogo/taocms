<?php
include "../config.php";
//载入公共变量
include SYS_ROOT.INC."common.php";
//数据处理
$dbit=new Dbclass(SYS_ROOT.DB_NAME);
$id=Base::safeword($_GET['id'],1);
$page=Base::safeword($_GET['p'],1);
$eachpage=EACHPAGE;
$cat=Base::safeword($_GET['cat'],1);
$name=Base::safeword( Base::safeword($_POST['name'],3),5);
$comment=Base::safeword( Base::safeword($_POST['comment'] , 3 ) , 5);
$article_id=Base::safeword($_POST['article_id'],1);
//配置说明
$webname=WEBNAME;
$webinfo=WEBINFO;
$weburl=WEBURL;
//公告
$announce=ANNOUNCE;
//直接用数组缓存
include(SYS_ROOT.CACHE."cat_array.inc");
//文章页参数缓存
include(SYS_ROOT.CACHE."art_array.inc");
$mobile=Base::safeword($_POST['mobile'],4);
if($mobile){
	if($name==''||$comment=='')die('Please input your name and comment correctly!<a href="?id='.$article_id.'">Back</a>');
	$tmp['article_id']=$article_id;
	$tmp['name']=Base::safeword($name,4);
	$tmp['emails']='ok@ok.com';
	$tmp['content']=Base::safeword($comment,5);
	$tmp['ips']=Base::realip();
	$tmp['times']=Base::getnowtime();
	$data['status']=1;
	$addstatus=$dbit->add_one(TB."comment",$tmp);
	$dbit->updatelist(TB."cms","cmtcount=cmtcount+1",$tmp['article_id']);
	die('^_^Submit Succefully!<a href="?id='.$article_id.'">GO ON!</a>');

}
if($id){

	//上一篇
	$upart=$dbit->get_one(TB."cms","status=1 and id<".($id),"id,name",1);
	//下一篇
	$downart=$dbit->get_one(TB."cms","status=1 and id>".($id),"id,name",1,'id ASC');
	//评论
	$commenttotal=$dbit->get_one(TB."comment","status=1 and article_id=".($id),"count(*)");
	$cmtotal=$commenttotal['count(*)'];
	$comments=$dbit->getlist(TB."comment","status=1 and article_id=".($id),"*");
	$atl=$dbit->get_one(TB."cms","status=1 and id=".$id,"*",1);
	$addtitle=$atl['name']?$atl['name']."_":"";
	$tpl = new Template();
	include($tpl->myTpl('wap_display','','$tpl'));
}else{
	//评论
	$recnetcmts=$dbit->getlist(TB."comment",'status=1',"content,article_id,name",10);
	$total=$articleData['count'];
	$indexs='wap_index';
	if($cat){
		$totaldata=$dbit->getlist(TB."cms","status=1 and cat=".$cat,"count(*)");
		$total=$totaldata[0]['count(*)'];
		$addtail="&cat=".$cat;
		$indexs='wap_index';
	}
	$uppage=$page>0?$page-1:0;
	$downpage=($page+1)*$eachpage<$total?$page+1:$page;	 
	$o=$dbit->getlist(TB."cms","status=1 and ".($cat?"cat=".$cat:"1=1"),"*",$eachpage*$page.','.$eachpage,"orders DESC,id DESC");
	$catinfo=$dbit->getlist(TB."category","status=1 and id=".$cat);
	$addtitle=$catinfo[0]['name']?$catinfo[0]['name']."_":"";
	//模板生效
	$tpl = new Template();
	include($tpl->myTpl($indexs,'','$tpl'));
}
?>  