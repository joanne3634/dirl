<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

$section_id = $dba->input(
    'page_section',
    null,
    array(
        'menupage' => array(
            'type' => 'number',
            'data' => $_POST['menupage']
        ),
        'title' => array(
            'type' => 'lang',
            'data' => $_POST['title']
        ),
        'image' => array(
            'type' => 'string',
            'data' => $_POST['image']
        ),
        'type' => array(
            'type' => 'string',
            'data' => $_POST['type']
        ),
        'source' => array(
            'type' => 'json',
            'data' => array( 'table' => '_NONE_', 'live-update' => false )
        ),
        'content' => array(
            'type' => 'json',
            'data' => array( $dba->default_language => '內容輸入處' )
        ),
        'last_mod' => array( 'data' => 'now()' )
    )
);

$page = $dba->get( 'page_menu', $_POST['menupage'], array( 'title' => 'lang' ) );
$title = $_POST['title'][$dba->default_language];
$dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'create', '在「'.$page['title'].'」頁面中增加新內容「'.$title.'」(page_section#'.$section_id.')。' );
die('{"status":"done","page":'.$_POST['menupage'].',"section":'.$section_id.'}');