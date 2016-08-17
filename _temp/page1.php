<?php
include('../_template/database.php');
$subtitle = array('TC' => '測試頁面','EN' => 'Testing Page');
include('../_template/header.php');
include('../_template/menu.php');
?>
<div id="main_content">
<?
$sec1 = array();
$sec1['title'] = array('TC' => '測試章節第一號','EN' => 'Testing Section number 1');
$sec1['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec1['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?>
<h3>header 3</h3>

<p>What the fuck...</p>

<?
    break;
default:
?>
<p>這是一段普通的文字。</p>

<p>這是一段被 p 標籤包住的文字。</p>

<p>
這是一段被 p 標籤包住，並在其中有斷行的文字。<br/>
這是一段被 p 標籤包住的第二段文字。
</p>

<p>這是第二段普通的文字。</p>

<p>這是第三段普通的文字。</p>

<?
    break;
}
?>
    </div>
<?
$sec2 = array();
$sec2['title'] = array('TC' => '資料表測試章節','EN' => 'Table Data Testing Section');
$sec2['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec2['title'][$lang]?></h2>
<ul>
<?
$sec2_query = $pdo->query("SELECT * FROM member_list WHERE type=5");
while( $row = $sec2_query->fetch(PDO::FETCH_ASSOC) )
{
?>
<li>
<?
$name = json_decode($row['name'],true);
echo $name[$lang] . '(' . $row['period'] . ')';
?>
</li>
<?
}
?>
</ul>
    </div>
</div>
<?
include('../_template/footer.php');