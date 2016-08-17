<?php
require_once('../mod_general.php');
if( checkAdministrator() )
{
    require_once('../mod_database.php');
    $pdo = new Accessor();
    $pdo->writeLog( $_SESSION[SES_ADMIN]['id'], 'logout', '在 '.IP_ADDRESS.' 登出。' );
    unset($_SESSION[SES_ADMIN]);
}
$redirect = URL_ROOT . 'index.php';
if( isset($_POST['redirect']) ) { $redirect = $_POST['redirect']; }
if( isset($_GET['redirect']) ) { $redirect = $_GET['redirect']; }
header('Location: '.$redirect);
die();