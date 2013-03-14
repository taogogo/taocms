<?php
class Index{
	static public $tpl;
	static private $_db;
	static public $data;
	static public $cfile;
	static private $_mem;
	static public function run($path)
	{
		self::createdata($path);
		echo self::$data;
	}
	static public function createdata($path){
		self::$_db=new Dbclass(SYS_ROOT.DB_NAME);
		self::$tpl=new Template();
		self::$_mem=new Memcached(MEMCACHE);
		//直接用数组缓存
		if(is_writable(SYS_ROOT.CACHE.'cat_array.inc')){
			include(SYS_ROOT.CACHE.'cat_array.inc');
		}else{
			$catsfromdb=self::$_db->getlist(TB."category",'status=1','*',100,'orders DESC');
			$cats=array(0=>array('name'=>'未分组','status'=>0,'orders'=>0,'id'=>0));
			foreach($catsfromdb as $v){
				$cats[$v['id']]=$v;
			}
		}
		
		//网站信息常用变量
		$webname=Base::magic2word(WEBNAME);
		$webinfo=Base::magic2word(WEBINFO);
		$weburl=Base::magic2word(WEBURL);
		$announce=Base::magic2word(ANNOUNCE);
		$tag=Base::safeword( Base::safeword(isset($_GET['tag'])?$_GET['tag']:null) , 5 );
		$index=new Index();
		$eachpage=EACHPAGE;
		$page=isset($_GET['p'])?intval($_GET['p']):0;
		$uppage=$page>0?$page-1:0;
		//替换掉url中分页的参数
		$path=preg_replace("/\?{0,1}&{0,1}p=(\d*)/",'',$path);
		//支持getlist模板标签
		$dbit=self::$_db;
		$addtail=$path;
		if($tag){
			//标签页
			$tagdata=self::$_db->get_one(TB.'relations',"name='".$tag."'","id",1)  or Base::sendheader(404);
			$totaldata=self::$_db->get_one(TB.'relatocms','relid='.$tagdata['id'],'count(*) as num');
			$totalnum=$totaldata['num'];
			$addtail='&tag='.$tag;
			$indexs='tag';
			$o=self::$_db->getlist(TB.'relatocms','relid='.$tagdata['id'],'cmsid',$eachpage*$page.','.$eachpage);
			$idlist='';
			if(is_array($o))
				foreach($o as $v){
					$idlist.=$v['cmsid'].',';
				}
			$addtitle=$tag;
			$articles=self::$_db->getlist(TB.'cms','status=1 and id in('.substr($idlist,0,-1).')','*',EACHPAGE,'orders DESC,times DESC,id DESC');
			$indexs='tag';
		}elseif($path==''){
			$articles=self::$_db->getlist(TB."cms",'status=1',"*",$eachpage*$page.','.$eachpage,"orders DESC,times DESC,id DESC");
			if(is_writable(SYS_ROOT.CACHE.'art_array.inc')){
				include(SYS_ROOT.CACHE.'art_array.inc');
				$totalnum=$articleData['count'];
			}else{
				$totaldata=self::$_db->get_one(TB.'cms','status=1','count(*) as total');
				$totalnum=$totaldata['total'];
			}
			$downpage=($page+1)*$eachpage<$totalnum?$page+1:$page;
			self::$cfile='index.html';
			$indexs='index';
		}elseif ($catinfo=self::$_db->get_one(TB."category",'status=1 and staticurl='."'".$path."'","*",1)) {
				$GLOBALS['catlist']='';
				//递归子栏目
				function getcat($cats,$dbit){
					foreach($cats as $cat){				
						$GLOBALS['catlist'].=''.$cat['id'].',';
						$cattails=$dbit->getlist(TB.'category','status=1 and fid='.intval($cat['id']),'id,fid');
						if($cattails){
							getcat($cattails,$dbit);
						}
					}
				}
				$catid=$catinfo['id'];
				getcat(array($catid=>array('id'=>$catid)),self::$_db);
				$catlist=substr($GLOBALS['catlist'],0,-1);
				$articles=self::$_db->getlist(TB."cms",'status=1 and cat='.$catinfo['id'],"*",$eachpage*$page.','.$eachpage,"orders DESC,times DESC,id DESC");
				$totaldata=self::$_db->get_one(TB.'cms','status=1 and cat in('.$catlist.')','count(*) as total');
				$totalnum=$totaldata['total'];
				$indexs=$page?($catinfo['listtpl']?$catinfo['listtpl']:'category'):($catinfo['cattpl']?$catinfo['cattpl']:'category');
				$addtitle=$catinfo['name'];
				self::$cfile=$path;
			}else{
				$tmp=self::$_db->get_one(TB."cms",'status=1 and staticurl='."'".$path."'","id",1) or Base::sendheader(404);
				$id=$tmp['id'];
				$atl=self::getatlbyid($id) or Base::sendheader(404);
				if(VIEWSCOUNT){
					$atl['views']=$atl['views']+1;
					if(MEMCACHE){
						self::$_mem=new Memcached(MEMCACHE);
						if($atl['views']%20==1){
							self::$_db->updatelist(TB."cms",'views=views+20',$atl['id']);
						}
						self::$_mem->set($id.'_cms',$atl);
					}else{
						self::$_db->updatelist(TB."cms",'views=views+1',$atl['id']);
					}
					
					
				}
				$addtitle=$atl['name']?$atl['name'].'_':'';
				//上一篇
				$tmp=self::$_db->get_one(TB."cms",'status=1 and id<'.$id,"id",1);
				$upart=self::getatlbyid($tmp['id']);
				//下一篇
				$tmp=self::$_db->get_one(TB."cms",'status=1 and id>'.$id,"id",1,'id');
				$downart=self::getatlbyid($tmp['id']);
				//评论
				$tmp=self::$_db->get_one(TB."comment",'status=1 and article_id='.$id,"count(*) as num");
				$cmtotal=$tmp['num'];
				$comments=self::$_db->getlist(TB."comment",'status=1 and article_id='.$id,"*");
				$addtitle=$atl['name'];
				$indexs=$cats[$atl['cat']]['distpl']?$cats[$atl['cat']]['distpl']:'display';
				self::$cfile=$path;
		}
		$downpage=($page+1)*$eachpage<$totalnum?$page+1:$page;
		ob_start();
		include(self::$tpl->myTpl($indexs,THEME,'self::$tpl'));
		self::$data=ob_get_contents();
		ob_clean();
		$runtime=microtime(true)-$GLOBALS['starttime'];
	}
	static public function getatlbyid($id){
		if(!$id)return null;
		self::$_db=new Dbclass(SYS_ROOT.DB_NAME);
		if(MEMCACHE){
			self::$_mem=new Memcached(MEMCACHE);
			if(!$atl=self::$_mem->get($id.'_cms')){
				$atl=self::$_db->get_one(TB."cms",'status=1 and id='.$id,"*",1);;
				self::$_mem->set($id.'_cms',$atl);
			}
		}else{
			$atl=self::$_db->get_one(TB."cms",'status=1 and id='.$id,"*",1);
		}
		return $atl;
	}
	static public function createhtml($id,$cat=null,$single=FALSE){
		$path='';
		self::$_db=new Dbclass(SYS_ROOT.DB_NAME);
		$nowcolums=$cat?'category':'cms';
		$nowtitle=$cat?'栏目':'页面';
		$nowvar=$cat?'cat':'id';
		$tmp=self::$_db->get_one(TB.$nowcolums,'id='.$$nowvar,"staticurl");
		$path=$tmp['staticurl'];
		$tmp=self::$_db->get_one(TB.$nowcolums,'id<'.$$nowvar,"id");
		$nextid=$tmp['id'];
		if (CREATHTML=='1'&&strstr($path,'?')===false) {
			self::createdata($path);
			File::taomkdir(SYS_ROOT.dirname($path), 0777);
			file_put_contents(SYS_ROOT.self::$cfile,self::$data);
		}
		if($single)Base::showmessage('生成'.$nowtitle.'成功');
		if($nextid){
			Base::showmessage('生成'.$nowtitle.'ID('.$$nowvar.')','index.php?createprocess=1&'.$nowvar.'='.$nextid,1);
		}else{
			Base::showmessage('生成'.$nowtitle.'成功','admin/admin.php?action=frame&ctrl=main');
		}
	}
}
?>