<?php

class hapi {

	public $outputtype = 'json';
	public $segments = array();
	public $method = '';
	public $bbb = null; // the write to local class
	public $bbburl = '';
	public $bbbid = null;
	public $path = '/srv/www/code/web/api';
	public $cached = FALSE;
	public $riak = null;
	public $connected = false;

	public function __construct() {
		global $output, $uri, $method, $localredis;
		$this->outputtype = $output;
		$this->redis = $localredis;
		$this->segments = $uri;
		$this->method = $method;
		list( $this->url, ) = explode( ".", $_SERVER['SERVER_NAME'] );	
		
		include "/srv/www/code/web/library/riak.php";

		if(!$this->riak) {
			$this->riak = new RiakClient("riakdb.app.hurdman.org", 8098);
			$this->riak->r=1;
			$this->riak->w=1;
			$this->riak->dw=1;
		}
			
	}
	
	public function keycreate( $id, $type, $params = array() ) {
		//$client = str_replace( "_API", "", get_class($this) );
		$client = 'manylists';
		return $client . "." . $id . ">" . $type . "(" . json_encode($params) . ")";
	}
	
	public function keydel( $id, $type, $params = array() ) {
		$this->redis->del( $this->keycreate( $id, $type, $params ) );
	}
	
	public function touch( $id, $type, $params ) {
		$key = $this->keycreate( $id, $type, $params );
		$version = $this->redis->incr( $key );
		return array( 'key' => $key, 'version' => $version );
	}
	
	public function riakGet( $key ) {
		$bucket = $this->riak->bucket('adaptcache');
		$item = $bucket->get($key);
		return $item;
	}
	
	public function riakPut( $key, $value ) {
		# Choose a bucket name
		$bucket = $this->riak->bucket('adaptcache');
		$item = $bucket->newObject( $key, array(
			'stamp' => time(),
			'data' => $value
		));
		return $item->store();
	}
	
	public function output( $data, $type = null ) {
		// header( "HTTP/1.1 200 OK" );
		if( is_string($data) ) {
			if( $this->outputtype == 'css' ) header( 'Content-type: text/css' );
			else if( $this->outputtype == 'js' ) header( 'Content-type: application/javascript' );
			else if( $this->outputtype == 'csv' ) {
				//header( "Content-Type: text/csv" );	
			}
			echo $data;
			return;
		} else {
			if( $this->outputtype == 'xml' ) {
				header( "Content-type: text/xml" );
				//$xml = Array2XML::createXML('results', $data);	
			} else {
				header("Content-type: text/plain;charset=iso-8859-1");
			}

			// check for results in there already and split it out
			if( isset($data['results'] ) ) {
				$return = array( 'results' => $data['results'] ); 
				// loop through each other one and add it in
				foreach( $data as $key => $value ) {
					if( $key != 'results' ) {
						$return [$key] = $value;
					}
				}
			} else $return = array( 'results' => $data );
			if( $this->cached ) {
				$return['cached'] = TRUE;
				$return['hash'] = $this->hash;
			}
			if( !isset($return['rows']) ) $return['rows'] = sizeof($return['results']);
		
			echo ( $this->outputtype == 'xml' ? $this->array2xml($data) : json_encode( $return ) );
		}
	}
	
	public function array2xml($array, $xml = false){
	    if($xml === false){
	        $xml = new SimpleXMLElement('<resultset/>');
	    }
	    foreach($array as $key => $value){
	        if(is_array($value)){
	            $this->array2xml($value, $xml->addChild((is_numeric($key) ? 'result' : $key ) ));
	        }else{
	            $xml->addChild($key, $value);
	        }
	    }
    	return $xml->asXML();
	}
	
	public function bind( $name, $value = null ) {
		if( is_array( $name ) ) {
			foreach( $name as $k => $v ) db::bind( $k, $v );
		} else {
			return db::bind( $name, $value );
		}
	}
	
	public function cache( $query, $expires ) {
		$this->hash = md5( sha1($query) . md5(serialize(db::$parameters)) . $_SERVER['SERVER_NAME'] );
		// see if there is an item in the cache
		$cache = $this->riakGet( $this->hash );
		if( $cache->exists ) {
			// cache exists, lets check to see if its expired
			if( $cache->data['stamp'] > (time()-$expires) ) {
				$this->cached = TRUE;
				return unserialize($cache->data['data']);
			}
		}
		return false;
		
		
		/*if( file_exists( $this->path . $this->hash ) ) {
			if( filemtime( $this->path . $name ) > (time()-$expires) ) {
				//echo 'cache';
				$this->cached = TRUE;
				return unserialize(file_get_contents( $this->path . $this->hash ));
			} else {
				unlink($this->path . $this->hash);
			}
		}
		return false;*/
	}
	
	public function read( $query, $expires = 1800, $bypass = FALSE, $db = NULL ) { // cache for 30 minutes
		// if there is a cache then return it
		//if( $cache = $this->cache($query, $expires) ) {
		//	return $cache;
		//}
		
		if( !$this->connected ) $this->dbconnect();

		if( $bypass ) $results = db::bypass( "/* api:adapt */ " . $query, $db );
		elseif( substr($query,0,1) == '[' ) $results = db::run( str_replace( array("[","]"), "", $query ) );
		else $results = db::query( "/* api:adapt */ " . $query );
		
		// write to the cache
		//$this->riakPut( $this->hash, serialize($results) );
		//file_put_contents( $this->path . $this->hash, serialize($results) );
		return $results;
	}
	
	public function write( $id, $query, $params = array() ) {
		$this->dbconnect();
		if( substr($query,0,1) == '[' ) {
			$query = db::resolve_pipes(db::getquery(str_replace(array("[","]"),"",$query)));
		} else {
			$query = db::resolve_pipes($query);
		}
//		echo "/* $id */ " . $query;
		$result = mysql_query( "/* $id */ " . $query );
		return array(
			"result" => $result,
			"error" => mysql_error(),
			"sql" => $query
		);
	}
	
	public function dbconnect() {
		db::connect();
		$this->connected = TRUE;
	}
	
	public function encrypt($plain_text, $iv_len = 16) {
		$password = 'beac5447a38f456fda840b3749444bb4';
	   $plain_text .= "\x13";
	   $n = strlen($plain_text);
	   if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
	   $i = 0;
	   $enc_text = get_rnd_iv($iv_len);
	   $iv = substr($password ^ $enc_text, 0, 512);
	   while ($i < $n) {
	       $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
	       $enc_text .= $block;
	       $iv = substr($block . $iv, 0, 512) ^ $password;
	       $i += 16;
	   }
	   return base64UrlEncode($enc_text);
	}

	public function decrypt($enc_text, $iv_len = 16) {
		$password = 'beac5447a38f456fda840b3749444bb4';
	   $enc_text = base64UrlDecode($enc_text);
	   $n = strlen($enc_text);
	   $i = $iv_len;
	   $plain_text = '';
	   $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
	   while ($i < $n) {
	       $block = substr($enc_text, $i, 16);
	       $plain_text .= $block ^ pack('H*', md5($iv));
	       $iv = substr($block . $iv, 0, 512) ^ $password;
	       $i += 16;
	   }
	   return preg_replace('/\\x13\\x00*$/', '', $plain_text);
	}
}

function get_rnd_iv($iv_len)
{
   $iv = '';
   while ($iv_len-- > 0) {
       $iv .= chr(mt_rand() & 0xff);
   }
   return $iv;
}

function base64UrlEncode($data)
{
  return strtr(rtrim(base64_encode($data), '='), '+/', '-_');
}

function base64UrlDecode($base64)
{
  return base64_decode(strtr($base64, '-_', '+/'));
}
?>