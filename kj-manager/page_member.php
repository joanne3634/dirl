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

$state_list = array(
    'working' => '顯示',
    'hidden' => '隱藏',
    'disabled' => '停用'
);
?>

<aside style="width:35%;">
<?
$dba->drawTable(array(
    'name' => 'member_type',
    'title' => '成員類別',
    'where' => "NOT v_state='removed'"
),array(
    'name' => array(
        'type' => 'lang',
        'title' => '成員類別'
    ),
    'level' => array(
        'title' => '層級',
        'class' => '-tac',
        'width' => '50px'
    )
),array(
    'insert' => array(
        'type' => 'pattern',
        'pattern' => 'member_type.form'
    ),
    'display1' => array(
        'triggers' => array(
            'v_state' => array( 'working' )
        ),
        'class' => array( '-working' ),
        'title' => '停用',
        'click' => "MMNet.alter('member_type','%id%','v_state','disabled',{'target':this,'turn':'working'});"
    ),
    'display2' => array(
        'triggers' => array(
            'v_state' => array( 'disabled' )
        ),
        'class' => array( '-disabled' ),
        'title' => '啟用',
        'click' => "MMNet.alter('member_type','%id%','v_state','working',{'target':this,'turn':'disabled'});"
    ),
    'edit' => array(
        'class' => array( 'edit-icon' ),
        'title' => '修改',
        'click' => "MMNet.Controller.load('member_type.form',{'type_id':%id%});"
    ),
    'remove' => array(
        'class' => array( 'remove-icon' ),
        'title' => '刪除',
        'click' => "MMNet.alter('member_type','%id%','v_state','removed',{'confirm':'是否刪除類別？','redirect':'member'});"
    )
));

$dba->drawTable(array(
    'name' => 'member_category',
    'title' => '資料',
),array(
    'name' => array(
        'title' => '名稱',
        'class' => '-tac',
        'width' => '100px',
        'tooltip' => 'id'
    ),
    'hint' => array(
        'title' => '說明'
    )
),array(
    'insert' => array(
        'type' => 'pattern',
        'pattern' => 'member_category.form'
    ),
    'edit' => array(
        'class' => array( 'edit-icon' ),
        'title' => '修改',
        'click' => "MMNet.Controller.load('member_category.form',{'category_id':'%id%'});"
    )
));
?>
</aside>

<div style="width:65%">
<!--<a class="btn-like" href="extends/contacts.php" target="_new">通訊錄</a>-->
<a class="btn-like" href="javascript:MMNet.scrollTo('#alumni');">離職人員清單</a>
<a class="btn-like" href="javascript:MMNet.link('extends/member.phone.export.php','extends/cache/members.vcf');">在職人員通訊錄</a>
</div>
<?
class MemberTablePainter implements AccessorTablePainter
{
    private $states = array();
    function __construct($states) { $this->states = $states; }

    public function drawHeader($columns)
    {
        echo '<tr>';
        echo '<th style="width: 80px;">成員類別</th>';
        echo '<th>姓名</th>';
        echo '<th style="width:100px;">帳號情況</th>';
        echo '<th style="width: 80px;">管理層級</th>';
        echo '<th style="width:100px;">加入時間</th>';
        echo '<th style="width:120px;">工具</th>';
        echo '</tr>';
        return 6;
    }

    public function drawFooter($result)
    {
        echo '<tr><td class="btn-like" colspan="6" onclick="MMNet.Controller.load(\'member.form\');">新增成員</td></tr>';
    }

    public function drawNoData() { echo '<tr><td class="no-border -tac" colspan="6">還沒有任何成員</td></tr>'; }
    public function needSort() { return true; }

    public function drawData($row,$lang)
    {
        $names = json_decode($row['name'],true);
        $name = $names[$lang];
        list($in_year,$in_month,$out_year,$out_month) = sscanf($row['period'],'%d/%d~%d/%d');
        $in_date = '--';
        if( $in_year > 0 AND $in_month > 0 ) { $in_date = $in_year . ' 年 ' . $in_month . ' 月'; }
        echo '<tr data-dbid="'.$row['id'].'">';
        echo '<td class="-tac">'.$row['type'].'</td>';
        echo '<td>'.$name.'('.$row['username'].')</td>';
        echo '<td class="-tac">';
        echo '<select onchange="MMNet.alter(\'member_list\','.$row['id'].',\'v_state\',this.value);">';
        foreach( $this->states as $state_v => $state_n )
        {
            $selected = ( $state_v == $row['v_state'] ) ? ' selected' : '';
            echo '<option value="'.$state_v.'"'.$selected.'>'.$state_n.'</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td class="-tac">';
        $member_level = intval($row['level']);
        $user_level = $_SESSION[SES_ADMIN]['level'];
        if( $user_level > $member_level )
        {
            echo '<select name="level" onchange="MMNet.alter(\'member_list\','.$row['id'].',\'level\',this.value,null);"';
            for($lv=$user_level;$lv>=0;$lv--)
            {
                $selected = ($lv==$member_level) ? ' selected' : '';
                echo '<option value="'.$lv.'"'.$selected.'>'.$lv.'</option>';
            }
            echo '</select>';
        } else { echo $member_level; }
        echo '</td>';
        echo '<td class="-tac">'.$in_date.'</td>';
        echo '<td class="-tac">';
        echo '<div class="btn-like edit-icon" title="修改" onclick="MMNet.Controller.load(\'member.form\',{\'member_id\':'.$row['id'].'});"></div>';
        echo '<div class="btn-like remove-icon" title="刪除" onclick="MMNet.alter(\'member_list\','.$row['id'].',\'v_state\',\'removed\',{\'confirm\':\'確定要刪除成員「'.$name.'」的資料？\'});"></div>';
        echo '</td>';
        echo '</tr>';
    }
}
$dba->drawTableByPainter(array(
    'name' => 'member_list',
    'where' => "NOT type=5 AND NOT v_state='removed'",
    'style' => 'width:65%;'
),new MemberTablePainter($state_list));
?>

<div class="end-of-line"></div>
<?
class AlumniTablePainter implements AccessorTablePainter
{
    private $states = array();
    function __construct($states) { $this->states = $states; }

    public function drawHeader($columns)
    {
        echo '<tr>';
        echo '<th>姓名</th>';
        echo '<th style="width:100px;">帳號情況</th>';
        echo '<th style="width:100px;">加入時間</th>';
        echo '<th style="width:100px;">離開時間</th>';
        echo '<th style="width:120px;">工具</th>';
        echo '</tr>';
        return 4;
    }

    public function drawFooter($result) {}
    public function drawNoData() { echo '<tr><td class="no-border -tac" colspan="4">沒有任何Alumni成員</td></tr>'; }
    public function needSort() { return true; }

    public function drawData($row,$lang)
    {
        $names = json_decode($row['name'],true);
        $name = $names[$lang];
        list($in_year,$in_month,$out_year,$out_month) = sscanf($row['period'],'%d/%d~%d/%d');
        $in_date = '--';
        $out_date = '--';
        if( $in_year > 0 AND $in_month > 0 ) { $in_date = $in_year . ' 年 ' . $in_month . ' 月'; }
        if( $out_year > 0 AND $out_month > 0 ) { $out_date = $out_year . ' 年 ' . $out_month . ' 月'; }
        echo '<tr data-dbid="'.$row['id'].'">';
        echo '<td>'.$name.'('.$row['username'].')</td>';
        echo '<td class="-tac">';
        echo '<select onchange="MMNet.alter(\'member_list\','.$row['id'].',\'v_state\',this.value);">';
        foreach( $this->states as $state_v => $state_n )
        {
            $selected = ( $state_v == $row['v_state'] ) ? ' selected' : '';
            echo '<option value="'.$state_v.'"'.$selected.'>'.$state_n.'</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td class="-tac">'.$in_date.'</td>';
        echo '<td class="-tac">'.$out_date.'</td>';
        echo '<td class="-tac">';
        echo '<div class="btn-like edit-icon" title="修改" onclick="MMNet.Controller.load(\'member.form\',{\'member_id\':'.$row['id'].'});"></div>';
        echo '<div class="btn-like remove-icon" title="刪除" onclick="MMNet.alter(\'member_list\','.$row['id'].',\'v_state\',\'removed\',{\'confirm\':\'確定刪除離職成員「'.$name.'」的資料？\'});"></div>';
        echo '</td>';
        echo '</tr>';
    }
}
$dba->drawTableByPainter(array(
    'id' => 'alumni',
    'name' => 'member_list',
    'title' => '離職人員',
    'where' => "type=5 AND NOT v_state='removed'"
),new AlumniTablePainter($state_list));
?>
<script>
MMNet.DragDropList.initialize("table#table-of-member_type > tbody.sortable","member_type");
MMNet.DragDropList.initialize("table#table-of-member_list > tbody.sortable","member_list");
MMNet.DragDropList.initialize("table#alumni > tbody.sortable","member_list");
</script>
