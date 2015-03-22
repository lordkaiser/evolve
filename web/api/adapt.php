<?
// ini_set('display_errors','1');
// error_reporting(E_ALL);
define( '_ROOT', 'F:/Console Hobby/wamp/www/adapt/api' );

date_default_timezone_set('America/Denver');
session_start();

// get rid of the get query string
if( strpos($_SERVER['REQUEST_URI'],"?") ) list( $_SERVER['REQUEST_URI'], $get ) = explode( "?", $_SERVER['REQUEST_URI'] );

// check for the output
$output = 'json';
$outputtypes = array('json','xml','css','js','txt');
if( preg_match( "/.json$|.xml$|.css$|.js$|.txt$/i", $_SERVER['REQUEST_URI'] ) ) {
	//$output = str_replace( ".", "", substr( $_SERVER['REQUEST_URI'], -4 ) );
	$output = end(explode(".", $_SERVER['REQUEST_URI']));
	if( !in_array($output,$outputtypes ) ) $output = 'json';
	$_SERVER['REQUEST_URI'] = str_replace($outputtypes, "", $_SERVER['REQUEST_URI'] );
}

// parse the url
$_SERVER['REQUEST_URI'] = str_replace( "/apis/", "", preg_replace( "/\/$/", "", preg_replace( "/\.$/", "", $_SERVER['REQUEST_URI'] ) ) );
$uri = explode( "/", $_SERVER['REQUEST_URI'] );



$path = _ROOT . "/" . $uri[0] . "/";

$class = $uri[0];
$subclass = $uri[1];

if( $uri[1] && file_exists( $path . $uri[1] . ".php" ) ) {
	// do the sub class and run its method
	$path = $path . $uri[1] . '.php';
	$class = $uri[1] . '_API';
	$method = ( $uri[2] ? $uri[2] : 'index' );
} else {
	$path = $path . $uri[0] . ".php";
	$class = $uri[0] . '_API';
	$method = ( $uri[1] ? $uri[1] : 'index' );
}
/*
echo $path;
. '<br />';
print_r($uri);
exit;*/

// include hapi (hurdman api class)

include "../library/db.php";
include "../library/hapi.php";
// include the class
include $path;

// make sure the method exists
if( !in_array($method,get_class_methods($class)) ) {
	echo "No such method $class::$method";
	exit;
}

/*class Base {
	static $segments = array();
}

Base::$segments = $uri;*/
$api = new $class();
$api->segments = $uri;
$api->output($api->$method());
?>