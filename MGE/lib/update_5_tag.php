<?php
session_start();
unset($_SESSION['target']);

$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
$pdo = new PDO('mysql:host=localhost;dbname=MMNET_WEB','mmnet_web','web123',$options);
if( !$pdo ) { die('{"error":100,"message":"PDO cannot created."}'); }

$row = $pdo->query('SELECT raw FROM publication WHERE id='.$_POST['id'].' LIMIT 1');
if( $row === false ) { die('{"error":102,"message":"SQL Exception: no id['.$POST['id'].']"}'); }

$article = $row->fetch(PDO::FETCH_ASSOC);
include("bibtex.html");
$bib = new BibTeX($article['raw']);
$bib->items['x_tag'] = $_POST['tag'];
$sql = "UPDATE publication SET raw='".$bib->to_string(0,"\n")."' WHERE id={$_POST['id']} LIMIT 1";

if( !$pdo->exec($sql) )
{
    $err_msg = $pdo->errorInfo();
    die('{"error":102,"message":"SQL Exception:'.$err_msg[2].'"}');
}

$_SESSION['target'] = $_POST['id'];
echo '{"code":0,"affect":'.$_POST['id'].',"message":"Update publication tag."}';