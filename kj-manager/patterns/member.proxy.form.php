<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

$dba = new Accessor();
$commitments = array(
    'items' => '物品財產管理'
);

$userlist = $dba->cache('member_list',array(
    'type' => 'number:reference',
    'name' => 'json:language'
));
$users = array();
foreach( $userlist as $user )
{
    $uid = $user['id'];
    $users[$uid] = $user['name'][$dba->default_language];
}
?>
<form action="act/proxy.update" method="post">
    讓 <? drawSelect('agent',array(),$users); ?>
    代理 <? drawSelect('principal',array(),$users); ?>
    的 <? drawSelect('commitment',array(),$commitments); ?>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();">確定</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>