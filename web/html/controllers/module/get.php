<?php
// ini_set('display_errors','1');
// error_reporting(E_ALL);
$_ignore_template = true;
class Module_Index extends Controller {

	public function initialize(){
		$output = array();

		$post['mid'] = $_POST['mid'];
		$post['sid'] = $_POST['sid'];
		$output = $this->getData('sample/get', array(), $post);
		$output = $output[0];
		$output = $output->description;
		$output = explode("\n", $output);

		echo json_encode($output);
	}
	
}

$index = new Module_Index();
return $index->initialize();
?>
