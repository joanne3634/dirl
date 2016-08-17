<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();
$users = $dba->query('mmnet_user',array(),array(
    'user_id' => 'string',
    'password' => 'string',
    'activated' => 'number'
));
foreach( $users as $user )
{
    $username = $user['user_id'];
    $member = $dba->query('member_list',array('iis_user'=>array('value'=>$username),'limit'=>1),array('password'=>'string'));
    if( empty($member) ) { continue; }
    $act = ( $user['activated'] == 1 ) ? 'YES' : 'NO';
    $pwd = ( md5($username.$user['password']) == $member['password'] ) ? 'ok.' : 'need sync password:' . $member['password'];
    echo sprintf('%16s (%3s) : %s' . PHP_EOL, $username, $act, $pwd );
}
