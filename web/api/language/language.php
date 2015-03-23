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
		return $this->write("test:go", "-- phpMyAdmin SQL Dump
			-- version 4.1.14
			-- http://www.phpmyadmin.net
			--
			-- Host: 127.0.0.1
			-- Generation Time: Mar 23, 2015 at 03:11 AM
			-- Server version: 5.6.17
			-- PHP Version: 5.5.12

			SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
			SET time_zone = \"+00:00\";


			/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
			/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
			/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
			/*!40101 SET NAMES utf8 */;

			--
			-- Database: `adapt`
			--

			-- --------------------------------------------------------

			--
			-- Table structure for table `language`
			--

			CREATE TABLE IF NOT EXISTS `language` (
			  `L_ID` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(20) NOT NULL,
			  `variablePrecedence` varchar(15) NOT NULL,
			  `declarationPrecedence` varchar(15) NOT NULL,
			  `omniPrecedence` tinyint(1) NOT NULL,
			  `spacedPrecedence` tinyint(1) NOT NULL,
			  PRIMARY KEY (`L_ID`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

			--
			-- Dumping data for table `language`
			--

			INSERT INTO `language` (`L_ID`, `name`, `variablePrecedence`, `declarationPrecedence`, `omniPrecedence`, `spacedPrecedence`) VALUES
			(1, 'PHP', '$', '$', 1, 0),
			(2, 'Javascript', '', 'var', 0, 1);

			-- --------------------------------------------------------

			--
			-- Table structure for table `statement`
			--

			CREATE TABLE IF NOT EXISTS `statement` (
			  `S_ID` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(20) NOT NULL,
			  `type` varchar(20) NOT NULL,
			  PRIMARY KEY (`S_ID`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

			--
			-- Dumping data for table `statement`
			--

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

			-- --------------------------------------------------------

			--
			-- Table structure for table `syntax`
			--

			CREATE TABLE IF NOT EXISTS `syntax` (
			  `L_ID` int(11) NOT NULL,
			  `structure` varchar(155) NOT NULL,
			  `S_ID` int(11) NOT NULL,
			  PRIMARY KEY (`L_ID`,`S_ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

			--
			-- Dumping data for table `syntax`
			--

			INSERT INTO `syntax` (`L_ID`, `structure`, `S_ID`) VALUES
			(1, '^isvar^=^isequ/isvar/isnum^;', 1),
			(1, '^isfunc^\\(^isparam^\\);', 2),
			(2, '^isvar(d)^=^isvar/isope/isnum^;', 1);

			/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
			/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
			/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
		");
	}
}
?>