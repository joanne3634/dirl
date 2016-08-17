<?php
define( 'DB_SERVER', 'localhost' );
define( 'DB_USERNAME', 'mmnet_web' );
define( 'DB_PASSWORD', 'web123' );
define( 'DB_DATABASE', 'MMNET_WEB' );
define( 'DB_ENCODING', 'utf8' );

$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.DB_ENCODING );
$pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE,DB_USERNAME,DB_PASSWORD,$options);
if( !$pdo )
{
    // fail construct with pdo...
    die('{"error":100,"message":"PDO cannot created."}');
}