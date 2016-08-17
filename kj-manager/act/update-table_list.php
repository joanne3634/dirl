<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

$fields = array();
$header = array();
foreach( $_POST['fields'] as $f_key => $f_data )
{
    $datatype = $database_type_list[$f_data['type']];
    $structure = array( $datatype['datatype'] );
    $fields[$f_key] = array( 'type' => $f_data['type'] );
    if( !isset($f_data['be_null']) OR $datatype['be_null'] === false )
    {
        $fields[$f_key]['be_null'] = false;
        $structure[] = 'NOT NULL';
    }
    if( !empty($f_data['default']) )
    {
        $fields[$f_key]['default'] = $f_data['default'];
        $default = ( gettype($f_data['default']) == 'number' ) ? $f_data['default'] : "'{$f_data['default']}'";
        $structure[] = 'DEFUALT '.$default;
    }
    if( isset($datatype['key']) )
    {
        $key = ( ( count($f_data['key']) > 1 ) ? implode(' ',$f_data['key']) : $f_data['key'] ) . ' key';
        $structure[] = strtoupper($key);
    }
    if( !empty($f_data['comment']) )
    {
        $fields[$f_key]['comment'] = $f_data['comment'];
        $structure[] = "COMMENT '{$f_data['comment']}'";
    }
    $fields[$f_key]['structure'] = implode(' ',$structure);
    if( isset($f_data['show']) ) { $header[] = $f_key; }
}

$data = array(
    'descript' => array(
        'type' => 'string:lines',
        'data' => $_POST['descript']
    ),
    'fields' => array(
        'type' => 'json',
        'data' => $fields
    ),
    'header' => array(
        'type' => 'list:stroke',
        'data' => array_unique($header)
    ),
    'since' => array(
        'type' => 'datetime:now',
        'data' => 'now()'
    )
);
//die(json_encode($data));

if( !$dba->hasTable($_POST['table']) )
{ // CREAT TABLE
    $fieldlist = array(
        "id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '識別碼'",
        "ordinal INT NOT NULL COMMENT '排序號碼'",
        "v_state VARCHAR(15) NOT NULL DEFAULT 'working' COMMENT '顯示狀態'"
    );
    foreach( $fields as $field => $f_data ) { $fieldlist[] = $field . ' ' . $f_data['structure']; }
    $sql = 'CREATE TABLE '.$_POST['table'].' ('.implode(',',$fieldlist).') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    //$dba->execute($sql);
    //$dba->writeLog($_SESSION[SES_ADMIN]['id'],'create','建立自定資料表 '.$_POST['table'].'。');
}

$dba->input('table_list',$_POST['table'],$data);
$dba->writeLog($_SESSION[SES_ADMIN]['id'],'setup','設定資料表 '.$_POST['table'].' 的結構。');
die('{"table":"'.$_POST['table'].'","reload":"general"}');