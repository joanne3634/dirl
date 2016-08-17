<?
session_start();
unset($_SESSION['target']);

function mass_replace($str,$pairs)
{
    $result = $str;
    foreach( $pairs as $k => $v ) { $result = str_replace($k,$v,$result); }
    return $result;
}

include("bibtex.html");
$bib = new BibTeX($_POST['bibtex']);

$data = array(
    null,
    $bib->type,
    str_replace(":", "_", $bib->uniq_key),
    mass_replace($_POST['abstract'],array("'"=>"\'","\""=>"\\\"")),
    $bib->items['year'],
    $bib->items['group'],
    $bib->items['publisher'],
    $bib->items['page-start'],
    $bib->items['page-end'],
    $bib->items['pages'],
    $bib->items['address'],
    $bib->items['url'],
    $bib->items['volume'],
    $bib->items['chapter'],
    $bib->items['journal'],
    $bib->items['author'],
    $bib->to_string(0,"\n"),
    mass_replace($bib->items['title'],array('{'=>'','}'=>'')),
    $bib->items['booktitle'],
    $bib->items['folder'],
    $bib->items['type'],
    $bib->items['linebegin'],
    $bib->items['lineend'],
    1,
    $bib->items['crossref'],
    $bib->items['key'],
    $bib->items['month'],
    $bib->items['note'],
    $bib->items['number'],
    $bib->items['organization'],
    $bib->items['x_domestic'],
    $bib->items['x_appear'],
    $bib->items['num_month'],
    0
);

$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
$pdo = new PDO('mysql:host=localhost;dbname=MMNET_WEB','mmnet_web','web123',$options);
if( !$pdo ) { die('{"error":100,"message":"PDO cannot created."}'); }
$stmt = $pdo->prepare('INSERT INTO publication VALUE( ?'.str_repeat(",?", count($data)-1).' )');
if( !$stmt->execute($data) )
{
    $err_msg = $pdo->errorInfo();
    die('{"error":102,"message":"SQL Exception:'.$err_msg[2].'"}');
}

$new_id = $pdo->lastInsertId();
if( !$pdo->exec('UPDATE publication SET x_priority=id WHERE id='.$new_id.' LIMIT 1') )
{
    $err_msg = $pdo->errorInfo();
    die('{"error":102,"message":"SQL Exception:'.$err_msg[2].'"}');
}

$_SESSION['target'] = $new_id;
echo '{"code":0,"affect":'.$new_id.',"message":"Update publication."}';