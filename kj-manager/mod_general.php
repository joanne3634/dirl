<?php
session_start();
define( 'URL_ROOT', 'http://'.$_SERVER['SERVER_NAME'].'/kj-manager/' );
define( 'IP_ADDRESS', $_SERVER['REMOTE_ADDR'] );
define( 'SES_ADMIN', 'administrator' );

function checkAdministrator($level = -1)
{
    $is_user = isset($_SESSION[SES_ADMIN]);
    if( !$is_user ) { return false; }
    $user_level = $_SESSION[SES_ADMIN]['level'];
    return ( $user_level > $level );
}

function checkIISUser() { return ( strpos(IP_ADDRESS,'140.109.') == 0 ); }
function checkMMNetUser() { return ( strpos(IP_ADDRESS,'140.109.22') == 0 ); }

function drawImageSelector($img_key,$img_val,$opt_json)
{
    $img_val = str_replace('"',"\"",$img_val);
    $opt_json = str_replace('"',"\"",$opt_json);
    echo '<input type="hidden" name="'.$img_key.'" value="'.$img_val.'" />';
    echo '<button type="button" onclick="javascript:MMNet.ImageViewer.open('.$img_val.');">檢視圖片</button>';
    echo '<button type="button" onclick="javascript:MMNet.FileManager.select(\'jpg|jpeg|gif|png\','.$opt_json.');">選擇圖片</button>';
}

function drawPageTypeOptions($select = null)
{
    $page_type_list = array(
        'basic.input' => '直接輸入',
        'markdown.input' => 'Markdown 插件',
        'table.input' => '簡易資料表',
        //'layers.input' => '群組化資料表'
    );
    foreach( $page_type_list as $value => $title )
    {
        $selected = ( $value == $select ) ? ' selected' : '';
        echo '<option value="'.$value.'"'.$selected.'>'.$title.'</option>';
    }
}

function drawSelect($name,$attr,$options,$select = null,$ignore = array())
{
    echo '<select name="'.$name.'"';
    foreach( $attr as $a_key => $a_value ) { echo ' '.$a_key.'="'.$a_value.'"'; }
    echo '>';
    foreach( $options as $value => $title )
    {
        if( in_array( $value, $ignore ) ) { continue; }
        $selected = ( $value == $select ) ? ' selected' : '';
        echo '<option value="'.$value.'"'.$selected.'>'.$title.'</option>';
    }
    echo '</select>';
}

function getTermByKeyname(&$db,$keyname,$child)
{
    $type = $db->query(
        'term_list',
        array(
            'keyname' => array( 'value' => $keyname ),
            'limit' => 1
        ),
        array( 'id' => 'number:reference' )
    );

    $types = array();
    if( $child === true )
    {
        $unsearched = array( $type['id'] );
        while( $tid = array_shift($unsearched) )
        {
            array_push($types,$tid);
            $terms = $db->query(
                'term_list',
                array( 'type' => array( 'value' => $tid ) ),
                array( 'id' => 'number:reference' )
            );
            if( empty($terms) ) { continue; }
            foreach( $terms as $term ) { array_push($unsearched,$term['id']); }
        }
    } else { $types[] = $type['id']; }
    $termlist = $db->query(
        'term_list',
        array(
            'type' => array(
                'type' => 'in',
                'values' => $types
            )
        ),
        array(
            'id' => 'number:reference',
            'type' => 'number:reference',
            'keyname' => 'string',
            'title' => 'json:language',
            'data' => 'json'
        )
    );
    return $termlist;
}