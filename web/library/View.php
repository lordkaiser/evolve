<?php

class View{

	public $data;
	
	function __construct(){
		$this->data = array();
	}
	
	public function render($path, $data = array()){		
		if( ! empty($data) ){
			// Make data variables accessible to the wrapper
			foreach($data as $key => $value) {
				global $$key;
			}
		}
		
		// Initialize variables and make it available to the view file
		extract($data);
		// echo "path: " . $path;
		include($path);
	}
	
}