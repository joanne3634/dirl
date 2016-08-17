<?php
include('../_template/database.php');
$subtitle = array('TC' => '首頁','EN' => 'Home');
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
<?
switch($lang)
{
default:
?>

<?
    break;
}
?>
    </div>
<?
$sec2 = array();
$sec2['title'] = array('TC' => '關於我們','EN' => 'About MMNet');
$sec2['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec2['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?>
<p><a href="http://en.wikipedia.org/wiki/Hans_Eysenck">Prof. H.J. Eysenck</a>, a famous psychologist, has been once asked: "What it feels when a research brings you satisfying results?"</p>

<p>He answered: <em>"The feeling is just like (the joyfulness of) a cat that got the cream, which is beyond description.  I think it's a substantial and wonderful feeling, and you will feel everything is so delightful.  I am often somewhat surprised when what I expected finally realized, as I always think it's almost impossible. <p>However, it is the truth now, it merits to celebrate.  Thus, you will feel happy for yourself, and feel happy about the nature, for the whole world.  You will feel the life is wonderful, and worth living."</em></p>

<p><img src="http://www.iis.sinica.edu.tw/~swc/cat_cake.jpg" alt="a cat is happy with butter" /></p>

<?
    break;
default:
?>
<p>有人訪問 <a href="http://en.wikipedia.org/wiki/Hans_Eysenck">Prof. H.J. Eysenck</a> (一位知名的心理學家)：「當某個研究帶來令您滿意的成果時，您的感覺如何？」</p>

<p>他的回答是：「那種感覺就好像貓得到奶油一樣，很難加以形容。我想那是一種充盈的美好感覺，你覺得一切都是那麼地令人愉快。我個人對自己原先預測的某些東西終於成為事實，多少會感到驚訝，因為我總覺得那不太可能，但現在卻成為事實，實在值得慶祝。所以你會對自己感到高興，對自然、對整個世界感到高興，你覺得生命真美好，值得繼續活下去。」</p>

<p>你也想一起來體會「貓得到奶油」的感覺嗎？:-)</p>

<p><img src="http://www.iis.sinica.edu.tw/~swc/cat_cake.jpg" alt="貓吃蛋糕" /></p>

<?
    break;
}
?>
    </div>
<?
$sec3 = array();
$sec3['title'] = array('TC' => '研究領域','EN' => 'Research Area');
$sec3['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec3['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?>
<ul>
<li>Multimedia Systems</li>
<li>Quality of Experience</li>
<li>Social Computing and Computational Social Science</li>
<li>Crowdsourcing / Human Computation</li>
</ul>

<?
    break;
default:
?>
<ul>
<li>多媒體系統</li>
<li>使用者經驗</li>
<li>社群計算及計算社會學</li>
<li>群眾外包 / 人智運算</li>
</ul>

<?
    break;
}
?>
    </div>
<?
$sec4 = array();
$sec4['title'] = array('TC' => '聯絡我們','EN' => 'Contact Us');
$sec4['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec4['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?>
<table>

<tr>
<th>email</th>
<td>swc@iis.sinica.edu.tw</td>
</tr>

<tr>
<th>tel</th>
<td>02-27883799#1666</td>
</tr>

<tr>
<th>fax</th>
<td>02-27824814</td>
</tr>

<tr>
<th>addr</th>
<td>Institute of Information Science, Academia Sinica, No. 128, Sec. 2,  Academia Road, Nankang Distr, Taipei 115, Taiwan</td>
</tr>

</table>
<?
    break;
default:
?>
<table>

<tr>
<th>email</th>
<td>swc@iis.sinica.edu.tw</td>
</tr>

<tr>
<th>tel</th>
<td>02-27883799 分機 1666</td>
</tr>

<tr>
<th>fax</th>
<td>02-27824814</td>
</tr>

<tr>
<th>addr</th>
<td>115 台北市南港區研究院路二段128號 中央研究院資訊科學研究所</td>
</tr>

</table>
<?
    break;
}
?>
    </div>
</div>
<?
include('../_template/footer.php');