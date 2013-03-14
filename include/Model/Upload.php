<?php
class Upload{
	public $table;
	public $tpl;
	function __construct($table='',$id=0){
		$this->table=$table;
	}
	function display(){
		$this->tpl=new Template();
		include($this->tpl->myTpl('formupload'));
	}
	function execute(){
		$state=$this->upload('filedata','pictures/',WEBURL);
		if($state['err']){
			Base::execmsg('上传失败，错误为：'.$state['err'],-1);
		}
		if($_POST['inid']){
			echo '<script>window.opener.document.getElementById("'.$_POST['inid'].'").value= "'.$state['msg'].'";window.close();</script></body></html>';
		}else{
			echo '<hr /><center>文件地址：<input title="请全选后手动复制此处的文件地址" type="text" onclick="this.select();" value="'.$state['msg'].'" /><hr />点击复制→
			<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="100" height="40">
  <param name="movie" value="template/images/copy.swf?u='.$state['msg'].'" />
  <param name="quality" value="high" />
  <embed src="template/images/copy.swf?u='.$state['msg'].'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="100" height="40"></embed>
</object>←点击复制</object></center></body></html>';
		}
	}
	function editor(){
		echo json_encode($this->upload('filedata','pictures/',WEBURL));
	}
	function upload($inputname,$attachdir='pictures/',$urldir="",$dirtype=1,$maxattachsize=10097152)
	{
		$upext='txt,rar,zip,jpg,jpeg,gif,png,swf,wmv,avi,wma,mp3,mid,jar,jad,exe,html,htm,css,js,doc';//上传扩展名
		$err = "";
		$msg = "";
		$upfile=$_FILES[$inputname];
		$fileinfo=pathinfo($upfile['name']);
		switch($dirtype)
		{
			case 1:
				$attach_dir=$attachdir.'month_'.date('ym').'/';
				$filename=date("YmdHis").rand(1000,9999);
			break;
			case 2:
				$attach_dir=$attachdir;
				$filename=$fileinfo['filename'];
			break;
		}
		if(isset($_SERVER['HTTP_CONTENT_DISPOSITION'])&&preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)){
			//HTML5上传
			$fileinfo=pathinfo($info[2]);
			$extension=$fileinfo['extension'];
			if(!preg_match('/'.str_replace(',','|',$upext).'/i',$extension))
			{
				$err='上传文件扩展名必需为：'.$upext;
			}else{
				$status = $this->saveFile($info[2], $filename.'.'.$extension, $attach_dir, $urldir,file_get_contents("php://input"));
				$err=$status['err'];
				$msg=$status['msg'];
				
				if($status){
					$msg=array('url'=>$msg,'localname'=>$filename);
				}
				else{
					$err='文件为空文件或保存到服务器不成功';
				}
				
			}
		}else{
			if(!empty($upfile['error']))
			{
				switch($upfile['error'])
				{
					case '1':
						$err = '文件大小超过了php.ini定义的upload_max_filesize值';
						break;
					case '2':
						$err = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
						break;
					case '3':
						$err = '文件上传不完全';
						break;
					case '4':
						$err = '无文件上传';
						break;
					case '6':
						$err = '缺少临时文件夹';
						break;
					case '7':
						$err = '写文件失败';
						break;
					case '8':
						$err = '上传被其它扩展中断';
						break;
					case '999':
					default:
						$err = '无有效错误代码';
				}
			}
			elseif(empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none'){
				$err = '无文件上传';
			}
			else
			{
				$temppath=$upfile['name'];
				$extension=$fileinfo['extension'];
				if(preg_match('/'.str_replace(',','|',$upext).'/i',$extension))
				{
					$filesize=filesize($temppath);
					if($filesize > $maxattachsize){
						$err='文件大小超过'.$maxattachsize.'字节';
					}
					else
					{
						$status = $this->saveFile($upfile['tmp_name'], $filename.'.'.$extension, $attach_dir, $urldir);
						$err=$status['err'];
						$msg=$status['msg'];
					}
				}else{
					$err='上传文件扩展名必需为：'.$upext;
				}
			}
		}
		return array('err'=>$err,'msg'=>$msg);
	}
	
	function saveFile( $tempFilePath,$filename,$attach_dir,$urldir,$filebinary=null)
	{
		$fileinfo=pathinfo($tempFilePath);
		//$extension=$fileinfo['extension'];
		//$filename.='.'.$extension;
		$status=false;
		if(RUNONSAE){
			if( $filebinary==null){
				$tmpfile=TAO_TMP_PATH.md5(uniqid(rand(),true));
				move_uploaded_file($tempFilePath,$tmpfile);
				$storage = new SaeStorage();
				$storage->write('taocms',$attach_dir.$filename,file_get_contents($tmpfile));
			}
			else{
				$storage = new SaeStorage();
				$storage->write('taocms',$attach_dir.$filename,$filebinary);
			}
			
			$status=$msg=$storage->getUrl('taocms',$attach_dir.$filename);
			$err=$storage->errno()==-7?'请在后台申请一个名为taocms的Storage Domain':'';
		}elseif(RUNONBAE){
			$err='BAE不能上传';
		}else{
			File::taomkdir(SYS_ROOT.$attach_dir);
			if($filebinary==null){
				$status=move_uploaded_file($tempFilePath,SYS_ROOT.$attach_dir.$filename);
			}else{
				file_put_contents(SYS_ROOT.$attach_dir.$filename,$filebinary);
				$status = file_exists(SYS_ROOT.$attach_dir.$filename);
			}
			
			$msg = $urldir.$attach_dir.$filename;
		}
		
		return array('err'=>$err,'msg'=>$msg,'status'=>$status);
	}
}
?>