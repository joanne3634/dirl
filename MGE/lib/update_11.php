<?
require_once('/home/kj/mmnet-manager-2015/mod_database.php');
$dba = new Accessor();
session_start();

$split_char = "&&&";

if($_SESSION['login_mmnet'] == "ok_mmnet"){
	if( isset($_SESSION['pos']) && $_SESSION['pos'] == "panel_11" ) {
		include("link.html");
		if($con) {
			if($db_selected) {
                if ( $_POST['identity'] == '' ) {
                    $status = mysql_query("SHOW TABLE STATUS LIKE 'collaborators'");
                    $status_row = mysql_fetch_array($status);
                    $status_next = $status_row['Auto_increment'];
                    mysql_free_result($status);

                    $sql = 'INSERT INTO collaborators VALUES ('
                        . "null,'{$_POST['image_link']}',{$_POST['image_show']},'{$_POST['language']}',1,{$status_next},'{$_POST['lab_link']}','{$_POST['lab_name']}','{$_POST['unit_link']}','{$_POST['unit_name']}','{$_POST['chair_link']}','{$_POST['chair_name']}'"
                        . ')';
                    $result = mysql_query($sql);
                    if ($result && mysql_affected_rows() == 1) {
                        //echo "0".$split_char;                        傳送錯誤碼，成功
$dba->writeLog(0,'MGE::Report',$_SESSION['user_mmnet'].' 新增「合作夥伴」 '.$_POST['chair_name'].' 的資料。');
                    } else { echo "3".$split_char.mysql_error(); }  // 傳送錯誤碼，sql失敗
                } else {
                    $sql = 'UPDATE collaborators SET '
                        . "image_link='{$_POST['image_link']}',image_show={$_POST['image_show']},"
                        . "language='{$_POST['language']}',"
                        . "lab_link='{$_POST['lab_link']}',lab_name='{$_POST['lab_name']}',"
                        . "unit_link='{$_POST['unit_link']}',unit_name='{$_POST['unit_name']}',"
                        . "chair_link='{$_POST['chair_link']}',chair_name='{$_POST['chair_name']}'"
                        . " WHERE identity={$_POST['identity']}";
                    $result = mysql_query($sql);
$dba->writeLog(0,'MGE::Report',$_SESSION['user_mmnet'].' 更新「合作夥伴」的資料。 (id: '.$_POST['identity'].')');
                    //echo "0".$split_char;        傳送錯誤碼，成功
                }
            } else { echo "2".$split_char; }    // 傳送錯誤碼，Table 失敗
        } else { echo "1".$split_char; }        // 傳送錯誤碼，Connect 失敗
    } else { echo "5".$split_char; }            // 傳送錯誤碼，參數錯誤
} else { echo "1".$split_char; }                // 傳送錯誤碼

include('../panel_11.html');