<?php
class Dbclass {
	var $conn=NULL;
	var $querynum = 0;
	function __construct($dbhost,$pconnect = 0){
  		$this->connect($dbhost,$pconnect);
  	}
	function connect($dbhost,$pconnect = 0){
		$error = '';
		$func = $pconnect == 1 ? 'sqlite_popen' : 'sqlite_open';
		if (!$this->conn = $func($dbhost,0666,$error)){
			$this->halt($error);
		} 

		return $this->conn;
	} 
	function select_db($dbname){
		return ;
	}
	function query($sql,$type = ''){
		//echo $sql;
		$error = '';
		$func = $type == 'UNBUFFERED' ? 'sqlite_unbuffered_query' : 'sqlite_query';
		if (preg_match("/^\s*SELECT/i",$sql)){
			$query = $func($this->conn,$sql,SQLITE_ASSOC,$error);
		} else {
			$query = sqlite_exec($this->conn,$sql,$error);
		} 
		if ($error){
			$this->halt($error,$sql);
		} 

		$this->querynum++;
		return $query;
	}
	function fetch_array($query,$result_type = SQLITE_ASSOC){
		return sqlite_fetch_array($query,$result_type);
	} 
	function getlist($table,$wheres = "1=1", $colums = '*',$limits = '20',$orderbys="id DESC"){
		$sql="select ".$colums." from ".$table." where ".$wheres." ORDER BY  ".$orderbys."  limit ".$limits;
		$query = $this->query($sql);
		while($rs=$this->fetch_array($query)){
			$datas[]=Base::magic2word($rs);
			}
		$this->free_result($query);
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
	function add_one($table,$data){
		if (is_array($data)){
			foreach ($data as $k=>$v){
				$colums.=Base::safeword($k).',';
				$columsData.="'".Base::safeword($v)."',";
			}
		$sql="INSERT INTO ".$table." (".substr($colums,0,-1).") VALUES(".substr($columsData,0,-1).")";
		$query = $this->query($sql,$type,$expires,$dbname);
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
		$query = $this->query($sql,$type,$expires,$dbname);
		$rs = Base::magic2word($this->fetch_array($query));
		$this->free_result($query);
		return $rs ;
	} 
	function affected_rows(){
		return sqlite_changes($this->conn);
	} 
	function num_rows($query){
		return sqlite_num_rows($query);
	} 
	function num_fields($query){
		return sqlite_num_fields($query);
	} 
	function result($query,$row){
		return @sqlite_fetch_all($query,SQLITE_ASSOC);
	}
	function free_result($query){
		return ;
	}
	function insert_id(){
		return sqlite_last_insert_rowid($this->conn);
	}
	function fetch_row($query){
		return sqlite_fetch_array($query,SQLITE_NUM);
	}
	function fetch_assoc($query){
		return $this->fetch_array($query,SQLITE_ASSOC);
	}
	function version(){
		return sqlite_libversion();
	} 

	function close(){
		return sqlite_close($this->conn);
	}
	function error(){
		return sqlite_error_string($this->errno);
	}
	function errno(){
		return sqlite_last_error($this->conn);
	}
	function halt($message = '',$sql = ''){
		if( $this->errno() == 1 ){
			Base::showmessage( '未找到数据，可能是taoCMS未被正确安装，正在跳转到安装页面' , WEBURL . 'install.php' );
		}
		exit("SqliteQuery:$sql <br> SqliteError:" . $this->error() . " <br> SqliteErrno:" . $this->errno() . " <br> Message:$message");
	} 
} 

?>