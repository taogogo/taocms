<?php
class Datastore{
	public $table;
	public $db;
	public $tpl;
	public $id;
	function __construct($table,$id=0){
		$this->table=$table;
		$this->db=new Dbclass(SYS_ROOT.DB_NAME);
		$this->tpl=new Template();
		$this->id=$id;
	}
	function display(){
		include($this->tpl->myTpl('form'.$this->table));
	}
	function create(){
		header('Content-type: application/txt');
		header('Content-Disposition: attachment; filename="backup-'.date('Y-m-d').'.sql"');
		$backups='';
		$bulist=explode('|',$_GET['bulist']);
		foreach($bulist as $bus){
			$addsql=($bus=='cms'&&$_GET['from'])?' limit '.$_GET['from'].','.$_GET['to']:'';
			$sql='select *from '.TB.$bus.$addsql;
			$o=$this->db->query($sql);
			while($data=$this->db->fetch_array($o)){
				$colums='';
				$datas='';
				foreach($data as $key=>$v){
					$colums.=$key.',';
					$datas.="'".Base::safeword($v)."',";
				}
				$backups.= 'REPLACE INTO '.TB.$bus.' ('.substr($colums,0,-1).') VALUES('.substr($datas,0,-1).');'."\n";
			}
		}
		echo substr($backups,0,-2);
	}
	function update(){
		$filedata=file_get_contents($_FILES['file']['tmp_name']);
		$queryarray = explode(";\n",$filedata);
		foreach ($queryarray as $k =>$v){
			$this->db->query($v) or Base::showmessage('恢复中出错','-1');
		}
		Base::execmsg("数据恢复成功",'?action=datastore&ctrl=display',TRUE);
	}
}
?>