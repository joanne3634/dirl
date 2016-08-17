<?php
define( 'DB_SERVER', 'localhost' );
define( 'DB_USERNAME', 'mmnet_web' );
define( 'DB_PASSWORD', 'web123' );
define( 'DB_DATABASE', 'MMNET_WEB' );
define( 'DB_ENCODING', 'utf8' );

define( 'TABLE_LANG', 'language' );
define( 'TABLE_LOG', 'log' );
define( 'TABLE_USER', 'member_list' );

define( 'ORDINAL', 'ordinal' );
define( 'STATE', 'v_state' );
define( 'USERNAME', 'username' );

require_once('plugins/kj.php');

/*-------------------------------------------------------------\
 * Database Accessor TablePainter
\-------------------------------------------------------------*/
interface AccessorTablePainter
{
    public function drawHeader($columns);
    public function drawFooter($result);
    public function drawNoData();

    public function needSort();
    public function drawData($row,$lang);
}

/*-------------------------------------------------------------\
 * Database Accessor
\-------------------------------------------------------------*/
class Accessor {
    private $pdo = null;
    private $tables = array();
    private $users = array();
    private $languages = array();
    public $default_language = 'TC';

    function __construct()
    {
        $options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.DB_ENCODING );
        $this->pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE,DB_USERNAME,DB_PASSWORD,$options);
        if( !$this->pdo ) { die('{"error":100,"message":"PDO cannot created."}'); }

        $table_query = $this->pdo->query('SHOW TABLES');
        while( $table = $table_query->fetch(PDO::FETCH_NUM) )
        {
            $this->tables[] = $table[0];
        }

        if( in_array(TABLE_LANG,$this->tables) )
        {
            $languages = $this->pdo->query('SELECT * FROM '.TABLE_LANG.' WHERE '.STATE.'=\'working\' ORDER BY '.ORDINAL.' ASC');
            while( $language = $languages->fetch(PDO::FETCH_ASSOC) ) { $this->languages[] = $language; }
            $this->default_language = $this->languages[0]['id'];
        }

        if( in_array(TABLE_USER,$this->tables) )
        {
            $users = $this->pdo->query('SELECT * FROM '.TABLE_USER);
            while( $user = $users->fetch(PDO::FETCH_ASSOC) )
            {
                $userdata = array(
                    'id' => $user['id'],
                    'type' => $user['type'],
                    'name' => json_decode($user['name'],true),
                    'account' => $user[USERNAME],
                    'level' => $user['level']
                );
                $this->users[$user['id']] = $userdata;
                $this->users[$user[USERNAME]] = $userdata;
            }
        }
    }

/*-------------------------------------------------------------\
 * Accessor::log
 - writeLogFile(lines):
 - writeLog(who,what,how):
\-------------------------------------------------------------*/
    private function writeLogFile($lines)
    {
        $fp = fopen( __DIR__ . '/logs/' . date('Y-m-d') . '.log','a');
        if( $fp )
        {
            fwrite($fp,date('---- Y-m-d H:i:s ----').PHP_EOL);
            foreach($lines as $line) { fwrite($fp,$line.PHP_EOL); }
            fclose($fp);
        }
    }

    public function writeLog( $who, $what, $how )
    {
        if( !isset($this->users[$who]) )
        {
            $user = array(
                'id' => 0,
                'level' => 10
            );
        } else { $user = $this->users[$who]; }

        $sql = "INSERT INTO ".TABLE_LOG." VALUES (0,{$user['id']},{$user['level']},'{$what}','{$how}',now())";
        if( $this->pdo->exec($sql) == false )
        {
            $err_msg = $this->pdo->errorInfo();
            $this->writeLogFile(array(
                'MySQL Exception: '.$err_msg[2],
                'WriteLog('.$who.','.$what.','.$how.')'
            ));
        }
    }

/*-------------------------------------------------------------\
 * Accessor::basic
 - _query(sql):
 - execute(sql):
\-------------------------------------------------------------*/
    public function _query($sql) { return $this->pdo->query($sql); }
    public function execute($sql) {
        $result = $this->pdo->exec($sql);
        if( $result === false )
        {
            $err_msg = $this->pdo->errorInfo();
            $this->writeLogFile(array(
                'MySQL Exception: '.$err_msg[2],
                'Accessor::Execute('.$sql.')'
            ));
        }
    }

/*-------------------------------------------------------------\
 * Accessor::decode
\-------------------------------------------------------------*/
    private function decode( $type, $data )
    {
        switch($type)
        {
        case 'json':
        case 'json:list':
        case 'json:array':
        case 'json:lang':
        case 'json:language':
        case 'json:lines':
            return json_decode($data,true);
        case 'list:comma':
            return explode(',',$data);
        case 'list:stroke':
            return explode('|',$data);
        case 'string':
        case 'string:unique':
        case 'string:line':
        case 'string:lines':
            return $data;
        case 'number':
        case 'number:unsigned':
        case 'number:reference':
        case 'number:flag':
            return intval($data);
        case 'datetime':
            return $data;
        case 'boolean':
            return !( intval($data) == 0 );
        case 'user':
            return $this->users[$who];
        case 'user:name':
            $user = $this->users[$who];
            return $user['name'][$this->default_language];
        case 'lang:default':
            return $this->translate($data);
        default:
            return sscanf($data,$type);
        }
        return null;
    }

/*-------------------------------------------------------------\
 * Accessor::encode
\-------------------------------------------------------------*/
    private function encode( $type, $data )
    {
        switch($type)
        {
        case 'json':
            return "'".kjPHP\toJSON($data,true)."'";
        case 'json:list':
        case 'json:array':
            $data_array = array();
            foreach( $data as $value ) { $data_array[] = $value; }
            return "'".kjPHP\toJSON($data_array,true)."'";
        case 'lang':
        case 'json:lang':
        case 'json:language':
            $data_dictionary = array();
            foreach( $data as $lang => $line )
            {
                //if( !in_array($lang,$this->languages) ) { continue; }
                $data_dictionary[$lang] = $line;
            }
            return "'".kjPHP\toJSON($data_dictionary)."'";
        case 'json:lines':
            $data_dictionary = array();
            foreach( $data as $lang => $line )
            {
                //if( !in_array($lang,$this->languages) ) { continue; }
                if( empty($line) ) { continue; }
                $data_dictionary[$lang] = explode("\r\n",$line);
            }
            return "'".kjPHP\toJSON($data_dictionary,true)."'";
        case 'list:comma':
            return "'".implode(',',$data)."'";
        case 'list:stroke':
            return "'".implode('|',$data)."'";
        case 'string':
        case 'string:unique':
        case 'string:line':
            return "'".$data."'";
        case 'string:lines':
            return "'".$data."'";
        case 'boolean':
            return ($data?'1':'0');
        case 'datetime:now':
            return 'now()';
        case 'number':
        case 'number:unsigned':
        case 'number:reference':
        case 'number:flag':
        case 'datetime':
        default:
            return $data;
        }
        return '';
    }

/*-------------------------------------------------------------\
 * Accessor::get
 - get(table,id,parser): use query.
 - query(table,filter,parser): single table select.
 - select(table,tables,parser): multiple table select.
\-------------------------------------------------------------*/
    public function get( $table, $id, $parser )
    {
        $filter = array(
            'id' => array( 'value' => $id ),
            'limit' => 1
        );
        return $this->query($table,$filter,$parser);
    }

    public function query( $table, $filter, $parser )
    {
        $sql = 'SELECT * FROM ' . $table;
        $limit = 0;
        if( isset($filter['limit']) )
        {
            $limit = intval($filter['limit']);
            unset($filter['limit']);
        }
        $orderby = array();
        if( isset($filter['order']) )
        {
            $orderby = $filter['order'];
            unset($filter['order']);
        }

        $fields = array();
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table);
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) ) { $fields[] = $column['Field']; }

        $cond_values = array();
        if( !empty($filter) )
        {
            $conf_fields = array();
            foreach( $filter as $field => $cond )
            {
                if( !in_array($field,$fields) ) { continue; }

                $cond_type = isset($cond['type']) ? strtolower($cond['type']) : '=';
                $cond_prefix = '';
                if( strpos($cond_type,'not') === 0 )
                {
                    $cond_type = ltrim(substr($cond_type,3),' ');
                    $cond_prefix = 'NOT ';
                }

                switch($cond_type)
                {
                case 'in':
                    $qms = array();
                    foreach( $cond['values'] as $value )
                    {
                        if( empty($value) ) { continue; }
                        $qms[] = '?';
                        $cond_values[] = $value;
                    }
                    $conf_fields[] = $cond_prefix . $field . ' IN (' . implode(',',$qms) . ')';
                    break;
                case 'like':
                    $conf_fields[] = $cond_prefix . $field . ' LIKE ?';
                    $cond_values[] = $cond['value'];
                    break;
                case 'bigger':
                    $conf_fields[] = $cond_prefix . $field . ' > ?';
                    $cond_values[] = $cond['value'];
                    break;
                case 'smaller':
                    $conf_fields[] = $cond_prefix . $field . ' < ?';
                    $cond_values[] = $cond['value'];
                    break;
                case 'between':
                    $conf_fields[] = $cond_prefix . $field . ' BETWEEN ? AND ?';
                    $cond_values[] = $cond['min'];
                    $cond_values[] = $cond['max'];
                    break;
                default:
                    $conf_fields[] = $cond_prefix . $field . ' = ?';
                    $cond_values[] = $cond['value'];
                    break;
                }
            }
            $sql .= ' WHERE ' . implode(' AND ',$conf_fields);
        }

        if( !empty($orderby) )
        {
            $orders = array();
            foreach( $orderby as $field => $sort )
            {
                if( !in_array($field,$fields) ) { continue; }
                $sort = strtoupper($sort);
                if( !in_array($sort,array('DESC','ASC')) ) { $sort = 'DESC'; }
                $orders[] = $field . ' ' . $sort;
            }
            if( in_array(ORDINAL,$fields) AND !isset($orderby[ORDINAL]) ) { $orders[] = ORDINAL . ' ASC'; }
            $sql .= ' ORDER BY ' . implode(', ',$orders);
        } else if( in_array(ORDINAL,$fields) ) { $sql .= ' ORDER BY ' . ORDINAL . ' ASC'; }

        if( $limit > 0 ) { $sql .= ' LIMIT ' . $limit; }

        $stmt = $this->pdo->prepare($sql);
        if( $stmt->execute($cond_values) )
        {
            $result = array();
            if( $limit == 1 )
            {
                if( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
                {
                    if( isset($parser) )
                    {
                        foreach( $parser as $field => $type )
                        {
                            $result[$field] = $this->decode($type,$row[$field]);
                        }
                    } else { $result = $row; }
                }
            }
            else
            {
                while( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
                {
                    if( isset($parser) )
                    {
                        $result_row = array();
                        foreach( $parser as $field => $type )
                        {
                            $result_row[$field] = $this->decode($type,$row[$field]);
                        }
                        if( !empty($result_row) ) { $result[] = $result_row; }
                    } else { $result[] = $row; }
                }
            }
            return $result;
        }
        else
        {
            $err_info = $stmt->errorInfo();
            $this->writeLogFile(array(
                'MySQL Exception: '.$err_info[2],
                'Accessor::Query('.$sql.')'
            ));
            return null;
        }
    }

    /*public function select( $table, $tables, $parser )
    {
    }*/

    public function _select( $table, $field, $value, $limit=0 )
    {
        $fields = array();
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table);
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) ) { $fields[] = $column['Field']; }

        $sql = "SELECT * FROM {$table} WHERE {$field}='{$value}'";
        if( in_array(ORDINAL,$fields) ) { $sql .= ' ORDER BY '.ORDINAL.' ASC'; }
        if( $limit > 0 ) { $sql .= ' LIMIT '.$limit; }
        $query = $this->pdo->query($sql);
        if( !$query ) { return false; }
        return ($limit==1) ? $query->fetch(PDO::FETCH_ASSOC) : $query->fetchAll(PDO::FETCH_ASSOC);
    }

/*-------------------------------------------------------------\
 * Accessor::set
 - input(table,id,data):
\-------------------------------------------------------------*/
    public function input($table,$id,$data)
    {
        $fields = array();
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table);
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) ) { $fields[] = $column['Field']; }

        if( in_array('id',$fields) AND $id != null )
        {
            $duplicate = $this->pdo->query("SELECT * FROM {$table} WHERE id='{$id}' LIMIT 1");
            if( $old = $duplicate->fetch(PDO::FETCH_ASSOC) )
            {
                $this->writeLogFile( array('Backup: update '.$table.'#'.$id.':') );
                $mod_msgs = array();
                $sql = 'UPDATE '.$table.' SET ';
                $updates = array();
                foreach( $data as $field => $value )
                {
                    if( !in_array($field,$fields) ) { continue; }
                    $old_value = (strpos($value['type'],'number')===0) ? $old[$field] : "'{$old[$field]}'";
                    $new_value = $this->encode($value['type'],$value['data']);
                    if( $old_value != $new_value )
                    {
                        $updates[] = $field . '=' . $new_value;
                        $mod_msgs[] = 'Modify ['.$field.']: ' . $old_value . ' => ' . $new_value;
                    }
                }
                $sql .= implode(',',$updates);
                $sql .= " WHERE id='{$id}' LIMIT 1";
                if( empty($updates) ) { $sql = ''; }
                $this->writeLogFile( empty($mod_msgs) ? array('Nothing change.') : $mod_msgs);
            }
        }

        if( !isset($sql) )
        {
            $field_list = array();
            $value_list = array();
            if( in_array('id',$fields) AND !isset($data['id']) )
            {
                $field_list[] = 'id';
                $value_list[] = "'{$id}'";
            }
            if( in_array(ORDINAL,$fields) )
            {
                $order = 1;
                $ordinals = $this->pdo->query('SELECT '.ORDINAL.' FROM '.$table.' ORDER BY '.ORDINAL.' DESC');
                if( $max_ordinal = $ordinals->fetch(PDO::FETCH_NUM) ) { $order = max($max_ordinal[0],$ordinals->rowCount()) + 1; }

                $field_list[] = ORDINAL;
                $value_list[] = $order;
            }
            foreach( $data as $field => $value )
            {
                if( !in_array($field,$fields) ) { continue; }
                if( in_array($field,$field_list) ) { continue; }
                $field_list[] = $field;
                $value_list[] = $this->encode($value['type'],$value['data']);
            }
            $sql = 'INSERT INTO '.$table.' ('.implode(',',$field_list).') VALUES ('.implode(',',$value_list).')';
        }

        if( !empty($sql) ) { $this->execute($sql); }
        return ($id==null) ? $this->pdo->lastInsertId() : $id;
    }

/*-------------------------------------------------------------\
 * Accessor::modify
 - arrange(table,id,reference,before_flag):
\-------------------------------------------------------------*/
    public function arrange( $table, $id, $refer, $before = true )
    {
        $this->execute('UPDATE '.$table.' SET '.ORDINAL.'='.ORDINAL.'*2');

        $new_order_query = $this->pdo->query("SELECT id,".ORDINAL." FROM {$table} WHERE id='{$refer}' LIMIT 1");
        if( $new_order_array = $new_order_query->fetch(PDO::FETCH_ASSOC) )
        {
            $new_order = intval($new_order_array[ORDINAL]) + ($before?-1:1);
            $this->execute("UPDATE {$table} SET ".ORDINAL."={$new_order} WHERE id='{$id}' LIMIT 1");
        }

        $this->pdo->exec('SET @pos := 0');
        $this->execute('UPDATE '.$table.' SET '.ORDINAL.'=( SELECT @pos := @pos + 1 ) ORDER BY '.ORDINAL.' ASC, id DESC');
    }

/*-------------------------------------------------------------\
 * Accessor::utility
 - hasTable(table)
 - translate(json,language):
\-------------------------------------------------------------*/
    public function hasTable($table) { return in_array($table,$this->tables); }

    public function translate($data,$lang = null)
    {
        $json = json_decode($data,true);
        if( empty($lang) ) { $lang = $this->default_language; }
        return $json[$lang];
    }

/*-------------------------------------------------------------\
 * Accessor::utility
 - cache(table,fields,option)
\-------------------------------------------------------------*/
    public function cache($table,$fields,$option = array())
    {
        if( !in_array($table,$this->tables) ) { return; }
        $key_field = isset($option['key']) ? $option['key'] : 'id';
        $return_type = isset($option['type']) ? strtoupper($option['type']) : '';

        $parser = array();
        $file = __DIR__ . '/caches/' . $table . '.cache.json';
        $clear = isset($option['clear']) ? ($option['clear']===true) : false;
        unset($option['clear']);
        if( !$clear AND file_exists($file) )
        {
            $cache = json_decode(file_get_contents($file),true);
            if( isset($cache['parser']) )
            {
                $parser = $cache['parser'];
                $field_need = array_keys($fields);
                $field_no = array_diff($field_need,array_keys($parser));
                if( count($field_no) == 0 )
                {
                    $result = array();
                    $list = $cache['list'];
                    if( count($field_need) == 1 AND $return_type == 'OPTION_ARRAY' )
                    {
                        $field = $field_need[0];
                        $field_type = $fields[$field];
                        $use_language = in_array($field_type,array('json:lang','json:language'));
                        $language = isset($option['language']) ? $option['language'] : $this->default_language;

                        foreach( $list as $item )
                        {
                            if( !isset($item[$key_field]) ) { continue; }
                            if( !isset($item[$field]) ) { continue; }
                            $title = ($use_language) ? $item[$field][$language] : $item[$field];
                            $result[$item[$key_field]] = $title;
                        }
                    }
                    else
                    {
                        foreach( $list as $item )
                        {
                            $sample = array();
                            foreach( $item as $key => $value )
                            {
                                if( !in_array($key,$field_need) AND $key != $key_field ) { continue; }
                                $sample[$key] = $value;
                            }
                            $result[] = $sample;
                        }
                    }
                    return $result;
                }
            }
        }

        $this->writeLogFile( array('Start caching '.$table.' ...') );
        foreach( $fields as $f => $type ) { $parser[$f] = $type; }
        $cache = array(
            'parser' => $parser,
            'list' => array()
        );
        $parser[$key_field] = isset($option['keyType']) ? $option['keyType'] : 'number:reference';
        $cache['list'] = $this->query($table,array(),$parser);
        $fp = fopen($file,'w');
        if( $fp )
        {
            fwrite($fp,kjPHP\toJSON($cache));
            fclose($fp);
            return $this->cache($table,$fields,$option);
        }
        return array();
    }
    public function decache($table)
    {
        $file = __DIR__ . '/caches/' . $table . '.cache.json';
        if( file_exists($file) )
        {
            $cache = json_decode(file_get_contents($file),true);
            $parser = $cache['parser'];
            $this->cache($table,$parser,array('clear'=>true));
        }
    }


    public function listUser($filter,$lang = null)
    {
        $list = array();
        if( empty($lang) ) { $lang = $this->default_language; }
        foreach( $this->users as $user )
        {
            if( isset($filter['type']) AND !in_array($user['type'],$filter['type']) ) { continue; }
            if( isset($filter['level']) AND ( $user['level'] < $filter['level'] ) ) { continue; }
            $list[$user['id']] = $user['name'][$lang];
        }
        return $list;
    }


/*-------------------------------------------------------------\
 * Accessor::HTMLBuilder
 - drawLanguageRows(name,data,require):
 - getSimpleHeader(table,ignore):
 - drawTable(table,header,tools):
 - drawTableByPainter(table,painter):
 - drawTableList(select,none,ignore):
\-------------------------------------------------------------*/
    public function drawLanguageRows($name,$data,$require = true)
    {
        //if( !isset($require) ) { $require = true; }
        foreach( $this->languages as $language )
        {
            $lid = $language['id'];
            $value = '';
            if( isset($data) AND isset($data[$lid]) ) { $value = ' value="' . $data[$lid] . '"'; }
            if( $require AND $lid == $this->default_language ) { $value .= ' required'; }
            echo '<tr>';
            echo '<th class="-tar">'.$language['title'].'</th>';
            echo '<td class="form-border"><input type="text" name="'.$name.'['.$lid.']"'.$value.' />';
            echo '</tr>';
        }
    }

    public function getSimpleHeader($table,$ignore = array())
    {
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table);
        $results = array();
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) )
        {
            $header = array( 'type' => 'string' );
            if( !empty($column['Comment']) ) { $header['title'] = $column['Comment']; }
            $results[$column['Field']] = $header;
        }
        return $results;
    }

    public function drawTable($table,$header,$tools)
    {
        $fields = array();
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table['name']);
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) ) { $fields[] = $column['Field']; }

        $attr = array( 'id' => 'table-of-'.$table['name'] );
        if( isset($table['id']) ) { $attr['id'] = $table['id']; }
        if( isset($table['class']) ) { $attr['class'] = $table['class']; }
        if( isset($table['style']) ) { $attr['style'] = $table['style']; }
        echo '<table';
        foreach( $attr as $attr_key => $attr_val )
        {
            if( empty($attr_val) ) { continue; }
            echo ' '.$attr_key.'="'.$attr_val.'"';
        }
        echo '>';
        if( isset($table['title']) ) { echo '<caption>'.$table['title'].'</caption>'; }

        echo '<thead><tr>';
        foreach( $header as $field => $data )
        {
            if( !in_array($field,$fields) ) { continue; }
            $style = '';
            if( isset($data['width']) ) { $style = ' style="width:'.$data['width'].'"'; }
            echo '<th'.(isset($data['class'])?' class="'.$data['class'].'"':'').$style.'>';
            echo isset($data['title']) ? $data['title'] : $field;
            echo '</th>';
        }
        if( isset($tools) ) { echo '<th style="width:80px;">工具</th>'; }
        echo '</tr></thead>';

        if( isset($tools) AND isset($tools['insert']) )
        {
            echo '<tfoot>';
            $insert = $tools['insert'];
            $title = '增加' . $table['title'];
            if( isset($insert['title']) ) { $title = $insert['title']; }
            switch( $insert['type'] )
            {
            case 'by-header':
                echo '<tr action="'.$insert['action'].'" method="post">';
                foreach( $header as $field => $data )
                {
                    echo '<td class="form-border">';
                    echo '<input type="text" name="'.$field.'" />';
                    echo '</td>';
                }
                echo '<td class="btn-like" onclick="MMNet.submitFromTableRow(this.parentNode);">'.$title.'</td>';
                echo '</tr>';
                break;
            default:
                $javascript = "MMNet.Controller.load('{$insert['pattern']}'";
                if( isset($insert['data']) ) { $javascript .= ',' . $insert['data']; }
                $javascript .= ');';
                echo '<tr>';
                echo '<td class="btn-like" colspan="'.(count($header)+1).'" onclick="'.$javascript.'">'.$title.'</td>';
                echo '</tr>';
                break;
            }
            echo '</tfoot>';
        }

        $sortable = in_array(ORDINAL,$fields);
        if( isset($table['sortable']) ) { $sortable = ( $sortable AND ( $table['sortable'] !== false ) ); }
        echo '<tbody'.($sortable?' class="sortable"':'').'>';

        $sql = 'SELECT * FROM ' . $table['name'];
        if( isset($table['where']) ) { $sql .= ' WHERE ' . $table['where']; }
        if( $sortable ) { $sql .= ' ORDER BY '.ORDINAL.' ASC'; }
        else if( isset($table['order']) ) { $sql .= ' ORDER BY '.$table['order']; }
        if( isset($table['limit']) ) { $sql .= ' LIMIT '.$table['limit']; }

        $row_query = $this->pdo->query($sql);
        if( $row_query->rowCount() > 0 )
        {
            while( $row = $row_query->fetch(PDO::FETCH_ASSOC) )
            {
                $dbid = '';
                if( $sortable AND isset($row['id']) ) { $dbid = ' data-dbid="'.$row['id'].'"'; }
                echo '<tr'.$dbid.'>';
                foreach( $header as $field => $data )
                {
                    $class = array();
                    if( isset($data['class']) ) { $class[] = $data['class']; }
                    $content = $row[$field];

                    if( !isset($data['type']) ) { $data['type'] = 'unknown'; }
                    switch( $data['type'] )
                    {
                    case 'edit':
                        $class[] = 'form-border';
                        $content = '<input class="edit-by-dbclick" type="text" name="'.$field.'" value="'.$content.'" readonly title="雙擊以修改內容" />';
                        break;
                    case 'lang':
                        $json = json_decode($content,true);
                        if( !isset($json[$this->default_language]) ) { break; }
                        $content = $json[$this->default_language];
                        break;
                    case 'user':
                        if( !isset($this->users[$content]) )
                        {
                            if( $content == 0 ) { $content = 'MMNet大神'; }
                            break;
                        }
                        $user = $this->users[$content];
                        $content = $user['name'][$this->default_language].'('.$user['account'].')';
                        break;
                    case 'date':
                        if( !isset($data['format']) ) { break; }
                        $datetime = strtotime($content);
                        $content = date($data['format'],$datetime);
                    default:
                        $colon = strpos($data['type'],':');
                        if( $colon < 0 ) { break; }
                        $ref_table = substr($data['type'],0,$colon);
                        $fields = explode('+',substr($data['type'],$colon+1));
                        if( !$this->hasTable($ref_table) ) { break; }
                        $cache_filter = array();
                        foreach( $fields as $field ) { $cache_filter[$field] = 'string'; }
                        $caches = $this->cache($ref_table,$cache_filter);
                        foreach( $caches as $cache )
                        {
                            if( $cache['id'] == $content )
                            {
                                $result = array();
                                foreach( $fields as $field ) { $result[] = $cache[$field]; }
                                $content = implode(' ',$result);
                                break;
                            }
                        }
                        break;
                    }

                    $attrs = empty($class) ? '' : ' class="'.implode(' ',$class).'"';
                    if( isset($data['tooltip']) )
                    {
                        $tooltip = $data['tooltip'];
                        if( isset($row[$tooltip]) ) { $attrs .= ' title="'.$row[$tooltip].'"'; }
                    }
                    echo '<td'.$attrs.'>'.$content.'</td>';
                }

                if( isset($tools) )
                {
                    echo '<td class="-tac">';
                    foreach( $tools as $tool => $data )
                    {
                        if( $tool == 'insert' ) { continue; }

                        $attrs = array( 'class' => 'btn-like' );
                        if( isset($data['title']) ) { $attrs['title'] = $data['title']; }
                        if( isset($data['class']) )
                        {
                            foreach( $data['class'] as $class )
                            {
                                $attrs['class'] .= ' ' . $class;
                            }
                        }
                        if( isset($data['click']) )
                        {
                            $click = $data['click'];
                            preg_match('/%(.+)%/',$data['click'],$matches);
                            foreach( $matches as $m )
                            {
                                if( !isset($row[$m]) ) { continue; }
                                $click = str_replace( '%'.$m.'%', $row[$m], $click );
                            }
                            $attrs['onclick'] = $click;
                        }

                        $show = true;
                        if( isset($data['triggers']) )
                        {
                            $show = false;
                            foreach( $data['triggers'] as $field => $values )
                            {
                                if( !isset($row[$field]) ) { continue; }
                                if( in_array($row[$field],$values) )
                                {
                                    $show = true;
                                    break;
                                }
                            }
                        }

                        if( $show ) { kjPHP\drawHTMLElement('div',$attrs,''); }
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
        }
        else
        {
            $total = count($header) + ( isset($tools) ? 1 : 0 );
            echo '<tr><td class="no-border -tac" colspan="'.$total.'">資料表中沒有資料</td></tr>';
        }
        echo '</tbody>';

        echo '</table>';
    }

    public function drawTableByPainter( $table, $painter )
    {
        $fields = array();
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table['name']);
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) ) { $fields[] = $column['Field']; }

        $sortable = ( in_array(ORDINAL,$fields) AND $painter->needSort() );
        $sql = 'SELECT * FROM ' . $table['name'];
        if( isset($table['where']) ) { $sql .= ' WHERE ' . $table['where']; }
        if( $sortable ) { $sql .= ' ORDER BY '.ORDINAL.' ASC'; }
        $this->writeLogFile(array($sql));

        $page_bar = '';
        if( isset($table['paging']) )
        {
            $paging = $table['paging'];
            $pb = isset($paging['painter']) ? $table['paging']['painter'] : new kjPHP\PageBar();

            $total_query = $this->pdo->query($sql);
            $rpp = isset($paging['items']) ? intval($paging['items']) : 20;
            if( $rpp < 1 ) { $rpp = 20; }
            $rows = $total_query->rowCount();
            $pages = ceil($rows/$rpp);

            $page = isset($paging['page']) ? intval($paging['page']) : 1;
            if( $page > $pages ) { $page = $pages; }
            if( $page < 1 ) { $page = 1; }

            $page_bar = $pb->draw($pages,$page);
            $sql .= ' LIMIT ' . $rpp . ' OFFSET ' . (($page-1)*$rpp);
        }
        $row_query = $this->pdo->query($sql);
        $rows = $row_query->fetchAll(PDO::FETCH_ASSOC);

        echo $page_bar;
        $attr = array( 'id' => 'table-of-'.$table['name'] );
        if( isset($table['id']) ) { $attr['id'] = $table['id']; }
        if( isset($table['class']) ) { $attr['class'] = $table['class']; }
        if( isset($table['style']) ) { $attr['style'] = $table['style']; }
        echo '<table';
        foreach( $attr as $attr_key => $attr_val )
        {
            if( empty($attr_val) ) { continue; }
            echo ' '.$attr_key.'="'.$attr_val.'"';
        }
        echo '>';
        if( isset($table['title']) ) { echo '<caption>'.$table['title'].'</caption>'; }
        echo '<thead>';
        $painter->drawHeader($columns);
        echo '</thead>';
        echo '<tfoot>';
        $painter->drawFooter($rows);
        echo '</tfoot>';

        echo '<tbody'.($sortable?' class="sortable"':'').'>';
        if( count($rows) > 0 )
        {
            $drawlins = 0;
            foreach( $rows as $row )
            {
                $draw = $painter->drawData($row,$this->default_language);
                if( $draw !== false ) { $drawlines++; }
            }
            if( $drawlines == 0 ) { $painter->drawNoData(); }
        } else { $painter->drawNoData(); }
        echo '</table>';
        echo $page_bar;
    }

    public function drawTableList( $select = null, $none = true, $ignores = array() )
    {
        if( $none ) { echo '<option value="">不使用資料表</option>'; }
        foreach( $this->tables as $table )
        {
            if( in_array($table,$ignores) ) { continue; }
            $selected = ( $table == $select ) ? ' selected' : '';
            echo '<option value="'.$table.'"'.$selected.'>'.$table.'</option>';
        }
    }

    /*public function drawTableFieldList( $table )
    {
        if( !$this->hasTable($table) ) { return; }
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM '.$table);
        while( $column = $columns->fetch(PDO::FETCH_ASSOC) )
        {
            $name = !empty($column['Comment']) ? $column['Comment'].'('.$column['Field'].')' : $column['Field'];
            echo '<option value="'.$column['Field'].'">'.$name.'</option>';
        }
    }*/

    
}

$database_type_list = array(
    'json' => array(
        'title' => 'JSON物件',
        'datatype' => 'MEDIUMTEXT',
        'be_null' => false,
        'default' => '{}'
    ),
    'json:list' => array(
        'title' => 'JSON清單',
        'datatype' => 'MEDIUMTEXT',
        'be_null' => false,
        'default' => '[]'
    ),
    'json:array' => array(
        'title' => 'JSON清單',
        'datatype' => 'MEDIUMTEXT',
        'be_null' => false,
        'default' => '[]'
    ),
    'json:lang' => array(
        'title' => '多語系',
        'datatype' => 'MEDIUMTEXT',
        'be_null' => false,
        'default' => '{}'
    ),
    'json:language' => array(
        'title' => '多語系',
        'datatype' => 'MEDIUMTEXT',
        'be_null' => false,
        'default' => '{}'
    ),
    'json:lines' => array(
        'title' => '多語系文章',
        'datatype' => 'MEDIUMTEXT',
        'be_null' => false,
        'default' => '{}'
    ),
    'list:comma' => array(
        'title' => '以,區隔清單',
        'datatype' => 'TEXT'
    ),
    'list:stroke' => array(
        'title' => '以|區隔清單',
        'datatype' => 'TEXT'
    ),
    'string' => array(
        'title' => '字串',
        'datatype' => 'VARCHAR(32)'
    ),
    'string:unique' => array(
        'title' => '唯一字串',
        'datatype' => 'VARCHAR(32)',
        'be_null' => false,
        'key' => 'unique'
    ),
    'string:line' => array(
        'title' => '文字',
        'datatype' => 'TEXT'
    ),
    'string:lines' => array(
        'title' => '多行文字',
        'datatype' => 'MEDIUMTEXT'
    ),
    'number' => array(
        'title' => '整數',
        'datatype' => 'INT'
    ),
    'number:unsigned' => array(
        'title' => '正整數',
        'datatype' => 'INT UNSIGNED'
    ),
    'number:reference' => array(
        'title' => '指稱碼',
        'datatype' => 'INT UNSIGNED',
        'be_null' => false,
        'default' => 0
    ),
    'number:flag' => array(
        'title' => '旗標',
        'datatype' => 'TINYINT',
        'be_null' => false,
        'default' => 0
    ),
    'datetime' => array(
        'title' => '時間',
        'datatype' => 'DATETIME'
    ),
    'datetime:now' => array(
        'title' => '現在時間',
        'datatype' => 'DATETIME',
        'be_null' => false
    )
);
