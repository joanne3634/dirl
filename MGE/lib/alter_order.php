<?php
session_start();
include("link.html");

if($con) {
	if ($db_selected) {
        $origin = mysql_query('SELECT x_priority FROM publication WHERE id='.$_POST['id']);
        $target = mysql_query('SELECT x_priority FROM publication WHERE id='.$_POST['with']);
        if( $target_new_row = mysql_fetch_row($origin) ) {
            mysql_query('UPDATE publication SET x_priority='.$target_new_row[0].' WHERE id='.$_POST['with']);
        } else { die('3&&&'); }
        if( $origin_new_row = mysql_fetch_row($target) ) {
            mysql_query('UPDATE publication SET x_priority='.$origin_new_row[0].' WHERE id='.$_POST['id']);
        } else { die('3&&&'); }
        $alter_target = $_POST['id'];
        include('../panel_5.html');
    } else { die('2&&&'); }
} else { die('1&&&'); }
