<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

if( !$dba->hasTable($_POST['table']) ) { die('{"error":404,"message":"No table."}'); }

$columns = $dba->query('SHOW FULL COLUMNS FROM '.$_POST['table']);
if( !$columns ) { die('{"error":404,"message":"No columns."}'); }

$column_list = array();
while( $column = $columns->fetch(PDO::FETCH_ASSOC) )
{
    $column_list[] = $column['Field'];
}