<?php
include('../_template/database.php');
$subtitle = array('TC' => '研究領域','EN' => 'Research Areas');
include('../_template/header.php');
include('../_template/menu.php');
?>
<div id="main_content">
<?
$sec1 = array();
$sec1['title'] = array('TC' => '研究領域','EN' => 'Research Areas');
$sec1['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec1['title'][$lang]?></h2>
<?
switch($lang)
{
case 'EN':
?>
<p>My research areas span over multimedia systems and social computing, with an emphasis on the former. Though the two fields look unrelated, they are now collectively part of modern life and affecting what people do and think in countless ways. For example, demonstrations and any social events can now be captured and streamed online by any participant from any perspective via the (mobile) Internet and the public opinions can be easily disseminated and aggregated in an unprecedented speed over social information systems. The advances in multimedia and social computer systems have drastically changed how we sense, interpret, and respond to other people and the society. I am briefly describing my research portfolio and interests below:</p>

<ul>
<li><h2>1) Multimedia Systems</h2>

<p>My interest on multimedia systems is primarily in real-time interactive networked multimedia systems, particularly the audio/video remote communication systems and the network gaming systems, which address two of the major motivations why the general public uses the Internet: Interpersonal interaction and entertainment.</p>

<p>While it has been more than 20 years since audio/video communication over the Internet has been made possible, still, remote communication remains much less natural and efficient than face-to-face communication. People still fly to office branches, customers' companies, and conferences to attend meetings whenever possible for an obvious reason－the current systems only allow us to do the most simple visual and verbal communication, whereas more subtle social signals, such as proximity, direction of gesture, eye contact are not directly communicable via the current systems.  To address the strong needs, my long-term goal along this line of research is to develop an affordable immersive remote communication system: 1) I believe that such systems would largely reduce the demand of business flights, which in turn reduces travel costs and fatigue, and also helps to protect the global environment; 2) in some extreme circumstances, such as during natural disasters, travel would not be possible, but rich remote communication will be definitely required; and 3) I believe that richer and more immersive remote communication would help people to understand and collaborate with each other in more depth (imagining that a politician can have eye contacts with ten thousands of audience at the same time when giving a speech over the Internet). While most problems in this world might be due to misunderstanding, I hope that better remote communication tools would help people across the globe understand each other and the world better and create a world with less conflicts, arguments, and wars.</p>

<p>My another line of research on network gaming dates back to 2004 with the goal of providing a scalable, efficient, and secure gaming infrastructure for all kinds of network games. Network gaming has been one of the most profitable businesses on the Internet, and its market share has surpassed those of the movie and music industries. To ensure that this line of research is solid and practical, I collaborate with a number of game companies that provide their domain knowledge, real-life operation logs, and funding for research projects to together address various challenges in building better network games. More recently, I moved to a new form of network gaming called cloud gaming, which brings the benefits of cloud computing and thin clients into computer gaming. I work closely with fellow researchers in this area and together we have addressed a number of challenges and made some well-known contributions to this area, including a performance instrumentation methodology for closed and proprietary cloud gaming systems, the first-ever open cloud gaming system called GamingAnywhere, and a series of resource allocation algorithms that can optimize cloud resources to provide quality cloud gaming services in an efficient and scalable fashion.</p></li>
<li><h2>2) Social Computing and Computational Social Science</h2>

<p>The strongest incentive that drives me to get into the social computing and computational social science is the concern of social welfare. I would say that my research along this line is around poverty and happiness, as poverty is one of the biggest social problems in the world (and unfortunately becoming worse over time), while people who are far away from poverty may still be unhappy due to various social psychological issues, such as anxiety disorders, relative deprivation, social disparity, alienation, and so on.  As we can learn from the human history, the technology advancement and economic growth would not completely resolve the huge, deep, unbearable problem of humans—how to live our lives happily? I work with sociologists and psychologists with a hope to address the social well-being problems from an interdisciplinary perspective.</p></li>
</ul>

<?
    break;
default:
?>
<p>我的研究領域涵蓋多媒體系統和社群運算。雖然這兩個領域看似沒有直接關連，但他們共同影響了許多現代人們的想法和行為，且已經在不知不覺中成為我們生活的一部分。舉例來說，現在的示威遊行等群眾運動已經可以由隨處可得的智慧型手機直接在網路上串流直播，而人們的意見和看法，也正以前所未見的速度在各種社群平台上散布和匯流。多媒體和社群平台的發展正大幅地改變人們彼此溝通和理解的方式。</p>

<ul>
<li><h2>多媒體系統</h2>

<p>我對於多媒體系統的研究主要著重在即時互動網路多媒體系統上，特別是遠端影音會議系統和網路遊戲系統，而這涵蓋了人們使用網際網路的兩個主要目的：人際交流和娛樂。</p>

<p>網路影音通訊技術發展至今已20餘年，僅管如此，目前的遠端通訊仍然無法讓人們像面對面般自然且有效率的溝通。尤其在會議或是與客戶談生意等重要場合，許多人們還是寧願花相對高的金錢和時間成本，也要長途拔涉做面對面的溝通，主要原因就在於，現有的遠端通訊系統只提供最基本的語音及視覺交流，然而，現實世界中的溝通卻遠不只於此。許多社交訊號，例如：親密度、方向手勢、眼神交流等，在現有的系統中都無法被實現。</p>

<p>我們建構大眾可負擔的沈浸式遠端通訊系統的主要動機包括：1）我們相信這樣的系統能大幅降低公務旅行的需求，如此不僅可省下大量的金錢與時間成本，人們也可免於長途旅行的勞累和不便，此外也可減少交通工具的碳排放，對環境保護有正面的影響。2）在某些極端情境，如天災時的救援等，人們將無法親臨現場做必要的環境評估，此時，沈浸式遠端通訊系統將能有極大的用處。3）我們認為，透過互動性更強的沈浸式遠端通訊，人與人之間的合作將更自然，也更容易互相理解彼此的想法（想像如果政治人物在透過網路發表演說時能同時和所有的聽眾作眼神交流）。現今世界上的許多問題都因一些小小的誤會而起，我們希望透過更好的遠端通訊工具，讓遠在地球彼端的人們也能互相理解，進而減少衝突、紛爭、和戰爭。</p>

<p>另一方面，我從小就對電腦遊戲有極大的興趣，早在2004年起，我就開始了我對網路遊戲的研究，目的是希望為各類型的網路遊戲提供一個有彈性、高效率、且安全的基礎環境。網路遊戲儼然已是目前獲利最高的網路相關事業，其市佔率甚至已經超越電影和音樂產業。為了確保我在網路遊戲方面的研究是可靠且有實用價值的，我與多家遊戲公司都有合作關係，他們不只是在經濟上贊助我們的研究，也提供了許多他們的專業知識、經驗、和現實的營運記錄等。我的網路遊戲研究涵蓋了各種系統設計的議題，其中包括：網路流量分析、傳輸協議、和雲端運算在遊戲領域的應用等；此外，我們也致力於一些網路遊戲上的安全性議題，如作弊機器人及竊取遊戲帳號的偵測等。</p></li>
<li><h2>社群計算</h2>

<p>對社會福利的關心是促使我進入社群計算領域的最大動機。我對社群計算的研究主要圍繞在貧窮與快樂等議題上。貧窮是目前世界上最嚴重的社會問題之一，但同時，即使是沒有經濟困難的人們，也不見得能免於各種心理或社會層面的不快樂因子，如焦慮、相對剝奪感、社會不公、疏離感、空虛感等。歷史的教訓告訴我們，科技的進步和經濟的成長並不一定會為人們帶來快樂的生活。基於上述動機，我們和許多社會、心理方面的學者合作，希望能從跨領域的觀點，更深入探討和社會福利相關的各種問題。</p></li>
</ul>

<?
    break;
}
?>
    </div>
</div>
<?
include('../_template/footer.php');