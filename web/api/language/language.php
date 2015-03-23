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
		return $this->write("test:go", "INSERT INTO `language` (`L_ID`, `name`, `variablePrecedence`, `declarationPrecedence`, `omniPrecedence`, `spacedPrecedence`) VALUES	(1, 'PHP', '$', '$', 1, 0),	(2, 'Javascript', '', 'var', 0, 1);");
	}
}
?>