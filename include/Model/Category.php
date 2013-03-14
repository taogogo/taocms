<?php
class Category extends Article {
	function edit(){
		$tpllist=array();
		$tpllist=$this->getTpl();
		$getArray=$this->db->getlist(TB.$this->table,'id='.$this->id[0]);
		$category=$this->db->getlist(TB.'category');
		$o=$getArray[0];
		$goctrl='update';
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function add(){
		$tpllist=array();
		$tpllist=$this->getTpl();
		$category=$this->db->getlist(TB.'category');
		$o=$getArray[0];
		$goctrl='save';
		include($this->tpl->myTpl('edit'.$this->table));
	}
	function save(){
		$data=parent::columsdata();
		$status=$this->db->add_one(TB.$this->table,$data);
		$data['id']=$status;
		$staticurl=Base::creaturl($data,2);
		$this->db->updatelist(TB."category","staticurl='".$staticurl."'",$status);
		Base::execmsg("添加","?action=".$this->table.'&ctrl=createcache',$status);
	}
	function update(){
		$data=parent::columsdata();
		$status=$this->db->updatelist(TB.$this->table,$data,$this->id);
		$data=$this->db->getlist(TB."category","id=".$this->id[0],"id,nickname",1);
		$staticurl=Base::creaturl($data[0],2);
		$this->db->updatelist(TB."category","staticurl='".$staticurl."'",$this->id[0]);
		Base::execmsg("修改","?action=".$this->table.'&ctrl=createcache',$status);
	}
	function createcache(){
		$o=$this->db->getlist(TB."category");
		//对数组进行排序
		foreach ($o as $k => $v) {
			$ids[$k] = $v['id'];
			$orders[$k] = $v['orders'];
		}
		array_multisort($orders,SORT_DESC, $ids,SORT_DESC, $o);
		//数组排序结束
		$arrayData="<?php\r\n\$cats=array(\r\n";
		$arrayData.="0=>array(\r\n'name'=>'未分组',\r\n'status'=>0,\r\n'orders'=>0,\r\n'id'=>0),\r\n";
		foreach($o as $cat){
			//$data[$cat['id']]=$cat['name'];
			$arrayData.=$cat['id']."=>array(\r\n'name'=>'".$cat['name']."',\r\n'nikename'=>'".$cat['nikename']."',\r\n'id'=>'".$cat['id']."',\r\n'status'=>".$cat['status'].",\r\n'orders'=>".intval($cat['orders']).",\r\n'staticurl'=>'".$cat['staticurl']."',\r\n'cattpl'=>'".$cat['cattpl']."',\r\n'listtpl'=>'".$cat['listtpl']."',\r\n'distpl'=>'".$cat['distpl']."'),\r\n";
		}
		$arrayData.=")\r\n?>";
		file_put_contents(SYS_ROOT.CACHE.'cat_array.inc',$arrayData);
		Base::showmessage("目录缓存生成完毕","?action=category&ctrl=lists");
	}
	function del(){
		$status=$this->db->delist(TB.'category',$this->id);
		Base::execmsg("删除","?action=".$this->table.'&ctrl=createcache',$status);
	}
	function updateurl(){
		$id=$this->id[0];
		$addsql=$id?' and id<'.$id:'';
		if($_GET['exec']==1&&$id==0)Base::showmessage('全部生成成功','admin.php?action=category&ctrl=lists');
		$urldata=$this->db->getlist(TB."category","status=1".$addsql,"id,nickname",1);
		$staticurl=Base::creaturl($urldata[0],2);
		$id=$urldata[0]['id'];
		$o=$this->db->updatelist(TB."category","staticurl='".$staticurl."'",$id);
		Base::showmessage('开始生成栏目URL[栏目id'.$id.']','admin.php?action=category&ctrl=updateurl&exec=1&id='.$id,0.01);
	}
	function getTpl(){
		$fhandle = opendir('../template/'.THEME);
		while(false !== $file=(readdir($fhandle))){
			if($file!='.'&&$file!='..'&&!is_dir('../template/'.THEME.$file)){
				$tpllist[]=substr($file,0,-4);
			}
		}
		return $tpllist;
	}
	//生成栏目静态
	function createhtml(){
		if(CREATHTML==0){
			Base::showmessage('请到设置页面开启生成静态选项','admin.php?action=config');
		}
		$catinfo=$this->db->get_one(TB."category","status=1",'id',1);
		Base::showmessage('开始生成静态','../index.php?cat='.($catinfo['id']).'&createprocess=1',1);
	}
}
?>