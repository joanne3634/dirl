<?php
require_once('/home/kj/mmnet-manager-2015/mod_database.php');
$dba = new Accessor();
session_start();
include("link.html");

if($con) {
	if ($db_selected) {
        $sql = "SELECT {$_POST['field']} FROM {$_POST['db']} WHERE {$_POST['key']}='{$_POST['value']}' LIMIT 1";
        $origin = mysql_query($sql);
        if( $target_row = mysql_fetch_row($origin) ) {
            $target = ( intval($target_row[0]) + 1 ) % 2;
            mysql_query("UPDATE {$_POST['db']} SET {$_POST['field']}='{$target}' WHERE {$_POST['key']}='{$_POST['value']}' LIMIT 1");
$dba->writeLog(0,'MGE::Report',$_SESSION['user_mmnet'].' trigger '.$_POST['key'].'#'.$_POST['value'].' '.$_POST['field'].' from '.intval($target_row[0]).' to '.$target.'.');
        }
        include('../panel_11.html');
    } else { die('2&&&'); }
} else { die('1&&&'); }
