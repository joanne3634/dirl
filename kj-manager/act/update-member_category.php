<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();
$dba->input(
    'member_category',
    ( isset($_POST['edit']) ? $_POST['id'] : null ),
    array(
        'id' => array(
            'type' => 'string',
            'data' => $_POST['id']
        ),
        'name' => array(
            'type' => 'string',
            'data' => $_POST['name']
        ),
        'type' => array(
            'type' => 'string',
            'data' => $_POST['type']
        ),
        'required' => array(
            'type' => 'boolean',
            'data' => isset($_POST['required'])
        ),
        'hint' => array(
            'type' => 'string',
            'data' => $_POST['hint']
        )
    )
);

$act = isset($_POST['edit']) ? 'modify' : 'create';
$msg = ( isset($_POST['edit']) ? '修改' : '建立' ) . '成員資料類型「' . $_POST['name'] . '」。';
$dba->writeLog($_SESSION[SES_ADMIN]['id'],$act,$msg);
$dba->decache('member_category');
die('{"status":"done","reload":"member"}');