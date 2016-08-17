<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
$dba = new Accessor();

$folder = $dba->get(
    'file_folder',
    $_POST['id'],
    array(
        'name' => 'string',
        'path' => 'string',
        'level' => 'number',
        'stint' => 'string',
        'last_mod' => 'datetime'
    )
);
if( !file_exists($folder['path']) OR !is_dir($folder['path']) ) { die(); }

$dir_path = rtrim($folder['path'],'/');
$status = stat($dir_path);
if( $status['mtime'] > strtotime($folder['last_mod']) ) { include('../act/sync-folder.php'); }
?>
<div class="file-view">
<?
$dir_url = str_replace($_SERVER['DOCUMENT_ROOT'],'',$dir_path);
$file_list = $dba->query(
    'file_list',
    array(
        'folder' => array( 'value' => $_POST['id'] )
    ),
    array(
        'id' => 'string:unique',
        'type' => 'string',
        'info' => 'json',
        'name' => 'string',
    )
);
foreach( $file_list as $file )
{
    $rel_path = null;
    $rel_path_list = array(
        '/' . $file['name'] . '.' . $file['type'],
        '/' . $file['id'] . '.' . $file['type']
    );
    while( $cand = array_shift($rel_path_list) )
    {
        if( !file_exists($dir_path.$cand) ) { continue; }
        $rel_path = $cand;
        break;
    }
    if( $rel_path == null ) { continue; }

    $classes = array( 'file-icon' );
    $bgimage = 'unknown.png';
    switch(strtolower($file['type']))
    {
    case 'jpg':
    case 'gif':
    case 'png':
        $bgimage = $dir_url . $rel_path;
        $size = getimagesize($dir_path.$rel_path);
        $classes[] = ( $size[0] > 150 or $size[1] > 100 ) ? '-full-image' : '-image';
        break;
    case 'txt':
    case 'htm':
    case 'html':
    case 'php':
        $bgimage = 'text.png';
        $classes[] = '-full-image';
        break;
    default:
        break;
    }
?>
    <div class="<?=implode(' ',$classes)?>" style="background-image:url(<?=$bgimage?>);" onclick="MMNet.ui.report('Dialog',{'submit':'<?=$bgimage?>'},true);">
        <?=$file['name']?>
    </div>
<?
}
?>
    <div class="end-of-line"></div>
</div>
<button type="button" onclick="MMNet.ui.close('Dialog');">取消</button>