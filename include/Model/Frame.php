<?php
class Frame{
	public $tpl;
	public $db;
	function __construct(){
		$this->tpl=new Template();
	}
	function iframes(){
		include($this->tpl->myTpl('adminframe'));
	}
	function main(){
		//文章缓存
		if(is_writable(SYS_ROOT.CACHE.'art_array.inc')){
			include(SYS_ROOT.CACHE.'art_array.inc');
		}else{
			$this->db=new Dbclass(SYS_ROOT.DB_NAME);
			$totaldata=$this->db->get_one(TB.'cms','status=1','count(*) as total');
			$articleData['count']=$totaldata['total'];
		}
		include($this->tpl->myTpl('main'));
	}
	function menu(){
		$dbname=DB_NAME;
		include($this->tpl->myTpl('menu'));
	}
	function top(){
		$username=$_SESSION[TB.'admin_name'];
		$webname=WEBNAME;
		$weburl=WEBURL;
		include($this->tpl->myTpl('top'));
	}
	function logout(){
		unset($_SESSION[TB.'is_admin']);
		unset($_SESSION[TB.'admin_id']);
		echo '<script>window.parent.location="index.php";</script>';

	}
	public function login(){
		if($_SESSION[TB.'is_admin']){
			Base::showmessage("","admin.php?action=frame&ctrl=iframes",0.01);
		}
		include($this->tpl->myTpl('login'));
	}
	public function checkUser(){
		$this->db=new Dbclass(SYS_ROOT.DB_NAME);
		$user=$this->db->get_one(TB."admin","name='".Base::safeword($_POST['name'],6)."'");
		if( strlen($user['passwd'])==30){
			$autoOk=substr(md5($_POST['pwd']),0,30)==$user['passwd'];
		}else{
			$autoOk=$_POST['pwd']==$user['passwd'];
		}
		if($autoOk){
			//unset($_SESSION);
			$authlist=array();
			$authlist=explode('|',$user['auth']);
			$_SESSION[TB.'admin_name']=$user['name'];
			$_SESSION[TB.'admin_level']=$authlist[0];
			if(strstr($authlist[0], 'admin')){
				$_SESSION[TB.'is_admin']="1";
			}else{

				Base::showmessage("No Permiting","-1");
			}
			$_SESSION[TB.'admin_cat']=intval($authlist[1]);
			$_SESSION[TB.'admin_id']=$user['id'];
			Base::showmessage("登录成功","admin.php?action=frame&ctrl=iframes");
		}
		else{
			Base::showmessage("用户名或密码错误","-1");
		}

	}

}

?>