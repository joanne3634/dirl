<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
require_once('../plugins/bibtex.php');

$dba = new Accessor();
$filter = array( 'order' => array( 'pub_year' => 'DESC' ) );
$suffix = '';
if( isset($_GET['year']) )
{
    $filter = array( 'pub_year' => array( 'value' => $_GET['year'] ) );
    $suffix = substr($_GET['year'],2,2);
}
$pub_list = $dba->query( 'publications', $filter, array(
    'material' => 'json',
    'bibtex' => 'string:lines'
) );

$filename = 'bibtex-cache/ktchen'.$suffix.'.bib';
$fp = fopen($filename,'w');
if( $fp )
{
    foreach( $pub_list as $pub )
    {
        $bibx = new BibTeX($pub['bibtex']);
        $mat = $pub['material'];
        if( isset($mat['fulltext']) ) { $bibx->set('X_FULLTEXT',$mat['fulltext']); }
        fwrite($fp,$bibx->toString(array(),10)."\r\n\r\n");
    }
    fclose($fp);
}
header('Content-type: application/x-bibtex; charset=utf-8');
header('location: '.$filename);
die();