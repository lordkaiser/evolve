<?php
ini_set('display_errors','1');
error_reporting(E_ALL);
echo(getcwd());
define("BASE_PATH", "/srv/www/code/web/html");
define("VIEW_PATH", BASE_PATH . "/views/");
require("/srv/www/code/web/library/Controller.php");
require("/srv/www/code/web/library/aapi.php");

list( $uri, $get ) = explode( "?", $_SERVER['REQUEST_URI'] );
$segments = explode( "/", str_replace( "", "", preg_replace( "/\/$|^\//", "", $uri ) ) );
$_SERVER['segments'] = $segments;
$_ignore_template = false;
$_has_captcha = false;

function pages( $segments ) {
    $check = array();
    $levels = sizeof($segments);

    for( $i=0; $i<$levels; $i++ ) {
    	$_tmp = implode("/", explode( "/", implode("/", $segments), -$i ) );

    	if( strlen($segments[(sizeof($segments)-$i)]) > 0 ) {
    		$check[] = "/controllers/" . $_tmp . "/" . $segments[(sizeof($segments)-$i)] . ".php";
			}

    	$check[] = "/controllers/" . $_tmp . "/index.php";
			$check[] = "/controllers/" . $_tmp . strrchr( $_tmp, "/" ) . ".php";
    }

    $check[] = "/controllers/error/index.php"; 
    // print_r($check);
    return $check;
}

function route($segments) {
    // loop through each file and see if it exists
    foreach( pages($segments) as $file ) {
        // does it exist?
    	if( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
    		$_tmp = BASE_PATH . $file;
    		$_method_file = str_replace( basename($_tmp), $_SERVER['REQUEST_METHOD'] . "." . basename($_tmp), $_tmp );

    		if( file_exists( $_method_file ) ) {
    			return $_method_file;
    		}
    	}    		
    	if( file_exists( BASE_PATH . $file ) ) {
    		return BASE_PATH . $file;
    	}
    }
    header('HTTP/1.0 404 Not Found');
    return BASE_PATH ."/error/index.php";
}

// print_r(pages($segments));
//echo 'type: '. $_SERVER['REQUEST_METHOD'];
// echo route($segments);
//echo '</pre>';
// print_r($segments);

if( !isset($_SESSION) ) session_start();

$controller = route($segments);

ob_start();
if(isset($controller) && strlen($controller) > 0 ) include($controller);
$contents = ob_get_contents();
ob_end_clean();
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if ($_has_captcha) {
  require("/srv/www/code/web/assets/secureimage/secureimage.php");  
}
if (!$_ignore_template) {
    include BASE_PATH ."/templates/wrapper.php";
} else if ($_ignore_template) {
    echo $contents;
}
?>
