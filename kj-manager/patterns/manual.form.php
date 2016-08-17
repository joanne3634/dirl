<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
$dba = new Accessor();

$manual = array(
    'tree' => '/',
    'assoc' => array(),
    'caption' => 'Manual#'.date('YmdHis'),
    'content' => ''
);
if( isset($_POST['parent']) )
{
    $parent = $dba->get('manual',intval($_POST['parent']),array(
        'tree' => 'string:line',
        'caption' => 'string'
    ));
    if( !empty($parent) ) { $manual['tree'] = $parent['tree'] . $parent['caption'] . '/'; }
}
$manual_caption_list = $dba->cache('manual',array(
    'tree' => 'string:line',
    'caption' => 'string'
));
?>

<form method="post" action="act/update-manual.php">
<?
if( isset($_POST['id']) )
{
?>
    <input type="hidden" name="id" value="<?=$_POST['id']?>" />
<?
    $manual = $dba->get('manual',$_POST['id'],array(
        'tree' => 'string:line',
        'assoc' => 'json',
        'caption' => 'string',
        'content' => 'string:lines'
    ));
}
?>
    <table>
        <tbody>
            <tr>
                <th style="width:120px;">標題</th>
                <td class="form-border">
                    <input type="text" name="caption" value="<?=$manual['caption']?>" />
                </td>
            </tr>
            <tr>
                <th style="width:120px;">位置結構</th>
                <td class="form-border">
                    <input type="text" name="tree" value="<?=$manual['tree']?>" />
                </td>
            </tr>
            <tr>
                <th style="width:120px;">相關資料</th>
                <td class="no-border">
                <table class="define-list">
                    <thead>
                        <tr>
                            <th style="text-align:center;width:180px;">鍵</th>
                            <th style="text-align:center;">值</th>
                        </tr>
                    </thead>
                    <tfoot><tr><td colspan="2" class="btn-like" style="text-align:center;" onclick="MMNet.insertKeyValuePair(this,'assoc');">增加資料</td></tr></tfoot>
                    <tbody>
<?
$count = 0;
foreach( $manual['assoc'] as $key => $val )
{
?>
                        <tr>
                            <th><input type="text" name="assoc[<?=$count?>][key]" value="<?=$key?>" /></th>
                            <td><input type="text" name="assoc[<?=$count?>][val]" value="<?=$val?>" /></td>
                        </tr>
<?
    $count++;
}
?>
                    </tbody>
                </table>
                </td>
            </tr>
            <tr>
                <th style="width:120px;">內容</th>
                <td class="form-border"><textarea name="content" rows="20"><?=$manual['content']?></textarea></td>
            </tr>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();"><?=(isset($_POST['id'])?'修改':'新增')?>頁面</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>