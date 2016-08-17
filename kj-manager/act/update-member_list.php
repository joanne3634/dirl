<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();
$data = array(
    'pic' => array(
        'type' => 'string:line',
        'data' => $_POST['pic']
    ),
    'name' => array(
        'type' => 'json:language',
        'data' => $_POST['name']
    ),
    'alias' => array(
        'type' => 'json:language',
        'data' => $_POST['alias']
    ),
    'email' => array(
        'type' => 'string:line',
        'data' => $_POST['contact']['email']
    ),
    'phone' => array(
        'type' => 'string',
        'data' => $_POST['contact']['office']
    ),
    'last_mod' => array(
        'type' => 'datetime:now',
        'data' => 'now()'
    ),
    'research' => array(
        'type' => 'list:stroke',
        'data' => explode("\r\n",$_POST['research'])
    ),
    'contact' => array(
        'type' => 'json',
        'data' => $_POST['contact']
    ),
    'others' => array(
        'type' => 'json',
        'data' => $_POST['others']
    )
);

if( isset($_POST['iis_user']) )
{
    $type = $dba->get('member_type',$_POST['type'],array('name'=>'string','level'=>'number'));
    $data['iis_user'] = array(
        'type' => 'string',
        'data' => $_POST['iis_user']
    );
    $data['username'] = array(
        'type' => 'string',
        'data' => $_POST['iis_user']
    );
    if( !empty($_POST['password']) )
    {
        $data['password'] = array(
            'type' => 'string',
            'data' => md5($_POST['iis_user'].$_POST['password'])
        );
    }
    if( isset($_POST['level']) )
    {
        $data['level'] = array(
            'type' => 'number',
            'data' => isset($_POST['level']) ? $_POST['level'] : $type['level']
        );
    }
}

if( isset($_POST['type']) )
{
    $data['type'] = array(
        'type' => 'number:reference',
        'data' => $_POST['type']
    );
}

if( isset($_POST['title']) )
{
    $data['title'] = array(
        'type' => 'json:language',
        'data' => $_POST['title']
    );
}

if( isset($_POST['period']) )
{
    $data['period'] = array(
        'type' => 'string',
        'data' => sprintf('%s/%s~%s/%s',$_POST['period']['i_year'],$_POST['period']['i_mon'],$_POST['period']['o_year'],$_POST['period']['o_mon'])
    );
}

$mod_id = $dba->input( 'member_list', ( isset($_POST['id']) ? $_POST['id'] : null ), $data );
$message = (isset($_POST['id'])?'修改':'建立').'使用者資料(member_list#'.$mod_id.':'.$_POST['iis_user'].')。';
$dba->writeLog( $_SESSION[SES_ADMIN]['id'], isset($_POST['id'])?'modified':'create', $message );
$dba->decache('member_list');
die('{"status":"done"'.(( $_SESSION[SES_ADMIN]['level'] >= 5 ) ? ',"reload":"member"' : '')."}");