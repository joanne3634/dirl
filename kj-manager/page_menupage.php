<?php
require_once('mod_general.php');
require_once('mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"No Authority."}'); }
if( !isset($_POST['ajax']) )
{
    header('Location: index.php');
    die();
}
$dba = new Accessor();

$states = array(
    array(
        'key' => 'common',
        'name' => '顯示給所有人'
    ),
    array(
        'key' => 'member',
        'name' => '成員可見'
    ),
    array(
        'key' => 'hidden',
        'name' => '不在目錄欄顯示'
    ),
    array(
        'key' => 'disabled',
        'name' => '停用'
    )
);

$section_states = array(
    array(
        'key' => 'visible',
        'name' => '所有人可見'
    ),
    array(
        'key' => 'member',
        'name' => '成員可見'
    ),
    array(
        'key' => 'disabled',
        'name' => '停用'
    )
);

$page = $dba->get(
    'page_menu',
    $_POST['id'],
    array(
        'id' => 'number:reference',
        'v_state' => 'string',
        'title' => 'json:lang',
        'image' => 'string:line',
        'page' => 'string',
        'folder' => 'string',
        'last_mod' => 'datetime'
    )
);

$page_name = $page['title'][$dba->default_language];
if( !$page )
{
    echo 'No such page.';
    die();
}
?>
<h1 class="caption"><?=$page_name?><span class="-small">最後一次更新：<?=$page['last_mod']?>。</span></h1>
<section class="dashboard">
    <form>
        <input type="hidden" name="id" value="<?=$page['id']?>" />
        <table style="float:left;width:65%;">
            <tbody>
                <tr><th>名稱</th></tr>
                <? $dba->drawLanguageRows('title',$page['title']); ?>
            </tbody>
        </table>
        <table style="float:left;width:35%;">
            <tbody>
                <tr>
                    <th>顯示狀態</th>
                    <td class="form-border">
                        <select name="visibility">
<?
foreach( $states as $state )
{
    $selected = ($state['key']==$page['v_state']) ? ' selected' : '';
    echo '<option value="'.$state['key'].'"'.$selected.'>'.$state['name'].'</option>';
}
?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>使用圖片</th>
                    <td class="no-border"><? drawImageSelector('image',$page['image'],"{'normal':'一般','hover':'滑鼠移上去','active':'使用中'}"); ?></td>
                </tr>
                <tr>
                    <th>連結名稱</th>
                    <td class="form-border"><input type="text" name="page" value="<?=$page['page']?>" /></td>
                </tr>
                <tr>
                    <th>資料夾</th>
                    <td class="form-border"><input type="text" name="folder" value="<?=$page['folder']?>" /></td>
                </tr>
            </tbody>
        </table>
    </form>
    <div class="end-of-line"></div>
    <div id="remote">
        <a class="btn-like" href="javascript:MMNet.savePage('preview');">預覽頁面</a>
        <a class="btn-like" href="javascript:MMNet.savePage('update');">更新頁面</a>
        <a class="btn-like" href="javascript:MMNet.alter('page_menu',<?=$page['id']?>,'v_state','removed',{'confirm':'確定要刪除「<?=$page_name?>」','reload':true});">刪除頁面</a>
        <a class="btn-like" href="javascript:MMNet.savePage();">儲存頁面</a>
        <a class="btn-like" href="javascript:MMNet.Controller.load('section.form',{'menu':<?=$page['id']?>});">增加內容</a>
        <div class="end-of-line"></div>
    </div>
</section>

<ul id="sections" class="list">
<?
$sections = $dba->query(
    'page_section',
    array(
        'menupage' => array( 'value' => $page['id'] ),
        'v_state' => array( 'type' => 'not', 'value' => 'removed' )
    ),
    array(
        'id' => 'number:reference',
        'v_state' => 'string',
        'type' => 'string',
        'title' => 'json:language',
        'image' => 'json:language',
        'source' => 'json',
        'content' => 'json'
    )
);
foreach( $sections as $section )
{
    $section['prefix'] = 'section['.$section['id'].']';
?>
<li data-dbid="<?=$section['id']?>">
    <h2><?=$section['title'][$dba->default_language]?></h2>

    <form id="s<?=$section['id']?>form">
        <table style="float:left;width:60%;">
            <tbody>
                <tr>
                    <th>內容標題</th>
                    <? $dba->drawLanguageRows($section['prefix'].'[title]',$section['title'],false); ?>
                </tr>
            </tbody>
        </table>

        <table style="float:left;width:40%;">
            <tbody>
                <tr>
                    <th>顯示狀態</th>
                    <td class="form-border">
                        <select name="<?=$section['prefix']?>[state]">
<?
    foreach( $section_states as $state )
    {
        $selected = ($state['key']==$section['v_state']) ? ' selected' : '';
        echo '<option value="'.$state['key'].'"'.$selected.'>'.$state['name'].'</option>';
    }
?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>標題圖片</th>
                    <td class="no-border"><? drawImageSelector($section['prefix'].'[image]','',"{'TC':'中文標題圖片','EN':'英文標題圖片'}"); ?></td>
                </tr>
                <tr>
                    <th>頁面模版</th>
                    <td class="form-border">
                        <select name="<?=$section['prefix']?>[type]" onchange="MMNet.Section.load(this.value,{'id':<?=$section['id']?>},$(this.form).children('.section-content'));">
                            <? drawPageTypeOptions($section['type']); ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="end-of-line"></div>
        <div class="language-selector">
<?
    $langs = $dba->query(
        TABLE_LANG,
        array(
            'v_state' => array( 'value' => 'working' )
        ),
        array(
            'id' => 'string',
            'title' => 'string'
        )
    );
    foreach( $langs as $language )
    {
        $aid = $language['id'];
        kjPHP\drawHTMLElement('a',array(
            'id' => 's'.$section['id'].$language['id'],
            'class' => 'btn-like',
            'href' => "javascript:MMNet.Section.select('{$language['id']}',$('#s{$section['id']}{$language['id']}'));"
        ),$language['title']);
    }   
?>
            <div class="end-of-line"></div>
        </div>
        <div class="section-content"><? include('patterns/'.$section['type'].'.php'); ?></div>
        <div class="end-of-line"></div>
    </form>

</li>
<?
}
?>
</ul>

<script type="text/javascript">
MMNet.DragDropList.initialize("ul#sections","page_section",{"handle":"h2"});
MMNet.Page.save("id",<?=$_POST['id']?>);
</script>