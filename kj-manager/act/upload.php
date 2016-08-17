<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();

if( !isset($_POST['folder_id']) ) { die('{"error":412,"message":"No FOLDER is assigned to store file."}'); }
$folder = $dba->query(
    'file_folder',
    array(
        'id' => array( 'value' => $_POST['folder_id'] ),
        'v_state' => array( 'value' => 'working' ),
        'limit' => 1
    ),
    array(
        'id' => 'number',
        'name' => 'string',
        'path' => 'string',
        'level' => 'number',
        'stint' => 'string'
    )
);
if( empty($folder) ) { die('{"error":412,"message":"Wrong FOLDER id, or point to an unavailable FOLDER."}'); }

$success = 0;
$results = array();
foreach( $_FILES['uploads']['tmp_name'] as $num => $temp )
{
    if( empty($temp) )
    {
        $result = array(
            'status' => 'fail',
            'file' => $_FILES['uploads']['name'][$num],
            'message' => 'Unknown Exception.'
        );
        switch($_FILES['uploads']['error'][$num])
        {
        case UPLOAD_ERR_INI_SIZE:
            $result['message'] = 'File size is bigger than '.ini_get('upload_max_filesize').'.';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $result['message'] = 'Too many files in this upload.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $result['message'] = 'The uploaded file was only partially uploaded.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $result['message'] = 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $result['message'] = 'Missing a temporary folder.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $result['message'] = 'Failed to write file.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $result['message'] = 'A PHP extension stopped the file upload.';
            break;
        default:
            break;
        }
        $results[] = $result;
        continue;
    }

    $f_name = $_FILES['uploads']['name'][$num];
    $dot = strrpos($f_name,'.');
    $f_type = substr( $f_name, $dot+1 );
    $f_name = substr( $f_name, 0, $dot );
    if( isset($_POST['name']) ) { $f_name = $_POST['name']; }
    $file_id = md5($f_name.'_dir'.$folder['id']);
    $duplicate = $dba->get('file_list',$file_id,array('last_mod'=>'string'));
    if( !empty($duplicate) AND ( !isset($_POST['overwrite']) OR $_POST['overwrite'] === 'false' ) )
    {
        $results[] = array(
            'status' => 'fail',
            'file' => $f_name,
            'message' => 'Duplicate file name.'
        );
        continue;
    }

    $info = array();
    $info['origin'] = $_FILES['uploads']['name'][$num];
    $info['size'] = $_FILES['uploads']['size'][$num] / 1024;

    $tags = array();
    if( isset($_POST['tags']) )
    {
        foreach( explode('/\s\r\n,/',$_POST['tags']) as $tag )
        {
            if( empty($tag) ) { continue; }
            $tags[] = $tag;
        }
    }

    $dir_path = rtrim($folder['path'],'/');
    $file_path = $dir_path . '/' . $file_id . '.' . $f_type;
    move_uploaded_file($temp,$file_path);
    $dba->input('file_list',$file_id,array(
        'folder' => array(
            'type' => 'number:reference',
            'data' => $folder['id']
        ),
        'type' => array(
            'type' => 'string',
            'data' => $f_type
        ),
        'info' => array(
            'type' => 'json',
            'data' => $info
        ),
        'name' => array(
            'type' => 'string',
            'data' => $f_name
        ),
        'taglist' => array(
            'type' => 'list:comma',
            'data' => array_unique($tags)
        )
    ));
    $results[] = array(
        'status' => 'success',
        'refer' => str_replace($_SERVER['DOCUMENT_ROOT'],'',$file_path),
        'path' => $file_path
    );
    $success++;
}
$dba->writeLog($_SESSION[SES_ADMIN]['id'],'upload','上傳 '.$success.' 個檔案到資料夾「'.$folder['name'].'」中。');
die(json_encode($results));
