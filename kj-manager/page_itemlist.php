<?php
require_once('mod_general.php');
require_once('mod_database.php');
require_once('extends/item.function.php');

if( !checkAdministrator(5) ) { die('{"error":401,"message":"No Authority."}'); }
if( !isset($_POST['ajax']) )
{
    header('Location: index.php');
    die();
}

$dba = new Accessor();

class ItemListPainter implements AccessorTablePainter
{
    private $types = array();
    private $places = array();
    private $status = array();

    private $items = array();
    private $members = array();
    private $actived = array();
    function __construct($data,$lang)
    {
        $type_types = array();
        $place_types = array();
        $status_types = array();
        foreach( $data['terms'] as $term )
        {
            // Type
            if( $term['keyname'] == 'item-type' ) { $type_types[] = $term['id']; }
            if( in_array( $term['type'], $type_types ) ) { $this->types[$term['id']] = $term['title'][$lang]; }
            // Place
            if( $term['keyname'] == 'location' ) { $place_types[] = $term['id']; }
            if( in_array( $term['type'], $place_types ) )
            {
                $prefix = isset($this->places[$term['type']]) ? $this->places[$term['type']] : '';
                $this->places[$term['id']] = $prefix . ' ' . $term['title'][$lang];
                $place_types[] = $term['id'];
            }
            // Status
            if( $term['keyname'] == 'item-status' ) { $status_types[] = $term['id']; }
            if( in_array( $term['type'], $status_types ) ) { $this->status[$term['keyname']] = $term['title'][$lang]; }
        }

        echo '<datalist id="serial-list">';
        foreach( $data['users'] as $user )
        {
            if( in_array($user['type'],array(1,2,3,6)) )
            {
                $this->actived[$user['id']] = $user['name'][$lang];
            }
            $this->members[$user['id']] = $user['name'][$lang];
        }
        $this->actived[0] = '(未指定)';
        ksort($this->actived);

        foreach( $data['items'] as $item )
        {
            if( empty($item['serial']) OR $item['serial'] == '無編號' ) { continue; }
            $user = ( $item['member'] > 0 ) ? '(' . $this->members[$item['member']] . ')' : '';
            echo '<option value="'.$item['serial'].'">'.$item['model'].$user.'</option>';
            $this->items[$item['id']] = $item['serial'];
        }
        ksort($this->items);
        echo '</datalist>';
    }

    public function drawHeader($columns)
    {
        echo '<tr>';
        echo '<th style="width:180px;">類型/編號</th>';
        echo '<th>廠牌型號</th>';
        echo '<th style="width:300px;">位置</th>';
        echo '<th style="width:100px;">使用者</th>';
        echo '<th style="width:200px;">狀態</th>';
        echo '</tr>';
        return 5;
    }

    public function drawFooter($result) {}
    public function drawNoData() { echo '<tr><td class="no-border -tac" colspan="5">沒有任何財產</td></tr>'; }
    public function needSort() { return false; }

    public function drawData($row,$lang)
    {
        //echo '<tr data-dbid="'.$row['id'].'">';
        echo '<tr>';
        $type = isset($this->types[$row['type']]) ? $this->types[$row['type']] : '未知類型('.$row['type'].')';
        echo '<td class="btn-like -tac" onclick="MMNet.Controller.load(\'item.form\',{\'item_id\':'.$row['id'].'});">' . $type . '<br/>' . $row['serial'] . '</td>';
        $title = get_item_title($row,ITEM_TITLE_NUMBER);
        echo '<td class="-tac">' . $title . '</td>';

        // Location Editor
        echo '<td>';
        echo '<label class="block-label">存放於 ';
        echo '<select onchange="MMNet.alter(\'item_list\','.$row['id'].',\'place\',this.value,{\'redirect\':\'itemlist\',\'confirm\':\'確定變換物品存放位置？\'});">';
        foreach( $this->places as $key => $val )
        {
            $selected = ( $key == $row['place'] ) ? ' selected' : '';
            echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
        }
        echo '</select></label>';
        echo '<label class="block-label">附加/附屬於 ';
        $insiderof_serial = isset($this->items[$row['insideof']]) ? $this->items[$row['insideof']] : '';
        echo '<input type="text" list="serial-list" value="'.$insiderof_serial.'" '
            . 'onchange="MMNet.alter(\'item_list\','.$row['id'].',\'insideof\',this.value,{\'redirect\':\'itemlist\',\'confirm\':\'確定變換物品附屬位置？\'});" />';
        echo '</label>';
        echo '</td>';

        // Member Editor
        echo '<td class="-tac">';
        echo '<select onchange="MMNet.alter(\'item_list\','.$row['id'].',\'member\',this.value,{\'redirect\':\'itemlist\',\'confirm\':\'確定變換物品使用人？\'});">';
        foreach( $this->actived as $key => $val )
        {
            $selected = ( $key == $row['member'] ) ? ' selected' : '';
            echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
        }
        if( !in_array($row['member'],array_keys($this->actived)) AND isset($this->members[$row['member']]) )
        {
            $member_name = $this->members[$row['member']];
            echo '<option value="'.$row['member'].'" selected>'.$member_name.'</option>';
        }
        echo '</select>';
        echo '</td>';

        echo '<td class="-tac">';
        if( $row['status'] != 'checking' )
        {
            $status = $this->status[$row['status']] . '(' . $row['status'] . ')';
            echo '<div>' . $status . '</div>';
            if( $row['member'] > 0 )
            {
                $verify = "MMNet.alter('item_list',{$row['id']},'status','checking',{'redirect':'itemlist'});";
                echo '<button type="button" onclick="'.$verify.'">盤點此物品</button>';
            }
        }
        else if( $row['report'] != null )
        {
            $status = $this->status[$row['report']] . '(' . $row['report'] . ')';
            echo $status . '<br/>' . $row['reportat'];
        } else { echo '盤點中...'; }
        echo '</td>';
        
        echo '</tr>';

        if( $row['status'] == 'checking' AND $row['report'] != null )
        {
            $report = '使用人回報：' . $row['reason'];
            if( $row['confirm'] != null )
            {
                $last_report = strtotime($row['reportat']);
                $last_confirm = strtotime($row['confirm']);
                if( $last_confirm > $last_report )
                {
                    $report = '已於 ' . $row['confirm'] . ' 要求使用人再次確認。';
                }
            }
            echo '<tr>';
            echo '<td colspan="3">' . $report . '</td>';
            echo '<td colspan="2" class="-tac">';
            echo '<button type="button" onclick="MMNet.alter(\'item_list\','.$row['id'].',\'confirm\',\'true\',{\'redirect\':\'itemlist\'});">接受回報</button> ';
            echo '<button type="button" onclick="MMNet.alter(\'item_list\','.$row['id'].',\'confirm\',\'false\',{\'redirect\':\'itemlist\'});">請使用人再次確認</button>';
            echo '</td>';
            echo '</tr>';
        }
    }
}
?>
<h1 class="caption">財產管理系統<span class="-small">v. Alpha</span></h1>
<section class="dashboard">
<?
$page = 1;
if( isset($_POST['page']) )
{
    $page = intval($_POST['page']);
    echo '<script>MMNet.Page.save("page",'.$page.');</script>';
}

$where = "v_state='working'";
if( isset($_POST['serial']) AND !empty($_POST['serial']) )
{
    echo '<script>MMNet.Page.save("serial","'.$_POST['serial'].'");</script>';
    echo '顯示編號符合 ' . $_POST['serial'] . ' 的財產。';
    $where .= " AND serial LIKE '" . str_replace('*','%',$_POST['serial']) . "'";
}
else
{
    echo '<script>MMNet.Page.save("serial",null,"remove");</script>';
    $itf = new ItemFilter();
    $itf->insert('TEXT','serial','serial','');
    $itf->insert('TERM','type','type','類別');
    $itf->insert('TERM','place','place','地點');
    $itf->insert('USER','member','user','使用人');
    $itf->insert('NOTEMPTY','outsider','only_outside','只顯示外借物品');
    $itf->insert('TEXT','outsider','outsider','外借使用人');
    $itf->drawHTML('顯示符合以下條件的財產：','ul','li');
    $where = $itf->get();
    $check_button_title = $itf->hasFilter() ? '符合條件' : '所有';
}
?>
    <div class="end-of-line"></div>
    <div id="remote">
        <a class="btn-like" href="javascript:MMNet.Controller.load('item.search');">搜尋</a>
        <a class="btn-like" href="javascript:MMNet.Controller.load('item.form');">新增物品財產</a>
        <!--<a class="btn-like" href="javascript:MMNet.Controller.load('csv.parse',{'table':'item_list','act':'extends/item.import.php'});">匯入財產清單</a>
        <a class="btn-like" href="javascript:MMNet.Controller.load('csv.parse',{'table':'item_list','act':'extends/item.fix.php'});">修正財產清單</a>-->
<?
if (isset($check_button_title))
{
?>
        <a class="btn-like" href="javascript:MMNet.Item.check();">盤點<?=$check_button_title?>的物品</a>
        <a class="btn-like" href="javascript:MMNet.Item.export();">匯出<?=$check_button_title?>物品</a>
<?
}
?>
        <div class="end-of-line"></div>
    </div>
</section><br/>
<?
$dba->drawTableByPainter(array(
    'name' => 'item_list',
    'style' => 'margin-top:10px;',
    'where' => $where,
    'paging' => array(
        'painter' => new kjPHP\PageBar(5,array(
            'button' => array( 'pattern' => "javascript:MMNet.view('main','itemlist',{'page':%id%});" )
        )),
        'items' => 50,
        'page' => $page
    )
),new ItemListPainter(array(
    'terms' => $dba->cache('term_list',array(
        'type' => 'number:reference',
        'keyname' => 'string',
        'title' => 'json:language'
    )),
    'items' => $dba->cache('item_list',array(
        'serial' => 'string',
        'model' => 'string',
        'format' => 'string',
        'number' => 'string',
        'member' => 'number:reference'
    )),
    'users' => $dba->cache('member_list',array(
        'type' => 'number:reference',
        'name' => 'json:language'
    )),
),$dba->default_language));

if( isset($itf) ) { $itf->drawScript(); }