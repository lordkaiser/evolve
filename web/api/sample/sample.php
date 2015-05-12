<?php
// ini_set('display_errors','1');
// error_reporting(E_ALL);
class sample_API extends hapi {
	public function getAllUnderModule() {
		$this->bind('mid', $_POST['mid']);
		return $this->read("select * from sample where M_ID = '|mid|'", 0);
	}

	public function get() {
		$this->bind('mid', $_POST['mid']);
		$this->bind('sid', $_POST['sid']);
		return $this->read("select * from sample where M_ID = '|mid|' and S_ID = '|sid|'");
	}
}
?>