<?php
class Config{
	public $table;
	public $db;
	public $tpl;
	function __construct($table){
		$this->tpl=new Template();
		$this->table=$table;
	}
	function display(){
		$themeArray=array();
		$fhandle = opendir('../template');
		$i = 0;

		while(false !== $file=(readdir($fhandle))){
			if($file!='.'&&$file!='..'){
				$themeArray[]=$file;
			}
		}
		$configArray=array(
		'webname'=>Base::magic2word(WEBNAME),
		'weburl'=>Base::magic2word(WEBURL),
		'webinfo'=>Base::magic2word(WEBINFO),
		'announce'=>Base::magic2word(ANNOUNCE),
		'sys_root'=>SYS_ROOT,
		'admindir'=>ADMINDIR,
		'timemod'=>TIMEMOD,
		'modtime'=>date("Y-m-d H:i:s",Base::getnowtime()),
		'nowtime'=>date("Y-m-d H:i:s"),
		'cache'=>CACHE,
		'cachelast'=>CACHELAST,
		'inc'=>INC,
		'db'=>DB,
		'db_name'=>DB_NAME,
		'memcache'=>MEMCACHE,
		'tb'=>TB,
		'eachpage'=>EACHPAGE,
		'taodebug'=>TAODEBUG,
		'taoditor'=>TAOEDITOR,
		'creathtml'=>CREATHTML,
		'viewscount'=>VIEWSCOUNT,
		'local'=>str_replace("\\", '/',dirname(realpath('../config.php')).'/'),
		'caturl'=>str_replace('\"','&quot;',CATURL),
		'atlurl'=>str_replace('\"','&quot;',ATLURL),
		'themelist'=>$themeArray,
		'theme'=>THEME,
		'webbaseurl'=> "http://".$_SERVER['HTTP_HOST'].str_replace(ADMINDIR.'admin.php','',$_SERVER['SCRIPT_NAME']),
		);
		extract($configArray);
		include($this->tpl->myTpl('form'.$this->table));
	}
	function update(){
		is_writable(SYS_ROOT.'config.php')||Base::showmessage('无权限修改配置文件');
		unset($_POST['Submit']);unset($_POST['ctrl']);unset($_POST['action']);
		$configData="<?php\r\n";
		foreach($_POST as $key=>$configs){
			$configData.="define('".$key."',	'".Base::safeword($configs)."');\r\n";
		}
		$configData.="?>";
		$status=file_put_contents(SYS_ROOT."config.php",$configData);
		Base::execmsg("保存设置","?action=".$this->table.'&ctrl=display',TRUE);
	}
	function memflush(){
		if(MEMCACHE){
			$mem=new Memcached(MEMCACHE);
			$mem->flush();
			Base::showmessage('清空Memcache缓存成功');
		}else{
			Base::showmessage('清空Memcache缓存失败，请检查Memcache配置是否正确');
		}
	}
}
	
?>