<?php
class Cms extends Article {
	function save(){
		$this->data=parent::columsdata();
		$this->data['times']=Base::getnowtime();
		$this->data['orders']=intval($this->data['orders']);
		$this->data['allowcmt']=intval($this->data['allowcmt']);
		$this->data['cat']=$_SESSION[TB.'admin_cat']?$_SESSION[TB.'admin_cat']:$this->data['cat'];
		$this->data['user_id']=$_SESSION[TB.'admin_id'];
		if( $this->data['remotepic'] == 1 ){
			$this->data['content'] = $this->saveremotepic( $this->data['content'] );
		}unset( $this->data['remotepic'] );
		
		//var_dump( $this->data['content'] );die;
		$status=$this->db->add_one(TB.$this->table,$this->data);
		$this->createtag($status);
		$this->data['id']=$status;
		$this->createurl($this->data);
		$this->save2memcache();
		$this->countcache();
		Base::execmsg("添加","?action=".$this->table.'&ctrl=lists',$status);
	}
	function saveremotepic( $content )
	{
		preg_match_all("/<img(.*) src=\"([^\"]+)\"[^>]+>/isU", $content,$matches);
		$spider=new Spider(null, null);
		foreach( $matches[2] as $picurl ){
			$fileinfo=pathinfo($picurl);
			$extension=$fileinfo['extension'];
			//不是图片跳过
			if( !in_array( $extension , array('png','jpg','gif','jpeg'))){
				continue;
			}
			//http打头，且域名与当前网站不一样，则抓取
			if( strpos($fileinfo['dirname'] ,'http')!==false && $fileinfo['dirname'] != substr(WEBURL,0,-1)){
				$picsrc=$spider->fetchurl( $picurl);
				
				$attach_dir='pictures/month_'.date('ym').'/';
				$filename=date("YmdHis").rand(1000,9999).'.'.$extension;
				$upload=new Upload(null);
				$upfile=$upload->saveFile($picurl, $filename, $attach_dir,WEBURL,$picsrc);
	
				$content=str_replace($picurl, $upfile['msg'], $content );
			}
	
		}
		
		return $content;
	}
	function update(){
		$this->data=parent::columsdata();
		$this->data['orders']=intval($this->data['orders']);
		$this->data['allowcmt']=intval($this->data['allowcmt']);
		$this->data['cat']=$_SESSION[TB.'admin_cat']?$_SESSION[TB.'admin_cat']:$this->data['cat'];
		if( $this->data['remotepic'] == 1 ){
			$this->data['content'] = $this->saveremotepic( $this->data['content'] );
		}unset( $this->data['remotepic'] );
		$status=$this->db->updatelist(TB.$this->table,$this->data,$this->id);
		$this->createtag($this->id);
		$this->data=$this->db->get_one(TB."cms","id=".$this->id[0],"*",1);
		$this->createurl($this->data);
		$this->save2memcache();
		$this->countcache();
		Base::execmsg("修改","?action=".$this->table.'&ctrl=lists',$status);
	}
	function del($wheres=''){
		$status=$this->db->delist(TB.$this->table,$this->id,$wheres);
		$this->createtag($this->id);
		$this->countcache();
		if(MEMCACHE)
		foreach((array)$this->id as $id){
			$this->mem->delete($id.'_cms');
		}
		Base::execmsg("删除","?action=".$this->table.'&ctrl=lists',$status);
	}
	function updateurl(){
		$id=$this->id[0];
		$addsql=$id?' and id<'.$id:'';
		if($_GET['exec']==1&&$id==0)Base::showmessage('全部生成成功',"?action=cms&ctrl=lists");
		$urldata=$this->db->getlist(TB."cms","status=1".$addsql,"id,slug,times",1);
		$filename=Base::creaturl($urldata[0]);
		if(substr($filename, -1)=='/'){
			$filename.='index.htm';
		}
		$id=$urldata[0]['id'];
		$data['staticurl']=$filename;
		$o=$this->db->updatelist(TB."cms",$data,$id);
		Base::showmessage('开始生成文章URL[文章id'.$id.']','admin.php?action=cms&ctrl=updateurl&exec=1&id='.$id,0.01);
	}
	function tocat(){
		$ids=(array)$_REQUEST['id'];
		if(MEMCACHE)
		foreach($ids as $id){
			$this->mem->delete($id.'_cms');
		}
		$data['cat']=$_SESSION[TB.'admin_cat']?$_SESSION[TB.'admin_cat']:$_POST['cat'];
		$o=$this->db->updatelist(TB."cms",$data,$ids);
		Base::execmsg("批量移动","?action=cms&ctrl=lists",$o);
	}
	function lists(){
		//栏目缓存
		if(is_writable(SYS_ROOT.CACHE.'cat_array.inc')){
			include(SYS_ROOT.CACHE.'cat_array.inc');
		}else{
			$catsfromdb=$this->db->getlist(TB."category",'status=1','*',100,'orders DESC');
			$cats=array(0=>array('name'=>'未分组','status'=>0,'orders'=>0,'id'=>0));
			foreach($catsfromdb as $v){
				$cats[$v['id']]=$v;
			}
		}
		$eachpage=EACHPAGE;
		$addsql=' ';
		$addsql.=($_GET['name']!='')?(' and name like "%'.$_GET['name'].'%"'):'';;
		$addsql.=($_GET['cat'])?(' and cat = '.$_GET['cat']):'';
		$addsql.=($_GET['status']!='')?(' and status = '.$_GET['status']):'';
		$totaldata=$this->db->getlist(TB."cms",'1=1'.$addsql,"count(*)");
		$total=$totaldata[0]['count(*)'];
		$page=$_GET['p'];
		$uppage=$page>0?$page-1:0;
		$downpage=($page+1)*$eachpage<$total?$page+1:$page;
		$list=$this->db->getlist(TB."cms",'1=1'.$addsql,"*",$eachpage*$page.','.$eachpage,"orders DESC,id DESC");
		include($this->tpl->myTpl('manage'.$this->table));
	}
	function createtag($articleId){
		//如果文章发布，则添加tag关系，对于存在的tag更新数量和添加对应
		$id=implode(',',(array)$articleId);
		if($_POST['status']==1&&trim($_POST['tags'])!=''){
			$taglist=array();
			$taglist=explode(',',Base::safeword($_POST['tags'],3));
			foreach($taglist as $tag){
				$tagdata=$this->db->getlist(TB."relations","name='".$tag."'","id,counts",1);
				if(!$tagdata){
					$data['name']=$tag;
					$data['counts']=1;
					$tagid=$this->db->add_one(TB."relations",$data);
					$cmsdata['relid']=$tagid;
					$cmsdata['cmsid']=$id;
					$this->db->add_one(TB."relatocms",$cmsdata);
				}else{
					$flag=$this->db->getlist(TB."relatocms","relid='".$tagdata[0]['id']."' and cmsid='".$id."'","id",1);
					if($flag)continue;
					$this->db->updatelist(TB."relations",'counts=counts+1',$tagdata[0]['id']);
					$cmsdata['relid']=$tagdata[0]['id'];
					$cmsdata['cmsid']=$id;
					$this->db->add_one(TB."relatocms",$cmsdata);
				}
			}
		}else{
			$tagdata=$this->db->getlist(TB.'relatocms','cmsid in('.$id.')',"relid",10);
			//计算准不准？
			if($tagdata){
				foreach($tagdata as $tags){
					$this->db->updatelist(TB."relations",'counts=counts-1',$tags['relid']);
				}
			}
			$this->db->delist(TB."relatocms",-1,'cmsid in('.$id.')');
		}
	}
	function countcache(){
		$totalnum['count']=$this->counts();
		$arrayData=Base::phpcache('articleData',$totalnum);
		file_put_contents(SYS_ROOT.CACHE."art_array.inc",$arrayData);
	}
	public function createurl($data){
		//生成url
		$filename=Base::creaturl($data);
		if(substr($filename, -1)=='/'){
			$filename.='index.htm';
		}
		$this->data['staticurl']=$urldata['staticurl']=$filename;
		$this->db->updatelist(TB."cms",$urldata,$data['id']);
	}
	function save2memcache(){
		if(MEMCACHE)
		return $this->mem->set( $this->data['id'].'_cms',$this->data);
	}
	function createhtml(){
		if(CREATHTML==0){
			Base::showmessage('请到设置页面开启生成静态选项','admin.php?action=config');
		}
		$catinfo=$this->db->getlist(TB."cms","status=1",'id',1);
		Base::showmessage('开始生成静态','../index.php?id='.($catinfo[0]['id']).'&createprocess=1',0.1);
	}
}
?>
