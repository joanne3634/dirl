<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();
$tid = isset($_POST['id']) ? $_POST['id'] : null;
$rank = 1;
$keycode = strtolower($_POST['keyname']);
$belong = '';
$type = $_POST['type'];
if( $type != '_meta_' )
{
    $type_term = $dba->get('term_list',intval($type),array(
        'id' => 'number:reference',
        'rank' => 'number',
        'title' => 'json:language'
    ));
    if( !empty($type_term) )
    {
        $rank = intval($type_term['rank']) + 1;
        $type = intval($type_term['id']);
        $belong = '在標籤「' . $type_term['title'][$dba->default_language] . '(term_list#'.$type.')」下';
        $dba->input('term_list',$type,array(
            'count' => array(
                'type' => 'number',
                'data' => 'count+1'
            )
        ));
    }
} else { $type = 0; }

$term_id = $dba->input(
    'term_list',
    $tid,
    array(
        'rank' => array(
            'type' => 'number',
            'data' => $rank
        ),
        'type' => array(
            'type' => 'string',
            'data' => $type
        ),
        'keyname' => array(
            'type' => 'string',
            'data' => $keycode
        ),
        'title' => array(
            'type' => 'json:language',
            'data' => $_POST['title']
        ),
        'data' => array(
            'type' => 'string:lines',
            'data' => $_POST['data']
        ),
        'since' => array(
            'type' => 'datetime:now',
            'data' => 'now()'
        )
    )
);

$message = $belong . (isset($_POST['id'])?'修改':'建立').'標籤(term_list#'.$term_id.':'.$keycode.')。';
$dba->writeLog( $_SESSION[SES_ADMIN]['id'], isset($_POST['id'])?'modify':'create', $message );
$dba->cache('term_list',array(
    'type' => 'number:reference',
    'keyname' => 'string',
    'title' => 'json:language'
),array('clear'=>true));

die('{"status":"done"'.(( $_SESSION[SES_ADMIN]['level'] >= 5 ) ? ',"reload":"terms"' : '')."}");