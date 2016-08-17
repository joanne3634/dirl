<?php
require_once('mod_general.php');
require_once('mod_database.php');
if( !checkAdministrator(9) )
{
    die('<h1>非系統管理人請勿使用測試功能。</h1>');
}
?>
<a class="btn-like" href="javascript:MMNet.Controller.load('member.proxy.form');">代理</a>