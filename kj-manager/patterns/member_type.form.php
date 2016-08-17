<?php
require_once('../mod_database.php');
$dba = new Accessor();

$manager_level_list = array(
    array(
        'title' => '普通成員',
        'level' => 0
    ),
    array(
        'title' => '管理員',
        'level' => 5
    ),
    array(
        'title' => '最高領導',
        'level' => 9
    )
);

$type = null;
if( isset($_POST['type_id']) )
{
    $type = $dba->get(
        'member_type',
        $_POST['type_id'],
        array(
            'id' => 'number:reference',
            'name' => 'json',
            'level' => 'number'
        )
    );
}
?>
<form method="post" action="act/update-member_type.php">
<?
if( $type != null )
{
?>
    <input type="hidden" name="id" value="<?=$type['id']?>" />
<?
}
?>
    <table>
        <tbody>
            <tr><th>類型名稱</th></tr>
            <? $dba->drawLanguageRows('name',$type['name']); ?>
            <tr>
                <th>預設管理等級</th>
                <td class="form-border">
                    <select name="level">
<?
foreach( $manager_level_list as $manager )
{
    $attr = ' value="'.$manager['level'].'"';
    if($manager['level']==$type['level']) { $attr .= ' selected'; }
    echo '<option'.$attr.'>'.$manager['title'].'</option>';
}
?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <button type="button" onclick="MMNet.Controller.submit();"><?=($type==null)?'新增':'修改'?>類型</button>
    <button type="reset">重填</button>
    <button type="button" onclick="MMNet.Controller.close();">取消</button>
</form>