<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }

$done = array();
$undonelines = array();
$dba = new Accessor();

$name_mapping = array();
$members = $dba->cache('member_list',array(
    'name' => 'json:language',
    'alias' => 'json:language'
));
foreach( $members as $member )
{
    $name_list = array();
    foreach( array('name','alias') as $key )
    {
        foreach( $member[$key] as $lang => $name )
        {
            $name_list[] = $name;
        }
    }
    $name_mapping[$member['id']] = $name_list;
}
$term_mapping = $dba->cache('term_list',array('title'=>'json:language'),array('type'=>'OPTION_ARRAY'));

$csv = fopen($_FILES['csvfile']['tmp_name'][0],'r');
$header = fgetcsv($csv);
while( $line = fgetcsv($csv) )
{
    $data = array(
        'serial' => array(
            'type' => 'string',
            'data' => ''
        ),
        'unit' => array(
            'type' => 'number:reference',
            'data' => 17
        ),
        'type' => array(
            'type' => 'number:reference',
            'data' => 0
        ),
        'classname' => array(
            'type' => 'string:line',
            'data' => ''
        ),
        'brand' => array(
            'type' => 'string:line',
            'data' => ''
        ),
        'model' => array(
            'type' => 'string:line',
            'data' => ''
        ),
        'format' => array(
            'type' => 'string:line',
            'data' => ''
        ),
        'number' => array(
            'type' => 'string:line',
            'data' => ''
        ),
        'place' => array(
            'type' => 'number:reference',
            'data' => 68
        ),
        'insideof' => array(
            'type' => 'number:reference',
            'data' => 0
        ),
        'since' => array(
            'type' => 'string',
            'data' => '0000-00-00'
        ),
        'money' => array(
            'type' => 'number',
            'data' => 0
        ),
        'expiredin' => array(
            'type' => 'number',
            'data' => 0
        ),
        'firm' => array(
            'type' => 'json',
            'data' => array()
        ),
        'custos' => array(
            'type' => 'number:reference',
            'data' => 110
        ),
        'member' => array(
            'type' => 'number:reference',
            'data' => 0
        ),
        'outsider' => array(
            'type' => 'string:line',
            'data' => ''
        ),
        'jsondata' => array(
            'type' => 'json',
            'data' => array()
        ),
        'note' => array(
            'type' => 'string:lines',
            'data' => ''
        )
    );

    $exception = array();
    foreach( $_POST['item'] as $num => $f_data )
    {
        $column = $f_data['field'];
        if( $column == '_none_' ) { continue; }
        $d = trim($line[$num],' ');

        if( $column == 'unit' )
        {
            switch(strtolower($d))
            {
            case 'citi': $data['unit']['data'] = 111; break;
            case 'iis':  $data['unit']['data'] = 17;  break;
            default:
                break;
            }
            continue;
        }
        if( $column == 'since' )
        {
            $d = str_replace('/','-',$d);
        }

        switch($f_data['type'])
        {
        case 'append':
            $data[$column]['data'] .= $d;
            break;
        case 'replace':
            $data[$column]['data'] = $d;
            break;
        case 'member':
            $member = null;
            foreach( $name_mapping as $mid => $namelist )
            {
                if( in_array($d,$namelist) )
                {
                    $member = $mid;
                    break;
                }
            }
            if( $member !== null )
            {
                $data[$column] = array(
                    'type' => 'number:reference',
                    'data' => $member
                );
            } else { $exception[] = '無法辨識的成員「' . $d . '」'; }
            break;
        case 'term':
            $matched = array();
            foreach( $term_mapping as $tid => $term_pattern )
            {
                $m = array();
                $r = preg_match('/.*'.$term_pattern.'$/',$d,$m);
                if( $r > 0 )
                {
                    $matched[] = array(
                        'term' => $tid,
                        'size' => count($m) - 1,
                        'title' => str_replace('.+?',' ',$term_pattern)
                    );
                }
            }
            if( !empty($matched) )
            {
                $match = array_pop($matched);
                $data[$column] = array(
                    'type' => 'number:reference',
                    'data' => $match['term']
                );
            } else { $exception[] = '無法辨識的標籤「' . $d . '」'; }
            break;
        case 'json':
            $keyword = $f_data['data'];
            if( empty($keyword) ) { $keyword = $header[$num]; }
            if( !isset($data[$column]) )
            {
                $data[$column] = array(
                    'type' => 'json',
                    'data' => array( $keyword => $d )
                );
            } else { $data[$column]['data'][$keyword] = $d; }
            break;
        case 'list':
            if( !isset($data[$column]) )
            {
                $data[$column] = array(
                    'type' => 'list',
                    'data' => array( $d )
                );
            } else { $data[$column]['data'][] = $d; }
            break;
        default:
            break;
        }
    }
    if( !empty($exception) ) { $data['note']['data'] .= PHP_EOL . implode(PHP_EOL,$exception); }

    $iid = $dba->input('item_list',null,$data);
    if( $iid > 0 ) { $done[] = $iid; } else { $undonelines[] = implode(',',$line); }
}
fclose($csv);

$dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'import', '匯入 '.count($done).' 筆物品資料。' );
if( !empty($undonelines) )
{
    $err = fopen('log/item_import_fail.csv','w');
    fwrite($err,implode(',',$header).PHP_EOL);
    fwrite($err,implode(PHP_EOL,$undonelines));
    fclose($err);
}
die('{"status":"done"'.(( $_SESSION[SES_ADMIN]['level'] >= 5 ) ? ',"reload":"itemlist"' : '')."}");