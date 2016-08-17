<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();
$table = array();
if( isset($_POST['table_id']) )
{
    $table = $dba->get(
        'table_list',
        $_POST['table_id'],
        array(
            'id' => 'string',
            'descript' => 'string:lines',
            'fields' => 'json',
            'header' => 'list:stroke',
            'since' => 'datetime'
        )
    );

    if( empty($table) )
    {
        if( $dba->hasTable($_POST['table_id']) )
        {
            $table = array(
                'id' => $_POST['table_id'],
                'descript' => '',
                'fields' => array(),
                'header' => array(),
            );

            $columns = $dba->_query('SHOW FULL COLUMNS FROM '.$_POST['table_id']);
            while( $column = $columns->fetch(PDO::FETCH_ASSOC) )
            {
                $f = $column['Field'];
                if( in_array($f,array('id',ORDINAL,STATE)) ) { continue; }
                $f_data = array(
                    'default' => $column['Default'],
                    'comment' => $column['Comment']
                );
                if( $column['Null'] == 'NO' ) { $f_data['be_null'] = false; }
                $f_type = array();
                if( preg_match('/(.*)\(?(.*?)\)?(.*)/',$column['Type'],$f_type) == 0 ) { continue; }
                switch($f_type[1])
                {
                case 'bigint':
                case 'int':
                case 'smallint':
                    $f_data['type'] = 'number';
                    if( in_array('unsigned',explode(' ',$f_type[3])) ) { $f_data['type'] .= ':unsigned'; }
                    break;
                case 'tinyint':
                    $f_data['type'] = 'number:flag';
                    break;
                case 'datetime':
                    $f_data['type'] = 'datetime';
                    break;
                case 'timestamp':
                    $f_data['type'] = 'datetime:now';
                    break;
                case 'varchar':
                    $f_data['type'] = 'string';
                    if( $column['Key'] == 'UNI' ) { $f_data['type'] .= ':unique'; }
                    break;
                case 'text':
                    $f_data['type'] = 'string:line';
                    break;
                case 'mediumtext':
                    $f_data['type'] = 'string:lines';
                    break;
                default:
                    $f_data['type'] = 'string';
                    break;
                }
                $table['fields'][$f] = $f_data;
            }
        }
    }
}

$dbtype_options = array();
foreach( $database_type_list AS $type => $data ) { $dbtype_options[$type] = $data['title'] . '(' . $type . ')'; }

?>
<form method="post" action="act/update-table_list.php">
    <table>
        <tbody>
            <tr>
                <th>名稱*</th>
                <td class="form-border"><input type="text" name="table"<?=( empty($table) ? '' : ' value="'.$table['id'].'" readonly' )?> /></td>
            </tr>
            <tr>
                <th>功能</th>
                <td class="form-border"><textarea name="descript"><?=$table['descript']?></textarea></td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">顯示</th>
                <th style="width:120px;">欄位名</th>
                <th style="width:240px;">資料類型</th>
                <th style="width: 40px;">空值</th>
                <th style="width:160px;">預設</th>
                <th>說明</th>
            </tr>
        </thead>
        <!--<tfoot>
            <tr>
                <td><button onclick="MMNet."></button></td>
                <td>欄位名</td>
                <td>資料類型</td>
                <td>資料結構</td>
                <td>空值</td>
                <td>預設</td>
                <td>說明</td>
            </tr>
        </tfoot>-->
        <tbody>
<?
foreach( $table['fields'] as $f_key => $f_data )
{
    $datatype = isset($database_type_list[$f_data['type']]) ? $database_type_list[$f_data['type']] : array();

    $display = in_array($f_key,$table['header']) ? ' checked' : '';
    $be_null = $f_data['be_null'] ? '' : ' checked';
    $default = ( isset($f_data['default']) AND !empty($f_data['default']) ) ? $f_data['default'] : $datatype['default'];
    $comment = ( isset($f_data['comment']) AND !empty($f_data['comment']) ) ? $f_data['comment'] : $datatype['title'];
    $prefix = 'fields[' . $f_key . ']';
?>
            <tr>
                <td class="-tac"><input type="checkbox" name="<?=$prefix?>[show]"<?=$display?> /></td>
                <td class="-tac"><?=$f_key?></td>
                <td class="form-border"><? drawSelect($prefix.'[type]',array(),$dbtype_options,$f_data['type']); ?></td>
                <td class="form-border -tac"><input type="checkbox" name="<?=$prefix?>[be_null]"<?=$be_null?> /></td>
                <td class="form-border"><input type="text" name="<?=$prefix?>[default]" value="<?=$default?>" /></td>
                <td class="form-border"><input type="text" name="<?=$prefix?>[comment]" value="<?=$comment?>" /></td>
            </tr>
<?
}
?>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();">設定資料表</button>
        <button type="reset">重新設定</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>