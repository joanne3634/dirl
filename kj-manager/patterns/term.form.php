<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();

$parent = null;
$thisterm = null;
if( isset($_POST['term_id']) )
{
    $thisterm = $dba->get('term_list',$_POST['term_id'],array(
        'id' => 'number:reference',
        'type' => 'number:reference',
        'keyname' => 'string',
        'title' => 'json:language',
        'data' => 'string:lines'
    ));
    $parent = $thisterm['type'];
} else { $parent = $_POST['parent']; }

$terms = $dba->cache('term_list',array(
    'type' => 'number:reference',
    'rank' => 'number',
    'keyname' => 'string',
    'title' => 'json:language'
));
function cmp($t1,$t2) {
    $dr = $t1['rank'] - $t2['rank'];
    if( $dr == 0 ) { return 0; }
    return ( $dr < 0 ? -1 : 1 );
}
usort($terms,'cmp');

$term_options = array();
$term_options['_meta_'] = '標籤分類';
foreach( $terms as $term )
{
    $title = $term['title'][$dba->default_language];
    if( isset($term_options[$term['type']]) )
    {
        $par = $term_options[$term['type']];
        $title = $par . '＞' . $title;
    }
    $term_options[$term['id']] = $title;
}
?>

<form method="post" action="act/update-term.php">
<?
if( !empty($thisterm) )
{
    echo '<input type="hidden" name="id" value="' . $thisterm['id'] . '" />';
}
?>
    <table>
        <tbody>
            <tr>
                <th>代碼*</th>
                <td class="form-border"><input type="text" name="keyname" value="<?=$thisterm['keyname']?>" /></td>
            </tr>
            <tr><th>標題*</th><td></td></tr>
            <? $dba->drawLanguageRows('title',$thisterm['title']); ?>
            <tr>
                <th>類別</th>
                <td class="form-border">
                    <? drawSelect('type',array(),$term_options,$parent,(empty($thisterm)?array():array($thisterm['id']))); ?>
                </td>
            </tr>
            <tr>
                <th>擴充資料</th>
                <td class="form-border"><textarea name="data"><?=$thisterm['data']?></textarea></td>
            </tr>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();"><?=(empty($thisterm)?'新增':'修改')?>標籤</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>
