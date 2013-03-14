<?php
class Spider{
	public $table;
	public $db;
	public $tpl;
	function __construct($db,$table){
		$this->table=$table;
		$this->db=new Dbclass(SYS_ROOT.DB_NAME);;
		$this->tpl=new Template();
	}
	function display(){
	$category=$this->db->getlist(TB."category");
	include($this->tpl->myTpl('spider'));
	}
	function createpreg($preg,$name){
		$str=urldecode($preg);
		$str=str_replace('"','\"',$str);
		$str=str_replace('/','\/',$str);
		return '/'.str_replace('['.$name.']','([^||-|||]*?)',$str).'/i';
	}
	function execute(){
		//直接用数组缓存
		include(SYS_ROOT.CACHE."cat_array.inc");
		//文章页参数缓存
		include(SYS_ROOT.CACHE."art_array.inc");
		$action=$_GET['action'];
		$subdata=$_GET['sub'];
		$ctrl=$_GET['ctrl'];
		$repword=$_GET['repword'];
		$id=Base::safeword($_GET['id'],1);
		$cat=Base::safeword($_GET['cat'],1);
		//采集开始
		$urlfront=$_GET['front'];
		$urlback=$_GET['back'];
		$rstart=intval($_GET['start']);
		$reach=$_GET['each'];
		$rcount=intval($_GET['count']);
		$rend=$_GET['rend'];
		//标题正则
		$titlepreg=$this->createpreg($_GET['titlepreg'],'title');
		//内容正则
		$contentpreg=$this->createpreg($_GET['contentpreg'],'content');
		$basecode=strtoupper($_GET['basecode']);
		for($i=$rstart;$i<$rstart+$reach;$i++){
			$getcontent=$this->fetchurl(urldecode($urlfront.$i.$urlback));
			if($basecode!="UTF-8"){
				$article=iconv($basecode,"UTF-8",$getcontent);
			}else{
				$article= $getcontent;
			}
			//文章标题
			preg_match($titlepreg,$article,$titlearray);
			$data['name']=$titlearray[1];
			//正则匹配文章内容
			preg_match($contentpreg,$article,$code);
			if($data['name']==''||!$code){
				if($_GET['test']=='1')die('无法匹配到标题或内容，请确定网址存在，且规则正确');
				continue;
			}
			echo '采集到《'.$data['name'].'》 ';
			$leavetags="<p><br><img><embed>";
			$leavetags.=$_GET['llink']==1?'<a>':'';
			$clearCode=strip_tags($code[1],$leavetags);
			$clearCode=trim($clearCode);
			//去除&nbsp;空格
			$clearCode=str_replace("&nbsp;"," ",$clearCode);
			//$clearCode=str_replace("'","‘",$clearCode);
			$reparray=explode("\n",urldecode($repword));
			foreach($reparray as $reps){
				$repwords=explode("|",$reps);
				$data['name']=str_replace($repwords[0],$repwords[1],$data['name']);
				$clearCode=str_replace($repwords[0],$repwords[1],$clearCode);
			}
			$data['content']=Base::magic2word($clearCode);
			$data['times']=Base::getnowtime();
			if($_GET['test']=='1'){
				echo '<br /><b>测试采集数据如下:</b><br /><b>标题为:</b>'.$data['name'].'<br /><b>内容为</b>'.$data['content'];
				die();
			}
			$data['cat']=$cat;
			$data['allowcmt']=1;
			$data['status']=1;
			$data['user_id']=Base::safeword($_SESSION[TB.'admin_id'],1);
			$data['id']=$this->db->add_one(TB."cms",$data);
			Cms::createurl($data);
			Cms::countcache();
			if($data['id'])$rcount++;
		}
		if($rstart<$rend){
			echo'<br />正在采集第'.$rstart.'篇，每页'.$reach.'篇，采集到'.$rend.',已采集'.$rcount;
			echo '<script>window.location.href="admin.php?action=spider&basecode='.$_GET['basecode'].'&ctrl=execute&start='.($rstart+$reach).'&front='.($urlfront).'&back='.($urlback).'&each='.$reach.'&rend='.$rend.'&count='.$rcount.'&titlepreg='.urlencode($_GET['titlepreg']).'&contentpreg='.urlencode($_GET['contentpreg']).'&cat='.$cat.'&repword='.urlencode($repword).'&llink='.intval($_GET['llink']).'"</script>';

		}
		else{
			echo 'end';
		}
	}
	function counts($wheres='1=1',$add='status=1'){
		$arttotal=$this->db->getlist(TB.'cms',$add." and ".$wheres,"count(*)");
		return $arttotal[0]['count(*)'];
	}
	function fetchurl($src){
		$content='';
		if(RUNONSAE){
			$scurl = new SaeFetchurl();
			$content = $scurl->fetch($src);
		}elseif (function_exists('curl_init')){
			$ch = curl_init(); 
			curl_setopt($ch,CURLOPT_URL,$src); 
			curl_setopt($ch,CURLOPT_HEADER,0); 
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
			$content = curl_exec($ch);
			curl_close($ch);
		}elseif((boolean)ini_get('allow_url_fopen')){
			$content = file_get_contents($src);
		}else{
			$src=parse_url($src);
			$host=$src['host'];
			$path=$src['path'];
			$line='';
			if (($s = @fsockopen($host,80,$errno,$errstr,5))===false)
			{
				return false;
			}
			fwrite($s,
				'GET '.$path." HTTP/1.0\r\n"
				.'Host: '.$host."\r\n"
				."User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b13pre) Gecko/20110307 Firefox/4.0b13pre\r\n"
				."Accept: text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*\r\n"
				."\r\n"
			);
			while (!feof($s))
			{
				$content.=fgets($s,4096);
			}
			fclose($s);
		}
		if ($content)
		{
			return $content;
		}
	}
}