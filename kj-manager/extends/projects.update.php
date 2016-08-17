<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();
$data = array(
    'title' => array(
        'type' => 'json:language',
        'data' => $_POST['title']
    ),
    'link' => array(
        'type' => 'string',
        'data' => $_POST['link']
    ),
    'descript' => array(
        'type' => 'json:language',
        'data' => $_POST['descript']
    ),
    'use_lang' => array(
        'type' => 'string',
        'data' => isset($_POST['use-eng-for-cht']) ? 'EN' : ''
    )
);
$mod_id = $dba->input( 'projects', isset($_POST['id']) ? $_POST['id'] : null, $data );
$act = isset($_POST['id']) ? '修改' : '新增';
$dba->writeLog(0,'MGE::Report',$_SESSION['user_mmnet'].' '.$act.' project#'.$mod_id.'。');
$_SESSION['last_modified_id'] = $mod_id;
die('{"status":"done","project":'.$mod_id.',"message":""}');
