<?php
if( !isset($iid) ) { $iid = $_POST['id']; }
if( !isset($iid) ) { die('<div style="color:red">沒有指定物品或財產</div>'); }
if( !isset($user) )
{
    require_once('../mod_general.php');
    if( !checkAdministrator() ) { die('<div style="color:red">請登入</div>'); }
    $user = $_SESSION[SES_ADMIN];
}

global $dba;
if( !isset($dba) )
{
    require_once('../mod_general.php');
    require_once('../mod_database.php');
    $dba = new Accessor();
}
$item = $dba->get('item_list',$iid,array(
    'serial' => 'string',
    'model' => 'string',
    'format' => 'string',
    'member' => 'number:reference',
    'jsondata' => 'json'
));
$item_title = '「' . $item['model'];
if( !empty($item['format']) ) { $item_title .= ' ' . $item['format']; }
$item_title .= '(' . $item['serial'] . ')」';

if( $item['member'] != 0 ) { die('<div class="error">物品財產已經有人使用</div>'); }
$jsondata = $item['jsondata'];
if( isset($jsondata['public']) ) { die('<div class="error">物品財產沒有開放認領</div>'); }

$applies = $dba->query('item_history',array(
    'v_state' => array( 'value' => 'working' ),
    'itemkey' => array( 'value' => $iid ),
    'type' => array( 'value' => 'apply' ),
    'manager' => array( 'value' => $user['id'] )
),array('id'=>'number:reference'));
$apply_status = !empty($applies);

if( $_POST['act'] )
{
    switch($_POST['act'])
    {
    case 'apply':
        //$jsondata['claim']['applier'][] = $user['id'];
        if( empty($applies) )
        {
            $dba->input('item_history',null,array(
                'itemkey' => array(
                    'type' => 'number:reference',
                    'data' => $iid
                ),
                'type' => array(
                    'type' => 'string',
                    'data' => 'apply'
                ),
                'manager' => array(
                    'type' => 'number:reference',
                    'data' => $user['id']
                ),
                'helptext' => array(
                    'type' => 'string:line',
                    'data' => '申請認領' . $item_title . '。'
                ),
                'logtime' => array(
                    'type' => 'datetime:now',
                    'data' => 'now()'
                )
            ));
        }
        $apply_status = true;
        break;
    case 'retract':
        foreach( $applies as $apply )
        {
            $dba->input('item_history',$apply['id'],array(
                'v_state' => array(
                    'type' => 'string',
                    'data' => 'hidden'
                ),
                'logtime' => array(
                    'type' => 'datetime:now',
                    'data' => 'now()'
                )
            ));
        }
        $apply_status = false;
        break;
    default:
        die('<div style="color:red">未知的行為: '.$_POST['act'].'</div>');
    }
}

if( $apply_status )
{
    echo '<div>已經申請，等待管理員批准。</div>';
    echo '<button type="button" onclick="MMNet.view(\'td#item-'.$iid.'-apply-area\',\'extends/item.apply.php\',{\'act\':\'retract\',\'id\':'.$iid.'});">撤回申請</button>';
}
else
{
    echo '<button type="button" onclick="MMNet.view(\'td#item-'.$iid.'-apply-area\',\'extends/item.apply.php\',{\'act\':\'apply\',\'id\':'.$iid.'});">申請</button>';
}