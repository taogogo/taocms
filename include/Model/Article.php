<?php
class Article{
	public $table;
	public $db;
	public $tpl;
	public $id;
	public $data;
	public $mem;
	function __construct($table,$id=0){
		$this->table=$table;
		$this->db=new Dbclass(SYS_ROOT.DB_NAME);
		$this->mem=MEMCACHE?new Memcached(MEMCACHE):null;
		$this->tpl=new Template();
		$this->id=$id;
	}
	function add(){
		$goctrl='save';
		$category=$this->db->getlist(TB.'category',$_SESSION[TB.'admin_cat']?'id='.$_SESSION[TB.'admin_cat']:'1=1');
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function save(){
		$data=$this->columsdata();
		$status=$this->db->add_one(TB.$this->table,$data);
		Base::execmsg("添加","?action=".$this->table.'&ctrl=lists',$status);
	}
	function edit(){
		$getArray=$this->db->getlist(TB.$this->table,'id='.$this->id[0]);
		$category=$this->db->getlist(TB.'category',$_SESSION[TB.'admin_cat']?'id='.$_SESSION[TB.'admin_cat']:'1=1');
		$o=$getArray[0];
		$goctrl='update';
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function update(){
		$data=$this->columsdata();
		$status=$this->db->updatelist(TB.$this->table,$data,$this->id);
		Base::execmsg("修改","?action=".$this->table.'&ctrl=lists',$status);
	}
	function del($wheres=''){
		$status=$this->db->delist(TB.$this->table,$this->id,$wheres);
		Base::execmsg("删除","?action=".$this->table.'&ctrl=lists',$status);
	}
	function lists($wheres='1=1',$colums='*',$limit='20',$orderbys='id DESC'){
		$list=$this->db->getlist(TB.$this->table,$wheres,$colums,$limit,$orderbys);
		$dbit=$this->db;
		include($this->tpl->myTpl('manage'.$this->table));
	}
	function counts($wheres='1=1',$add='status=1'){
		$arttotal=$this->db->getlist(TB.$this->table,$add." and ".$wheres,"count(*)");
		return $arttotal[0]['count(*)'];
	}
	function columsdata(){
		
		unset($_POST['action']);
		unset($_POST['ctrl']);
		unset($_POST['id']);
		unset($_POST['Submit']);
		return $_POST;
	}
}
?>