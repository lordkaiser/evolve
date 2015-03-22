<?php
// ini_set('display_errors','1');
// error_reporting(E_ALL);
class syntax_API extends hapi {
	public function get() {
		$this->bind('lid', $_POST['lid']);
		$this->bind('sid', $_POST['sid']);
		return $this->read( "select sy.structure as struct, st.name as name, sy.S_ID as sid from syntax sy left join statement st on sy.S_ID = st.S_ID where sy.L_ID = '|lid|' and sy.S_ID = '|sid|'", 0 );
	}

	public function getAllUnderLanguage() {
		$this->bind('lid', $_POST['lid']);
		return $this->read( "select sy.structure as struct, st.name as name, sy.S_ID as sid from syntax sy left join statement st on sy.S_ID = st.S_ID where sy.L_ID = '|lid|'", 0 );
	}
}
?>