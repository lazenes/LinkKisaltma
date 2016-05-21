<?php
/*******************************************************************************
 * Utility class
 *
 * @created     02/02/2011
 * @modified    02/02/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/
 
define('UTILITIES_CLASS_PHP_INCLUDED', true);

require_once realpath(dirname(__FILE__)) . '/javascriptpacker.class.php';

final class Utilities {
	/**
	 * Validate URL
	 *
	 * @param String $url  URL to validate
	 * @return Boolean
	 */
	public static function validateURL($url) {
		$urlregex = "^(https?|ftp)\:\/\/"
					. "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"
					. "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";
		if (!eregi($urlregex, $url)) {
			return false;
		}
		$url = parse_url($url);
		return preg_match('/[^.]+\.[^.]+$/', $url['host'], $matches) ? $matches[0] : false;
	}
	
	/**
	 * Get current URL
	 *
	 * @return String
	 */
	public static function getCurrentURL() {
		$requri = !isset($_SERVER['REQUEST_URI']) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];
		$protocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
		$protocol = substr($protocol, 0, strpos($protocol, '/')) . ($_SERVER['HTTPS']=='on'?'s':'');
		$port = $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT'];
		return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $requri;
	}
	
	public static function getVAT($amt) {
		return 0; //$amt * 0.2;
	}
	
	public static function arrayToURI($arr) {
		if(!is_array($arr)) return false;
		$c = 0;
		$out = '';
		foreach($arr as $name => $value) {
			if($c++ != 0) $out .= '&';
			$out .= urlencode("$name").'=';
			$out .= is_array($value) ? urlencode(serialize($value)) : urlencode("$value");
		}
		return $out;
	}
	
	/**
	 * Remove queries from a URL
	 *
	 * @param String $url  URL to filter
	 * @return String
	 */
	public static function removeURLQueries($url) {
		return strpos($url, '?') ? substr($url, 0, strpos($url, '?')) : $url;
	}
	
	/**
	 * Parse integer into compressed string
	 *
	 * @param Integer $int  Integer to compress
	 * @return String
	 */
	 public static function compressInt($int) {
		$base = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $len = strlen($base);
		while($int > $len - 1) {
			$out = $base[fmod($int, $len)] . $out;
			$int = floor($int / $len);
		}
		return $base[$int] . $out;
	 }
	 
	 public static function JSPack($js, $encoding = 10, $fastDecode = true) {
		$jsp = new JavaScriptPacker($js, $encoding, $fastDecode, false);
		return $jsp->pack();
	 }
}