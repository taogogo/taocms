<?php
class User extends Article{
	function mod(){
		$this->id[0]=$_SESSION[TB.'admin_id'];
		$o=$this->db->getlist(TB.'admin','id='.$this->id[0]);
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function update(){
		if(strlen($_POST['pwd'])<30){
			$_POST['pwd']=substr( md5( $_POST['pwd']),0,30);
		}
		$data=array('passwd'=>$_POST['pwd']);
		$o=$this->db->updatelist(TB."admin",$data,$_SESSION[TB.'admin_id']);
		Base::execmsg('修改','?action='.$this->table.'&ctrl=mod',$o);
	}
}
?>