<?php
require_once('mod_general.php');
require_once('mod_database.php');

$dba = new Accessor();
?>
<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8" />
    <title>管理介面 - 多媒體網路與系統實驗室</title>
    <link rel="stylesheet" type="text/css" href="plugins/jQuery/jquery-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="styles/common.css" />
    <link rel="stylesheet" type="text/css" href="styles/form.css" />
    <link rel="stylesheet" type="text/css" href="styles/form.login.css" />
    <link rel="stylesheet" type="text/css" href="styles/page.css" />
    <link rel="stylesheet" type="text/css" href="styles/file.css" />
    <link rel="stylesheet" type="text/css" href="styles/manual.css" />
    <script type="text/javascript" src="plugins/jQuery/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="plugins/jQuery/jquery-ui.min.js"></script>
    <script type="text/javascript" src="plugins/jQuery/jquery.numeric.min.js"></script>
    <!--<script type="text/javascript" src="plugins/jQuery/jquery.validate.min.js"></script>-->
    <script type="text/javascript" src="scripts/mmnet.core.js"></script>
    <script type="text/javascript" src="mmnet.js"></script>
    <script type="text/javascript" src="scripts/mmnet.item.js"></script>
</head>

<body>
<?
if( !checkAdministrator() )
{
?>
    <div class="header"></div>
<?
    include('patterns/login.form.php');
}
else
{
?>
<div class="header">
<?
    $default_menu = null;
    if( $_SESSION[SES_ADMIN]['level'] >= 5 )
    {
        $default_menu = 'manager';
?>
    <a class="btn-like" href="javascript:MMNet.view('menu','manager');">管理員主頁</a>
<?
    }
    else
    {
        $default_menu = 'member';
?>
    <a class="btn-like" href="javascript:MMNet.view('menu','member');">個人主頁</a>
<?
    }
?>
    <a class="btn-like" href="javascript:MMNet.view('menu','item');">物品財產管理</a>

    <a class="btn-like -right" href="act/logout.php">登出</a>
    <a class="btn-like -right" href="javascript:MMNet.view('menu','manual');">手冊</a>
</div>

<nav id="menu-container"></nav>
<div id="main-content"></div>
<div id="footer-controller"></div>

<script type="text/javascript">
$(document).ready(function() {
    MMNet.Page.reload("<?=$default_menu?>");
    $("#main-content").scroll(function() {
        var remote = $("#remote");
        if( remote.length == 0 ) { return; }
        var top = $("#main-content").scrollTop();
        var presib = remote.prev().get(0);
        var posit = ( presib == null ) ? 0 : presib.offsetTop;
        if( posit < top ) {
            posit = top;
            remote.addClass("floating");
        } else { remote.removeClass("floating"); }
        remote.css("top",posit+"px");
    });
});
</script>
<?
}
?>
</body>

</html>