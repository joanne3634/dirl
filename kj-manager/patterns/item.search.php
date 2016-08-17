<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();

$terms = $dba->cache('term_list',array(
    'v_state' => 'string',
    'type' => 'number:reference',
    'keyname' => 'string',
    'title' => 'json:language',
    'data' => 'json'
));
$type_options = array();
$place_options = array();

$type_types = array();
$place_types = array();
foreach( $terms as $term )
{
    if( $term['v_state'] != 'working' ) { continue; }
    $term_title = $term['title'][$dba->default_language];
    // Type
    if( $term['keyname'] == 'item-type' ) { $type_types[] = $term['id']; }
    if( in_array( $term['type'], $type_types ) ) { $type_options[$term['id']] = $term_title; }
    // Place
    if( $term['keyname'] == 'location' ) { $place_types[] = $term['id']; }
    if( in_array( $term['type'], $place_types ) )
    {
        $prefix = isset($place_options[$term['type']]) ? $place_options[$term['type']] : '';
        $place_options[$term['id']] = $prefix . ' ' . $term_title;
        $place_types[] = $term['id'];
    }
}

$member_id_list = array();
$items = $dba->cache('item_list',array(
    'v_state' => 'string',
    'serial' => 'string',
    'model' => 'string',
    'format' => 'string',
    'member' => 'number:reference'
));
echo '<datalist id="serial">';
foreach( $items as $item )
{
    if( $item['v_state'] != 'working' ) { continue; }
    if( !in_array($item['member'],$member_id_list) ) { $member_id_list[] = $item['member']; }
    echo '<option value="'.$item['serial'].'">'.$item['model'].' '.$item['format'].'</option>';
}
echo '</datalist>';

$users = $dba->cache('member_list',array(
    'type' => 'number:reference',
    'name' => 'json:langauge'
));
$userlist = array();
foreach( $users as $user )
{
    $actived = in_array($user['type'],array(1,2,3,6));
    $using = in_array($user['type'],$member_id_list);
    if( !$actived AND !$using ) { continue; }
    $userlist[$user['id']] = $user['name'][$dba->default_language];
}
ksort($userlist);
?>

<form>
    <input type="hidden" name="page" value="1" />
    <table style="float:left;">
        <tbody>
            <tr>
                <th>編號</th>
                <td class="form-border">
                    <input type="text" name="serial" list="serial" />
                </td>
            </tr>
            <tr>
                <th>類型</th>
                <td class="form-border"><? drawSelect('type',array('multiple'=>'multiple','size'=>5),$type_options); ?></td>
            </tr>
            <tr>
                <th>地點</th>
                <td class="form-border"><? drawSelect('place',array('multiple'=>'multiple','size'=>5),$place_options); ?></td>
            </tr>
            <tr>
                <th>使用者</th>
                <td class="form-border"><? drawSelect('user',array('multiple'=>'multiple','size'=>5),$userlist); ?></td>
            </tr>
            <tr>
                <th>外借物品</th>
                <td class="form-border">
                    <label>
                        <input type="checkbox" name="only_outside" />
                        只顯示外借物品
                    </label>
                </td>
            </tr>
            <tr>
                <th>外借使用人</th>
                <td class="form-border"><input type="text" name="outsider" /></td>
            </tr>
        </tbody>
    </table>
    <div class="end-of-line">
        <button type="button" onclick="MMNet.Controller.close();MMNet.view('main','itemlist',this.form,true);">搜尋</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>
<div class="end-of-line"></div>

