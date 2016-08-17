<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();

$terms = $dba->cache('term_list',array(
    'type' => 'number:reference',
    'keyname' => 'string',
    'title' => 'json:language',
    'data' => 'json'
));
$type_options = array( '0' => '(未確定)' );
$custos_options = array();
$unit_options = array( '0' => '(未確定)' );
$place_options = array();

$type_types = array();
$place_types = array();
$custos_types = array();
foreach( $terms as $term )
{
    $term_title = $term['title'][$dba->default_language];
    // Type
    if( $term['keyname'] == 'item-type' ) { $type_types[] = $term['id']; }
    if( in_array( $term['type'], $type_types ) ) { $type_options[$term['id']] = $term_title; }
    // Custos
    if( $term['keyname'] == 'custos' ) { $custos_types[] = $term['id']; }
    if( in_array( $term['type'], $custos_types ) ) { $custos_options[$term['id']] = $term_title; }
    // Place
    if( $term['keyname'] == 'location' ) { $place_types[] = $term['id']; }
    if( in_array( $term['type'], $place_types ) )
    {
        $prefix = isset($place_options[$term['type']]) ? $place_options[$term['type']] : '';
        $place_options[$term['id']] = $prefix . ' ' . $term_title;
        $place_types[] = $term['id'];
        if( isset($term['data']['unit']) AND $term['data']['unit'] ) { $unit_options[$term['id']] = $prefix . ' ' . $term_title; }
    }
}

$users = $dba->cache('member_list',array(
    'type' => 'number:reference',
    'name' => 'json:langauge'
));
$userlist = array( 0 => '(未指定)' );
foreach( $users as $user )
{
    if( !in_array($user['type'],array(1,2,3,6)) ) { continue; }
    $userlist[$user['id']] = $user['name'][$dba->default_language];
}
ksort($userlist);
?>

<datalist id="no_serial">
    <option value="無編號"></option>
    <option value="等待編號"></option>
</datalist>

<datalist id="itemlist">
<?
$serials = array();
$items = $dba->query('item_list',
array(
    'v_state' => array( 'value' => 'working' ),
    'CHAR_LENGTH(serial)' => array(
        'type' => 'bigger',
        'value' => 0
    )
),array(
    'id' => 'number:reference',
    'serial' => 'string',
    'model' => 'string:line',
    'format' => 'string:line',
    'number' => 'string:line'
));
foreach( $items as $item )
{
    if( empty($item['serial']) OR $item['serial'] == '無編號' ) { continue; }
    $serials[$item['id']] = $item['serial'];
    echo '<option value="'.$item['serial'].'">'.$item['model'].'</option>';
}
?>
</datalist>

<form method="post" action="act/update-item.php">
<?
$iid = 0;
$nowhere_term = $dba->query(
    'term_list',
    array(
        'keyname' => array( 'value' => 'nowhere' ),
        'limit' => 1
    ),
    array( 'id' => 'number:reference' )
);
$item = array(
    'serial' => '',
    'unit' => null,
    'type' => null,
    'clasname' => '',
    'brand' => '',
    'model' => '',
    'format' => '',
    'number' => '',
    'place' => $nowhere_term['id'],
    'insideof' => null,
    'since' => 'YYYY-MM-DD',
    'expiredin' => '',
    'money' => '0',
    'firm' => '',
    'firm_tel' => '',
    'custos' => null,
    'member' => 0,
    'outsider' => '',
    'jsondata' => array(),
    'note' => ''
);
$lock = '';
if( isset($_POST['item_id']) )
{
    $iid = intval($_POST['item_id']);
    $item = $dba->get('item_list',$_POST['item_id'],array(
        'serial' => 'string',
        'unit' => 'number:reference',
        'type' => 'number:reference',
        'clasname' => 'string',
        'brand' => 'string',
        'model' => 'string',
        'format' => 'string',
        'number' => 'string',
        'place' => 'number:reference',
        'insideof' => 'number:reference',
        'since' => 'datetime',
        'expiredin' => 'number',
        'money' => 'number',
        'firm' => 'json',
        'custos' => 'number:reference',
        'member' => 'number:reference',
        'outsider' => 'string:line',
        'claim' => 'string',
        'jsondata' => 'json',
        'note' => 'string:lines'
    ));
    echo '<input type="hidden" name="id" value="' . $_POST['item_id'] . '" />';
    //$lock = ' readonly';
}
?>
    <table style="float:left;width:50%;">
        <tbody>
            <tr>
                <th>編號</th>
                <td class="form-border">
                    <input type="text" name="serial" value="<?=$item['serial']?>" list="no_serial"<?=$lock?> />
                </td>
            </tr>
            <tr>
                <th>單位</th>
                <td class="form-border">
                    <? drawSelect('unit',array(),$unit_options,$item['unit']); ?>
                </td>
            </tr>
            <tr>
                <th>類別</th>
                <td class="form-border">
                    <? drawSelect('type',array(),$type_options,$item['type']); ?>
                </td>
            </tr>
            <tr>
                <th>財產名稱</th>
                <td class="form-border"><input type="text" name="classname" value="<?=$item['classname']?>" /></td>
            </tr>
            <tr>
                <th>廠牌</th>
                <td class="form-border"><input type="text" name="brand" value="<?=$item['brand']?>" /></td>
            </tr>
            <tr>
                <th>型號*</th>
                <td class="form-border"><input type="text" name="model" value="<?=$item['model']?>" required="true" /></td>
            </tr>
            <tr>
                <th>規格</th>
                <td class="form-border"><input type="text" name="format" value="<?=$item['format']?>" /></td>
            </tr>
            <tr>
                <th>序號</th>
                <td class="form-border"><input type="text" name="number" value="<?=$item['number']?>" /></td>
            </tr>
            <tr>
                <th>購入時間</th>
                <td class="form-border"><input type="text" name="since" value="<?=substr($item['since'],0,10)?>" /></td>
            </tr>
            <tr>
                <th>使用年限</th>
                <td class="form-border"><input type="number" name="expiredin" value="<?=$item['expiredin']?>" /></td>
            </tr>
            <tr>
                <th>購入金額</th>
                <td class="form-border"><input type="number" name="money" value="<?=$item['money']?>" /></td>
            </tr>
            <tr>
                <th>廠商</th>
                <td class="form-border"><input type="text" name="firm[title]" value="<?=$item['firm']['title']?>" /></td>
            </tr>
            <tr>
                <th>廠商電話</th>
                <td class="form-border"><input type="text" name="firm[phone]" value="<?=$item['firm']['phone']?>" /></td>
            </tr>
        </tbody>
    </table>
    <table style="float:left;width:50%;">
        <tbody>
            <tr>
                <th>存放位置*</th>
                <td class="form-border">
                    <? drawSelect('place',array('required'=>'true'),$place_options,$item['place']); ?>
                </td>
            </tr>
            <tr>
                <th>附屬位置</th>
                <td class="form-border"><input type="text" name="insideof" value="<?=$serials[$item['insideof']]?>" list="itemlist" /></td>
            </tr>
            <tr>
                <th>使用人</th>
                <td class="form-border">
                    <? drawSelect('member',array(),$userlist,$item['member']); ?>
                </td>
            </tr>
            <tr>
                <th>不可認領</th>
                <td class="form-border">
                    <label>
                        <input type="checkbox" name="public"<?=(($item['claim']=='public')?' checked':'')?> />
                        當使用人為未指定時，不可以在認領頁面申請。
                    </label>
                </td>
            </tr>
            <tr>
                <th>外借使用人</th>
                <td class="form-border"><input type="text" name="outsider" value="<?=$item['outsider']?>" /></td>
            </tr>
            <tr>
                <th>保管人</th>
                <td class="form-border">
                    <? drawSelect('custos',array(),$custos_options,$item['custos']); ?>
                </td>
            </tr>
            <tr>
                <th>備註</th>
                <td class="form-border"><textarea name="note"><?=$item['note']?></textarea></td>
            </tr>
            <tr><th colspan="2">附加資料</th></tr>
            <tr><td colspan="2" class="no-border">
                <table class="define-list">
                    <thead>
                        <tr>
                            <th style="text-align:center;width:180px;">鍵</th>
                            <th style="text-align:center;">值</th>
                        </tr>
                    </thead>
                    <tfoot><tr><td colspan="2" class="btn-like" style="text-align:center;" onclick="MMNet.insertKeyValuePair(this,'jsondata');">增加資料</td></tr></tfoot>
                    <tbody>
<?
$count = 0;
foreach( $item['jsondata'] as $key => $val )
{
    if( in_array($key,array('public')) ) { continue; }
?>
                        <tr>
                            <th><input type="text" name="jsondata[<?=$count?>][key]" value="<?=$key?>" /></th>
                            <td><input type="text" name="jsondata[<?=$count?>][val]" value="<?=$val?>" /></td>
                        </tr>
<?
    $count++;
}
?>
                    </tbody>
                </table>
            </td></tr>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();"><?=($iid>0)?'修改':'新增'?>實驗室財產</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>

<div class="end-of-line">

<?
if( $iid > 0 )
{
    $dba->drawTable(
        array(
            'name' => 'item_list',
            'title' => '附加物品',
            'where' => 'insideof='.$iid,
            'sortable' => false
        ),
        array(
            'serial' => array(
                'title' => '編碼',
                'class' => 'no-border -tac',
                'width' => '200px'
            ),
            'model' => array(
                'class' => 'no-border',
                'title' => '型號'
            ),
            'format' => array(
                'class' => 'no-border',
                'title' => '規格'
            ),
            'number' => array(
                'class' => 'no-border',
                'title' => '序號'
            )
        ),
        null
    );

    $dba->drawTable(
        array(
            'name' => 'item_history',
            'title' => '變更記錄',
            'where' => 'itemkey='.$iid,
            'sortable' => false,
            'order' => 'logtime DESC',
            'limit' => 10
        ),
        array(
            'logtime' => array(
                'title' => '記錄時間',
                'class' => 'no-border -tac'
            ),
            'type' => array(
                'title' => '動作',
                'class' => 'no-border -tac',
                'width' => '120px'
            ),
            'manager' => array(
                'type' => 'user',
                'title' => '管理員',
                'class' => 'no-border -tac',
                'width' => '120px'
            ),
            'helptext' => array(
                'title' => '事件內容',
                'class' => 'no-border',
            )
        ),
        null
    );
?>
    <button type="button" onclick="MMNet.view('main','history',{'table':'item_history','page':1,'item':<?=$iid?>});">更多記錄</button>
<?
}
?>
