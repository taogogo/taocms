<?php   
include "config.php";
include SYS_ROOT.INC."common.php";
$db=new Dbclass(SYS_ROOT.DB_NAME);
$rss=$db->getlist(TB."cms","status=1","*",6);
echo'<?xml version="1.0" encoding="utf-8"?>';
?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title><?php echo WEBNAME?></title>
    <id><?php echo WEBURL?></id>
    <subtitle><?php echo WEBINFO?></subtitle>
    <link href="<?php echo WEBURL?>" />
    <link href="<?php echo WEBURL?>rss.php" rel="self" />
    <updated><?php echo date('Y-m-d')?></updated>
    <author>
      <name><?php echo WEBNAME?></name>
    </author>
<?php foreach($rss as $v){?>	
<entry>
      <link href="<?php echo WEBURL."?id=".$v['id'];?>"/>
      <id><?php echo $v['id']?></id>
      <title><?php echo $v['name']?></title>
      <content type="html"><![CDATA[<?php echo stripcslashes($v['content'])?>]]></content>
      <author>
          <name><?php echo WEBNAME?></name>
      </author>
      <updated><?php echo date('Y-m-d H:m:s',$v['times']);?></updated>
  </entry>
<?php ;}?>	

</feed>