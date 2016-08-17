<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
$dba = new Accessor();
?>
<form method="post" action="act/insert-section.php">
    <input type="hidden" name="menupage" value="<?=$_POST['menu']?>" />
    <table>
        <tbody>
            <tr><th>標題*</th></tr>
            <? $dba->drawLanguageRows('title',array(),true) ?>
            <tr>
                <th>標題圖片</th>
                <td class="no-border"><? drawImageSelector('image','',"{'TC':'中文標題圖片','EN':'英文標題圖片'}"); ?></td>
            </tr>
            <tr>
                <th>類型*</th>
                <td class="form-border">
                    <select name="type"><? drawPageTypeOptions(); ?></select>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();">新增內容</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>