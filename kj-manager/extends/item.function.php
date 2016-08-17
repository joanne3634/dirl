<?php
define( 'ITEM_TITLE_INLINE', 0x1 );
define( 'ITEM_TITLE_SERIAL', 0x2 );
define( 'ITEM_TITLE_NUMBER', 0x4 );

function get_item_title($item_row,$option = null)
{
    if( $option == null ) { $option = ITEM_TITLE_INLINE | ITEM_TITLE_SERIAL; }
    $sperator = ( $option & ITEM_TITLE_INLINE ) ? ' ' : '<br/>';
    $title = $item_row['model'];
    if( !empty($item_row['brand']) ) { $title = $item_row['brand'] . $sperator . $title; }
    if( !empty($item_row['format']) ) { $title .= ' ' . $item_row['format']; }
    if( ( $option & ITEM_TITLE_NUMBER ) AND !empty($item_row['number']) ) { $title .= $sperator . $item_row['number']; }
    if( ( $option & ITEM_TITLE_SERIAL ) AND !empty($item_row['serial']) ) { $title .= '(' . $item_row['serial'] . ')'; }

    return $title;
}

class ItemFilter
{
    private $filter = array();
    private $terms = null;
    private $users = null;
    private $js_save = array();
    private $human_readable = array();
    function __construct() {
        global $dba;
        $this->terms = $dba->cache('term_list',array('title'=>'json:language'),array('type'=>'OPTION_ARRAY'));
        $this->users = $dba->cache('member_list',array('name'=>'json:language'),array('type'=>'OPTION_ARRAY'));
        $this->filter = array( "NOT v_state='removed'" );
    }

    public function insert($type,$key,$field,$head = null)
    {
        global $_POST;
        if( !isset($_POST[$field]) OR empty($_POST[$field]) )
        {
            $this->js_save[$field] = '_REMOVE';
            return;
        }

        $item = $_POST[$field];
        $str = $head ? $head.'：' : $field.': ';
        switch ($type)
        {
        case 'TERM':
            $i_type = gettype($item);
            if ( $i_type == 'array' )
            {
                $this->filter[] = $key . ' IN (' . implode(',',$item) . ')';
                $items = array();
                foreach( $item as $it ) { $items[] = $this->terms[$it]; }
                $str .= implode('，',$items) . '。';
            }
            else
            {
                $this->filter[] = $key . '=' . $item;
                $str .= $this->terms[$item] . '。';
            }
            break;
        case 'USER':
            $i_type = gettype($item);
            if ( $i_type == 'array' )
            {
                $this->filter[] = $key . ' IN (' . implode(',',$item) . ')';
                $items = array();
                foreach( $item as $it ) { $items[] = $this->users[$it]; }
                $str .= implode('，',$items) . '。';
            }
            else
            {
                $this->filter[] = $key . '=' . $item;
                $str .= $this->users[$item] . '。';
            }
            break;
        case 'NOTEMPTY':
            $this->filter[] = "NOT {$key}=''";
            $str .= '是。';
            break;
        default:
            $this->filter[] = "{$key} LIKE '%{$item}%'";
            $str .= '具有「' . $item . '」關鍵字。';
            break;
        }
        $this->js_save[$field] = json_encode($item);
        $this->human_readable[] = $str;
    }

    public function hasFilter() { return ( count($this->filter) > 1 ); }
    public function get() { return implode(' AND ',$this->filter); }
    public function drawHTML($title,$container,$item)
    {
        if( empty($this->human_readable) ) { return; }
        echo $title;
        echo '<' . $container . '>';
        foreach( $this->human_readable as $hr )
        {
            echo '<' . $item . '>' . $hr . '</' . $item . '>';
        }
        echo '</' . $container . '>';
    }
    public function drawScript()
    {
        echo '<script>';
        foreach( $this->js_save as $key => $json )
        {
            $js = ( $json == '_REMOVE' ) ? '[],"replace"' : $json;
            echo 'MMNet.Page.save("' . $key . '",' . $js . ');' . PHP_EOL;
        }
        echo '</script>';
    }
}