<?php
if( !isset($dba) )
{
    require_once('../mod_database.php');
    $dba = new Accessor();
}
if( !isset($folder_id) )
{
    if( !isset($_POST['id']) )
    {
        die();
    }
    $folder_id = $_POST['id'];
}

$folder = $dba->get(
    'file_folder',
    $folder_id,
    array(
        'id' => 'number',
        'path' => 'string',
        'level' => 'number',
        'stint' => 'string'
    )
);
$dir_path = rtrim($folder['path'],'/');
$files = array();

// scan Directory
$ignore_list = array( '.', '..' );
foreach( scandir($dir_path) as $entry )
{
    if( in_array($entry,$ignore_list) ) { continue; }
    if( is_dir($dir_path.'/'.$entry) ) { continue; } // no recursive.
    $files[] = $entry;
}

// Synchronize file_list
//$dba->execute('DELTE FROM file_list WHERE folder='.$_POST['id']);
foreach( $files as $file_name )
{
    $file_path = $dir_path . '/' . $file_name;
    $ext = substr( $file_name, strrpos($file_name,'.')+1 );
    $name = substr( $file_name, 0, strrpos($file_name,'.') );
    $dba->input('file_list',md5($file_path),array(
        'folder' => array(
            'type' => 'number',
            'data' => $folder['id']
        ),
        'type' => array(
            'type' => 'string',
            'data' => strtolower($ext)
        ),
        'info' => array(
            'type' => 'json',
            'data' => array()//kjPHP\getFileInfo($file_path)
        ),
        'name' => array(
            'type' => 'string',
            'data' => $name
        )
    ));
}

$dba->input('file_folder',$folder_id,array( 'last_mod' => array( 'data' => 'now()' ) ));