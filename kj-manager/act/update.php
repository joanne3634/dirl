<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

$alter_extends = $dba->query('table_extends',array(
    'event' => array( 'value' => 'alter' ),
    'status' => array( 'value' => 'actived' )
),array(
    'id' => 'string',
    'tablename' => 'string',
    'name' => 'string'
));
$used = array();
foreach( $alter_extends as $extend )
{
    if( $_POST['table'] != $extend['tablename'] ) { continue; }
    $extend_file = '../extends/' . $extend['id'];
    if( !file_exists($extend_file) ) { continue; }
    include($extend_file);
    $used[] = '「' . $extend['name'] . '」';
}

if( isset($_POST['value']) )
{
    $dba->execute("UPDATE {$_POST['table']} SET {$_POST['field']}='{$_POST['value']}' WHERE id='{$_POST['id']}'");
    $msg = '將 ' . $_POST['table'] . '#' . $_POST['id'] . ' 的 ' . $_POST['field'] . ' 設為「' . $_POST['value'] . '」';
    if( !empty($used) ) { $msg .= '，使用擴充功能' . implode('、',$used) . '。'; }
    $dba->writeLog($_SESSION[SES_ADMIN]['id'],'update',$msg);
}

die('{"message":"done","scroll":false}');