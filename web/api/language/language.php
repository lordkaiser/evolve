<?php
// ini_set('display_errors','1');
// error_reporting(E_ALL);
class language_API extends hapi {
	//getByID
	public function getByID() {
		$this->bind('lid', $_POST['lid']);
		return $this->read("select * from language where L_ID = '|lid|'", 0);
	}

	public function listing() {
		return $this->read("select * from language", 0);
	}

	public function constructDB() {
		return $this->write("test:go", "INSERT INTO `statement` (`S_ID`, `name`, `type`) VALUES
(1, 'assignment', 'simple'),
(2, 'call', 'simple'),
(3, 'return', 'simple'),
(4, 'goto', 'simple'),
(5, 'assertion', 'simple'),
(6, 'block', 'compound'),
(7, 'if-statement', 'compound'),
(8, 'switch-statement', 'compound'),
(9, 'while-loop', 'compound'),
(10, 'do-loop', 'compound'),
(11, 'for-loop', 'compound');");
	}
}
?>