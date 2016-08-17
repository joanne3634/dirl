<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
require_once('../extends/item.function.php');

if( !checkAdministrator(5) ) { die('{"error":401,"message":"No Authority."}'); }

$dba = new Accessor();

$itf = new ItemFilter();
$itf->insert('TEXT','serial','serial','');
$itf->insert('TERM','type','type','類別');
$itf->insert('TERM','place','place','地點');
$itf->insert('USER','member','user','使用人');
$itf->insert('NOTEMPTY','outsider','only_outside','只顯示外借物品');
$itf->insert('TEXT','outsider','outsider','外借使用人');

$sql = "UPDATE item_list SET status='checking' WHERE " . $itf->get();
$dba->execute($sql);