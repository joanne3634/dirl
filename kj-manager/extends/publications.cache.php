<?php
//require_once('../mod_general.php');
require_once('publications.config.php');

$cache_file = __DIR__ . '/cache/publications';
$newest = filemtime(PUBLICATION_FOLDER_ABS);
$latest = file_exists($cache_file) ? filemtime($cache_file) : 0;
if( $newest > $latest )
{
    require_once($_SERVER['DOCUMENT_ROOT'].'/kj-manager/mod_database.php');
    $dba = new Accessor();
    $cache = fopen($cache_file,'a');
    fwrite($cache,date('Y-m-d H:i:s').' -> Cache file in /pub/.'.PHP_EOL);
    fclose($cache);

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
}