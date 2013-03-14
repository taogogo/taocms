<?php
class Template{
	function Myurl($atlarrayname=' '){
		$atlsarray=explode(':',$atlarrayname);
		$atlflag=explode('|',$atlsarray[1]);
		if($atlarrayname==' ' || $atlflag[1]==1){
			$atlarrayname=$atlflag[1]?$atlflag[0]:'atl';
			$configurl=str_replace('{slug}',"urlencode(\$".$atlarrayname."['slug'])",Base::magic2word(ATLURL));
			$configurl=str_replace('{id}',"\$".$atlarrayname."['id']",$configurl);
			$configurl=str_replace('{Y}',"date('Y',\$".$atlarrayname."['times'])",$configurl);
			$configurl=str_replace('{m}',"date('m',\$".$atlarrayname."['times'])",$configurl);
			$configurl=str_replace('{d}',"date('d',\$".$atlarrayname."['times'])",$configurl);
		}else{
			$atlarrayname=$atlsarray[1];
			$configurl=str_replace('{nickname}',"\$".$atlarrayname."['nickname']",Base::magic2word(CATURL));
			if($atlflag[1]==2){
				$configurl=str_replace('{id}',"\$".$atlarrayname."['cat']",$configurl);
			}
			$configurl=str_replace('{id}',"\$".$atlarrayname."['id']",$configurl);

		}

		if(substr($configurl, -2)!='/"'){
			$configurl.='.htm';
		}
		$OutStr = "echo $configurl;";
		return '";'.$OutStr.'echo "';

	}
	function myTpl($tplname,$tpldir='',$vars='$this->tpl'){
		$cache=TAO_TMP_PATH .'tplcache/'.$tpldir.$tplname.'.php';
		if (time()-$this->cacheTime($cache)<CACHELAST) {
			return $cache;
		}
		$template=file_get_contents('template/'.$tpldir.$tplname.'.htm');
		if($template=='')Base::showmessage("请确保模板文件存在且有内容(".'template/'.$tpldir.$tplname.'.htm'.")可写！",'');
		$CompileBasic = array(
		'/(\{\s*|<!--\s*)inc_php:([a-zA-Z0-9_\[\]\.\,\/\?\=\#\:\;\-\|\^]{5,200})(\s*\}|\s*-->)/eis',
		'/<!--\s*DEL\s*-->/is',
		'/<!--\s*IF\s*(\[|\()(.+?)(\]|\))\s*-->/is',
		'/<!--\s*ELSEIF\s*(\[|\()(.+?)(\]|\))\s*-->/is',
		'/<!--\s*ELSE\s*-->/is',
		'/<!--\s*END\s*-->/is',
		'/<!--\s*([a-zA-Z0-9_\$\[\]\'\"\(\)]{2,60})\s*(AS|as)\s*(.+?)\s*-->/',
		'/<!--\s*while\:\s*(.+?)\s*-->/is',
		//article list begin
		'/<!--\s*taolist([a-z0-9]{0,30})\:\s*(.+?)\s*-->/is',
		//article list end
		'/\{ET_Inc\:(.+?),(.+?)\}/eis',
		'/(\{\s*|<!--\s*)lang\:(.+?)(\s*\}|\s*-->)/eis',
		'/(\{\s*|<!--\s*)row\:(.+?)(\s*\}|\s*-->)/eis',
		'/(\{\s*|<!--\s*)url(.+?)(\s*\}|\s*-->)/eis',
		'/(\{\s*|<!--\s*)color\:\s*([\#0-9A-Za-z]+\,[\#0-9A-Za-z]+)(\s*\}|\s*-->)/eis',
		'/(\{\s*|<!--\s*)run\:(\}|\s*-->)\s*(.+?)\s*(\{|<!--\s*)\/run(\s*\}|\s*-->)/is',
		'/(\{\s*|<!--\s*)run\:(.+?)(\s*\}|\s*-->)/is',
		'/(\{\s*|<!--\s*)inc\:([^\{\} ]{1,100})(\s*\}|\s*-->)/i',
		'/\{([a-zA-Z0-9_\'\"\[\]\$\->]{1,100})\}/',
		);
		$AnalysisBasic = array(
		'$this->inc_php("\\2")',
		'<?php if($ET_Del==true){ ?>',
		'<?php if(\\2){ ?>',
		'<?php ;}elseif(\\2){ ?>',
		'<?php ;}else{ ?>',
		'<?php ;} ?>',
		'<?php \$_i=0;if(is_array(\\1))foreach(\\1 AS \\3){\$_i++; ?>',
		'<?php \$_i=0;while(\\1){\$_i++; ?>',
		'<?php \$_i\\1=0;\$QR\\1 = \$dbit->getquery(TB."\\2"); while(\$list\\1 = $dbit->fetch_array($QR\\1)){\$_i\\1++; ?>',
		'<?php $this->ET_Inc("\\1","\\2"); ?>',
		'<?php $this->lang("\\2"); ?>',
		'<?php $this->Row("\\2"); ?>',
		'<?php $this->Myurl("\\2"); ?>',
		'<?php $this->Color("\\2"); ?>',
		'<?php ;\\3; ?>',
		'<?php ;\\2; ?>',
		'<?php include('.$vars.'->myTpl("\\2","'.$tpldir.'",\''.$vars.'\')); ?>',
		'<?php ;echo \$\\1; ?>',
		);
		$template = preg_replace($CompileBasic, $AnalysisBasic, $template);
		$template=str_replace('images/','template/'.$tpldir.'images/',$template);
		$cachedir=dirname($cache);
		if(!is_dir($cachedir)){
			mkdir($cachedir,0755,true);
		}
		$template = '<?php if(constant("taoCMS!") !== true)die;?>'.$template;
		if(!file_put_contents($cache,$template)){
			Base::showmessage("写入文件失败，请确认模板目录(".$cachedir.")可写！",'');
		}
		return $cache;
	}
	function cacheTime($file){
		if (file_exists($file)) {
			return filemtime($file);
		}
		return 1;
		
	}

}

?>