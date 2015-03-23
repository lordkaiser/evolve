<?php

class db {

	static private $host = "localhost";
	static private $user = "lordkaiser912";
	static private $pass = "Alan34064324";

	static $connect = NULL; // the connection to uber
	static $db = NULL; // the database we'll be querying on
	static $bbbid = NULL; // what bbbid we are connected to
	static $parameters = array(); // the bound parameters for queries
	static $dict = NULL; // dictionary associated with the mergequery
	
	/**
	 * Make the construct private to prevent external instantiation
	 */
	private function __construct() {}
	
	/**
	 * Bind a key=>value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	static public function bind( $key, $value = null ) {
		if( is_array($key) ) {
			foreach( $key as $k => $v ) self::bind( $k, $v );
		} else {
			self::$parameters[ $key ] = $value;
		}
	}
	
	/**
	 * Run a query
	 *
	 * @param string $bbbid 
	 * @param string $query
	 *
	 * @return array
	 */
	static public function query( $query, $dbname = 'adapt' ) {
		if( !self::$connect ) self::connect();
		mysql_select_db(self::$db,self::$connect);
		//echo self::resolve_pipes($query) . "\n\n";
		$sql = mysql_query( self::resolve_pipes($query), self::$connect );/* or die(mysql_error(self::$connect) . " --> " . self::resolve_pipes($query));*/
		
		if( is_resource($sql) ) {
			// make sure there were results
			if( mysql_num_rows($sql) < 1 ) {
				return array();
			} else {
				while( $row = mysql_fetch_assoc( $sql ) ) {
					$result[] = $row;
				}
			}
	
			return $result;
		}
	}
	
	static public function bypass( $query, $dbname = 'adapt' ) {
		if( $dbname == 'adapt' ) $dbname = 'adapt';
		if( self::$connect ) {
			mysql_select_db($dbname,self::$connect);
			$sql = mysql_query( self::resolve_pipes($query), self::$connect ) or die(mysql_error(self::$connect));
			// make sur ethere were results
			if( mysql_num_rows($sql) < 1 ) {
				return array();
			} else {
				while( $row = mysql_fetch_assoc($sql) ) {
					$result[] = $row;
				}
			}
			
			return $result;
		}
	}
	
	/**
	 * Run a mergecode
	 *
	 * @param string $bbbid
	 * @param string $mergecode
	 * @param string $response
	 * @return array
	 */
	static public function run( $mergecode, $dbname = FALSE ) {
		if( !self::$connect ) self::connect();
		
		foreach( explode( "||", str_replace( "\r\n", "", self::resolve_pipes(self::getquery($mergecode)) ) ) as $query ) {
			if( preg_match( "/directory2/i", $mergecode ) ) $query = "/* api:abpages2 " . $mergecode . " */ " . $query;
			mysql_select_db(self::$db,self::$connect);
			//echo $query;
			$sql = mysql_query( $query, self::$connect );// or die(mysql_error(self::$connect));
			//echo $query;
		}
			
		// make sure there were results
		if( @mysql_num_rows($sql) < 1 ) {
			//echo mysql_error(self::$connect);
			return array();
		} else {
			while( $row = mysql_fetch_assoc( $sql ) ) {
				$result[] = $row;
			}
		}
		
		return $result;
	}
	
	/*
		Get a single value
	*/
	static public function value( $mergecode ) {
		if( substr( $mergecode, 0, 1 ) == '[' ) $result = self::run( $mergecode );
		else $result = self::query( $bbbid, $mergecode );
		if( $result ) {
			$i=0;
			foreach( $result[0] as $field )
				if( $i == 0 ) return $field;
		}
	}
	
	/**
	 * Connect to uber
	 */
	static public function connect( $dbname = null ) {
		$creds_string = file_get_contents($_ENV['CRED_FILE'], false);
		if ($creds_string == false) {
		    die('FATAL: Could not read credentials file');
		}
		$creds = json_decode($creds_string, true);
		$dbname = $creds['MYSQLS']['MYSQLS_DATABASE'];
		self::$db = $creds['MYSQLS']['MYSQLS_DATABASE'];
		self::$host = $creds['MYSQLS']['MYSQLS_HOSTNAME'];
		$port       = $creds['MYSQLS']['MYSQLS_PORT'];
		self::$user = $creds['MYSQLS']['MYSQLS_USERNAME'];
		self::$pass = $creds['MYSQLS']['MYSQLS_PASSWORD'];

		if( $dbname == $creds['MYSQLS']['MYSQLS_DATABASE'] ) $dbname = $creds['MYSQLS']['MYSQLS_DATABASE'];
		
		//print_r($_SERVER['backbone']);
				
		if( is_null(self::$connect) ) self::$connect = mysql_connect( self::$host, self::$user, self::$pass );
		// if there is no connection trigger an error
		if( !self::$connect ) {
			trigger_error("Cannot connect to mysql");
		} else {	
			if( !isset(self::$db ) ) mysql_select_db($creds['MYSQLS']['MYSQLS_DATABASE']);
			else mysql_select_db( $creds['MYSQLS']['MYSQLS_DATABASE'], self::$connect );
			// get the database by bbbid
			if( !is_null($dbname) ) {
				self::$db = $dbname;
			}
			return self::$connect;
		}
	}
	
	// get the query from the mergequery table
	static function getquery( $name ) {
		if( empty(self::$db) ) self::$db = $_SERVER['adapt']['db'];
		mysql_select_db(self::$db,self::$connect);
		//$find = mysql_query( "select sqlstatement from mergequery where mergecode = '".$name."'", self::$connect );
		$find = mysql_query( "select m.sqlstatement, m2.sqlstatement as dict from manylists.mergequery m left join manylists.mergequery m2 on concat(m.mergecode,'.dict') = m2.mergecode where m.mergecode = '".$name."'", self::$connect );
		// if we couldn't get it from the local
		/*if( @mysql_num_rows($find) != 1 ) {
			$find = mysql_db_query( "common", "select sqlstatement from mergequery where mergecode = '".$name."'", self::$connect );
		}*/
		
		list($sql,$dict) = mysql_fetch_row( $find );	 
		if( strlen($dict) > 0 ) self::$dict = json_decode($dict );
		$sql = str_replace( ", ~", "", str_replace( ",~", "", str_replace( ", ^", "", str_replace( ",^", "", str_replace( "^,", "", preg_replace( "/\]$/", "", preg_replace( "/^\[/", "", $sql ) ) ) ) ) ) );
		
		return $sql;
			
	}
	
	/**
	 * @return string
	 * @param mytext string
	 * @desc gets the next merge code in sequence
	 */
	static function get_next_merge_code($mytext) {
		preg_match ("/\[[^]]*\]/",  $mytext, $returned);
		return $returned[0];
	}
	
	/*
		This is used in the loop to get the next parameter name and value
	*/
	static function get_next_pipe($mytext) {
		preg_match ("/[^|](\|[^|]+\|)([^|]|$)/",  $mytext, $returned);
		return ( isset($returned[1]) ? $returned[1] : '' );
	}
	
	/*
		This function will loop through the sqlstatement string and try to find
		parameters and will resolve them based on get_param();
	*/		
	static function resolve_pipes($mytext) {
		$mytext = str_replace( "{PIPE}", "|", $mytext );
		while ($param = self::get_next_pipe($mytext))
		{
			$new_param = self::get_param(str_replace("|","",$param));
			if($new_param == 'NULL')
				$new_param = '';
			else
				$new_param = $new_param;
			$mytext = substr_replace($mytext, $new_param, strpos($mytext,$param), strlen($param));
		}
		return $mytext;
	}
	
	/**
	 * @return string
	 * @param mytext string
	 * @desc resolves the merge code and sets it up to move through each code removing the [ ]  around each code
	 */
	static function resolve_merge( $mytext )
	{
		$mergecode = self::get_next_merge_code($mytext);
		do
		{
			if(!$mergecode) break;
			$mytext = str_replace($mergecode, self::merge_code($mergecode), $mytext);
		}
		while ($mergecode = self::get_next_merge_code($mytext));
		return $mytext;
	}
	
	/*
		Retrieves the possible parameter values to replace into the queries
	*/
	static function get_param($myparam) {
		return ( isset(self::$parameters[$myparam]) ? self::$parameters[$myparam] : '' );
	}
	
	/**
	 * @return string
	 * @param code string
	 * @desc gets the sql query according to merge code from the databaes and executes it for its content
	 */
	static function merge_code($code) {
	
		$code = str_replace("[","",str_replace("]","",$code));
		$param = self::get_param( $code );
		
		return self::get_param(str_replace("[","",str_replace("]","",$code)));
	}

}