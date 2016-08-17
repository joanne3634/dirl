<?php
define( 'LOG_PER_PAGE', 20 );
define( 'NEAR_PAGE_LIMIT', 4 );
require_once('mod_general.php');
require_once('mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"No Authority."}'); }

$dba = new Accessor();
$user = $_SESSION[SES_ADMIN];

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
if( $page <= 0 ) { $page = 1; }

$table = isset($_POST['table']) ? $_POST['table'] : 'log';
$key_field = null;
$selector = array();
$header = array();
switch($table)
{
case 'item_history':
    $key_field = 'id';
    $selector = array(
        'name' => 'item_history',
        'title' => '物品財產變更記錄',
        'sortable' => false,
        'order' => 'logtime DESC',
    );
    $header = array(
        'logtime' => array(
            'title' => '記錄時間',
            'class' => '-tac',
            'width' => '150px'
        ),
        'type' => array(
            'title' => '動作',
            'class' => '-tac',
            'width' => '80px'
        ),
        'itemkey' => array(
            'type' => 'item_list:model+format',
            'title' => '物品',
            'class' => '-tac',
            'width' => '150px'
        ),
        'manager' => array(
            'type' => 'user',
            'title' => '管理員',
            'class' => '-tac',
            'width' => '120px'
        ),
        'helptext' => array( 'title' => '事件內容' )
    );
    $where = array();
    if( !empty($_POST['item']) )
    {
        $where[] = 'itemkey=' . $_POST['item'];
        unset($header['itemkey']);
    }
    $user = !empty($_POST['user']) ? $_POST['user'] : $user['id'];
    if( $user != 0 )
    {
        $where[] = 'manager=' . $user;
        unset($header['manager']);
    }
    if( !empty($where) ) { $selector['where'] = implode(' AND ',$where); }
    break;
case 'log':
default:
    $key_field = 'num';
    $selector = array(
        'name' => 'log',
        'title' => '歷史記錄',
        'where' => 'level<='.$user['level'].' OR user='.$user['id'],
        'order' => 'stamp DESC, num DESC'
    );
    $header = array(
        /*'num' => array(
            'title' => '#',
            'class' => '-tac',
            'width' => '50px'
        ),*/
        'stamp' => array(
            'type' => 'date',
            'format' => 'Y-m-d H:i:s',
            //'type' => 'datetime(Y-m-d H:i:s)',
            'title' => '時間',
            'class' => '-tac',
            'width' => '150px'
        ),
        'user' => array(
            'type' => 'user',
            'title' => '管理員',
            'class' => '-tac',
            'width' => '120px'
        ),
        'action' => array(
            'title' => '動作',
            'class' => '-tac',
            'width' => '100px'
        ),
        'message' => array(
            'title' => '事件'
        )
    );
    break;
}

$page_bar = '<div class="page-bar">';
$sql = 'SELECT COUNT(' . $key_field . ') FROM ' . $table;
if( isset($selector['where']) ) { $sql .= ' WHERE ' . $selector['where']; }
$count_query = $dba->_query($sql);
if( $counter = $count_query->fetch(PDO::FETCH_NUM) )
{
    if( $page > 1 )
    {
        if( $page > (NEAR_PAGE_LIMIT+1) ) { $page_bar .= '<a href="javascript:MMNet.view(\'main\',\'history\',{\'page\':1});">1</a>'; }
        $page_bar .= '<a href="javascript:MMNet.view(\'main\',\'history\',{\'page\':'.($page-1).'});"> prev </a>';
    }
    $max_page = floor( $counter[0] / LOG_PER_PAGE );
    if( ( $counter[0] % LOG_PER_PAGE ) == 0 ) { $max_page -= 1; }
    for($i=0;$i<=$max_page;$i++)
    {
        $num = $i + 1;
        if( ($page-$num) > NEAR_PAGE_LIMIT ) { continue; }
        if( ($num-$page) > NEAR_PAGE_LIMIT ) { break; }
        if( $num == $page )
        {
            $page_bar .= '<span>'.$num.'</span>';
            continue;
        }
        $page_bar .= '<a href="javascript:MMNet.view(\'main\',\'history\',{\'page\':'.$num.'});">'.$num.'</a>';
    }
    if( $page < ($max_page+1) )
    {
        $page_bar .= '<a href="javascript:MMNet.view(\'main\',\'history\',{\'page\':'.($page+1).'});"> next </a>';
        if( $page < ($max_page-NEAR_PAGE_LIMIT+1) ) { $page_bar .= '<a href="javascript:MMNet.view(\'main\',\'history\',{\'page\':'.($max_page+1).'});">'.($max_page+1).'</a>'; }
    }
}
$page_bar .= '</div>';

$selector['limit'] = LOG_PER_PAGE . ' OFFSET ' . ( ( $page - 1 ) * LOG_PER_PAGE );
echo $page_bar;
$dba->drawTable($selector,$header,null);
/*array(
    'name' => 'log',
    'title' => '歷史記錄',
    'where' => $where,
    'order' => 'stamp DESC, num DESC',
    'limit' => LOG_PER_PAGE . ' OFFSET ' . ( ( $page - 1 ) * LOG_PER_PAGE )
),,null);*/
echo $page_bar;
?>
<script>
MMNet.Page.save("table","<?=$table?>");
MMNet.Page.save("page",<?=$page?>);
MMNet.Page.save("user",<?=(empty($_POST['user'])?'null':$_POST['user'])?>);
MMNet.Page.save("item",<?=(empty($_POST['item'])?'null':$_POST['item'])?>);
</script>