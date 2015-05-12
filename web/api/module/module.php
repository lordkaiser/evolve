<?php
// ini_set('display_errors','1');
// error_reporting(E_ALL);
class module_API extends hapi {
	public function listing() {
		return $this->read("select * from module", 0);
	}
}
?>