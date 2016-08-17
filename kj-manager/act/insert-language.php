<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();
$duplicate = $dba->get(TABLE_LANG,$_POST['id'],array('id'=>'string'));
if( !empty($duplicate) )
{
    die('{"error":602,"message":"ID['.$_POST['id'].'] exists."}');
}

$dba->input('language',$_POST['id'],array(
    'title' => array(
        'type' => 'string',
        'data' => $_POST['title']
    )
));
$dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'create', '增加語言「'.$_POST['title'].'('.$_POST['id'].')」。' );
die('{"status":"done","reload":"general"}');