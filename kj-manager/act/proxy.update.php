<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator(7) ) { die('{"error":401,"message":"No Authority."}'); }

$dba = new Accessor();

$log = '將 Member#'.$_POST['principal'].' 的 '.$_POST['commitment'].' 權限授予 Member#'.$_POST['agent'].'。';
$handle = md5($log);
$duplicate = $dba->query('proxy',array( 'handle' => array('value'=>$handle) ),array( 'id' => 'number:reference' ));
if( !empty($duplicate) )
{
    /*$dba->insert('proxy',$duplicate['id'],array(
        'since'
    ));*/
    die('{"error":404,"message":"Duplicated."}');
}

$pin = $dba->get('member_list',intval($_POST['principal']),array( 'id' => 'number:reference', 'name' => 'json:language' ));
if( empty($pin) ) { die('{"error":404,"message":"No Principal."}'); }
$agt = $dba->get('member_list',intval($_POST['agent']),array( 'id' => 'number:reference', 'name' => 'json:language' ));
if( empty($agt) ) { die('{"error":404,"message":"No Agent."}'); }

if( $pin['id'] == $agt['id'] ) { die('{"error":202.1,"message":"Principal and Agent should be different member."}'); }

$commit_fmt = null;
switch($_POST['commitment'])
{
case 'items':
case 'itemlist':
case 'inventory':
    $commit_fmt = '%s保管的物品';
    break;
default:
    die('{"error":403,"message":"No Commitment: '.$_POST['commitment'].'."}');
    break;
}

$dba->input('proxy',null,array(
    'ordinal' => array(
        'type' => 'number',
        'data' => 1
    ),
    'handle' => array(
        'type' => 'string',
        'data' => $handle
    ),
    'title' => array(
        'type' => 'string',
        'data' => sprintf($commit_fmt,$pin['name'][$dba->default_language])
    ),
    'principal' => array(
        'type' => 'number:reference',
        'data' => $pin['id']
    ),
    'agent' => array(
        'type' => 'number:reference',
        'data' => $agt['id']
    ),
    'commitment' => array(
        'type' => 'string',
        'data' => $_POST['commitment']
    ),
    'password' => array(
        'type' => 'number',
        'data' => 'null'
    ),
    'since' => array(
        'type' => 'datetime:now',
        'data' => 'now()'
    ),
));
$dba->writeLog( $_SESSION[SES_ADMIN]['id'], 'proxy', $log );
echo '{"status":"done"}';