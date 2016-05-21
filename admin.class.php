<?php
/*******************************************************************************
 * Admin class
 *
 * @created     02/25/2011
 * @modified    02/26/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/
 
define('ADMIN_CLASS_PHP_INCLUDED', true); 

class Admin {
	protected $tables = array();
	protected $table  = '';
	protected $db     = null;
	protected $data   = array();
	protected $aid    = 0;
	
	public function __construct($tables, $aid, $db) {
		$this->tables = $tables;
		$this->table  = $tables['ADMINS'];
		$this->db     = $db;
		$this->aid    = $aid;
	}
	
	public function data() {
		if (!$this->aid) return false;
		$this->data = $this->db->getRows($this->table, "`id`='{$this->aid}'", '', '1');
		return $this->data;
	}
	
	public function get($field, $fresh = false) {
		if ($fresh || !$this->data) $this->data();
		return $this->data[$field] ? $this->data[$field] : false;
	}
	
	public function update($data, $safe = true) {
		return $this->db->update($this->table, $data, "`id`='{$this->aid}'", $safe);
	}
	
	public function loggedIn() {
		return !$this->aid ? false : $this->getAccountPerms();
	}
	
	public function getAccountPerms() {
		return $this->get('perms', true);
	}
}