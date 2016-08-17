<?php
session_start();
unset($_SESSION['target']);

include("bibtex.html");

function mass_replace($str,$pairs)
{
    $result = $str;
    foreach( $pairs as $k => $v ) { $result = str_replace($k,$v,$result); }
    return $result;
}

$bib = new BibTeX($_POST['bibtex']);
$data = array(
    'entry_type'    => $bib->type,
    'uniq_key'      => str_replace(':','_',$bib->uniq_key),
    'abstract'      => mass_replace($_POST['abstract'],array("'"=>"\'","\""=>"\\\"")),
    'year'          => $bib->items['year'],
    'groupes'       => $bib->items['group'],
    'publisher'     => $bib->items['publisher'],
    'page_start'    => $bib->items['page-start'],
    'page_end'      => $bib->items['page-end'],
    'pages'         => $bib->items['pages'],
    'address'       => $bib->items['address'],
    'url'           => $bib->items['url'],
    'volume'        => $bib->items['volume'],
    'chapter'       => $bib->items['chapter'],
    'journal'       => $bib->items['journal'],
    'author'        => $bib->items['author'],
    'raw'           => $bib->items['raw'],
    'title'         => mass_replace($bib->items['title'],array('{'=>'','}'=>'')),
    'booktitle'     => $bib->items['booktitle'],
    'folder'        => $bib->items['folder'],
    'types'         => $bib->items['type'],
    'linebegin'     => $bib->items['linebegin'],
    'lineend'       => $bib->items['lineend'],
    'crossref'      => $bib->items['crossref'],
    'key'           => $bib->items['key'],
    'month'         => $bib->items['month'],
    'note'          => $bib->items['note'],
    'number'        => $bib->items['number'],
    'organization'  => $bib->items['organization'],
    'x_domestic'    => $bib->items['x_domestic'],
    'x_appear'      => $bib->items['x_appear'],
    'num_month'     => $bib->items['num_month']
);

//die(json_encode($data));

$data_array = array();
foreach( $data as $field => $value ) { $data_array[] = "`{$field}`='{$value}'"; }
$sql = 'UPDATE publication SET '.implode(', ',$data_array).' WHERE id='.$_POST['id'].' LIMIT 1';

$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
$pdo = new PDO('mysql:host=localhost;dbname=MMNET_WEB','mmnet_web','web123',$options);
if( !$pdo ) { die('{"error":100,"message":"PDO cannot created."}'); }
if( !$pdo->exec($sql) )
{
    $err_msg = $pdo->errorInfo();
    die('{"error":102,"message":"SQL Exception:'.$err_msg[2].'"}');
}

$_SESSION['target'] = $_POST['id'];
echo '{"code":0,"affect":'.$_POST['id'].',"message":"Update publication."}';