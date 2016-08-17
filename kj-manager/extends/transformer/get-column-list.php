<?php
define( 'DB_SERVER', 'localhost' );
define( 'DB_USERNAME', 'mmnet_web' );
define( 'DB_PASSWORD', 'web123' );
define( 'DB_DATABASE', 'MMNET_WEB' );

$connection = mysql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD);
if (!$connection) { die('No Connection.'); }

mysql_select_db(DB_DATABASE,$connection);
$from_column_query = mysql_query('SHOW COLUMNS FROM '.$_POST['from'],$connection);
$from_column_list = array();
while( $column = mysql_fetch_array($from_column_query) ) { $from_column_list[$column['Field']] = $column['Type']; }
mysql_close($connection);

$pdo =  new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE,DB_USERNAME,DB_PASSWORD,array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' ));
$to_column_query = $pdo->query('SHOW FULL COLUMNS FROM '.$_POST['to']);
$to_column_list = array();
while( $column = $to_column_query->fetch(PDO::FETCH_ASSOC) )
{
    if( $column['Field'] == 'id' ) { continue; }
    $to_column_list[$column['Field']] = array(
        'title' => isset($column['Comment']) ? $column['Comment'] : $column['Field'],
        'type' => $column['Type']
    );
}
?>

<input type="hidden" name="old" value="<?=$_POST['from']?>" />
<input type="hidden" name="new" value="<?=$_POST['to']?>" />
<table>
    <thead>
        <tr>
            <th style="wdith:120px;">欄位</th>
            <th style="wdith:120px;">型別</th>
            <th>轉存欄位</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
<?
foreach( $from_column_list as $f_name => $f_type )
{
    $prefix = 'f['.$f_name.']';
?>
        <tr class="-sample" data-num="0">
            <td class="-tac"><?=$f_name?></td>
            <td class="-tac"><?=$f_type?></td>
            <td>
                <select name="<?=$prefix?>[0][into]">
                    <option value="_none_">資料不轉移</option>
                    <option value="_key_">資料不轉移，視為key。</option>
<?
    foreach( $to_column_list as $t_key => $t_data )
    {
        $selected = ($f_name==$t_key) ? ' selected' : '';
?>
                        <option value="<?=$t_key?>" title="<?=$t_data['type']?>"<?=$selected?>><?=$t_data['title']?>(<?=$t_key?>)</option>
<?
    }
?>
                </select>
                <select name="<?=$prefix?>[0][type]" onchange="trigger($(this).next('input'),this.value);">
                    <option value="string">純文字</option>
                    <option value="number">數字</option>
                    <option value="json">JSON形式</option>
                    <option value="date">時間形式</option>
                    <option value="format">格式化文字</option>
                </select>
                <input type="text" name="<?=$prefix?>[0][format]" disabled />
            </td>
            <td class="-button" onclick="insert($(this).parent(),$(this).parents('tbody').get(0));">增加</td>
        </tr>
<?
}
?>
    </tbody>
</table>