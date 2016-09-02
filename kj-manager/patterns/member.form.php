<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
$dba = new Accessor();

class CustomRowPainter implements AccessorTablePainter
{
    private $title = '';
    private $type = '';
    private $data = array();
    function __construct($title,$type,$data)
    {
        $this->title = $title;
        $this->type = $type;
        if( isset($data) ) { $this->data = $data; }
    }

    public function drawHeader($columns)
    {
        echo '<th width="100px">'.$this->title.'</th>';
        echo '<td></td>';
    }
    public function drawFooter($result)
    {
        echo '<tr><td colspan="2">';
        echo '<select style="width:100%;" onchange="MMNet.insertRow(this,\''.($this->type).'\');">';
        echo '<option value="">增加'.$this->title.'</option>';
        foreach( $result as $row )
        {
            $attr = '';
            if( !empty($row['hint']) ) { $attr .= ' title="'.$row['hint'].'"'; }
            if( $row['required'] > 0 ) { $attr .= ' data-require="true"'; }
            echo '<option value="'.$row['id'].'"'.$attr.'>'.$row['name'].'</option>';
        }
        echo '</select>';
        echo '</td></tr>';
    }
    public function drawNoData() {}

    public function needSort() { return false; }
    public function drawData($row,$lang)
    {
        $required = intval($row['required']) > 0;
        $value = isset($this->data[$row['id']]) ? ' value="'.$this->data[$row['id']].'"' : '';
        if( $required OR !empty($value) )
        {
            if( $required ) { $value .= ' required'; }
            echo '<tr>';
            echo '<th title="'.$row['hint'].'">'.$row['name'].'</th>';
            echo '<td><input type="text" placeholder="'.($row['name']=='生日'?'yyyy-mm-dd':'').'"name="'.$this->type.'['.$row['id'].']"'.$value.' /></td>';
            echo '</tr>';
        }
    }
}

function drawRangeSelector($name,$from,$to,$first,$selected)
{
    echo '<select name="'.$name.'">';
    $step = ( $to > $from ) ? 1 : -1;
    if( $first != null ) { echo '<option value="-1">'.$first.'</option>'; }
    for($i=$from;$i<=$to;$i+=$step)
    {
        $checked = '';
        if( $i == $selected ) { $checked = ' selected'; }
        echo '<option value="'.$i.'"'.$checked.'>'.$i.'</option>';
    }
    echo '</select>';
}

$thisyear = intval( date('Y') );
$member = array();
$pwd_required = ' required';
if( isset($_POST['member_id']) )
{
    $member = $dba->get(
        'member_list',
        $_POST['member_id'],
        array(
            'id' => 'number:reference',
            'pic' => 'string',
            'name' => 'json:language',
            'alias' => 'json:language',
            'contact' => 'json',
            'others' => 'json',
            'type' => 'number:reference',
            'level' => 'number',
            'period' => '%d/%d~%d/%d',
            'title' => 'json:language',
            'iis_user' => 'string',
            'research' => 'list:stroke'
        )
    );
    $pwd_required = '';
}
?>
<form method="post" action="act/update-member_list.php">
<?
if( !empty($member) )
{
    kjPHP\drawHTMLElement('input',array(
        'type' => 'hidden',
        'name' => 'id',
        'value' => $member['id']
    ));
}
?>
    <table style="float:left;width:50%;">
        <tbody>
            <tr>
                <th>照片</th>
                <td class="no-border">
                    <input type="hidden" id="member_picture" name="pic" value="<?=$member['pic']?>" />
                    <button type="button" onclick="MMNet.FileManager.select(2,$('#member_picture'));">選擇圖片</button><br/>
                    上傳圖片(小於2M)：<input type="file" onchange="MMNet.upload(2,this,$('#member_picture'),{'overwrite':true});" />
                </td>
            </tr>
            <tr><th>姓名*</th><td></td></tr>
            <? $dba->drawLanguageRows('name',$member['name']) ?>
            <tr><th>別名</th><td></td></tr>
            <? $dba->drawLanguageRows('alias',$member['alias'],false) ?>

            <tr><td class="no-border dome" colspan="2">
                <? $dba->drawTableByPainter(
                    array('name'=>'member_category','id'=>'','class'=>'define-list','where'=>"type='contact'"),
                    new CustomRowPainter('連絡資訊','contact',$member['contact'])
                ); ?>
            </td></tr>
            <tr><td class="no-border dome" colspan="2">
                <? $dba->drawTableByPainter(
                    array('name'=>'member_category','id'=>'','class'=>'define-list','where'=>"type='others'"),
                    new CustomRowPainter('其他資訊','others',$member['others'])
                ); ?>
            </td></tr>
        </tbody>
    </table>
    <table style="float:left;width:50%;">
        <tbody>
<?
$m_lv = empty($member) ? 0 : intval($member['level']);
$s_lv = min(9,$_SESSION[SES_ADMIN]['level']);
if( $_SESSION[SES_ADMIN]['level'] > $m_lv OR $_SESSION[SES_ADMIN]['id'] == $member['id'] )
{
    $account_visible = 'readonly';
    if( $s_lv > 0 ) { $account_visible = 'required'; }
?>
            <tr>
                <th>資訊所帳號*</th>
                <td class="form-border"><input type="text" name="iis_user" value="<?=$member['iis_user']?>" <?=$account_visible?> /></td>
            </tr>
            <tr>
                <th>MMNet 密碼*</th>
                <td class="form-border"><input type="password" name="password"<?=$pwd_required?> /></td>
            </tr>
            <!--<tr>
                <th>確認密碼*</th>
                <td class="form-border"><input type="password" name="confirm" required /></td>
            </tr>-->
<?
    $types = $dba->cache('member_type',array(
        'v_state' => 'string',
        'level' => 'number',
        'name' => 'lang:default'
    ),array());
?>
            <tr>
                <th>類型</th>
                <td class="form-border">
                    <select name="type">
<?
        foreach( $types as $type )
        {
            if( $type['v_state'] != 'working' ) { continue; }
            if( $type['level'] > $_SESSION[SES_ADMIN]['level'] ) { continue; }
            $selected = '';
            if( $type['id'] == $member['type'] ) { $selected = ' selected'; }
            echo '<option value="'.$type['id'].'"'.$selected.'>'.$type['name'].'</option>';
        }
?>
                    </select>
                </td>
            </tr>
<?
    
    if( $s_lv > 0 )
    {
?>
            <tr>
                <th>管理權限等級</th>
                <td class="form-border"><? drawRangeSelector('level',0,$s_lv,null,$m_lv); ?></td>
            </tr>
            <tr>
                <th>在職期間</th>
                <td class="no-border" style="padding-left:5px;">
                    <? drawRangeSelector('period[i_year]',1950,$thisyear,'----',$member['period'][0]); ?> 年
                    <? drawRangeSelector('period[i_mon]',1,12,'--',$member['period'][1]); ?> 月
                    ～
                    <? drawRangeSelector('period[o_year]',$thisyear-10,$thisyear+10,'----',$member['period'][2]); ?> 年
                    <? drawRangeSelector('period[o_mon]',1,12,'--',$member['period'][3]); ?> 月
                </td>
            </tr>
            <tr><th>抬頭職稱</th><td></td></tr>
<?
        $dba->drawLanguageRows('title',$member['title'],false);
    }
}
?>
            <tr>
                <th>研究領域</th>
                <td class="form-border"><textarea name="research"><?=implode(PHP_EOL,$member['research'])?></textarea></td>
            </tr>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();"><?=($member==null)?'新增':'修改'?>實驗室成員</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>