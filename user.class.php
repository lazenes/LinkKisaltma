<?php
/*******************************************************************************
 * User class
 *
 * @created     02/02/2011
 * @modified    02/24/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/
 
define('USER_CLASS_PHP_INCLUDED', true); 

class User {
	protected $table = '';
	protected $db    = null;
	protected $data  = array();
	protected $uid   = 0;
	
	public function __construct($table, $uid, $db) {
		$this->table = $table;
		$this->db    = $db;
		$this->uid   = $uid;
	}
	
	public function data() {
		if (!$this->uid) return false;
		$this->data = $this->db->getRows($this->table, "`id`='{$this->uid}'", '', '1');
		return $this->data;
	}
	
	public function get($field, $fresh = false) {
		if ($fresh || !$this->data) $this->data();
		return $this->data[$field] ? $this->data[$field] : false;
	}
	
	public function update($data, $safe = true) {
		return $this->db->update($this->table, $data, "`id`='{$this->uid}'", $safe);
	}
	
	public function raiseEarning($e) {
		if ($e && $e > 0) {
			$this->update(array('available_earning' => 'available_earning+' . $e), false);
		}
		return $this->get('available_earning');;
	}
	
	public function isVerified() {
		return $this->get('astatus', true) == 1;
	}
	
	public function isBanned() {
		return $this->get('astatus', true) == 2;
	}
	
	public function loggedIn() {
		return $this->getAccountCode();
	}
	
	public function getAccountCode() {
		return $this->get('atype', true) ? ($this->get('atype') == 'Shrinker' ? 1 : 2) : 0;
	}
}