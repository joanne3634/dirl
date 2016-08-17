<?php
if( !isset($dba) )
{
    require_once('../mod_general.php');
    require_once('../mod_database.php');
    $dba = new Accessor();
}

$iid = $_POST['id'];
$value = $_POST['value'];
$old_item = $dba->get('item_list',$iid,array(
    'member' => 'number:reference',
    'place' => 'number:reference',
    'insideof' => 'number:reference',
    'report' => 'string',
    'jsondata' => 'json'
));
$jsondata = $old_item['jsondata'];

$term_cache = $dba->cache('term_list',array('title'=>'json:language'),array('type'=>'OPTION_ARRAY'));
$member_cache = $dba->cache('member_list',array('name'=>'json:language'),array('type'=>'OPTION_ARRAY'));
$records = array();

switch($_POST['field'])
{
case 'place':
    $msg = '從 ' . $term_cache[$old_item['place']] . ' 移動到 ' . $term_cache[$value];
    $insides = $dba->query('item_list',array( 'insideof' => $iid ),array(
        'id' => 'number:reference',
        'place' => 'number:reference'
    ));
    $count_inside = count($insides);
    if( $count_inside > 0 )
    {
        foreach( $insides as $inside )
        {
            $dba->input('item_list',$inside['id'],array(
                'place' => array(
                    'type' => 'number:reference',
                    'data' => $value
                )
            ));
            $records[] = array(
                'id' => $inside['id'],
                'type' => 'auto',
                'message' => '自動從 ' . $term_cache[$inside['place']] . ' 移動到 ' . $term_cache[$value] . '。'
            );
        }
        $msg .= '，自動調整內部 ' . $count_inside . ' 樣物品的位置。';
    } else { $msg .= '。'; }
    $records[] = array( 'type' => 'move', 'message' => $msg );
    break;
case 'insideof':
    $new_outside = ( !empty($value) ) ? $dba->query('item_list',array(
        'serial' => array( 'value' => $value ),
        'limit' => 1
    ),array(
        'id' => 'number:reference',
        'model' => 'string',
        'format' => 'string',
        'serial' => 'string',
        'member' => 'number:reference',
        'place' => 'number:reference'
    )) : null;

    if( empty($new_outside) )
    {
        $outside = $dba->get('item_list',$old_item['insideof'],array(
            'model' => 'string',
            'format' => 'string',
            'serial' => 'string'
        ));
        if( empty($outside) ) { break; }
        $records[] = array(
            'type' => 'move',
            'message' => '從「'.$outside['model'].(empty($outside['format'])?'':' '.$outside['format']).'('.$outside['serial'].')」移出。'
        );
        break;
    }

    $_POST['value'] = $new_outside['id'];
    $msg = '移動到「' . $new_outside['model'];
    if( !empty($new_outside['format']) ) { $msg .= ' ' . $new_outside['format']; }
    $msg .= '(' . $new_outside['serial'] . ')」內部';

    $modified = array();
    $modata = array();
    if( $old_item['place'] != $new_outside['place'] )
    {
        $modata['place'] = array(
            'type' => 'number:reference',
            'data' => $new_outside['place']
        );
        $modified[] = '位置到 ' . $term_cache[$new_outside['place']];
    }
    if( $old_item['member'] != $new_outside['member'] )
    {
        $modata['member'] = array(
            'type' => 'number:reference',
            'data' => $new_outside['member']
        );
        $modified[] = ($new_outside['member']==0) ? '暫無使用人' : '使用人為 ' . $member_cache[$new_outside['member']];
    }
    if( !empty($modata) )
    {
        $dba->input('item_list',$iid,$modata);
        $msg .= '，自動調整' . implode('，',$modified) . '。';
    } else { $msg .= '。'; }
    $records[] = array( 'type' => 'move', 'message' => $msg );
    break;
case 'member':
    $msg = '變更使用人為 ' . $member_cache[$value];
    $inside_msg = '自動' . $msg . '。';
    if( $value == 0 )
    {
        $msg = '暫無成員使用中';
        $inside_msg = $msg . '。';
    } else { $dba->execute("UPDATE item_history SET v_state='closed' WHERE itemkey={$iid} AND type='apply'"); }
    $insides = $dba->query('item_list',array( 'insideof' => $iid ),array( 'id' => 'number:reference' ));
    $count_inside = count($insides);
    if( $count_inside > 0 )
    {
        foreach( $insides as $inside )
        {
            $dba->input('item_list',$inside['id'],array(
                'member' => array(
                    'type' => 'number:reference',
                    'data' => $value
                )
            ));
            $records[] = array(
                'id' => $inside['id'],
                'type' => 'auto',
                'message' => $inside_msg
            );
        }
        $msg .= '，自動調整內部 ' . $count_inside . ' 樣物品的使用人。';
    } else { $msg .= '。'; }
    $records[] = array( 'type' => 'shift', 'message' => $msg );
    break;
case 'status':
    if( $value == "checking" )
    {
        $dba->input('item_list',$iid,array(
            'report' => array(
                'type' => 'datetime',
                'data' => 'null'
            ),
            'reportat' => array(
                'type' => 'datetime:now',
                'data' => 'null'
            ),
            'reason' => array(
                'type' => 'datetime',
                'data' => 'null'
            ),
            'confirm' => array(
                'type' => 'datetime',
                'data' => 'null'
            )
        ));
    }
    break;
case 'report':
    $dba->input('item_list',$iid,array(
        'reportat' => array(
            'type' => 'datetime:now',
            'data' => 'now()'
        ),
        'reason' => array(
            'type' => 'string',
            'data' => $_POST['reason']
        )
    ));
    $records[] = array(
        'type' => 'report',
        'message' => '使用者回報物品財產為 ' . $value . '，' . ( empty($_POST['reason']) ? '沒有輸入理由。' : '理由是：' . $_POST['reason'] )
    );
    break;
case 'confirm':
    unset($_POST['value']);
    $update_data = array(
        'confirm' => array(
            'type' => 'datetime:now',
            'data' => 'now()'
        )
    );
    $msg = '要求使用者重新判斷物品財產的狀態。';
    if( $value !== 'false' )
    {
        $update_data['status'] = array(
            'type' => 'string',
            'data' => $old_item['report']
        );
        $msg = '確認物品財產的狀態為 ' . $old_item['report'] . '，完成盤點。';
    }
    $dba->input('item_list',$iid,$update_data);
    $records[] = array( 'type' => 'confirm', 'message' => $msg );
    break;
default:
    break;
}


foreach( $records as $record )
{
    $item_id = isset($record['id']) ? $record['id'] : $iid;
    $dba->input('item_history',null,array(
        'itemkey' => array(
            'type' => 'number:reference',
            'data' => $item_id
        ),
        'type' => array(
            'type' => 'string',
            'data' => $record['type']
        ),
        'manager' => array(
            'type' => 'number:reference',
            'data' => $_SESSION[SES_ADMIN]['id']
        ),
        'helptext' => array(
            'type' => 'string:line',
            'data' => $record['message']
        ),
        'logtime' => array(
            'type' => 'datetime:now',
            'data' => 'now()'
        )
    ));
}