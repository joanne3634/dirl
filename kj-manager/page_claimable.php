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
    private $items = array();
    private $users = array();
    function __construct($data,$lang)
    {
        $type_types = array();
        $place_types = array();
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
        }

        $this->items[0] = '';
        foreach( $data['items'] as $item )
        {
            $this->items[$item['id']] = $item['model'] . ' ' . $item['format'] . '(' . $item['serial'] . ')';
        }
        ksort($this->items);

        $this->users = $data['username'];
    }

    public function drawHeader($columns)
    {
        echo '<tr>';
        echo '<th style="width:180px;">類型/編號</th>';
        echo '<th style="width:200px;">廠牌型號</th>';
        echo '<th>位置</th>';
        echo '<th style="width:200px;">購入日期</th>';
        echo '<th style="width:250px;">認領</th>';
        echo '</tr>';
        return 5;
    }

    public function drawFooter($result) {}
    public function drawNoData() { echo '<tr><td class="no-border -tac" colspan="5">沒有需要認領的物品財產</td></tr>'; }
    public function needSort() { return false; }

    public function drawData($row,$lang)
    {
        echo '<tr>';
        $type = isset($this->types[$row['type']]) ? $this->types[$row['type']] : '未知類型('.$row['type'].')';
        echo '<td class="-tac">' . $type . '<br/>' . $row['serial'] . '</td>';
        $title = get_item_title($row,ITEM_TITLE_NUMBER);
        echo '<td class="-tac">' . $title . '</td>';
        echo '<td>' . $this->places[$row['place']] . '<br/>' . $this->items[$row['insideof']] . '</td>';
        $expired = ( intval($row['expiredin']) > 0 ) ? '使用年限：' . $row['expiredin'] . ' 年' : '沒有年限';
        echo '<td class="-tac">' . substr($row['since'],0,10) . '<br/>' . $expired . '</td>';

        $iid = $row['id'];
        global $user;
        echo '<td id="item-'.$iid.'-apply-area" class="-tac">';
        if( $user['level'] > 4 )
        {
            global $dba;
            $appliers = $dba->query('item_history',array(
                'v_state' => array( 'value' => 'working' ),
                'itemkey' => array( 'value' => $iid ),
                'type' => array( 'value' => 'apply' )
            ),array(
                'manager' => 'number:reference',
                'logtime' => 'datetime'
            ));
            if( !empty($appliers) )
            {
                echo '<div>共有 ' . count($appliers) . ' 位申請人。</div>';
                echo '<select onchange="MMNet.alter(\'item_list\','.$row['id'].',\'member\',this.value);">';
                echo '<option value="0" selected>(未指定)</option>';
                foreach( $appliers as $applier )
                {
                    $uid = $applier['manager'];
                    $title = $this->users[$uid] . '(' . $applier['logtime'] . ')';
                    echo '<option value="' . $uid . '">' . $title . '</option>';
                }
                echo '</select>';
            } else { echo '暫無使用者申請。'; }
        } else { include('extends/item.apply.php'); }
        echo '</td>';

        echo '</tr>';
    }
}

$dba->drawTableByPainter(array(
    'name' => 'item_list',
    'where' => "v_state='working' AND member=0 AND claim='claimable'",
    'paging' => array(
        'painter' => new kjPHP\PageBar(5,array(
            'button' => array( 'pattern' => "javascript:MMNet.view('main','claimable',{'page':%id%});" )
        )),
        'items' => 20,
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
        'model' => 'string',
        'format' => 'string',
        'number' => 'string'
    )),
    'username' => $dba->cache('member_list',array('name'=>'json:language'),array('type'=>'OPTION_ARRAY'))
),$dba->default_language));