<?php
require_once('mod_general.php');
require_once('mod_database.php');

if( !checkAdministrator() ) { die(); }
?>

<div class="-block"><a href="javascript:MMNet.view('main','manual',{'id':0});">目錄</a></div>

<?
$dba = new Accessor();
$manuals = $dba->query('manual',array(
    'tree' => array( 'value' => '/' )
),array(
    'id' => 'number:reference',
    'assoc' => 'json',
    'caption' => 'string'
));
foreach( $manuals as $manual_item )
{
?>
<div class="-block"><a href="javascript:MMNet.view('main','manual',{'id':<?=$manual_item['id']?>});"><?=$manual_item['caption']?></a></div>
<?
}
?>

<?
if( checkAdministrator(5) )
{
?>
<div class="-block"><a href="javascript:MMNet.Controller.load('manual.form');">增加頁面</a></div>
<?
}
?>