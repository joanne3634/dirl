<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
$dba = new Accessor();

$category_type_list = array(
    'others' => '其他資訊',
    'contact' => '聯絡資訊'
);

$category = null;
if( isset($_POST['category_id']) )
{
    $category = $dba->get(
        'member_category',
        $_POST['category_id'],
        array(
            'id' => 'string',
            'name' => 'string',
            'required' => 'number:flag',
            'type' => 'string',
            'hint' => 'string'
        )
    );
}
?>

<form method="post" action="act/update-member_category.php">
<?
$name_value = '';
if( $category != null )
{
    kjPHP\drawHTMLElement('input',array(
        'type' => 'hidden',
        'name' => 'edit',
        'value' => $category['name']
    ));
    $name_value = ' value="'.$category['id'].'" readonly';
}
?>
    <table>
        <tbody>
            <tr>
                <th>標題識別名稱</th>
                <td class="form-border"><input type="text" name="id"<?=$name_value?> /></td>
            </tr>
            <tr>
                <th>標題顯示名稱</th>
                <td class="form-border"><input type="text" name="name" value="<?=$category['name']?>" /></td>
            </tr>
            <tr>
                <th>必要欄位</th>
                <td class="no-border">
                    <label><input type="checkbox" name="required"<?=($category['required']?' checked':'')?> />必要欄位</label>
                </td>
            </tr>
            <tr>
                <th>類別</th>
                <td class="form-border">
                    <select name="type">
<?
foreach( $category_type_list as $key => $title )
{
    $selected = '';
    if( $category['type'] == $key ) { $selected = ' selected'; }
    echo '<option value="'.$key.'"'.$selected.'>'.$title.'</option>';
}
?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>說明</th>
                <td class="form-border"><input type="text" name="hint" value="<?=$category['hint']?>" /></td>
            </tr>
        </tbody>
    </table>
    <button type="button" onclick="MMNet.Controller.submit();"><?=($category==null)?'新增':'修改'?>資料標題</button>
    <button type="reset">重填</button>
    <button type="button" onclick="MMNet.Controller.close();">取消</button>
</form>