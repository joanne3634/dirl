<?php
require_once('mod_general.php');
require_once('mod_database.php');
$dba = new Accessor();

if( !isset($_POST['table']) ) { die(); }
$table = array(
    'name' => $_POST['table'],
    'style' => 'width:75%'
);
if( isset($_POST['where']) AND !empty($_POST['where']) ) { $table['where'] = $_POST['where']; }
if( isset($_POST['limit']) AND intval($_POST['limit']) > 0 ) { $table['limit'] = intval($_POST['limit']); }
?>

<aside width="25%">
這是用來測試的東西
</aside>

<?
$setting = $dba->query(
    'table_list',
    array(
        'id' => array( 'value' => $_POST['table'] ),
        'limit' => 1
    ),
    array(
        'id' => 'string',
        'fields' => 'json',
        'header' => 'list:stroke'
    )
);
?>
<table style="width:75%">
    <thead>
        <tr>
<?
$header = array();
foreach( $setting['header'] as $head )
{
    $field = $setting['fields'][$head];
    $header[$head] = $field['type'];
?>
            <th><?=$field['comment']?></th>
<?
}
?>
        </tr>
    </thead>
    <tbody>
<?
$content = $dba->query($_POST['table'],array(),$header);
foreach( $content as $row )
{
?>
        <tr>
<?
    foreach( $header as $head => $type )
    {
        echo '<td>';
        $data = $row[$head];
        echo '</td>';
    }
?>
        </tr>
<?
}
?>
    </tbody>
</table>