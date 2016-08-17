<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : URL_ROOT;
if( checkAdministrator() )
{
    header('Location: '.$redirect);
    die();
}
$dba = new Accessor();

// the All-Mighty: MMNet大神(root)
if( checkIISUser() AND $_POST['username'] == 'root' AND $_POST['password'] == 'mmnet!!!' )
{    
    $_SESSION[SES_ADMIN] = array(
        'id' => '0',
        'name' => 'MMNet大神(root)',
        'level' => 10
    );
    $dba->writeLog( 0, 'login', '從 '.IP_ADDRESS.' 登入。' );
    header('Location: '.( isset($_POST['error']) ? $_POST['error'] : $redirect ));
    die();
}

$user = $dba->query(
    'member_list',
    array(
        'username' => array( 'value' => $_POST['username'] ),
        'limit' => 1
    ),
    array(
        'id' => 'number:reference',
        'v_state' => 'string',
        'name' => 'lang:default',
        'level' => 'number:reference',
        'username' => 'string',
        'password' => 'string'
    )
);
if( $user !== false AND $user['v_state'] != 'removed' )
{
    $name = $dba->translate($user['name'],null) . '(' . $user['username'] . ')';
    if( $user['v_state'] == 'disabled' )
    {
        $_SESSION['error'] = array(
            'messages' => array(
                '帳號已經停用。',
                '請連絡系統管理員。'
            ),
            'username' => $_POST['username'],
            'focus' => 'username'
        );
        $dba->writeLog( $user['id'], 'fail', '已凍結帳號 '.$name.' 嘗試從 '.IP_ADDRESS.' 登入。' );
        if( isset($_POST['error']) ) { $redirect = $_POST['error']; }
    }

    $md5pwd = md5($_POST['username'].$_POST['password']);
    if( $user['password'] == $md5pwd )
    {
        $_SESSION[SES_ADMIN] = array(
            'id' => $user['id'],
            'name' => $name,
            'level' => intval($user['level'])
        );
        $dba->writeLog( $user['id'], 'login', '從 '.IP_ADDRESS.' 登入。' );
    }
    else
    {
        $_SESSION['error'] = array(
            'messages' => array(
                '密碼錯誤。',
                '請檢查拼字與大小寫。',
                '如果確定密碼無誤，請連絡系統管理員。'
            ),
            'username' => $_POST['username'],
            'focus' => 'password'
        );
        $dba->writeLog( $user['id'], 'fail', '從 '.IP_ADDRESS.' 嘗試登入失敗。' );
        if( isset($_POST['error']) ) { $redirect = $_POST['error']; }
    }
}
else
{
    $_SESSION['error'] = array(
        'messages' => array(
            '沒有這個使用者。',
            '請檢查拼字與大小寫。'
        ),
        'username' => $_POST['username'],
        'focus' => 'username'
    );
    if( isset($_POST['error']) ) { $redirect = $_POST['error']; }
}
header('Location: '.$redirect);