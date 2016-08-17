<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();
$dba->input(
    'member_type',
    ( isset($_POST['id']) ? $_POST['id'] : null ),
    array(
        'name' => array(
            'type' => 'lang',
            'data' => $_POST['name']
        ),
        'level' => array(
            'type' => 'number',
            'data' => max(min(intval($_POST['level']),9),0)
        )
    )
);
$act = isset($_POST['id']) ? 'modify' : 'create';
$msg = ( isset($_POST['id']) ? '修改' : '建立' ) . '成員類別「' . $_POST['name'][$dba->default_language] . '」。';
$dba->writeLog($_SESSION[SES_ADMIN]['id'],$act,$msg);
$dba->decache('member_type');
die('{"status":"done","reload":"member"}');