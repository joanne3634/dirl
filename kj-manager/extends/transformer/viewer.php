<pre><?
define( 'DB_SERVER', 'localhost' );
define( 'DB_USERNAME', 'mmnet_web' );
define( 'DB_PASSWORD', 'web123' );
define( 'DB_DATABASE', 'MMNET_WEB' );

$connection = mysql_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD);
if (!$connection) { die('No Connection.'); }

$fields = isset($_GET['field']) ? $_GET['field'] : '*';
mysql_select_db(DB_DATABASE,$connection);
$query = mysql_query('SELECT '.$_GET['field'].' FROM '.$_GET['table'],$connection);
while( $row = mysql_fetch_row($query) )
{
    echo vsprintf('%16s %8s %s %s %s',$row).PHP_EOL;
}
mysql_close($connection);
?></pre>