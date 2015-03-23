<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

class Home_Index extends Controller {

	public function initialize(){
		$data['languages'] = $this->getData('language/constructDB', array(), array());
		$this->load('index', $data);
	}
	
}

$index = new Home_Index();
return $index->initialize();
?>
