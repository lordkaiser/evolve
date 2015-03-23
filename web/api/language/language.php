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
		return $this->write("test:go", "

			CREATE TABLE IF NOT EXISTS `language` (
			  `L_ID` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(20) NOT NULL,
			  `variablePrecedence` varchar(15) NOT NULL,
			  `declarationPrecedence` varchar(15) NOT NULL,
			  `omniPrecedence` tinyint(1) NOT NULL,
			  `spacedPrecedence` tinyint(1) NOT NULL,
			  PRIMARY KEY (`L_ID`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

			INSERT INTO `language` (`L_ID`, `name`, `variablePrecedence`, `declarationPrecedence`, `omniPrecedence`, `spacedPrecedence`) VALUES
			(1, 'PHP', '$', '$', 1, 0),
			(2, 'Javascript', '', 'var', 0, 1);

			CREATE TABLE IF NOT EXISTS `statement` (
			  `S_ID` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(20) NOT NULL,
			  `type` varchar(20) NOT NULL,
			  PRIMARY KEY (`S_ID`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

			INSERT INTO `statement` (`S_ID`, `name`, `type`) VALUES
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
			(11, 'for-loop', 'compound');

			CREATE TABLE IF NOT EXISTS `syntax` (
			  `L_ID` int(11) NOT NULL,
			  `structure` varchar(155) NOT NULL,
			  `S_ID` int(11) NOT NULL,
			  PRIMARY KEY (`L_ID`,`S_ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

			INSERT INTO `syntax` (`L_ID`, `structure`, `S_ID`) VALUES
			(1, '^isvar^=^isequ/isvar/isnum^;', 1),
			(1, '^isfunc^\\(^isparam^\\);', 2),
			(2, '^isvar(d)^=^isvar/isope/isnum^;', 1);
		");
	}
}
?>