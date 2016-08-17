<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
require_once('item.function.php');

if( !checkAdministrator(5) ) { die('{"error":401,"message":"No Authority."}'); }

$dba = new Accessor();

$itf = new ItemFilter();
$itf->insert('TERM','type','type','類別');
$itf->insert('TERM','place','place','地點');
$itf->insert('USER','member','user','使用人');
$itf->insert('NOTEMPTY','outsider','only_outside','只顯示外借物品');
$itf->insert('TEXT','outsider','outsider','外借使用人');

$type_types = array();
$types = array();
$place_types = array();
$places = array();
$terms = $dba->cache('term_list',array(
    'type' => 'number:reference',
    'keyname' => 'string',
    'title' => 'json:language'
));
$lang = $dba->default_language;
foreach( $terms as $term )
{
    // Type
    if( $term['keyname'] == 'item-type' ) { $type_types[] = $term['id']; }
    if( in_array( $term['type'], $type_types ) ) { $types[$term['id']] = $term['title'][$lang]; }
    // Place
    if( $term['keyname'] == 'location' ) { $place_types[] = $term['id']; }
    if( in_array( $term['type'], $place_types ) )
    {
        $prefix = isset($places[$term['type']]) ? $places[$term['type']] . ' ' : '';
        $places[$term['id']] = $prefix . $term['title'][$lang];
        $place_types[] = $term['id'];
    }
}

$users = $dba->cache( 'member_list', array( 'name' => 'json:language' ), array( 'type' => 'OPTION_ARRAY' ) );

$lines = array( '編號,隸屬單位,類型,廠牌,型號,規格,序號,位置,附屬於,使用者,購置日期,外借使用人,備註' );
$sql = 'SELECT * FROM item_list as i WHERE ' . $itf->get();
$item_query = $dba->_query($sql);
while( $item = $item_query->fetch(PDO::FETCH_ASSOC) )
{
    $insideof = '';
    if( $item['insideof'] != 0 )
    {
        $outsider = $dba->get('item_list',$item['insideof'],array( 'serial' => 'string' ));
        $insideof = $outsider['serial'];
    }
    $line = array(
        $item['serial'],
        $places[$item['unit']],
        $types[$item['type']],
        $item['brand'],
        $item['model'],
        $item['format'],
        $item['number'],
        $places[$item['place']],
        $insideof,
        $users[$item['member']],
        date('Y-m-d',strtotime($item['since'])),
        $item['outsider'],
        preg_replace('/\r?\n/','+',$item['note'])
    );
    $lines[] = implode(',',$line);
}
$big5_str = iconv('UTF-8','Big5',implode(PHP_EOL,$lines));

$csv_file = 'cache/items' . date('Ymd') . '.csv';
$fp = fopen($csv_file,'w');
fwrite($fp,$big5_str);
fclose($fp);

echo '{"status":"CSV file created.","file":"extends/'.$csv_file.'"}';