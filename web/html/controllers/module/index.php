<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

class Module_Index extends Controller {

	public function initialize(){
		$data['modules'] = $this->getData('module/listing', array(), array());
		$data['master'] = new Controller();
		// $data['test'] = '';
		$this->load('index', $data);
	}
	
}

$index = new Module_Index();
return $index->initialize();
?>
