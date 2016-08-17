<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }

$dba = new Accessor();
$iid = isset($_POST['id']) ? $_POST['id'] : null;

$parent = 0;
if( !empty($_POST['insideof']) )
{
    $item_parent = $dba->query('item_list',array(
        'serial' => array( 'value' => $_POST['insideof'] ),
        'limit' => 1
    ),array( 'id' => 'number:reference' ));
    $parent = empty($item_parent) ? 0 : $item_parent['id'];
}

$place = intval($_POST['place']);
$member = intval($_POST['member']);
$jsondata = array();

if( $iid != null )
{
    $old_item_info = $dba->get('item_list',$iid,array(
        'member' => 'number:reference',
        'outsider' => 'string',
        'custos' => 'number:reference',

        'place' => 'number:reference',
        'insideof' => 'number:reference',
        'jsondata' => 'json'
    ));
    $jsondata = $old_item_info['jsondata'];

    $term_cache = $dba->cache('term_list',array('title'=>'json:language'),array('type'=>'OPTION_ARRAY'));
    $member_cache = $dba->cache('member_list',array('name'=>'json:language'),array('type'=>'OPTION_ARRAY'));
    $records = array();

    if( $place != $old_item_info['place'] )
    {
        $records[] = array(
            'type' => 'move',
            'text' => '從 ' . $term_cache[$old_item_info['place']] . ' 移動到 ' . $term_cache[$place] . '。'
        );
    }

    if( $parent > 0 AND $parent != $old_item_info['insideof'] )
    {
        $new_outside = $dba->get('item_list',$parent,array(
            'model' => 'string',
            'format' => 'string',
            'serial' => 'string',

            'member' => 'number:reference',
            'place' => 'number:reference'
        ));

        if( empty($new_outside) )
        {
            $outside = $dba->get('item_list',$old_item_info['insideof'],array(
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

        $msg = '移動到「' . $new_outside['model'];
        if( !empty($new_outside['format']) ) { $msg .= ' ' . $new_outside['format']; }
        $msg .= '(' . $new_outside['serial'] . ')」內部';

        $modified = array();
        $modata = array();
        if( $old_item_info['place'] != $new_outside['place'] )
        {
            $modata['place'] = array(
                'type' => 'number:reference',
                'data' => $new_outside['place']
            );
            $modified[] = '位置到 ' . $term_cache[$new_outside['place']];
        }
        if( $old_item_info['member'] != $new_outside['member'] )
        {
            $modata['member'] = array(
                'type' => 'number:reference',
                'data' => $new_outside['member']
            );
            $modified[] = '使用人為 ' . $member_cache[$new_outside['member']];
        }
        if( !empty($modata) )
        {
            $dba->input('item_list',$iid,$modata);
            $msg .= '，自動調整' . implode('，',$modified) . '。';
        } else { $msg .= '。'; }
        $records[] = array( 'type' => 'move', 'message' => $msg );
    }

    if( intval($_POST['custos']) != $old_item_info['custos'] )
    {
        $records[] = array(
            'type' => 'shift',
            'text' => '變更為 ' . $term_cache[intval($_POST['custos'])] . ' 保管。'
        );
    }

    if( $member != $old_item_info['member'] )
    {
        $text = '變更使用人為 ' . $member_cache[$member] . '。';
        if( $member == 0 ) { $text = '暫無成員使用中。'; }
        $records[] = array(
            'type' => 'shift',
            'text' => $text
        );
    }

    if( $_POST['outsider'] != $old_item_info['outsider'] )
    {
        $msg = '變更外借使用人為 ' . $_POST['outsider'] . '。';
        if( empty($_POST['outsider']) ) { $msg = '移除外借使用人。'; }
        $records[] = array(
            'type' => 'shift',
            'text' => $msg
        );
    }

    $insides = $dba->query('item_list',array( 'insideof' => $iid ),array(
        'id' => 'number:reference',
        'place' => 'number:reference',
        'member' => 'number:reference'
    ));
    $count_inside = count($insides);
    if( $count_inside > 0 )
    {
        foreach( $insides as $inside )
        {
            $dba->input('item_list',$inside['id'],array(
                'place' => array(
                    'type' => 'number:reference',
                    'data' => $place
                ),
                'member' => array(
                    'type' => 'number:reference',
                    'data' => $member
                )
            ));
            $mod_msg = array();
            if( $inside['place'] != $place ) { $mod_msg[] = '位置到 ' . $term_cache[$place]; }
            if( $inside['member'] != $member ) { $mod_msg[] = '使用人為 ' . $member_cache[$member]; }
            if( !empty($mod_msg) )
            {
                $records[] = array(
                    'id' => $inside['id'],
                    'type' => 'auto',
                    'text' => '自動調整' . implode('，',$mod_msg) . '。'
                );
            }
        }
        $records[] = array(
            'type' => 'auto',
            'text' => '自動調整內部 ' . $count_inside . ' 樣物品的位置與使用人。'
        );
    }

    foreach( $records as $line )
    {
        $item_key = isset($line['id']) ? $line['id'] : $iid;
        $dba->input('item_history',null,array(
            'itemkey' => array(
                'type' => 'number:reference',
                'data' => $item_key
            ),
            'type' => array(
                'type' => 'string',
                'data' => $line['type']
            ),
            'manager' => array(
                'type' => 'number:reference',
                'data' => $_SESSION[SES_ADMIN]['id']
            ),
            'helptext' => array(
                'type' => 'string:line',
                'data' => $line['text']
            ),
            'logtime' => array(
                'type' => 'datetime:now',
                'data' => 'now()'
            )
        ));
    }
}

if( isset($_POST['jsondata']) )
{
    $set_key = array();
    foreach( $_POST['jsondata'] as $line )
    {
        $key = $line['key'];
        if( !isset($key) OR empty($key) ) { continue; }
        $set_key[] = $line['key'];
        $jsondata[$key] = $line['val'];
    }
    foreach( $jsondata as $key => $value )
    {
        if( in_array($key,$set_key) ) { continue; }
        unset($jsondata[$key]);
    }
    unset($jsondata['public']);
}

$item_id = $dba->input('item_list',$iid,array(
    'serial' => array(
        'type' => 'string',
        'data' => $_POST['serial']
    ),
    'unit' => array(
        'type' => 'number:reference',
        'data' => intval($_POST['unit'])
    ),
    'type' => array(
        'type' => 'number:reference',
        'data' => intval($_POST['type'])
    ),
    'classname' => array(
        'type' => 'string:line',
        'data' => $_POST['classname']
    ),
    'brand' => array(
        'type' => 'string:line',
        'data' => $_POST['brand']
    ),
    'model' => array(
        'type' => 'string:line',
        'data' => $_POST['model']
    ),
    'format' => array(
        'type' => 'string:line',
        'data' => $_POST['format']
    ),
    'number' => array(
        'type' => 'string:line',
        'data' => $_POST['number']
    ),
    'place' => array(
        'type' => 'number:reference',
        'data' => $place
    ),
    'insideof' => array(
        'type' => 'number:reference',
        'data' => $parent
    ),
    'since' => array(
        'type' => 'string',
        'data' => $_POST['since']
    ),
    'money' => array(
        'type' => 'number',
        'data' => intval($_POST['money'])
    ),
    'expiredin' => array(
        'type' => 'number',
        'data' => intval($_POST['expiredin'])
    ),
    'firm' => array(
        'type' => 'json',
        'data' => $_POST['firm']
    ),
    'custos' => array(
        'type' => 'number:reference',
        'data' => intval($_POST['custos'])
    ),
    'member' => array(
        'type' => 'number:reference',
        'data' => $member
    ),
    'outsider' => array(
        'type' => 'string:line',
        'data' => $_POST['outsider']
    ),
    'claim' => array(
        'type' => 'string',
        'data' => isset($_POST['public']) ? 'public' : 'claimable'
    ),
    'jsondata' => array(
        'type' => 'json',
        'data' => $jsondata
    ),
    'note' => array(
        'type' => 'string:lines',
        'data' => $_POST['note']
    )
));
$message = (isset($_POST['id'])?'修改':'建立').'財產物品(item_list#'.$item_id.')。';
$dba->writeLog( $_SESSION[SES_ADMIN]['id'], isset($_POST['id'])?'modify':'create', $message );
$dba->cache('item_list',array(
    'serial' => 'string',
    'model' => 'string',
    'format' => 'string',
    'number' => 'string'
),array('clear'));

die('{"status":"done"'.(( $_SESSION[SES_ADMIN]['level'] >= 5 ) ? ',"reload":"itemlist"' : '')."}");