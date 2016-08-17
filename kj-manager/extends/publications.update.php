<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
require_once('../plugins/bibtex.php');

require_once('publications.config.php');

$dba = new Accessor();
$msgs = array();
$tags = array();
$data = array();

if( isset($_POST['taglist']) )
{
    $taglist = str_replace("\r\n",',',$_POST['taglist']);
    $tags = explode(',',$taglist);
}

if( isset($_POST['abstract']) )
{
    $data['abstract'] = array(
        'type' => 'string',
        'data' => htmlspecialchars($_POST['abstract'],ENT_QUOTES)
    );
}

if( isset($_POST['bibtex']) )
{
    $bib = new BibTeX($_POST['bibtex']);
    $data['type'] = array(
        'type' => 'string',
        'data' => $bib->Type()
    );
    $data['keyname'] = array(
        'type' => 'string',
        'data' => $bib->KeyName()
    );
    $data['bibtex'] = array(
        'type' => 'string',
        'data' => $bib->toString(array(),0,'')
    );

    // Get Title From BibTeX
    $title = $bib->get('title');
    $title = str_replace('{','',$title);
    $title = str_replace('}','',$title);
    $data['title'] = array(
        'type' => 'string',
        'data' => $title
    );
    // Get Author List From BibTeX
    $authors = explode(' and ',$bib->get('author'));
    $data['authors'] = array(
        'type' => 'string',
        'data' => implode('|',$authors)
    );
    // Get Collection From BibTeX
    $collection = '';
    if( !$bib->has('x_appear') )
    {
        if( $bib->has('booktitle') )
        {
            if( strtolower($bib->Type()) == 'inproceedings' ){
                $collection .= str_replace('Proceedings of ','',$bib->get('booktitle'));
            }else{
                $collection .= $bib->get('booktitle');
            }
        }

        if( $bib->has('journal') )
        {
            if( !empty($collection) ) { $collection .= ' '; }
            $collection .= $bib->get('journal');
            if( $bib->has('month') ) { $collection .= ', '.$bib->get('month'); }
            if( $bib->has('year') ) { $collection .= ', '.$bib->get('year'); }
        }

        if( $bib->has('extra_info') ) { $collection .= ' '.$bib->get('extra_info'); }

    } else { 
        if( strtolower($bib->Type()) == 'inproceedings' ){
            $collection = str_replace('Proceedings of','',$bib->get('x_appear')); 
        }else{
            $collection = $bib->get('x_appear'); 
        }
    }
    $data['collection'] = array(
        'type' => 'string',
        'data' => $collection
    );
    // Get TagList From BibTeX
    if( $bib->has('x_tag') )
    {
        $xtags = explode(',',$bib->get('x_tag'));
        $tags = array_unique(array_merge($tags,$xtags));
    }
    // Get PublishYear From BibTeX
    $data['pub_year'] = array(
        'type' => 'number',
        'data' => $bib->get('year')
    );
}

if( isset($_POST['related']) AND !empty($_POST['related']) )
{
    $related_keynames = array();
    foreach( explode("\r\n",$_POST['related']) as $relate )
    {
        $related_keyname = str_replace(':','_',$relate);
        $duplicate_keyname = $dba->query(
            'publications',
            array(
                'keyname' => array( 'value' => $related_keyname ),
                'limit' => 1
            ),
            array( 'keyname' => 'string' )
        );
        if( !empty($duplicate_keyname) )
        {
            $related_keynames[] = $related_keyname;
        }
    }
    $data['related'] = array(
        'type' => 'list:comma',
        'data' => array_unique($related_keynames)
    );
}

if( count($tags) > 0 )
{
    if( !isset($bib) )
    {
        $pub = $dba->get('publications',$_POST['id'],array('bibtex'=>'string'));
        $bib = new BibTeX($pub['bibtex']);
    }
    $bib->set('x_tag',implode(',',$tags));
    $data['taglist'] = array(
        'type' => 'list:comma',
        'data' => array_unique($tags)
    );
    $data['bibtex'] = array(
        'type' => 'string',
        'data' => $bib->toString(array(),0,'')
    );
}

$act = '新增';

$material = array();
// if has ID (means edit a publication), get old material setting.
if( isset($_POST['id']) )
{
    $act = '修改';
    $pub = $dba->get('publications',$_POST['id'],array('material'=>'json'));
    $material = $pub['material'];
    // report edit target id.
    if( isset($_POST['abstract']) ) { $_SESSION['target'] = $_POST['id']; }
}
// if has new one, replace old with new one.
if( isset($_POST['material']) )
{
    foreach( $_POST['material'] as $m_key => $m_value )
    {
        $material[$m_key] = $m_value;
    }
}
// if has KEYNAME, check publication folder to get material.
if( isset($data['keyname']) )
{
    $keyname = $data['keyname']['data'];
    $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'],'/');
    foreach( $publication_file_type_list as $m_key => $m_data )
    {
        if( !isset($m_data['suffix']) ) { continue; }
        if( isset($material[$m_key]) ) { unset($material[$m_key]); }
        $filepath = PUBLICATION_FOLDER . '/' . $keyname . $m_data['suffix'];
        if( !file_exists($doc_root.$filepath) ) { continue; }
        $material[$m_key] = $filepath;
    }
}
$data['material'] = array(
    'type' => 'json',
    'data' => empty($material) ? array('count'=>0) : $material
);


$mod_id = $dba->input( 'publications', isset($_POST['id']) ? $_POST['id'] : null, $data );
//$dba->writeLog($_SESSION[SES_ADMIN]['id'],'create','建立新頁面：'.$_POST['title'].' (page_menu#'.$pageid.')。');
$dba->writeLog(0,'MGE::Report',$_SESSION['user_mmnet'].' '.$act.' publication#'.$mod_id.'。');
die('{"status":"done","publication":'.$mod_id.',"message":"'.implode(',',$msgs).'"}');
