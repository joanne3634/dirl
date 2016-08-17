<?php
require_once('/home/kj/mmnet-manager-2015/mod_database.php');
$dba = new Accessor();
session_start();

$sql = 'DELETE FROM '.$_POST['db'].' WHERE '.$_POST['key'].'='.$_POST['name'].' LIMIT 1';

$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
$pdo = new PDO('mysql:host=localhost;dbname=MMNET_WEB','mmnet_web','web123',$options);
if( !$pdo ) { die('{"error":100,"message":"PDO cannot created."}'); }
if( !$pdo->exec($sql) )
{
    $err_msg = $pdo->errorInfo();
    //die('{"error":102,"message":"SQL Exception:'.$err_msg[2].'"}');
}
$dba->writeLog(0,'MGE::Report',$_SESSION['user_mmnet'].' '.$sql.'.');
include('../panel_11.html');