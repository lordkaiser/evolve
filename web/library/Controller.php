<?php 
require_once('View.php');
date_default_timezone_set('America/Denver');
class Controller{
	
	public $view;
	
	function __construct(){		
		$this->view = new View();
	}

	public function load($view, $queries = array()){
		$data = array();

		foreach($queries as $key => $value) {
			$data[$key] = $value;
		}

		$path = $this->getPath($view);

		$this->view->render($path, $data);		
	}
	
	private function getPath( $view = '' ){
		$path = str_replace('_', '/', strtolower(get_class($this)) );
		
		if( ! empty($view) )
			$path = substr($path, 0, strrpos($path, '/') + 1) . $view;
			
		return VIEW_PATH . $path . ".php";	
	}

	public function getData($location, $segments = array(), $posts = array()){
		global $aapi;

		$url = "https://".$_SERVER['SERVER_NAME']."/apis/".$location;
		// return $url;
		foreach($segments as $segment) {
			$url .= $segment . "/";
		}

		foreach($posts as $key => $value) {
			$_POST[$key] = $value;
		}
		$uri = explode( "/", $location );
		$path = "/srv/www/code/web/api/" . $uri[0] . "/";

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

		if( !class_exists('db') ) {
			include "/srv/www/code/web/library/db.php";
		}
		if( !class_exists('hapi') ) {
			include "/srv/www/code/web/library/hapi.php";
		}
		if( !class_exists($class) ) {
			include $path;
		}

		if( !in_array($method,get_class_methods($class)) ) {
			return "No such method $class::$method";
		}

		$api = new $class();
		$api->segments = $uri;
		$query = json_decode($api->output($api->$method()));
		if( isset($query) && isset($query->results) ) return $query->results;
		elseif( isset($query) ) return $query;
		else return array();
		// $query = json_decode($aapi->get($url));
		// if( isset($query) && isset($query->results) ) return $query->results;
		// elseif( isset($query) ) return $query;
		// else return array();
	}

	public function includeTemplate($path) {
		$backdir = false;
		$bypass = false;
		if (strpos($path,'http') !== false) {
			$bypass = true;
		}
		if (!$bypass) {
			if (strpos($path,'..') !== false) {
				$backdir = true;
			}
			$pathName = $_SERVER['REQUEST_URI'];
			//return file_get_contents("http://" . $_SERVER['SERVER_NAME'] . ");
		} else {
			
			$ch = curl_init();
			$_use_template = False;
			curl_setopt($ch, CURLOPT_URL, $path);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
			$output = curl_exec($ch);
			curl_close($ch);  
			return $output;
		}
	}
}