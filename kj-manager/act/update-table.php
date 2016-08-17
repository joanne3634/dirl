<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

//if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

$table = null;
$id = null;
$fields = array();
foreach( $_POST as $key => $value )
{
    switch($key)
    {
    case 'table':
        $table = $value;
        break;
    case 'id':
        if( !empty($value) ) { $id = $value; }
        break;
    default:
        $fields[$key] = $value;
        break;
    }
}

if( !$dba->hasTable($table) ) { die('{"error":404,"message":"No table['.$table.'] found."}'); }
$mod_id = $dba->input($table,$id,$fields);
/*$action = ($id==null) ? 'create' : 'modify';
$act_tc = ($id==null) ? '新增' : '修改';
$dba->writeLog($_SESSION[SES_ADMIN]['id'],$action,$act_tc.'在資料表 '.$table.' 中的資料。');*/
die('{"table":"'.$table.'","id":"'.$id.'"}');