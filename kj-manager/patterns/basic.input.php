<?php
if( !isset($dba) )
{
    require_once('../mod_general.php');
    if( !checkAdministrator() ) { die('{"error":403,"message":"No Authority."}'); }

    require_once('../mod_database.php');
    $dba = new Accessor();
}
if( !isset($section) )
{
    if( !isset($_POST['id']) ) { die(); }
    $section = $dba->get('page_section',$_POST['id'],array(
        'id' => 'number:reference',
        'content' => 'json'
    ));
    $section['prefix'] = 'section['.$section['id'].']';
}

$langs = $dba->query(
    TABLE_LANG,
    array(
        'v_state' => array( 'value' => 'working' )
    ),
    array(
        'id' => 'string'
    )
);
foreach( $langs as $language )
{
    kjPHP\drawHTMLElement('textarea',array(
        'name' => $section['prefix'].'[content]['.$language['id'].']',
        'lang' => $language['id'],
        'style' => 'display:none;'
    ),kjPHP\toHTML($section['content'][$language['id']],false));
}
?>

<script>
MMNet.Section.select('<?=$dba->default_language?>',$('#s<?=$section['id']?><?=$dba->default_language?>'));
</script>
