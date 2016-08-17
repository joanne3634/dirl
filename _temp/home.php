<?php
include('../_template/database.php');
$subtitle = array('TC' => '首頁','EN' => 'home');
include('../_template/header.php');
include('../_template/menu.php');
?>
<div id="main_content">
<?
$sec1 = array();
$sec1['title'] = array('TC' => '最新消息','EN' => 'News');
$sec1['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec1['title'][$lang]?></h2>
        <ul>
<?
$sec1_query = $pdo->query("SELECT * FROM news");
while( $row = $sec1_query->fetch(PDO::FETCH_ASSOC) )
{
?>
        <li><?=$row[subject]?></li>
<?
}
?>
        </ul>
    </div>
<?
$sec2 = array();
$sec2['title'] = array('TC' => '關於我們','EN' => 'About MMNet');
$sec2['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec2['title'][$lang]?></h2>
        
        <p>有人訪問 Prof. H.J. Eysenck (一位知名的心理學家)：「當某個研究帶來令您滿意的成果時，您的感覺如何？」</p>
        <p>他的回答是：「那種感覺就好像貓得到奶油一樣，很難加以形容。我想那是一種充盈的美好感覺，你覺得一切都是那麼地令人愉快。我個人對自己原先預測的某些東西終於成為事實，多少會感到驚訝，因為我總覺得那不太可能，但現在卻成為事實，實在值得慶祝。所以你會對自己感到高興，對自然、對整個世界感到高興，你覺得生命真美好，值得繼續活下去。」</p>
        <p>你也想一起來體會「貓得到奶油」的感覺嗎？:-)</p>
        
    </div>
</div>
<?
include('../_template/footer.php');