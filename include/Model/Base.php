<?php
class Base{
	static function cutStr($string, $sublen=10, $start = 0, $code = 'UTF-8')
	{
		$string=strip_tags($string);
		if($code == 'UTF-8')
		{
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $string, $t_string);

			//if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."...";
			return join('', array_slice($t_string[0], $start, $sublen));
		}
		else
		{
			$start = $start*2;
			$sublen = $sublen*2;
			$strlen = strlen($string);
			$tmpstr = '';
			for($i=0; $i<$strlen; $i++)
			{
				if($i>=$start && $i<($start+$sublen))
				{
					if(ord(substr($string, $i, 1))>129) $tmpstr.= substr($string, $i, 2);
					else $tmpstr.= substr($string, $i, 1);
				}
				if(ord(substr($string, $i, 1))>129) $i++;
			}
			//if(strlen($tmpstr)<$strlen ) $tmpstr.= "...";
			return $tmpstr;
		}
	}
	//取得真实ip
	static function realip(){
		if(getenv('HTTP_CLIENT_IP')){
			$ip=getenv('HTTP_CLIENT_IP');
		}elseif(getenv('HTTP_X_FORWARDED_FOR')){
			$ip=getenv('HTTP_X_FORWARDED_FOR');
		}elseif(getenv('REMOTE_ADDR')){
			$ip=getenv('REMOTE_ADDR');
		}else{
			$ip=$HTTP_SERVER_VARS['REMOTE_ADDR'];
		}
		
		$ip = long2ip( ip2long( $ip ) );
		return $ip;
	}

	//页面执行时间计时
	static function getmicrotime(){
		return microtime(true);
	}
	//当前时间函数
	static function getnowtime($format=''){
		$times=time()+TIMEMOD*3600;
		return $format?date($format,$times):$times;
	}
	//过滤参数的函数，待编写
	static function tosafeword($str,$k=1){
		return $str;
	}
	//清空session
	static function clearseesion(){
		session_unset();
		session_destroy();
	}
	//提示信息
	static function showmessage($msg, $url = '-1',$auto='',$ajax=false) {
		header("Content-type:text/html;charset=utf-8");
		$time=3;
		if ($auto){
			echo '<meta http-equiv="refresh" content="'.$auto.';url='.$url.'">';
			die($msg);
		}
		else
		{
			if ($url){
				if($url=="-1"){
					$omsg=$ajax?json_encode(array('msg'=>$msg,'no'=>-1)):"javascript:history.go(-1);";
				}elseif($url=="0"){
					$omsg=$ajax?json_encode(array('msg'=>$msg,'no'=>0)):"javascript:window.close();";
				}else{
					$omsg=$ajax?json_encode(array('msg'=>$msg,'no'=>1)):"$url";
				}
			}
			die($ajax?$omsg:'
    <div style="width: 600px; word-wrap: break-word; margin: 20px auto; border: black 4px solid; text-align: center; padding: 20px 4px;background: #EFEFF1;">
<a id="message_link_id" href="'.$omsg.'">'.$msg.'(<font id="percent">'.$time.'</font>秒后跳转，点击马上跳转）</a></div>
                        
<script language="javascript"> 
var bar='.$time.' ;
function count(){ 
    bar=bar-1 ;
    document.getElementById("percent").innerHTML=bar;
    if (bar>0){
        setTimeout("count()",1000);
    }else{
         document.getElementById("message_link_id").click();
    }  
}
count() ;
</script>');
	}
	}
	//操作状态提示
	static function execmsg($ctrl,$url,$status=TRUE){
		$msg=$ctrl."操作执行".($status?"成功":"失败");
		self::showmessage($msg, $url);
	}
	static function checkadmin(){
		if($_SESSION[TB.'is_admin']){
			return true;
		}else{
			return false;
		}

	}
	static function catauth($action){
		if($_SESSION[TB.'admin_level']=='admincat'){
			return in_array($action, array('cms','frame','user','admin'))?true:false;
		}
		return true;
	}
	
	static function safeword($text,$level=8){
		if(is_array($text))
		{
			foreach( $text as $key=>$value){
				$safeword[$key]=self::safeword($value);
			}
		}
		else
		{
			switch ($level)
			{
				case 0:
					$safeword=$text;
					break;
				case 1:
					$safeword=intval($text);
					break;
				case 3:
					$safeword=strip_tags($text);
					break;
				case 5:
					$safeword=nl2br(htmlspecialchars($text));
					break;
				case 6:
					$safeword=str_replace("'","",addslashes($text));
					$safeword=str_replace("select","",$safeword);
					$safeword=str_replace("union","",$safeword);
					$safeword=str_replace("=","",$safeword);
					break;
				default:
					if(ucfirst(DB)=='Sqlite'){
						$safeword=str_replace("'","''",$text);
					}
					else{
						$safeword=Base::_addslashs($text);
					}
					break;
			
			}
		}
		return $safeword;
	}
	static function _addslashs($text){
		$text = addslashes($text);
		return $text;
		
	}
	static function mystatus($level=1){
		switch ($level)
		{
			case 0:
				$s='草稿';
				break;
			case 1:
				$s='发表';
				break;
			case 2:
				$s='隐藏';
				break;
			case 3:
				$s='其他';
				break;
			default:
				break;

		}
		return $s;
	}
	static function catstatus($level=1){
		switch ($level)
		{
			case 0:
				$s='隐藏';
				break;
			case 1:
				$s='显示';
				break;
			case 2:
				$s='链接';
				break;
			case 3:
				$s='其他';
				break;
			default:
				break;

		}
		return $s;
	}
	static function cmstatus($level=1){
		switch ($level)
		{
			case 0:
				$s='否';
				break;
			case 1:
				$s='是';
				break;
		}
		return $s;
	}
	static function magic2word($text){
		if (is_array($text)) {
			foreach($text as $k=>$v){
			$text[$k]=self::magic2word($v);
			}
		}else{
			$text=stripslashes($text);
		}
		return $text;
	}
	//生成缓存php文件内容
	static function phpcache($name,$arrays){
		$data="<?php\n\$".$name."=";
		$data.=var_export($arrays,TRUE);
		$data.=";\n?>";
		return $data;
	}
	//生成自定义URL
	static function creaturl($data='',$flag=1){
		if($flag==1){
			$configurl=str_replace('{slug}',$data['slug'],Base::magic2word(ATLURL));
			$configurl=str_replace('{id}',$data['id'],$configurl);
			$configurl=str_replace('{Y}',date('Y',$data['times']),$configurl);
			$configurl=str_replace('{m}',date('m',$data['times']),$configurl);
			$configurl=str_replace('{d}',date('d',$data['times']),$configurl);
		}else{
			$configurl=str_replace('{nickname}',urlencode($data['nickname']),Base::magic2word(CATURL));
			$configurl=str_replace('{id}',$data['id'],$configurl);
		}
		return $configurl;
	}
	static function sendheader($status){
		switch ( $status){
			case 404:
				header("HTTP/1.1 404 Not Found");
				header("Status: 404 Not Found");
				exit;
				break;
			default:
				break;
		}
		
	}
}
?>
