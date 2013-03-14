<?php
class File{
	public $table;
	public $tpl;
	public $path;
	public $realpath;
	function __construct($table,$id=0){
		$this->table=$table;
		$this->path=$_REQUEST['path'];
		$this->realpath=SYS_ROOT.$this->path;
		$this->tpl=new Template();
	}
	function sizecount($size){
		if($size > 1073741824) {
			$size = round($size / 1073741824 * 100) / 100 . ' G';
		} elseif($size > 1048576) {
			$size = round($size / 1048576 * 100) / 100 . ' M';
		} elseif($size > 1024) {
			$size = round($size / 1024 * 100) / 100 . ' K';
		} else {
			$size = $size . ' B';
		}
		return $size;
	}

	function lists(){
		$path=$this->path?str_replace('//','/',$this->path.'/'):'';
		$fhandle = opendir($this->realpath);
		$dirdata=array();
		$filedata=array();
		while(false !== $file=(readdir($fhandle))){
			if($file!='.'&&$file!='..'){
				$filepath=SYS_ROOT.$path.$file;
				if(is_dir($filepath)){
					$dirdb['name']=$file;
					$dirdb['mtime']=date('Y-m-d H:i:s',filemtime($filepath));
					$dirdb['path']=$path.$file;
					$dirdata[]=$dirdb;
				} else {
					$filedb['name']=$file;
					$filedb['size']=$this->sizecount(filesize($filepath));
					$filedb['mtime']=date('Y-m-d H:i:s',filemtime($filepath));
					$filedb['path']=$path.$file;
					$filedata[]=$filedb;
				}
			}
		}
		unset($dirdb);
		unset($filedb);
		closedir($fhandle);
		sort($dirdata);
		sort($filedata);
		include($this->tpl->myTpl('manage'.$this->table));
	}
	function edit(){
		$path=$this->path;
		$filedata=file_get_contents($this->realpath);
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function del(){
		$path=$this->realpath;
		if(!is_writable($path))Base::showmessage('无删除权限');
		if(is_dir($path)){
			if(count(scandir($path))>2)
				Base::showmessage('目录非空，不能删除');
			rmdir($path);
		}else{
			unlink($path);
		}
		$info=pathinfo($this->path);
		Base::showmessage('删除成功','admin.php?action=file&ctrl=lists&path='.$info['dirname']);
	}
	function save(){
		$path=$this->path;
		if(!is_writable($this->realpath))Base::showmessage('无保存权限');
		$filedata=get_magic_quotes_gpc()?Base::magic2word($_POST['filedata']):$_POST['filedata'];
		$status=file_put_contents($this->realpath,$filedata);
		if($status){
			Base::showmessage('保存成功','admin.php?action=file&ctrl=lists');
		}
	}
	function download(){
		$info=pathinfo($this->path);
		header('Content-Disposition: attachment; filename="'.$info['basename'].'"');
		echo file_get_contents($this->realpath);
	}
	function create(){
		if(!$_GET['name']){
			Base::showmessage('请填写文件名/文件夹名');
		}
		$file=$this->realpath.'/'.$_GET['name'];
		if($_GET['isdir']==1){
			mkdir($file);
			$str='目录';
		}else{
			fopen($file,'a');
			$str='文件';
		}
		if(!is_writable($file))Base::showmessage('新建'.$str.'失败');
		$info=pathinfo($this->path.$_GET['name']);
		Base::showmessage('新建'.$str.'成功','admin.php?action=file&ctrl=lists&path='.$info['dirname']);
	}
	function upload(){
		echo '<form action="admin.php" method="post" id="toolform" enctype="multipart/form-data"> 
<b>选择文件：</b>  
 <input name="uploadfile" type="file" id="uploadfile" />
 <input type="hidden" name="action" value="file" />
	<input type="hidden" name="ctrl" value="executeupload" />
	<input type="hidden" name="path" value="'.$this->path.'" />
 <input type="submit" name="button" id="button" value="上传" />
</form>';
	}
	function executeupload(){
		$baseUrl=$_POST['path'];
		$upload=new Upload();
		$state=$upload->upload('uploadfile',$baseUrl,WEBURL,2);
		if($state['err']){
			Base::execmsg('上传失败，错误为：'.$state['err'],-1);
		}else
		{
			echo'<script>window.opener.document.location.href="admin.php?action=file&ctrl=lists&path='.$baseUrl.'";window.close();</script>';
		}
	}
	static function taomkdir($path,$mode=0777){
       if (!file_exists($path))
       {
           self::taomkdir(dirname($path), $mode);
           mkdir($path, $mode);
       }
   }
}
?>