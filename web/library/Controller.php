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
			if( is_array( $value ) ) {
				foreach( $value as $k => $v ) $aapi->set( $key, ( is_array($value) ? $value : addslashes($value) ) );
			} else {
				$aapi->set($key, ( is_array($value) ? $value : addslashes($value) ));
			}		
		}
		
		$query = json_decode($aapi->get($url));
		if( isset($query) && isset($query->results) ) return $query->results;
		elseif( isset($query) ) return $query;
		else return array();		
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