<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

if( file_exists($_POST['path']) AND is_dir($_POST['path']) )
{
    if( !is_writable($_POST['path']) )
    {
        die('{"error":401,"message":"Path:'.$_POST['path'].' cannot write."}');
    }
}
else
{
    if( mkdir($_POST['path']) )
    {
        $dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'create', '建立資料夾位置 '.$_POST['path'].'。' );
    }
    else
    {
        die('{"error":404,"message":"Create folder:'.$_POST['path'].' failed."}');
    }
}

$folder_id = $dba->input('file_folder',null,array(
    'name' => array(
        'type' => 'string',
        'data' => $_POST['name']
    ),
    'path' => array(
        'type' => 'string',
        'data' => $_POST['path']
    ),
    'stint' => array(
        'type' => 'string',
        'data' => '*'
    ),
    'last_mod' => array( 'type' => 'datetime:now' )
));

include('sync-folder.php');

$dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'create', '綁定資料夾位置「'.$_POST['name'].'('.$_POST['path'].')」。' );
die('{"status":"done","reload":"general"}');