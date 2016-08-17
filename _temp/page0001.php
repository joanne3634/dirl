<?php
include('../_template/database.php');
$subtitle = array('TC' => '首頁','EN' => '');
include('../_template/header.php');
include('../_template/menu.php');
?>
<div id="main_content">
<?
$sec0 = array();
$sec0['title'] = array('TC' => '最新消息','EN' => 'News');
$sec0['image'] = array();
?>
<div class="main_item">
<h2><?=$sec0['title'][$lang]?></h2>

<?
$sec0_query = $pdo->query("SELECT * FROM news");
while( $row = $sec0_query->fetch(PDO::FETCH_ASSOC) )
{
?>

<?
}
?>

</div>
</div>
<?
include('../_template/footer.php');