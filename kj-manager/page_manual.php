<?php
require_once('mod_general.php');
require_once('mod_database.php');
require_once('plugins/Michelf/Markdown.inc.php');
use \Michelf\Markdown;

if( !checkAdministrator() ) { die('{"error":401,"message":"No Authority."}'); }
if( !isset($_POST['ajax']) )
{
    header('Location: index.php');
    die();
}

$dba = new Accessor();
function traveling($id,$title,$tree,$limit = 0)
{
    global $dba;
    if( !empty($title) ) { echo '<div class="title">' . $title . '</div>'; }
    $base_rank = count(explode('/',$tree));

    $queue = array( array(
        'ref' => $id,
        'path' => $tree
    ) );
    while( $leaf = array_shift($queue) )
    {
        if( in_array($leaf,array('<ul>','</ul>')) )
        {
            echo $leaf;
            continue;
        }

        if( isset($leaf['title']) )
        {
            $leaf_title = $leaf['title'];
            if( $leaf['ref'] > 0 )
            {
                $href = "javascript:MMNet.view('main','manual',{'id':{$leaf['ref']}});";
                $leaf_title = '<a href="' . $href . '">' . $leaf_title . '</a>';
            }
            echo '<li>' . $leaf_title . '</li>';
        }

        $rank = count(explode('/',$leaf['path'])) - $base_rank;
        if( $limit > 0 AND $rank >= $limit ) { continue; }

        $branches = $dba->query('manual',array(
            'tree' => array( 'value' => $leaf['path'] ),
            'order' => array( 'id' => 'DESC' )
        ),array(
            'id' => 'number:reference',
            'tree' => 'string:line',
            'caption' => 'string'
        ));
        if( !empty($branches) )
        {
            array_unshift($queue,'</ul>');
            foreach( $branches as $branch )
            {
                array_unshift($queue,array(
                    'title' => $branch['caption'],
                    'ref' => $branch['id'],
                    'path' => $branch['tree'] . $branch['caption'] . '/',
                ));
            }
            array_unshift($queue,'<ul>');
        }
    }
}

$user = $_SESSION[SES_ADMIN];
$editable = ($user['level']>=5) ? ' class="editable"' : '';
$mid = 0;
if( isset($_POST['caption'])  )
{
    $try = $dba->query('manual',array( 'caption' => array( 'value' => $_POST['caption'] )),array( 'id' => 'number:reference' ));
    if( !empty($try) ) { $mid = intval($try[0]['id']); }
} else if( isset($_POST['id']) ) { $mid = intval($_POST['id']); }

if( $mid > 0 )
{
    $manual = $dba->get('manual',$mid,array(
        'tree' => 'string:line',
        'assoc' => 'json',
        'caption' => 'string',
        'content' => 'string:lines',
        'lasting' => 'datetime'
    ));
    $html = Markdown::defaultTransform($manual['content']);
?>
<h1 class="caption"><?=$manual['caption']?><span class="-small">最後更新時間<?=$manual['lasting']?></span></h1>
<aside class="tree">
<?
    traveling($mid,'子項目',$manual['tree'].$manual['caption'].'/',2);
    if( !empty($editable) )
    {
?>
    <a class="btn-like" href="javascript:MMNet.Controller.load('manual.form',{'parent':<?=$mid?>});">增加子頁</a>
<?
    }
    $parents = explode('/',rtrim($manual['tree'],'/'));
    $parent = array_pop($parents);
    if( !empty($parent) )
    {
?>
    <a class="btn-like" href="javascript:MMNet.view('main','manual',{'caption':'<?=$parent?>'});">返回上一層</a>
<?
    }
?>
</aside>
<article<?=$editable?>>
    <?=$html?>
    <? if( !empty($editable) ) { ?><div class="edit-button" onclick="MMNet.Controller.load('manual.form',{'id':<?=$mid?>});">編輯</div><? } ?>
</article>
<?
}
else
{
    echo '<h1 class="caption">目錄</h1>';
    traveling(0,null,'/');
}
?>
<script>MMNet.Page.save("id",<?=$mid?>);</script>