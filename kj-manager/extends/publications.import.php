<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

require_once('publications.config.php');

$dba = new Accessor();
/*
if( file_exists($argv[1]) )
{
    $csvp = fopen($argv[1],'r');
    $header = fgetcsv($csvp);
    $key_num = array_search('mmpub_key',$header);
    $ref_num = array_search('out_fn',$header);
    while( $pubs = fgetcsv($csvp) )
    {
        $key = $pubs[$key_num];
        $pub = $dba->query('publications',array('keyname'=>array('value'=>$key),'limit'=>1),array('id'=>'number','material'=>'json'));
        var_dump($pub);
        $mat = $pub['material'];
        if( !isset($mat['fulltext']) )
        {
            $fulltext = 'http://www.iis.sinica.edu.tw/~swc/pub/'.$pubs[$ref_num].'.html';
            $mat['fulltext'] = $fulltext;
        }
        $dba->input('publications',$pub['id'],array(
            'material' => array(
                'type' => 'json',
                'data' => $mat
            )
        ));
    }
    fclose($csvp);
} else { echo 'No Data.'; }
*/
$pubs = $dba->query('publications',array(),array('id'=>'number','keyname'=>'string','material'=>'json'));
$files = array(
    array( 'ext'=>'.pdf', 'key'=>'fulltext_pdf' ),
    array( 'ext'=>'.ppt', 'key'=> 'slides_ppt' ),
    array( 'ext'=>'_slides.pdf', 'key'=> 'slides_pdf' ),
    array( 'ext'=>'_poster.pdf', 'key'=> 'poster_pdf' )
);

foreach( $pubs as $pub )
{
    $mat = $pub['material'];
    $modified = false;
    foreach ($files as $file) {
        $f = '/srv/www/htdocs/home/pub/' . $pub['keyname'] . $file['ext'];
        if( !file_exists($f) ) { continue; }
        $k = $file['key'];
        if( !isset($mat[$k]) )
        {
            $mat[$k] = '/pub/' . $pub['keyname'] . $file['ext'];
            var_dump($mat);
            echo PHP_EOL;
            $modified = true;
        }
    }
    if( $modified )
    {
        $dba->input('publications',$pub['id'],array(
            'material' => array(
                'type' => 'json',
                'data' => $mat
            )
        ));
    }
}