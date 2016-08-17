<?php
if( !isset($_POST['id']) ) { die('{"error":404,"message":"No page ID to save page file."}'); }
define( 'TEMP_DIR', '/_temp/' );
define( 'PAGE_DIR', '/_page/' );

require_once('../mod_general.php');
require_once('../mod_database.php');
require_once('../plugins/Michelf/Markdown.inc.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

$page = $dba->get(
    'page_menu',
    $_POST['id'],
    array(
        'title' => 'json',
        'image' => 'json',
        'page' => 'string',
        'folder' => 'string'
    )
);

$pagename = '頁面「'.$page['title'][$dba->default_language].'」';

$file = $page['page'];
if( empty($file) ) { $file = sprintf('page%04d',$_POST['id']); }

$url_path = TEMP_DIR;

$path = $_SERVER['DOCUMENT_ROOT'].$url_path;
if( !file_exists($path) ) { mkdir($path); }

$lines = array('<?php');
$lines[] = "include('../_template/database.php');";
$lines[] = '$subtitle = '.kjPHP\toString($page['title']).';';
$lines[] = "include('../_template/header.php');";
$lines[] = "include('../_template/menu.php');";
$lines[] = '?>';

$lines[] = '<div id="main_content">';
$sections = $dba->_select('page_section','menupage',$_POST['id']);
foreach( $sections as $num => $section )
{
    $sec_var = '$sec'.($num+1);
    $lines[] = '<?';
    $lines[] = $sec_var.' = array();';
    $lines[] = $sec_var.'[\'title\'] = '.kjPHP\toString(json_decode($section['title'],true)).';';
    $lines[] = $sec_var.'[\'image\'] = '.kjPHP\toString(json_decode($section['image'],true)).';';
    $lines[] = '?>';
    $lines[] = '    <div class="main_item">';
    $lines[] = '        <h2><?='.$sec_var.'[\'title\'][$lang]?></h2>';
    /*$lines[] = '        <img src="<?='.$sec_var.'[\'image\'][$lang]?>" alt="<?='.$sec_var.'[\'title\'][$lang]?>" />';*/

    switch($section['type'])
    {
    case 'basic.input':
        $contents = json_decode($section['content'],true);
        $lines[] = '<?';
        $lines[] = 'switch($lang)';
        $lines[] = '{';
        foreach( $contents as $lang => $content )
        {
            if( $lang == $dba->default_language ) { continue; }
            $lines[] = "case '{$lang}':";
            $lines[] = '?>';
            $lines[] = kjPHP\toHTML($content);
            $lines[] = '<?';
            $lines[] = '    break;';
        }
        $lines[] = 'default:';
        $lines[] = '?>';
        $lines[] = kjPHP\toHTML($contents[$dba->default_language]);
        $lines[] = '<?';
        $lines[] = '    break;';
        $lines[] = '}';
        $lines[] = '?>';
        break;
    case 'markdown.input':
        $contents = json_decode($section['content'],true);
        $lines[] = '<?';
        $lines[] = 'switch($lang)';
        $lines[] = '{';
        foreach( $contents as $lang => $content )
        {
            if( $lang == $dba->default_language ) { continue; }
            $lines[] = "case '{$lang}':";
            $lines[] = '?>';
            $lines[] = Michelf\Markdown::defaultTransform(kjPHP\toHTML($content));
            $lines[] = '<?';
            $lines[] = '    break;';
        }
        $lines[] = 'default:';
        $lines[] = '?>';
        $lines[] = Michelf\Markdown::defaultTransform(kjPHP\toHTML($contents[$dba->default_language]));
        $lines[] = '<?';
        $lines[] = '    break;';
        $lines[] = '}';
        $lines[] = '?>';
        break;
    case 'table.input':
        $source = json_decode(urldecode($section['source']),true);
        if( !$dba->hasTable($source['table']) ) { break; }
        $sql = 'SELECT * FROM '.$source['table'];
        if( isset($source['where']) AND !empty($source['where']) ) { $sql .= ' WHERE '.$source['where']; }
        if( isset($source['limit']) AND $source['limit'] > 0 ) { $sql .= ' LIMIT '.$source['limit']; }

        $content = json_decode($section['content'],true);
        $lines[] = kjPHP\toHTML($content['header']);
        if( isset($source['liveupdate']) AND $source['liveupdate'] )
        {
            $lines[] = '<?';
            $lines[] = $sec_var.'_query = $pdo->query("'.$sql.'");';
            $lines[] = 'while( $row = '.$sec_var.'_query->fetch(PDO::FETCH_ASSOC) )';
            $lines[] = '{';
            $lines[] = '?>';
            $lines[] = kjPHP\toHTML($content['sample']);
            $lines[] = '<?';
            $lines[] = '}';
            $lines[] = '?>';
        }
        else
        {
            $sec_matches = array();
            $sec_sample = kjPHP\toHTML($content['sample']);
            preg_match_all('/\$row\[\'(.+)\'\]/',$sec_sample,$sec_matches);
            if( count($sec_matches) > 0 )
            {
                $sec_query = $dba->query($sql);
                while( $row = $sec_query->fetch(PDO::FETCH_ASSOC) )
                {
                    $line = $sec_sample;
                    foreach( $sec_matches[1] as $num => $match_key )
                    {
                        $search = $sec_matches[0][$num];
                        $line = str_replace($search,"'{$row[$match_key]}'",$line);
                    }
                    $lines[] = $line;
                }
            }
            
        }
        $lines[] = kjPHP\toHTML($content['footer']);
        break;
    default:
        break;
    }

    $lines[] = '    </div>';
}
$lines[] = '</div>';

$lines[] = '<?';
$lines[] = "include('../_template/footer.php');";

$fp = fopen($path.$file.'.php','w');
if( !$fp )
{
    $dba->writeLog($_SESSION[SES_ADMIN]['id'],'fail','儲存'.$pagename.'失敗。');
    die('{"error":500,"message":"Cannot create file at ['.$path.$file.'.php]."}');
}
fwrite($fp,implode(PHP_EOL,$lines));
fclose($fp);

$dba->writeLog($_SESSION[SES_ADMIN]['id'],'save','儲存'.$pagename.'於'.$path.$file.'.php');
die('{"url":"'.$url_path.$file.'.php"}');