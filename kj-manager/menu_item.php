<?php
require_once('mod_general.php');
require_once('mod_database.php');

if( checkAdministrator(5) )
{
?>
<div class="-block"><a href="javascript:MMNet.view('main','itemlist',{},true);">物品財產總覽</a></div>
<?
}
?>
<div class="-block"><a href="javascript:MMNet.view('main','inventory',{'proxy':null});">我的保管物品</a></div>
<?
$dba = new Accessor();
$user = $_SESSION[SES_ADMIN];
$proxy_list = $dba->query('proxy',array(
    'v_state' => array( 'value' => 'working' ),
    'agent' => array( 'value' => $user['id'] )
),array(
    'id' => 'number:reference',
    'title' => 'string'
));
foreach( $proxy_list as $proxy )
{
?>
<div class="-block"><a href="javascript:MMNet.view('main','inventory',{'proxy':<?=$proxy['id']?>},true);"><?=$proxy['title']?></a></div>
<?
}
?>
<div class="-block"><a href="javascript:MMNet.view('main','claimable');">認領物品</a></div>
<div class="-block"><a href="javascript:MMNet.view('main','history',{'table':'item_history','page':1,'item':null});">物品財產變更記錄</a></div>
<script>
MMNet.Page.set("page_history.php",{
    "table": "item_history",
    "page": 1
});
</script>