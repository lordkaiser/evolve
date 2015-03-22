<?php

if( !class_exists('aapi') ) {
class aapi {
	
	public $params = array(); // the parameters to be submitted with the request

	static public $autoinitialize = TRUE;

	public function __construct() {}
	
	/*
	 * Set parameters for the request
	 */
	public function set( $key, $value = null ) {
		if( is_array($key) && is_null($value) ) {
			if( sizeof($key) > 0 ) foreach($key as $k => $v) $this->set( $k, $v );
		} else {
			$this->params[urlencode($key)] = urlencode($value);
		}
	}
	
	public function getParams( $url = '' ) {
		$vars = '';
		if( sizeof( $this->params ) > 0 ) {
			foreach( $this->params as $key => $value ) {
				$vars .= $key . "=" . $value . "&";
			}
		}
		
		return ( $url . ( ( strstr( $url, '?' ) && strlen($url) > 0 ) ? '' : '?' ) . rtrim( $vars, "& " ) );
	}
	
	public function get( $url, $params = array() ) {
		$this->set($params);
		return file_get_contents( $this->getParams($url) );
	}
	
	public function post( $url, $params = array() ) {
		$this->set($params);
		$_parm = $this->getParams();
		$this->_url = $url . $_parm;
		$curl = curl_init($url);
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, str_replace( '?', '', $_parm ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		$temp = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);
		
		return $temp;
	}

}
}

$aapi = new aapi();

?>