<?php
//require_once('../mod_general.php');
require_once('../mod_database.php');

function phone_fix($data,$type = null)
{
    $note = '';
    $result = array();
    switch( $type )
    {
    case 'cellphone':
        $pn = str_replace(array(' ','-'),'',trim($data));
        $result = array( substr($pn,0,4), substr($pn,4,3), substr($pn,7,3) );
        break;
    default:
        $pn = str_replace( array('(',')',' '), array('','','-'), trim($data) );
        $hash = strrpos($pn,'#');
        if( $hash !== false )
        {
            $note = '分機 ' . substr($pn,$hash+1);
            $pn = substr($pn,0,$hash);
        }
        $pns = explode('-',$pn);
        $local = array_shift($pns);
        $phone = implode('',$pns);
        $result = array( $local );
        $f = ( strlen($phone) > 7 ) ? 4 : 3;
        $result[] = substr($phone,0,$f);
        $result[] = substr($phone,$f);
        break;
    }
    if( empty($result) ) { return FALSE; }
    return array(
        'number' => implode('-',$result),
        'note' => $note
    );
}

$dba = new Accessor();
$users = $dba->query(
    'member_list',
    array(
        'type' => array( 'type' => 'not =', 'value' => 5 ),
        'v_state' => array( 'type' => 'not =', 'value' => 'removed' )
    ),
    array(
        'name' => 'json:language',
        'contact' => 'json'
    )
);

ob_start();
foreach( $users as $user )
{
    echo 'BEGIN:VCARD' . PHP_EOL;
    $name = $user['name'][$dba->default_language];
    echo 'N:' . mb_substr($name,0,1,'utf-8') . ';' . mb_substr($name,1,2,'utf-8') . PHP_EOL;
    echo 'FN:' . $name . PHP_EOL;
    if( isset($user['contact']['office']) )
    {
        $work = phone_fix($user['contact']['office']);
        echo 'TEL;WORK:' . $work['number'] . PHP_EOL;
        if( !empty($work['note']) ) { echo 'NOTE:' . $work['note'] . PHP_EOL; }
    }
    if( isset($user['contact']['cellphone']) )
    {
        $cell = phone_fix($user['contact']['cellphone'],'cellphone');
        echo 'TEL;CELL:' . $cell['number'] . PHP_EOL;
    }
    /*if( isset($user['contact']['Home']) )
    {
        $home = phone_fix($user['contact']['Home']);
        echo 'TEL;HOME:' . $home['number'] . PHP_EOL;
    }*/
    echo 'END:VCARD' . PHP_EOL . PHP_EOL;
}
$vcard = ob_get_contents();
ob_end_clean();

$vcf = fopen('cache/members.vcf','w');
fwrite($vcf,$vcard);
fclose($vcf);

echo 'done';