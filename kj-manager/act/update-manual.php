<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }

$dba = new Accessor();
$mid = null;
$assoc_data = array();
if( isset($_POST['id']) )
{
    $mid = intval($_POST['id']);
    $manual = $dba->get('manual',$mid,array(
        'assoc' => 'json'
    ));
    $assoc_data = $manual['assoc'];
}

if( isset($_POST['assoc']) )
{
    $set_key = array();
    foreach( $_POST['assoc'] as $line )
    {
        $key = $line['key'];
        if( !isset($key) OR empty($key) ) { continue; }
        if( $key == 'public' ) { continue; }
        $set_key[] = $key;
        $assoc_data[$key] = $line['val'];
    }
    foreach( $assoc_data as $key => $value )
    {
        if( in_array($key,$set_key) ) { continue; }
        unset($assoc_data[$key]);
    }
}

$dba->input('manual',$mid,array(
    'tree' => array(
        'type' => 'string:line',
        'data' => rtrim($_POST['tree'],'/') . '/'
    ),
    'assoc' => array(
        'type' => 'json',
        'data' => $assoc_data
    ),
    'caption' => array(
        'type' => 'string',
        'data' => $_POST['caption']
    ),
    'content' => array(
        'type' => 'string:lines',
        'data' => $_POST['content']
    ),
    'creator' => array(
        'type' => 'number:reference',
        'data' => $_SESSION[SES_ADMIN]['id']
    )
));

//$message = (isset($_POST['id'])?'修改':'建立').'財產物品(item_list#'.$item_id.')。';
//$dba->writeLog( $_SESSION[SES_ADMIN]['id'], isset($_POST['id'])?'modify':'create', $message );
die('{"status":"done","reload":"manual"}');