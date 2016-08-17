<?php
$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
$pdo = new PDO('mysql:host=localhost;dbname=MMNET_WEB','mmnet_web','web123',$options);
if( !$pdo ) { die('PDO cannot created.'); }

$query = $pdo->query('SELECT raw FROM publication WHERE year='.$_GET['year'].' AND visible=1 ORDER BY year DESC, x_priority DESC, id DESC');
if( $query === false ) { die('No data of year '.$POST['year'].'.'); }

include("bibtex.html");
$filename = 'bibtex-cache/ktchen'.substr($_GET['year'],2,2).'.bib';
$fp = fopen($filename,'w');
if( $fp )
{
    while( $article = $query->fetch(PDO::FETCH_ASSOC) )
    {
        $bib = new BibTeX($article['raw']);
        fwrite($fp,$bib->to_string(10)."\r\n\r\n");
    }
    fclose($fp);
}
header('Content-type: application/x-bibtex; charset=utf-8');
header('location: '.$filename);
die();