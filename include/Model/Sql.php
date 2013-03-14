<?php
class Sql {
	public $db;
	public $tpl;
	public $id;
	function __construct($table){
		$this->table=$table;
		$this->tpl=new Template();
	}
	function display(){
		include($this->tpl->myTpl('form'.$this->table));
	}
	function excute(){
		$creatTable=Base::magic2word($_POST['sqltext']);
		$db=new Dbclass(SYS_ROOT.DB_NAME);
		$o=$db->query($creatTable);
		echo'<pre>';
		print_r($db->fetch_array($o));
		Base::execmsg('执行SQL','?action=sql&ctrl=display',$o);
	}
}
?>