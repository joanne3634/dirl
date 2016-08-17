<?php
define( 'DB_SERVER', 'localhost' );
define( 'DB_USERNAME', 'mmnet_web' );
define( 'DB_PASSWORD', 'web123' );
define( 'DB_DATABASE', 'MMNET_WEB' );

$pdo =  new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE,DB_USERNAME,DB_PASSWORD,array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' ));
$new_fields_query = $pdo->query('SHOW FULL COLUMNS FROM '.$_POST['new']);
$new_fields = array();
while( $column = $new_fields_query->fetch(PDO::FETCH_ASSOC) )
{
    if( $column['Field'] == 'id' ) { continue; }
    $new_fields[$column['Field']] = array(
        'necessary' => ($column['Null'] == 'YES') OR ($column['Default'] != 'NULL'),
        'fill_data' => array(),
        'fill_type' => 'string',
        'format' => array()
    );
}

$old_key = null;
foreach( $_POST['f'] as $field => $fills )
{
    if( $old_key == null ) { $old_key = $field; }
    foreach( $fills as $fill )
    {
        if( $fill['into'] == '_key_' ) { $old_key = $field; }
        if( !isset($new_fields[$fill['into']]) ) { continue; }
        $new_fields[$fill['into']]['fill_data'][] = $field;
        $num = count($new_fields[$fill['into']]['fill_data']);
        switch($fill['type'])
        {
        case 'json':
            $new_fields[$fill['into']]['fill_type'] = 'json';
            $key = isset($fill['format']) ? $fill['format'] : 'default';
            if( isset($new_fields[$fill['into']]['format'][$key]) )
            {
                $new_fields[$fill['into']]['format'][$key] .= ' %'.$num.'$s';
            }
            else
            {
                $new_fields[$fill['into']]['format'][$key] = '%'.$num.'$s';
            }
            break;
        case 'date':
            $new_fields[$fill['into']]['fill_type'] = 'date';
            $new_fields[$fill['into']]['format'] = $fill['format'];
            break;
        case 'format':
            $new_fields[$fill['into']]['fill_type'] = 'format';
            $new_fields[$fill['into']]['format'] = $fill['format'];
            break;
        case 'number':
            $new_fields[$fill['into']]['fill_type'] = 'number';
            if( strlen($new_fields[$fill['into']]['format']) == 0 )
            {
                $new_fields[$fill['into']]['format'] = '%1$d';
            }
            else
            {
                $new_fields[$fill['into']]['format'] .= '+%'.$num.'$d';
            }
            break;
        case 'string':
            if( strlen($new_fields[$fill['into']]['format']) == 0 )
            {
                $new_fields[$fill['into']]['format'] = '%1$s';
            }
            else
            {
                $new_fields[$fill['into']]['format'] .= ' %'.$num.'$s';
            }
            break;
        default:
            break;
        }
    }
}


foreach( $new_fields as $field => $data )
{
    switch($data['fill_type'])
    {
    case 'json':
        $new_fields[$field]['format'] = json_encode($data['format']);
        break;
    default:
        break;
    }
}

$messages = array();
$connection = mysql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD);
if (!$connection) { die('No Connection.'); }

mysql_select_db(DB_DATABASE,$connection);
$query = mysql_query('SELECT * FROM '.$_POST['old'],$connection);
while( $row = mysql_fetch_array($query) )
{
    $field_list = array();
    $value_list = array();
    foreach( $new_fields as $field => $data )
    {
        $datasize = count($data['fill_data']);
        if( $datasize == 0 )
        {
            if( $data['necessary'] ) { $sql .= "'_NONE_'"; }
            continue;
        }
        $field_list[] = $field;
        $val_arr = array();
        foreach( $data['fill_data'] as $f ) { $val_arr[] = $row[$f]; }
        $value = vsprintf($data['format'],$val_arr);
        $value_list[] = ( $data['fill_type'] != 'number' ) ? "'".str_replace("'","\'",$value)."'" : $value;
    }
    $sql = 'INSERT INTO '.$_POST['new'].'('.implode(',',$field_list).') VALUES ('.implode(',',$value_list).')';
    if( $pdo->exec($sql) === false )
    {
        $err_msg = $pdo->errorInfo();
        $messages[] = 'Translating '.$_POST['old'].' WHERE '.$old_key.'='.$row[$old_key].', error:'.$err_msg[2].'('.$err_msg[1].').';
    }
}
mysql_close($connection);
?>
<pre><? echo implode(PHP_EOL,$messages); ?></pre>