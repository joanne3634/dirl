<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }

$done = array();

$dba = new Accessor();

$type_id = intval($_POST['type']);
$parent = $dba->get('term_list',$type_id,array(
    'rank' => 'number',
    'title' => 'json:language'
));

if( empty($parent) ) { die('{"error":404,"message":"No term_list#'.$_POST['type'].'."}'); }

$csv = fopen($_FILES['csvfile']['tmp_name'][0],'r');
$header = fgetcsv($csv);
while( $line = fgetcsv($csv) )
{
    $data = array(
        'rank' => array(
            'type' => 'number',
            'data' => $parent['rank'] + 1
        ),
        'type' => array(
            'type' => 'number:reference',
            'data' => $type_id
        ),
        'since' => array(
            'type' => 'datetime:now',
            'data' => 'now()'
        )
    );

    foreach( $_POST['item'] as $num => $f_data )
    {
        $column = $f_data['field'];
        if( $column == '_none_' ) { continue; }
        $d = $line[$num];
        if( $column == 'keyname' )
        {
            $ds = explode('/',$d);
            if( count($ds) > 1 ) {
                $keyname = array_pop($ds);
                $type = array_pop($ds);
                $type_term = $dba->query(
                    'term_list',
                    array(
                        'keyname' => array( 'value' => $type ),
                        'limit' => 1
                    ),
                    array(
                        'id' => 'number:reference',
                        'rank' => 'number'
                    )
                );
                $data['type']['data'] = $type_term['id'];
                $data['rank']['data'] = $type_term['rank'] + 1;
                $data['keyname'] = array( 'type' => 'string', 'data' => $keyname );
            } else { $data['keyname'] = array( 'type' => 'string', 'data' => $d ); }
            continue;
        }

        switch($f_data['type'])
        {
        case 'string':
            $data[$column] = array( 'value' => $d );
            break;
        case 'json':
            if( !isset($data[$column]) )
            {
                $data[$column] = array(
                    'type' => 'json',
                    'data' => array(
                        $f_data['data'] => $d
                    )
                );
            } else { $data[$column]['data'][$f_data['data']] = $d; }
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

    $tid = $dba->input('term_list',null,$data);
    if( $tid > 0 ) { $done[] = $tid; }
}
fclose($csv);

$dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'import', '匯入 '.count($done).' 筆標籤資料。' );
die('{"status":"done"'.(( $_SESSION[SES_ADMIN]['level'] >= 5 ) ? ',"reload":"terms"' : '')."}");