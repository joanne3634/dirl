<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

//if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

if( isset($_POST['before']) )
{
    $dba->arrange($_POST['table'],$_POST['id'],$_POST['before'],true);
    $msg = '將 '.$_POST['table'].'#'.$_POST['id'].' 移到 '.$_POST['table'].'#'.$_POST['before'].' 之前。';
}
else if( isset($_POST['after']) )
{
    $dba->arrange($_POST['table'],$_POST['id'],$_POST['after'],false);
    $msg = '將 '.$_POST['table'].'#'.$_POST['id'].' 移到 '.$_POST['table'].'#'.$_POST['after'].' 之後。';
}
else { die('{"error":404,"message":"No reference position."}'); }

$dba->writeLog($_SESSION[SES_ADMIN]['id'],'notice','重新排序資料表，'.$msg);
die('{"status":"done"}');