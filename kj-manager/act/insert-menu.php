<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : URL_ROOT;
if( !empty($_POST['title']) AND checkAdministrator() ) {
    $dba = new Accessor();
    $pageid = $dba->_input(
        'page_menu',
        null,
        array(
            'v_state' => array(
                'type' => 'string',
                'data' => 'hidden'
            ),
            'title' => array(
                'type' => 'lang',
                'data' => array( $dba->default_language => $_POST['title'] )
            ),
            'page' => array(
                'type' => 'string',
                'data' => '_new_page_'
            ),
            'last_mod' => array(
                'data' => 'now()'
            )
        )
    );
    $dba->_input('page_menu',$pageid,array( 'page' => array('type'=>'string','data'=>'page'.$pageid) ));
    $dba->writeLog($_SESSION[SES_ADMIN]['id'],'create','建立新頁面：'.$_POST['title'].' (page_menu#'.$pageid.')。');
}

header('Location: '.$redirect);
die();