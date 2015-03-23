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
		return $this->write("test:go", "CREATE TABLE IF NOT EXISTS `statement` (
  `S_ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`S_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;");
	}
}
?>