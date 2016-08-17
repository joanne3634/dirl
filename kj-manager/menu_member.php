<?php
require_once('mod_general.php');
require_once('mod_database.php');

$dba = new Accessor();
?>
<!--<div class="-block"><a href="javascript:MMNet.view('main','inventory');">財產物品清點</a></div>-->
<div class="-block"><a href="javascript:MMNet.Controller.load('member.form',{'member_id':<?=$_SESSION[SES_ADMIN]['id']?>});">修改個人資料</a></div>
<!--<div class="-block"><a href="javascript:MMNet.Controller.load('folder.view',{'id':2});">上傳圖庫</a></div>-->
<div class="-block"><a href="javascript:MMNet.link('extends/member.phone.export.php','extends/cache/members.vcf');">在職人員通訊錄</a></div>