<?php
class Admin extends Article{
	function lists(){
		$list=$this->db->getlist(TB.$this->table);
		$dbit=$this->db;
		include(SYS_ROOT.CACHE."admin_array.inc");
		include(SYS_ROOT.CACHE."cat_array.inc");
		include($this->tpl->myTpl('manage'.$this->table));
	}
	function edit(){
		//管理员参数缓存
		include(SYS_ROOT.CACHE."admin_array.inc");
		$getArray=$this->db->getlist(TB.$this->table,'id='.$this->id[0]);
		$category=$this->db->getlist(TB.'category');
		$o=$getArray[0];
		$authlist=array();
		$authlist=explode('|',$o['auth']);
		$o['auth_level']=$authlist[0];
		$o['auth_cat']=intval($authlist[1]);
		$goctrl='update';
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function add(){
		//管理员参数缓存
		include(SYS_ROOT.CACHE."admin_array.inc");
		$goctrl='save';
		$category=$this->db->getlist(TB.'category');
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function save(){
		$data=$this->columsdatasafe();
		$mydata=$this->getauth($data);
		if(strlen($mydata['passwd'])<30){
			$mydata['passwd']=substr( md5( $mydata['passwd']),0,30);
		}
		$status=$this->db->add_one(TB.$this->table,$mydata);
		Base::execmsg("添加","?action=".$this->table.'&ctrl=lists',$status);
	}
	function update(){
		$data=$this->columsdatasafe();
		$mydata=$this->getauth($data);
		if(strlen($mydata['passwd'])<30){
			$mydata['passwd']=substr( md5( $mydata['passwd']),0,30);
		}
		$status=$this->db->updatelist(TB.$this->table,$mydata,$this->id);
		Base::execmsg("修改","?action=".$this->table.'&ctrl=lists',$status);
	}
	function getauth($data){
		unset($data['auth_level']);
		unset($data['auth_cat']);
		if(!$data['name'])Base::showmessage('用户名必须填写');
		$authcat=Base::safeword($_POST['auth_cat'],1)?'|'.Base::safeword($_POST['auth_cat'],1):'';
		$data['auth']=$_POST['auth_level'].$authcat;
		return $data;
	}
}
?>
