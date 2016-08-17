<?php
require_once('mod_general.php');
require_once('mod_database.php');

$dba = new Accessor();
if( checkAdministrator(9) )
{
?>
<div class="-block"><a href="javascript:MMNet.view('main','experiment');">實驗功能</a></div>
<?
}
?>
<div class="-block"><a href="javascript:MMNet.view('main','general');">後台總覽</a></div>
<div class="-block"><a href="javascript:MMNet.view('main','terms');">資料標籤</a></div>
<div class="-block"><a href="javascript:MMNet.view('main','member');">實驗室成員</a></div>
<div class="-block"><a href="javascript:MMNet.view('main','history',{'table':'log','page':1});">歷史記錄</a></div>
<ul id="menu-list" class="list sortable" title="拖動以調整目錄顯示順序">
<?
    $menu_query = $dba->query(
        'page_menu',
        array(
            'v_state' => array(
                'type' => 'not',
                'value' => 'removed'
            )
        ),
        array(
            'id' => 'number:reference',
            'title' => 'lang:default'
        )
    );

    foreach( $menu_query as $menu )
    {
?>
    <li data-dbid="<?=$menu['id']?>">
        <div class="-block">
            <a href="javascript:MMNet.view('main','menupage',{'id':<?=$menu['id']?>});"><?=$menu['title']?></a>
        </div>
    </li>
<?
    }
?>
</ul>
<form action="act/insert-menu.php" method="post">
    <button type="submit">增加頁面</button>
    <div class="textfield">
        <input type="text" name="title" />
    </div>
</form>
<script>
MMNet.DragDropList.initialize("ul#menu-list","page_menu");
MMNet.Page.set("page_history.php",{
    "table": "log",
    "page": 1
});
</script>
