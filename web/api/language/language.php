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
		return $this->write("test:go", "INSERT INTO `syntax` (`L_ID`, `structure`, `S_ID`) VALUES
(1, '^isvar^=^isequ/isvar/isnum^;', 1),
(1, '^isfunc^\\(^isparam^\\);', 2),
(2, '^isvar(d)^=^isvar/isope/isnum^;', 1);");
	}
}
?>