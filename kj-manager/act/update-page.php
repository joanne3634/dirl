<?
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"Authority required."}'); }
$dba = new Accessor();
$result = array();

if( isset($_POST['id']) )
{   // Update DB:page_menu
    $dba->input(
        'page_menu',
        $_POST['id'],
        array(
            'v_state' => array(
                'type' => 'string',
                'data' => $_POST['visibility']
            ),
            'title' => array(
                'type' => 'lang',
                'data' => $_POST['title']
            ),
            /*'image' => array(
                'type' => 'json',
                'data' => $_POST['image']
            ),*/
            'page' => array(
                'type' => 'string',
                'data' => $_POST['page']
            ),
            'folder' => array(
                'type' => 'string',
                'data' => $_POST['folder']
            )
        )
    );
    $result['page'] = $_POST['id'];
}

if( isset($_POST['section']) )
{   // Update DB:page_section
    foreach( $_POST['section'] as $section_id => $data )
    {
        if( !isset($data['state']) ) { continue; }
        $dba->input('page_section',$section_id,array(
            'v_state' => array(
                'type' => 'string',
                'data' => $data['state']
            ),
            'title' => array(
                'type' => 'lang',
                'data' => $data['title']
            ),
            /*'image' => array(
                'type' => 'string',
                'data' => $data['image']
            ),*/
            'type' => array(
                'type' => 'string',
                'data' => $data['type']
            ),
            'source' => array(
                'type' => 'json',
                'data' => isset($data['source']) ? $data['source'] : array('table'=>'_NONE_','liveupdate'=>'false')
            ),
            'content' => array(
                'type' => 'json',
                'data' => $data['content']
            ),
            'last_mod' => array( 'data' => 'now()' )
        ));
        if( !isset($result['section']) ) { $result['section'] = array(); }
        $result['section'][] = $section_id;
    }
}
$dba->writeLog($_SESSION[SES_ADMIN]['id'],'modify','修改頁面「'.$_POST['title'][$dba->default_language].'(page_menu#'.$_POST['id'].')」。');
die(json_encode($result));