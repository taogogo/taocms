<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<title>安装taoCMS</title> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<link rel="icon" type="image/ico" href="/favicon.ico" /> 
<meta name="description" content="安装taoCMS" />
</head> 
<body style="background: #DDDDDD"> 
<div style=" width:600px; height:300px;overflow-x:hidden; overflow-y:scroll; border: #666666 solid 6px; margin:100px auto; padding:2px 4px; font-size:14px; background:#FFF;">
<form action="" method="post" id="forms">
<?php
error_reporting( E_ERROR );
if(!is_writable('.htaccess'))
{
	echo 'htaccess文件无写权限，这可能会影响自定义URL的效果<br />';
}

if(file_exists('data/install.lock')){
	die('已经安装过，如需重新安装，请删除data目录下的install.lock文件');
}
	include "config.php";
	$permits=array();
	$db_name=!empty($_POST['db_name'])?$_POST['db_name']:DB_NAME;
	$tb=!empty($_POST['tb'])?$_POST['tb']:TB;
	$db=!empty($_POST['db'])?ucfirst($_POST['db']):DB;
	include SYS_ROOT.INC.'Model/Base.php';
	define('RUNONSAE',defined( 'SAE_TMP_PATH' ));
	$baedbip=getenv('HTTP_BAE_ENV_ADDR_SQL_IP');
	define('RUNONBAE',!empty( $baedbip ) );
	if(RUNONSAE||RUNONBAE){
		//$db_name='';
		$tb=TB;
		$db='Mysql';
		include SYS_ROOT.INC.'Db/Mysql.php';
		$nowdb=new Dbclass(SYS_ROOT.$db_name);
		$tmp=$nowdb->query("SELECT count(*) TABLES, table_schema FROM information_schema.TABLES  where table_schema = '".SAE_MYSQL_DB."' GROUP BY table_schema");
		if($tmp['TABLES'])die('已经安装过，如需重新安装，请删除数据库中所有表');
	}else{
		include SYS_ROOT.INC.'Db/'.$db.".php";
		if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'){
			is_writable('config.php') or $permits[]='config.php文件';
			if($db=='Sqlite'&&$_POST){
				!file_exists($db_name)&&file_put_contents($db_name,'');
				is_writable($db_name) or $permits[]=$db_name.'文件';
				is_writable(dirname($db_name)) or $permits[]=dirname($db_name).'文件夹';
				}

			is_writable(CACHE) or $permits[]=CACHE.'文件夹为空（非SAE服务器请删除文件夹内全部文件）,且'.CACHE.'文件夹';
			is_writable('pictures') or $permits[]='pictures文件夹';
			is_writable(CACHE.'art_array.inc') or $permits[]=CACHE.'art_array.inc文件';
			is_writable(CACHE.'cat_array.inc') or $permits[]=CACHE.'cat_array.inc文件';
		}
		if(!empty($permits))
		{
    		foreach($permits as $pv){
    			echo '请确认程序目录下的'.$pv."可写<br />";
    		}
    		
    		//有无写权限的就停止
    		die();
		}
	}
	if($_POST){
	$nowdb=new Dbclass(SYS_ROOT.$db_name);
	//写入htaccess
	$htaccess='RewriteEngine On
RewriteBase '.(dirname($_SERVER['PHP_SELF'])?str_replace("\\", '/',dirname($_SERVER['PHP_SELF'])):'').'/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* index.php/$0 [PT,L]';
	file_put_contents('.htaccess',$htaccess);
	if($db=='Sqlite'){
	$creatTable='CREATE TABLE ['.$tb.'category] (
[id] INTEGER  PRIMARY KEY NOT NULL,
[name] VARCHAR(60)  NULL,
[nickname] VARCHAR(60)  NULL,
[staticurl] VARCHAR(60)  NULL,
[cattpl] VARCHAR(100)  NULL,
[listtpl] VARCHAR(100)  NULL,
[distpl] VARCHAR(100)  NULL,
[fid] INTEGER  NULL,
[intro] TEXT  NULL,
[orders] INTEGER  NULL,
[status] INTEGER  NULL
);
CREATE TABLE ['.$tb.'admin] (
[id] integer  PRIMARY KEY NULL,
[name] varchar(30)  NULL,
[emails] varchar(60)  NULL,
[passwd] varchar(60)  NULL,
[auth] varchar(30)  NULL,
[times] varchar(30)  NULL,
[ips] varchar(60)  NULL,
[status] INTEGER  NULL
);
CREATE TABLE ['.$tb.'comment] (
[id] INTEGER  PRIMARY KEY NOT NULL,
[article_id] INTEGER  NULL,
[name] VARCHAR(30)  NULL,
[emails] VARCHAR(60)  NULL,
[websites] VARCHAR(60)  NULL,
[content] TEXT  NULL,
[ips] VARCHAR(80)  NULL,
[times] TIMESTAMP  NULL,
[status] INTEGER  NULL
);
CREATE TABLE ['.$tb.'link] (
[id] INTEGER  PRIMARY KEY NOT NULL,
[name] VARCHAR(30)  NULL,
[urls] VARCHAR(60)  NULL,
[content] VARCHAR(120)  NULL,
[cat] INTEGER  NULL,
[orders] INTEGER  NULL,
[status] INTEGER  NULL
);
CREATE TABLE ['.$tb.'cms] (
[id] integer  PRIMARY KEY NULL,
[name] varchar(120)  NULL,
[link] varchar(30)  NULL,
[staticurl] VARCHAR(60)  NULL,
[content] text  NULL,
[cat] varchar(30)  NULL,
[times] varchar(30)  NULL,
[ips] varchar(60)  NULL,
[status] INTEGER  NULL,
[allowcmt] INTEGER  NULL,
[orders] INTEGER DEFAULT \'0\' NULL,
[thumbpic] varchar(120)  NULL,
[views] INTEGER  DEFAULT \'0\' NULL,
[cmtcount] INTEGER DEFAULT \'0\' NULL,
[user_id] INTEGER  NULL,
[tags] varchar(120)  NULL,
[slug] varchar(120)  NULL,
[orders2] INTEGER DEFAULT \'0\' NULL,
[orders3] INTEGER DEFAULT \'0\' NULL
);
CREATE TABLE ['.$tb.'relations] (
[id] INTEGER  PRIMARY KEY NULL,
[name] varchar(100)  NULL,
[counts] INTEGER  NULL
);
CREATE TABLE ['.$tb.'relatocms] (
[id] INTEGER  PRIMARY KEY NULL,
[relid] INTEGER  NULL,
[cmsid] INTEGER  NULL
)';
}
else{
$creatTable="CREATE TABLE `".$tb."admin` (
  `id` int(120) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL DEFAULT '',
  `emails` varchar(60) NOT NULL DEFAULT '',
  `passwd` varchar(60) NOT NULL DEFAULT '',
  `auth` varchar(40) NOT NULL DEFAULT '',
  `times` varchar(60) NOT NULL DEFAULT '',
  `ips` varchar(60) NOT NULL DEFAULT '',
  `status` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;CREATE TABLE `".$tb."category` (
  `id` int(120) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL DEFAULT '',
  `nickname` varchar(60) NOT NULL DEFAULT '',
  `fid` int(120) NOT NULL DEFAULT '0',
  `intro` varchar(120) NOT NULL DEFAULT '',
  `orders` int(40) NOT NULL DEFAULT '0',
  `status` tinyint(2) NOT NULL DEFAULT '1',
	`staticurl` varchar(60) NOT NULL DEFAULT '',
	`cattpl` varchar(100) NOT NULL DEFAULT '',
	`listtpl` varchar(100) NOT NULL DEFAULT '',
	`distpl` varchar(100) NOT NULL DEFAULT '',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;CREATE TABLE `".$tb."cms` (
  `id` int(120) NOT NULL auto_increment,
  `name` varchar(120) NOT NULL DEFAULT '',
  `link` varchar(100) NOT NULL DEFAULT '',
  `content` text NOT NULL DEFAULT '',
  `cat` int(120) NOT NULL DEFAULT '0',
  `times` varchar(60) NOT NULL DEFAULT '',
  `ips` varchar(40) NOT NULL DEFAULT '',
  `allowcmt` tinyint(2) NOT NULL DEFAULT '1',
  `orders` int(40) NOT NULL default '0',
  `status` tinyint(2) NOT NULL DEFAULT '1',
  `thumbpic` varchar(140) NOT NULL DEFAULT '',
  `views` int(100) NOT NULL default '0',
  `user_id` int(120) NOT NULL DEFAULT '0',
  `slug` varchar(80) NOT NULL DEFAULT '',
  `tags` varchar(60) NOT NULL DEFAULT '',
  `cmtcount` int(100) NOT NULL default '0',
  `orders2` int(40) NOT NULL default '0',
  `orders3` int(40) NOT NULL default '0',
  `staticurl` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26;CREATE TABLE `".$tb."comment` (
  `id` int(120) NOT NULL auto_increment,
  `article_id` int(120) NOT NULL DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `emails` varchar(100) NOT NULL DEFAULT '',
  `websites` varchar(100) NOT NULL DEFAULT '',
  `content` text NOT NULL DEFAULT '',
  `ips` varchar(40) NOT NULL DEFAULT '',
  `times` varchar(40) NOT NULL DEFAULT '',
  `status` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;CREATE TABLE `".$tb."link` (
  `id` int(120) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL DEFAULT '',
  `urls` varchar(100) NOT NULL DEFAULT '',
  `content` varchar(200) NOT NULL DEFAULT '',
  `cat` int(120) NOT NULL DEFAULT '0',
  `orders` int(40) NOT NULL DEFAULT '0',
  `status` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;CREATE TABLE `".$tb."relations` (
  `id` int(120) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL DEFAULT '',
  `counts` int(120) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;CREATE TABLE `".$tb."relatocms` (
  `id` int(120) NOT NULL auto_increment,
  `relid` int(120) NOT NULL DEFAULT '0',
  `cmsid` int(120) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4";
}
$queryarray = explode(';',$creatTable);
foreach ($queryarray as $k =>$v){
	$nowdb->query($v) or die('creat table error'.$v);
}
$nowdb->query('INSERT INTO '.$tb.'admin(name,passwd,auth,status)VALUES("admin","tao","admin",1)');
$nowdb->query('INSERT INTO '.$tb.'link (name,urls,content,status,orders) VALUES("taoCMS","http://www.taocms.org","taoCMS官方网站","1","0")');
$nowdb->query('INSERT INTO '.$tb.'category (name,fid,intro,orders,status,staticurl) VALUES("日记","0","日记本","0","1","?cat=1")');
$nowdb->query('INSERT INTO '.$tb.'cms (name,content,cat,times,orders,status,allowcmt,staticurl) VALUES("你的第一个小脚印出现在这里哦","taoCMS已经正常运行了，记录你的梦想吧！觉得像博客？taoCMS官方网站的CMSer模板给你打造一个门户网站！快去看看吧！http://www.taocms.org/。【请您先到后台设置文章URL，然后生成URL】","0","1284920417","0","1","1","?id=1")');
	$configs=file_get_contents('config.php');
	$_POST['tb']&&$configs=str_replace('define(\'TB\',	\''.TB.'\');','define(\'TB\',	\''.$_POST['tb'].'\');',$configs);
	$_POST['db']&&$configs=str_replace('define(\'DB\',	\''.DB.'\');','define(\'DB\',	\''.$_POST['db'].'\');',$configs);
	$_POST['db_name']&&$configs=str_replace('define(\'DB_NAME\',	\''.DB_NAME.'\');','define(\'DB_NAME\',	\''.$_POST['db_name'].'\');',$configs);
	file_put_contents('config.php',$configs);
	file_put_contents('data/install.lock','');
	?><center style="font-size:25px;">☺系统安装完毕☺</center><br />默认<font color="red">用户名admin 默认密码tao</font>，请登陆后台后设置网站地址和生成栏目缓存，谢谢(建议安装成功后删除本文件)
<hr />
	你可能想去:<a href='./admin' target='_blank'>本站管理后台</a>·<a href='./' target='_blank'>本站网站首页</a> | <a href='http://www.taocms.org' target='_blank'>taoCMS官网</a> <a href='http://taobbs.sinaapp.com/' target='_blank'>taoCMS技术论坛</a>
    <?php 
	}else{?>
<center style="font-size:25px;">☺开始安装taoCMS☺</center><br />请根据需要选择Sqlite/Mysql数据库，并按照提示进行配置
<hr />
<?php if(!RUNONSAE&&!RUNONBAE){?>
系统的配置：<?php echo PHP_OS.'['.$_SERVER["SERVER_SOFTWARE"].']'?><hr />
数据库类型：<select name="db" id="db" onchange="if(this.value=='Mysql'){$('db_name').value='|数据库地址:端口号|用户名|用户密码|数据库名'}else{$('db_name').value='data/blog.db'}">
  <option value="Sqlite">sqlite</option>
  <option value="Mysql">mysql</option>
</select>
(Sqlite<?php if(!function_exists('sqlite_open')){?><font color="red">不支持</font><?php }else{?><font color="green">支持</font><?php }?>，Mysql<?php if(!function_exists('mysql_connect')){?><font color="red">不支持</font><?php }else{?><font color="green">支持</font><?php }?>)
<hr />
数据库配置：<input name="db_name" type="text" size="30" id="db_name" value="data/blog.db" />
<hr />
数据表前缀：<input name="tb" type="text" id="tb" value="cms_" />
<?php }elseif(RUNONSAE){?>
将要在SAE平台安装，请确保您已经启用了SAE的Mysql服务
<?php }elseif(RUNONBAE){?>

将要在BAE平台安装，请确保您已经启用了BAE的Mysql服务，且config.php内的DB_NAME配置为：define('DB_NAME',	'|test|test|test|xdoEHlrsefxpPBJtdcSM（您的mysql数据库名需要手动修改，前三项使用test即可）');
<?php }?>
<hr />
<center><input type="submit" name="Submit" value="点击此处开始安装免费开源的taoCMS系统" /></center>
<?php }?>
<hr />
<center style="font-size:13px;color:gray;">Powered By <a href="http://www.taocms.org" target="_taogogo">taoCMS</a>，taoCMS是一款小巧免费开源的CMS系统</center>
</div>
</body> 
</html>
<script>
function $(obj){
   return (typeof obj == "object")?obj:document.getElementById(obj);;
}
</script>