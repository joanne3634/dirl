<?php
define( 'OUTPUT_FILENAME', 'pub.html' );
define( 'PAGE_TITLE', "Sheng-Wei Chen (a.k.a. Kuan-Ta Chen)'s Publications" );

$title_of_category = array(
    'cg' => 'Cloud Gaming',
    'og' => 'Online Gaming',
    'qoe' => 'Quality of Experience',
    'mmsys' => 'Multimedia Systems',
    'mm' => 'Multimedia Content Analysis',
    'cs' => 'Crowdsourcing',
    'sc' => 'Social Computing',
    'css' => 'Computational Social Science',
    'im' => 'Internet Measurement',
    'ns' => 'Network Security'
);

$index_of_category = array(
    'cg',
    'og',
    'qoe',
    'mmsys',
    'mm',
    'cs',
    'sc',
    'css',
    'im',
    'ns'
);

class BibTeX
{
    private $type;
    private $key = null;
    private $items = array();

    function __construct($raw_bibtex_string)
    {
        $raw_bibtex_string = trim($raw_bibtex_string);
        $bql = strpos($raw_bibtex_string,'{');
        $bgr = strrpos($raw_bibtex_string,'}');
        $this->type = strtoupper(substr($raw_bibtex_string,1,$bql-1));

        $fc = strpos($raw_bibtex_string,',');
        $this->key = substr($raw_bibtex_string,$bql+1,$fc-$bql-1);
        $content = substr($raw_bibtex_string,$fc+1,$bgr-$fc-1);
        //echo $content;

        $pointer = 0;
        $key = null;
        while( true )
        {
            //$last = $pointer;
            if( $key == null )
            {
                $key_end = strpos($content,'=',$pointer);
                if( $key_end === false ) { break; }
                $key = strtolower(trim(substr($content,$pointer,$key_end-$pointer)));
                $pointer = strpos($content,'{',$key_end);
                continue;
            }

            $bqleft = strpos($content,'{',$pointer+1);
            $bqright = strpos($content,'}',$pointer);
            while( $bqleft !== false AND $bqleft < $bqright )
            {
                $bqleft = strpos($content,'{',$bqleft+1);
                $bqright = strpos($content,'}',$bqright+1);
                if( $bqright === false ) { break; }
            }
            if( $bqright === false )
            {
                $this->items[$key] = substr($content,$pointer+1);
                break;
            }
            $this->items[$key] = substr($content,$pointer+1,$bqright-$pointer-1);
            $key = null;
            $pointer = $bqright+2;
        }
    }

    public function Type() { return $this->type; }
    public function KeyName() { return str_replace(':','_',$this->key); }

    public function get($key) { return $this->items[strtolower($key)]; }
    public function has($key)
    {
        $k = strtolower($key);
        if( !isset($this->items[$k]) ) { return false; }
        return !empty($this->items[$k]);
    }

    public function toHTML($link,$break = '<br/>')
    {
        $html = '';

        $title = $this->items['title'];
        $title = str_replace('{','',$title);
        $title = str_replace('}','',$title);

        $html = '<b>'.$title.'</b>';
        if( !empty($link) ) { $html = '<a href="'.$link.'">'.$html.'</a>'; }
        $html .= $break;
        //fwrite($fp,'<b>'.$title.'</b></a><br/>'.PHP_EOL);

        $author_list = explode(' and ',$this->items['author']);
        if( count($author_list) > 2 )
        {
            $last_author = array_pop($author_list);
            $comma = ( count($author_list) > 1 ) ? ',' : '';
            $html .= implode(', ',$author_list).$comma.' and '.$last_author.$break;
        } else { $html .= $this->items['author'].$break; }

        if( !$this->has('x_appear') )
        {
            switch (strtolower($this->type))
            {
            case 'article':
                $html .= '<i>'.$this->items['journal'].'</i>, ';
                if ($this->has('month')) { $html .= $this->items['month'].', '; }
                $html .= $this->items['year'].'.';
                break;
            case 'inproceedings':
                $html .= '<i>'.str_replace('Proceedings of ','',$this->items['booktitle']).'</i>.';
                break;
            case 'media':
                $html .= '<i>'.$this->items['journal'].'</i>, ';
                if ($this->has('month')) { $html .= $this->items['month'].', '; }
                $html .= $this->items['year'].'.';
                break;
            default:
                break;
            }
        } else { $html .= '<i>'.str_replace('Proceedings of','',$this->get('x_appear')).'</i>'; }
        return $html;
    }
}

$data = 'ktchen.bib';
$html = null;

switch( $argc )
{
case 3:
    $html = $argv[2];
    $data = $argv[1];
    break;
case 2:
    $data = $argv[1];
    break;
default:
    break;
}

$bibtexes = array();
if( file_exists($data) )
{
    $bibs = explode("\r\n\r\n",file_get_contents($data));
    foreach( $bibs as $bibtext ) { $bibtexes[] = new BibTeX($bibtext); }
}
else
{
    echo 'No BibTeX data file to convert.';
    die();
}

if( empty($html) ) { $html = OUTPUT_FILENAME; }
$fp = fopen($html,'w');
fwrite($fp,'<!DOCTYPE html>'.PHP_EOL.PHP_EOL);
fwrite($fp,'<html>'.PHP_EOL);
fwrite($fp,'<head>'.PHP_EOL);
fwrite($fp,'<meta charset="utf-8" />'.PHP_EOL);
fwrite($fp,'<meta name="keywords" content="Sheng-Wei Chen" />'.PHP_EOL);
fwrite($fp,'<meta name="keywords" content="Chun-Yang Chen" />'.PHP_EOL);
fwrite($fp,'<meta name="keywords" content="Kuan-Ta Chen" />'.PHP_EOL);
fwrite($fp,'<title>'.PAGE_TITLE.'</title>'.PHP_EOL);
fwrite($fp,'<base target="_blank">'.PHP_EOL);
fwrite($fp,'<link href="swc.css" rel="stylesheet">'.PHP_EOL);
fwrite($fp,'</head>'.PHP_EOL);

fwrite($fp,'<body>'.PHP_EOL);
fwrite($fp,'<img border="0" src="title_both.jpg" align="baseline" alt="Sheng-Wei Chen">'.PHP_EOL);
fwrite($fp,'<h3><a name="pub"></a><u>Publications</u> (<a href="http://scholar.google.com/citations?user=mgva3UgAAAAJ&hl=en">Google Scholar</a>, <a href="http://mmnet.iis.sinica.edu.tw/publication.html" target="_blank">Chronological</a>)</h3>'.PHP_EOL);
foreach( $title_of_category as $category => $title)
{
    fwrite($fp,'<h4>&nbsp;<img border="0" src="bullet.gif" alt="" width="15" height="15">&nbsp;'.$title.'</h4>'.PHP_EOL);
    fwrite($fp,'<ul>'.PHP_EOL.PHP_EOL);
    foreach( $bibtexes as $bib )
    {
        if( !$bib->has('X_TAG') ) { continue; }
        $tag_list = explode(',',$bib->get('X_TAG'));
        if( !in_array($category,$tag_list) ) { continue; }
        //fwrite($fp,'<li>');
        $key = $bib->KeyName();
        $pub_href = 'http://mmnet.iis.sinica.edu.tw/publication_detail.html?key=' . $key;
        if( $bib->has('X_FULLTEXT') )
        {
            $fulltext = $bib->get('X_FULLTEXT');
            $pub_href = str_replace('http://www.iis.sinica.edu.tw/~swc/','',$fulltext);
        }
        fwrite($fp,'<li>'.PHP_EOL.$bib->toHTML($pub_href,'<br/>'.PHP_EOL).PHP_EOL.'</li>'.PHP_EOL.PHP_EOL);
    }
    fwrite($fp,'</ul>'.PHP_EOL.PHP_EOL.PHP_EOL);
}
fwrite($fp,'<!--#include virtual="footer.html" -->'.PHP_EOL);
fwrite($fp,'</body>'.PHP_EOL);
fwrite($fp,'</html>');
fclose($fp);
