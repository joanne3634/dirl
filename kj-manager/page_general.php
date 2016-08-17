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
$user = $_SESSION[SES_ADMIN];

if( $user['level'] > 4 )
{
?>
<aside style="width:25%">
<?
    $dba->drawTable(array(
        'name' => 'language',
        'title' => '語言設定',
        'where' => "NOT v_state='removed'"
    ),array(
        'id' => array(
            'title' => 'ID',
            'class' => '-tac',
            'width' => '60px'
        ),
        'title' => array(
            'title' => '語言'
        )
    ),array(
        'insert' => array(
            'type' => 'by-header',
            'action' => 'act/insert-language.php',
            'title' => '增加語言'
        ),
        'display1' => array(
            'triggers' => array(
                'v_state' => array( 'working' )
            ),
            'class' => array( '-working' ),
            'title' => '停用語言版本',
            'click' => "MMNet.alter('language','%id%','v_state','disabled',{'target':this,'turn':'working'});"
        ),
        'display2' => array(
            'triggers' => array(
                'v_state' => array( 'disabled' )
            ),
            'class' => array( '-disabled' ),
            'title' => '啟用語言版本',
            'click' => "MMNet.alter('language','%id%','v_state','working',{'target':this,'turn':'disabled'});"
        ),
        'remove' => array(
            'class' => array( 'remove-icon' ),
            'title' => '刪除語言版本',
            'click' => "MMNet.alter('language','%id%','v_state','removed',{'redirect':'general','confirm':'確定刪除語言？'});"
        )
    ));
?>
</aside>

<table style="width:75%">
    <caption>檔案</caption>
    <thead>
        <tr>
            <th style="width:120px;">ID</th>
            <th style="width:200px;">資料夾名稱</th>
            <th>資料夾路徑</th>
            <th style="width:120px;"></th>
        </tr>
    </thead>
    <tfoot>
        <tr method="post" action="act/bind-folder.php">
            <th>增加資料位置</th>
            <td class="form-border"><input type="text" name="name" /></td>
            <td class="form-border"><input type="text" name="path" /></td>
            <td class="btn-like" onclick="MMNet.submitFromTableRow(this.parentNode)">綁定</td>
        </tr>
    </tfoot>
    <tbody>
<?
$file_query = $dba->query(
    'file_folder',
    array(
        'level' => array(
            'type' => 'smaller',
            'value' => $user['level']
        )
    ),
    array(
        'id' => 'number:reference',
        'name' => 'string',
        'path' => 'string:line'
    )
);
foreach( $file_query as $folder )
{
?>
        <tr>
            <td class="-tac"><?=$folder['id']?></td>
            <td class="-tac"><a href="javascript:MMNet.Controller.load('folder.view',{'id':<?=$folder['id']?>});"><?=$folder['name']?></a></td>
            <td><?=$folder['path']?></td>
            <td class="-tac"></td>
        </tr>
<?
}
if( count($file_query) == 0 )
{
?>
        <tr><td class="no-border -tac" colspan="4">沒有資料夾位置</td></tr>
<?
}
?>
    </tbody>
</table>

<div class="end"></div>

<table>
    <caption>資料表</caption>
    <thead>
        <tr>
            <th style="width: 60px;">工具</th>
            <th style="width:200px;">資料表</th>
            <th style="width: 60px;">數量</th>
            <th>使用頁面</th>
            <th style="width:120px;">建立日期</th>
        </tr>
    </thead>
<?
    if( $user['level'] > 7 )
    {
?>
    <tfoot>
        <tr>
            <th colspan="2">解析已存在的資料表</th>
            <td class="form-border" colspan="2"><input type="text" name="table" /></td>
            <td class="btn-like" onclick="MMNet.Controller.load('table_list.form',{'table_id':$(this).prev().children('input').prop('value')});">解析</td>
        </tr>
        <!--<tr>
            <td class="btn-like" colspan="5" onclick="MMNet.Controller.load('table.create');">建立資料表</td>
        </tr>-->
    </tfoot>
<?
    }
?>
    <tbody>
<?
    $tables = $dba->query('table_list',array(),array(
        'id' => 'string',
        'descript' => 'string:lines',
        'since' => 'datetime'
    ));
    //var_dump($tables);
    foreach( $tables as $table )
    {
        $table_name = $table['id'];
        $counter_query = $dba->_query('SELECT COUNT(id) FROM '.$table_name);
        $table_size = ( $counter = $counter_query->fetch(PDO::FETCH_NUM) ) ? $counter[0] : 0;

        $use_page = array();
        $pages = $dba->_query('SELECT m.title,m.page,s.source FROM page_section AS s LEFT JOIN page_menu AS m ON s.menupage = m.id');
        while( $page = $pages->fetch(PDO::FETCH_ASSOC) )
        {
            $source = json_decode($page['source'],true);
            if( $source['table'] == $table_name ) { $use_page[] = $dba->translate($page['title']).'('.$page['page'].')'; }
        }
?>
    <tr>
        <td class="-tac">
            <div class="btn-like edit-icon" title="修改" onclick="MMNet.Controller.load('table_list.form',{'table_id':'<?=$table_name?>'});"></div>
            <div class="btn-like add-icon" title="增加插件" onclick="MMNet.Controller.load('table_list.form',{'table_id':'<?=$table_name?>'});"></div>
        </td>
        <td class="field"><a href="javascript:MMNet.configure('tableview',{'table':'<?=$table_name?>'});"><?=$table_name?></a></td>
        <td class="-tac"><?=$table_size?></td>
        <td><?=( count($use_page) ? implode(',',$use_page) : '尚無使用頁面。' )?></td>
        <td class="-tac"><?=date('Y-m-d',strtotime($table['since']))?></td>
    </tr>
<?
        $extends = $dba->_query("SELECT * FROM table_extends WHERE tablename='{$table_name}'");
        while( $extend = $extends->fetch(PDO::FETCH_ASSOC) )
        {
            if( $extend['status'] == 'removed' ) { continue; }
            if( $extend['status'] == 'disabled' ) { continue; }
            //$use_extends[] = $extend['name'].'('.$extend['id'].')';
?>
    <tr>
        <td class="no-border"></td>
        <td class="field"> - </td>
        <td class="-tac">啟用</td>
        <td></td>
        <td class="-tac"></td>
    </tr>
<?
        }
?>
<?
    }
?>
    </tbody>
</table>
<?
}
?>

<script>
MMNet.DragDropList.initialize("table#table-of-language > tbody.sortable","language");
</script>
