<?php
if( !isset($dba) )
{
    require_once('../mod_general.php');
    if( !checkAdministrator() ) { die('{"error":403,"message":"No Authority."}'); }

    require_once('../mod_database.php');
    $dba = new Accessor();
}
if( !isset($section) )
{
    if( !isset($_POST['id']) ) { die(); }
    $section = $dba->get('page_section',$_POST['id'],array(
        'id' => 'number:reference',
        'source' => 'json',
        'content' => 'json'
    ));
    $section['prefix'] = 'section['.$section['id'].']';
}

$liveupdated = isset($section['source']['liveupdate']) ? ' checked' : '';
$limit = max(intval($section['source']['limit']),0);
$tableviewid = uniqid('section');
?>

<table>
    <tbody>
        <tr>
            <th>資料表</th>
            <td class="form-border">
                <select name="<?=$section['prefix']?>[source][table]" onfocus="MMNet.Section.Table.hide($('#<?=$tableviewid?>'));">
                    <? $dba->drawTableList($section['source']['table'],false); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>篩選條件</th>
            <td class="form-border">
                <textarea class="-h50" name="<?=$section['prefix']?>[source][where]" onfocus="MMNet.Section.Table.hide($('#<?=$tableviewid?>'));"><?=$section['source']['where']?></textarea>
            </td>
        </tr>
        <tr>
            <th>筆數限制</th>
            <td class="form-border">
                <input type="number" name="<?=$section['prefix']?>[source][limit]" value="<?=$limit?>" min="0" step="1" onfocus="MMNet.Section.Table.hide($('#<?=$tableviewid?>'));" />
            </td>
        </tr>
        <tr>
            <th>動態更新</th>
            <td class="no-border">
                <label>
                    <input type="checkbox" name="<?=$section['prefix']?>[source][liveupdate]"<?=$liveupdated?> />
                    開啟動態更新，表示頁面會從後端資料庫讀取最新資料。
                </label>
            </td>
        </tr>
        <tr>
            <th class="-clickable" colspan="2" onclick="MMNet.Section.Table.show($('#<?=$tableviewid?>'),$(this).parents('form'));">檢視表格</th>
        </tr>
        <tr><td id="<?=$tableviewid?>" class="table-view" colspan="2"></td></tr>
    </tbody>
</table>

<div class="end-of-line"></div>
<textarea class="-h100 -tm10" title="前置內容(Header)" name="<?=$section['prefix']?>[content][header]"><?=kjPHP\toHTML($section['content']['header'],false)?></textarea>
<textarea class="-h200 -tm10" title="資料樣本(Sample)" name="<?=$section['prefix']?>[content][sample]"><?=kjPHP\toHTML($section['content']['sample'],false)?></textarea>
<textarea class="-h100 -tm10" title="後綴內容(Footer)" name="<?=$section['prefix']?>[content][footer]"><?=kjPHP\toHTML($section['content']['footer'],false)?></textarea>

<script>
$('input[type=number]').numeric();
MMNet.Section.Language.hide($("#s<?=$section['id']?>form"));
</script>