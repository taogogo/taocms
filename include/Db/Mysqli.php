<?php

class Dbclass {
	public $conn=NULL;
	public $querynum = 0;
	static private $_single  =null;
	function __construct($dbhost,$pconnect = 0){
  		$this->connect($dbhost,$pconnect = 0);
  	}
	function connect($dbhost,$pconnect = 0){
		if(isset(self::$_single[$dbhost])&&$mysqli->ping){
			return true;
		}

		if(RUNONSAE){
			$dbhost=SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
			$dbuser=SAE_MYSQL_USER;
			$dbpw=SAE_MYSQL_PASS;
			$dbname=SAE_MYSQL_DB;
		}elseif(RUNONBAE){
			$dbconfig=explode('|',$dbhost);
			$dbhost = getenv('HTTP_BAE_ENV_ADDR_SQL_IP').':'.getenv('HTTP_BAE_ENV_ADDR_SQL_PORT');
			$dbuser = getenv('HTTP_BAE_ENV_AK');
			$dbpw = getenv('HTTP_BAE_ENV_SK');
			$dbname=$dbconfig[4];
		}
		else{
			$dbconfig=explode('|',$dbhost);
			if(count($dbconfig)<4)Base::showmessage( 'taoCMS未被正确安装或配置导致无法读取数据'.$this->error().$this->errno() , WEBURL . 'install.php' );
			$dbhost=$dbconfig[1];
			$dbuser=$dbconfig[2];
			$dbpw=$dbconfig[3];
			$dbname=$dbconfig[4];
		}
		if($pconnect){
			if(!$this->conn = new mysqli($dbhost,$dbuser,$dbpw)){
				$this->halt();
			}
		} else {
			if(!$this->conn = new mysqli($dbhost,$dbuser,$dbpw)){
				$this->halt();
			}
		}

		$this->select_db($dbname);
		$this->query('set names utf8');
		self::$_single[$dbhost]=true;
	}
	function select_db($dbname){
		return $this->conn->select_db($dbname);
	}
	function query($sql){
		//echo $sql;
		$query = $this->conn->query($sql);
		return $query;
	}
	function fetch_array($query,$result_type = MYSQLI_ASSOC){
		return $query->fetch_array($result_type);
	}
	function getlist($table,$wheres = "1=1", $colums = '*',$limits = '20',$orderbys="id DESC"){
		$query = $this->query("select ".$colums." from ".$table." where ".$wheres." ORDER BY  ".$orderbys."  limit ".$limits);
		while($rs = $this->fetch_array($query)){
			$datas[]=Base::magic2word($rs);
		}
		return $datas ;
	}
	function getquery($sqltext){
		$sqlArray=array();
		$sqlArray=explode('|',$sqltext);
		$table=$sqlArray[0];
		if(!$sqlArray[0]){
			return NULL;
		}
		$wheres=$sqlArray[1]?$sqlArray[1]:'1=1';
		$limits=$sqlArray[2]?$sqlArray[2]:'10';
		$orderbys=$sqlArray[3]?$sqlArray[3]:"id DESC";
		$colums=$sqlArray[4]?$sqlArray[4]:"*";
		$query = $this->query("select ".$colums." from ".$table." where ".$wheres." ORDER BY  ".$orderbys."  limit ".$limits);
		return $query;
		}
	function add_one($table,$data ){
		if (is_array($data)){
			foreach ($data as $k=>$v){
				$colums.=Base::safeword($k).',';
				$columsData.="'".Base::safeword($v)."',";
			}
		$sql="INSERT INTO ".$table." (".substr($colums,0,-1).") VALUES(".substr($columsData,0,-1).")";
		$query = $this->query($sql);
		return $this->insert_id();
		}
		return FALSE;
	}
	function delist($table,$idArray,$wheres=""){
		if($wheres==''){
			$ids=implode(',',$idArray);
			$query = $this->query("DELETE FROM ".$table." WHERE id in(".$ids.")");
		}else{
			$query = $this->query("DELETE FROM ".$table." WHERE ".$wheres);
		}
		return $query;
	}
	function updatelist($table,$data,$idArray){
		if (is_array($data)){
			foreach ($data as $k=>$v){
				$updateData.=Base::safeword($k)."='".Base::safeword($v)."',";
			}
			$data=substr($updateData,0,-1);
		}
		$idArray=(array)$idArray;
		$ids=implode(',',$idArray);
		$query = $this->query("UPDATE ".$table." set ".$data."  WHERE id in(".$ids.")");
		return $query;
	}
	function get_one($table,$wheres = "1=1", $colums = '*',$limits = '1',$orderbys="id DESC"){
		$sql="select ".$colums." from ".$table." where ".$wheres." ORDER BY  ".$orderbys."  limit ".$limits;
		$query = $this->query($sql);
		if(empty($query)){
			return false;
		}
		$rs = Base::magic2word($this->fetch_array($query));
		$this->free_result($query);
		return $rs ;
	}
	function affected_rows(){
		return $this->conn->affected_rows;
	}

	function error(){
		return $this->conn->error;
	}

	function errno(){
		return $this->conn->errno;
	}

	function result($query,$row){
		$query->data_seek($row); $query = $query->fetch_array()[0];
		return $query;
	}

	function num_rows($query){
		$query = $query->num_rows;
		return $query;
	}

	function num_fields($query){
		return $query->field_count;
	}

	function free_result($query){
		return $query->free_result();
	}

	function insert_id(){
		$id = $this->conn->insert_id;
		return $id;
	}

	function fetch_row($query){
		$query = $query->fetch_row();
		return $query;
	}

	function halt(){
		if(in_array($this->errno(),array(1049,1146,2002,1046))){
			Base::showmessage( 'taoCMS未被正确安装或配置导致无法读取数据'.$this->error().$this->errno() , WEBURL . 'install.php' );
		}

		echo $this->error() . ':' . $this->errno();
	}
	function close(){
		mysqli_close($this->conn);
	}

	function __destruct(){
		$this->close();
	}
}
?>
