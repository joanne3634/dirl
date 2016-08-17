<?php
include('../_template/database.php');
$subtitle = array('TC' => '初心頁面','EN' => 'newpage');
include('../_template/header.php');
include('../_template/menu.php');
?>
<div id="main_content">
<?
$sec1 = array();
$sec1['title'] = array('TC' => '測試資料表使用','EN' => 'Testing Table Usage');
$sec1['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec1['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?><ul><?
    $sec1_query = $pdo->query("SELECT * FROM news");
    while( $row = $sec1_query->fetch(PDO::FETCH_ASSOC) )
    {
?><li><?=$row[action]?></li><?
    }
?></ul><?
    break;
case 'TC':
?>第一行
<ul><?
    $sec1_query = $pdo->query("SELECT * FROM news");
    while( $row = $sec1_query->fetch(PDO::FETCH_ASSOC) )
    {
?><li><?=$row[subject]?></li><?
    }
?></ul>
第二行<?
    break;
default: break;
}
?>
    </div>
<?
$sec2 = array();
$sec2['title'] = array('TC' => '測試一般輸入','EN' => 'Testing Normal Input');
$sec2['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec2['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?>
<p>This is a english sentence.</p>

<?
    break;
case 'TC':
?>
<h3>這是使用Markdown語法寫出來的HTML</h3>

<p>在kjManager中，我們建議頁面章節中內容的<strong>標題(header)</strong>，使用h3或更低層次的HTMLElement。</p>

<p>kjManager的HTML文件格式大概是下列的樣子：</p>

<ul>
<li>DIV#page-wrapper

<ul>
<li>HEADER : template/header.php</li>
<li>DIV.container</li>
<li>NAV#menu : template/menu.php</li>
<li>DIV#main-content

<ul>
<li>H1 : 頁面標題</li>
<li>SECTION</li>
<li>HEADER > H2 : 章節名稱或圖片</li>
<li>header(如果有使用資料表)</li>
<li>content(每一章節實際的內容將出現在這裡)</li>
<li>footer(如果有使用資料表)</li>
</ul></li>
<li>FOOTER : template/footer.php</li>
</ul></li>
</ul>

<p>在這樣的架構下，章節內容中的標題如果是h1或h2，可能會跟章節本身的標題混為一談。</p>

<?
    break;
default: break;
}
?>
    </div>
</div>
<?
include('../_template/footer.php');