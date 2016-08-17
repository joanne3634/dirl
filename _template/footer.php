<?php
/*-------------------------------------------------------------\
 * 檔案：_template/footer.php
 - 說明：所有頁面的結尾內容
\-------------------------------------------------------------*/
if( !isset($subtitle) OR !isset($lang) )
{
    header('location: /index.html');
    die();
}

$email_alt = "郵箱";
$tel_alt = "電話";
$fax_alt = "傳真";
$addr_alt = "地址";    

$email = "";
$tel = "";
$fax = "";
$addr = "";

if($lang == "EN"){
    $email_alt = "Email";
    $tel_alt = "Tel";
    $fax_alt = "Fax";
    $addr_alt = "Addr";    
}

include("MGE/lib/link.html");
mysql_query('SET NAMES latin1;');

// 抓取資料庫資料
if($con){
    if($db_selected){
        // 抓取資料表 contact
        $sql = "select email, tel, fax, addr from contact where lang='".$lang."'";
        $result = mysql_query($sql);

        if($result){
            $row = mysql_fetch_row($result);
            $email = $row[0];
            $tel = $row[1];
            $fax = $row[2];
            $addr = $row[3];                                
        }       
   }
}

?>
<!-- footer start -->
<!-- ================ -->
<footer id="footer" class="clearfix ">
    <!-- .footer start -->
    <!-- ================ -->
    <div class="footer">
        <div class="container">
            <div class="footer-inner">
                <div class="row">
                    <div class="col-xs-12 col-sm-7">
                        <div class="foot-row">
                            <div class="foot-alt"><? echo $tel_alt ?></div>
                            <div class="foot-value"><? echo $tel ?></div>
                        </div>
                        <div class="foot-row">
                        <div class="foot-alt"><? echo $fax_alt ?></div>
                        <div class="foot-value"><? echo $fax ?></div>
                        </div>
                        <div class="foot-row">
                        <div class="foot-alt"><? echo $addr_alt ?></div>
                        <div class="foot-value"><? echo $addr ?></div>
                        </div>
                        <!-- <span><? echo $tel_alt ?></span>  <? echo $tel ?>
                        <br/> <span><? echo $fax_alt ?></span>  <? echo $fax ?>
                        <br/> <span><? echo $addr_alt ?></span>  <? echo $addr ?>
                        <br/> -->
                    </div>
                    <div class="col-xs-12 col-sm-5 fbox text-right">
                    <?
                        $botlink_array = array();
                        foreach ($toplinks as $toplink)
                        {
                            $name = $toplink['name'][$lang];
                            $botlink_array[] = '<a href="'.$toplink['href'][$lang].'" title="'.$name.'" target="'.$toplink['target'].'">'.$name.'</a>';
                        }
                        echo implode(' | ',$botlink_array);
                    ?>                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- .footer end -->
</footer>
<!-- footer end -->
</div>
<!-- page-wrapper end -->
<!-- Jquery and Bootstap core js files -->
<script type="text/javascript" src="/_scripts/plugins/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/plugins/bootstrap.min.js"></script>
<!-- Modernizr javascript -->
<script type="text/javascript" src="/_scripts/plugins/modernizr.js"></script>
<!-- Appear javascript -->
<script type="text/javascript" src="/_scripts/plugins/jquery.waypoints.min.js"></script>
<!-- SmoothScroll javascript -->
<script type="text/javascript" src="/_scripts/plugins/jquery.browser.js"></script>
<script type="text/javascript" src="/_scripts/plugins/SmoothScroll.js"></script>
<!-- Initialization of Plugins -->
<script type="text/javascript" src="/_scripts/plugins/template.js"></script>

<script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));

    try {
        var pageTracker = _gat._getTracker("UA-13103029-2");
        pageTracker._trackPageview();
    } catch (err) {}
</script>
<!-- Start of StatCounter Code -->
<script type="text/javascript">
    var sc_project = 5623085;
    var sc_invisible = 1;
    var sc_partition = 60;
    var sc_click_stat = 1;
    var sc_security = "a6d468ae";
</script>
<script type="text/javascript" src="http://www.statcounter.com/counter/counter.js"></script>
<noscript>
    <div class="statcounter">
        <a title="wordpress counter" href="http://www.statcounter.com/wordpress.com/" target="_blank">
            <img class="statcounter" src="http://c.statcounter.com/5623085/0/a6d468ae/1/" alt="wordpress counter">
        </a>
    </div>
</noscript>
<!-- End of StatCounter Code -->
</body>
</html>