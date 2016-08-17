<?php

    
    $files = array();

    // scan Directory
    $ignore_list = array( '.', '..' );
    foreach( scandir($dir_path) as $entry )
    {
        if( in_array($entry,$ignore_list) ) { continue; }
        if( is_dir($dir_path.'/'.$entry) ) { continue; } // no recursive.
        $files[] = $entry;/*array(
            'id' => md5($file_path),
            'type' => substr( $file_path, strrpos($file_path,'.')+1 ),
            'path' => ,
            'url' => str_replace($_SERVER['DOCUMENT_ROOT'],'/',$file_path)
        );*/
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
                'data' => $_POST['id']
            ),
            'type' => array(
                'type' => 'string',
                'data' => $ext
            ),
            'info' => array(
                'type' => 'json',
                'data' => array()//kjPHP\getFileInfo($file_path)
            ),
            'name' => array(
                'type' => 'string',
                'data' => $name
            )/*,
            'taglist' => array(
                'type' => 'string',
                'data' => ''
            )*/
        ));
    }