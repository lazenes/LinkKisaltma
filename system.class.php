<?php
define('SYSTEM_CLASS_PHP_INCLUDED', true); 
 
if (!defined('CONST_INC_PHP_INCLUDED')) {
	require_once dirname(__FILE__) . '/const.inc.php';
}

if (!defined('DATABASE_CLASS_PHP_INCLUDED')) {
	require_once dirname(__FILE__) . '/database.class.php';
}

if (!defined('USER_CLASS_PHP_INCLUDED')) {
	require_once dirname(__FILE__) . '/user.class.php';
}

final class System {
	/**
	 * Database class bearing property
	 */
	protected static $DATABASE = null;
	
	/**
	 * User class bearing property
	 */
	protected static $USER = null;

	/**
	 * Sets the Database handler class for the system
	 *
	 * @param Database $db  Database class property
	 * @throws Exception
	 */
	public static function setDB($db) {
		if ($db instanceof Database) {
			self::$DATABASE = $db;
		} else {
			throw new Exception('Param MUST be an instance of a Database');
		}
	}

	/**
	 * Gets the database class bearing property
	 *
	 * @throws Exception
	 * @return Database
	 */
	public static function getDB() {
		if (self::$DATABASE == null) {
			throw new Exception('Database not set');
		}

		return self::$DATABASE;
	}
	
	/**
	 * Sets the user handler class for the system
	 *
	 * @param User $db  User class property
	 * @throws Exception
	 */
	public static function setUser($user) {
		if ($user instanceof User) {
			self::$USER = $user;
		} else {
			throw new Exception('Param MUST be an instance of User');
		}
	}

	/**
	 * Gets the user class bearing property
	 *
	 * @throws Exception
	 * @return User
	 */
	public static function getUser() {
		if (self::$USER == null) {
			throw new Exception('User not set');
		}

		return self::$USER;
	}
	
	public static function verifyAccess($to = 'index.php', $toAccount = true) {
		if (!self::getUser()->loggedIn()) self::redirect($to);
		if (self::getUser()->isBanned()) {
			self::redirect('index.php?logout&banned=' . self::getUser()->get('email'));
		}
		if (!self::getUser()->isVerified()) {
			self::redirect('verify.php?e=' . self::getUser()->get('email'));
		}
		if (($db = self::getDB()) && ($usr = self::getUser()) && $toAccount) {
			global $_GLOBAL;
			$reqs = array('name','address','city_town','zipcode','country','telephone');
			$dat = self::getDB()->getRows($_GLOBAL['TABLES']['USERS'], "`id`='" . $usr->get('id') . "'", '', '1');
			foreach ($reqs as $r) {
				if (!$dat[$r]) {
					self::redirect('account.php?err=Please+Update+Your+Account+Information');
					break;
				}
			}
		}
	}

	/**
	 * Quick client redirector
	 *
	 * @param string $url   The URL redirecting to
	 * @param bool   $js    If true, javascript will be used to redirect
	 */
	public static function redirect($url, $js = true, $retjs = false) {
		if ($js) {
			$html = "<script type=\"text/javascript\">\n";
			$html.= "<!--\n";
			$html.= "top.location.href = \"{$url}\";";
			$html.= "//-->\n";
			$html.= '</script>';
			if ($retjs) return "top.location.href = \"{$url}\";";
			@header("Location: {$url}");
			die($html);
		} else {
			header("Location: {$url}");
		}
	}
	
	/**
	 * Quick email sender
	 *
	 * @param string $to   The email sending to
	 * @param string $sub  The email subject
	 * @param string $msg  The email content
	 */
	public static function sendEmail($from, $reply, $to, $sub, $msg) {
		$headers  = "From: " . $from . "\r\n";
		$headers .= "Reply-To: ". $reply . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		return @mail($to, $sub, $msg, $headers);
	}
	
	/**
	 * Write the message to a log file
	 *
	 * @return string   The message to record
	 */
	public static function log($msg) {
		$fh = null;
		try {
			$fh = fopen(dirname(__FILE__) . '/logs/day_' . date('j') . '.txt', 'a');
		} catch (Exception $e) {
			$fh = fopen(dirname(__FILE__) . '/logs/day_' . date('j') . '.txt', 'w');
		}
		if ($fh) {
			fwrite($fh, "[" . self::getCaller() . "][" . date("Y/m/d h:i:s", mktime()) . "]: " . $msg . "\n");
			fclose($fh);
		}
		return $msg;
	}

	/**
	 * Gets the calling instance at which [this] function is being called from.
	 *
	 * @return string   The calling function name
	 */
	public static function getCaller() {
		$trace = debug_backtrace();
		$callr = $trace[0]['function'];
		return empty($callr) ? 'global' : $callr;
	}
}