<?php
require_once('mod_general.php');
require_once('mod_database.php');
require_once('extends/item.function.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
if( !isset($_POST['ajax']) )
{
    header('Location: index.php');
    die();
}

$dba = new Accessor();
$user = $_SESSION[SES_ADMIN];
class ItemListPainter implements AccessorTablePainter
{
    private $types = array();
    private $places = array();
    private $status = array();

    private $items = array();
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

        $this->items[0] = '';
        foreach( $data['items'] as $item )
        {
            $this->items[$item['id']] = get_item_title($item);
        }
        ksort($this->items);
    }

    public function drawHeader($columns)
    {
        echo '<tr>';
        echo '<th style="width:180px;">類型/編號</th>';
        echo '<th style="width:200px;">廠牌型號</th>';
        echo '<th>位置</th>';
        echo '<th style="width:200px;">購入日期</th>';
        echo '<th style="width:250px;">盤點狀態</th>';
        echo '</tr>';
        return 6;
    }

    public function drawFooter($result) {}
    public function drawNoData() { echo '<tr><td class="no-border -tac" colspan="6">沒有任何財產</td></tr>'; }
    public function needSort() { return false; }

    public function drawData($row,$lang)
    {
        echo '<tr>';
        $type = isset($this->types[$row['type']]) ? $this->types[$row['type']] : '未知類型('.$row['type'].')';
        echo '<td class="-tac">' . $type . '<br/>' . $row['serial'] . '</td>';
        $item_title = get_item_title($row,ITEM_TITLE_NUMBER);
        echo '<td class="-tac">' . $item_title . '</td>';
        echo '<td>' . $this->places[$row['place']] . '<br/>' . $this->items[$row['insideof']] . '</td>';
        $expired = ( intval($row['expiredin']) > 0 ) ? '使用年限：' . $row['expiredin'] . ' 年' : '沒有年限';
        echo '<td class="-tac">' . substr($row['since'],0,10) . '<br/>' . $expired . '</td>';

        echo '<td class="-tac">';
        if( $row['status'] == 'checking' )
        {
            $reported = $row['report'];
            if( $row['confirm'] != null AND ( $row['confirmat'] >= $row['reportat'] ) )
            {
                echo '<div style="color:red;">管理員要求再次確認。</div>';
                $reported = 'unchecked';
            }
            echo '<select onchange="MMNet.Item.report('.$row['id'].',this.value);">';
            foreach( $this->status as $value => $title )
            {
                $selected = ( $value == $reported ) ? ' selected' : '';
                echo '<option value="' . $value . '"'.$selected.'>' . $title . '</option>';
            }
            echo '</select>';
            if( $row['reportat'] != null ) { echo '<div>回報於 '.$row['reportat'].'。</div>'; }
            echo '<div>等待管理員確認...</div>';
        } else { echo $this->status[$row['status']].'('.$row['status'].')'; }
        echo '</td>';
        
        echo '</tr>';
    }
}

$proxy = 'null';
$owner = $user['id'];
if( isset($_POST['proxy']) )
{
    $row = $dba->get('proxy',intval($_POST['proxy']),array(
        'id' => 'number:reference',
        'principal' => 'number:reference',
        'agent' => 'number:reference',
        'commitment' => 'string'
    ));
    if( $row['commitment'] == 'items' )
    {
        if( $row['agent'] == $user['id'] )
        {
            $owner = $row['principal'];
            $proxy = $row['id'];
        }
    }
}

$dba->drawTableByPainter(array(
    'name' => 'item_list',
    'where' => "member={$owner} AND v_state='working'",
    'paging' => array(
        'painter' => new kjPHP\PageBar(5,array(
            'button' => array( 'pattern' => "javascript:MMNet.view('main','inventory',{'page':%id%});" )
        )),
        'items' => 50,
        'page' => $_POST['page']
    )
),new ItemListPainter(array(
    'terms' => $dba->cache('term_list',array(
        'type' => 'number:reference',
        'keyname' => 'string',
        'title' => 'json:language'
    )),
    'items' => $dba->cache('item_list',array(
        'serial' => 'string',
        'brand' => 'string',
        'model' => 'string',
        'format' => 'string',
        'number' => 'string'
    ))
),$dba->default_language));

$page = ( isset($_POST['page']) AND !empty($_POST['page']) ) ? $_POST['page'] : 'null';
?>
<script>
MMNet.Page.save("page",<?=$page?>);
MMNet.Page.save("proxy",<?=$proxy?>);
</script>