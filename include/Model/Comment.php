<?php
class Comment extends Article {
	function lists()
	{
		$eachpage=EACHPAGE;
		$totaldata=$this->db->getlist(TB."comment",'1=1',"count(*)");
		$total=$totaldata[0]['count(*)'];
		$page=$_GET['p'];
		$uppage=$page>0?$page-1:0;
		$downpage=($page+1)*$eachpage<$total?$page+1:$page;
		$list=$this->db->getlist(TB.'comment','1=1','*',$eachpage*$page.','.$eachpage,'id DESC');
		$dbit=$this->db;
		include($this->tpl->myTpl('managecomment'));
	}
	function save(){
		if(!isset($_POST['antirbt'])){
			showmessage("未填写验证码","-1");
		}
		
		//数据处理
		$dbit=new Dbclass(SYS_ROOT.DB_NAME);
		$data['article_id']=Base::safeword($_POST['article_id'],1);
		$data['name']=Base::safeword(Base::safeword($_POST['name'],3),5);
		$data['emails']=Base::safeword(Base::safeword($_POST['emails'],3),5);
		$data['websites']=Base::safeword(Base::safeword($_POST['websites'],3),5);
		$data['content']=Base::safeword( $_POST['comment'] ,5 );
		$data['ips']=Base::realip();
		$data['times']=Base::getnowtime();
		$data['status']=1;
		if($_SESSION['antirbt']!=$_POST['antirbt'])Base::showmessage("请准确填写验证码","-1");
		if($data['name']=='')Base::showmessage("请填写您的名字","-1");
		if($data['emails']=='')Base::showmessage("请填写您的电子邮箱","-1");
		if($data['article_id']=='')Base::showmessage("参数错误","-1");
		if(!preg_match("/^[a-z]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i",$data['emails'])){
			Base::showmessage("请准确填写您的电子邮箱","-1");
		}
		$urlArray = parse_url($data['websites']);
		if ($url &&( !$urlArray['scheme'] || !$urlArray['host'])) {
			Base::showmessage("请准确填写您的网址","-1");
		}
		if($data['content']=='')Base::showmessage("请填写您的留言内容","-1");
		$addstatus=$dbit->add_one(TB."comment",$data);
		if($addstatus){
			$dbit->updatelist(TB."cms","cmtcount=cmtcount+1",$data['article_id']);
			if(MEMCACHE){
				$mem=new Memcached(MEMCACHE);
				$mem->delete($data['article_id'].'_cms');
			}
			//清空本次使用的session变量
			session_destroy();
			Base::showmessage("留言成功",$_SERVER['HTTP_REFERER']);
		}else{
			showmessage("留言失败，请稍后再试",'-1');
		}
	}
	function code(){
		$fword=rand(0,100);
		$bword=rand(0,100);
		if($fword<$bword)list($fword,$bword) = array($bword,$fword);
		//session_start();
		//setcookie(session_name() ,session_id(), time() + 60, "/");
		$_SESSION['antirbt']=$fword-$bword;
		die('document.getElementById("antiarea").innerHTML=\''.$fword.'-<input type="text" name="antirbt" id="antirbt"  size="4" tabindex="4" />='.$bword.'\'');
	}
}
?>