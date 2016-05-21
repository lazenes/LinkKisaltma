<?php
/*******************************************************************************
 * Database handler, contains several database handling classes
 *
 * @created     02/01/2011
 * @modified    02/02/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

define('DATABASE_CLASS_PHP_INCLUDED', true);

if (!defined('CONST_INC_PHP_INCLUDED')) {
	require_once dirname(__FILE__) . '/const.inc.php';
}

if (!defined('SYSTEM_CLASS_PHP_INCLUDED')) {
	require_once dirname(__FILE__) . '/system.class.php';
}

abstract class Database { }

class MySQL extends Database {
	protected $lid        = 0;
	protected $qid        = 0;
	protected $affrows    = 0;

	public function __construct($host, $user, $pass, $db, $force = false) {
		global $_LANG;

		$this->lid = mysql_connect($host, $user, $pass, $force);

		if (!$this->lid) {
			$this->trigger_error('Unable to connect to database');
		}

		if (!mysql_select_db($db, $this->lid)) {
			$this->trigger_error('Database not found');
		}
	}

	public function __destruct() {
		if ($this->lid) $this->close();
	}

	public function query($sql) {
		$this->qid = mysql_query($sql, $this->lid);

		if (!$this->qid) {
			return 0;
		}
		
		$this->affrows = mysql_affected_rows($this->lid);
		return $this->qid;
	}

	public function fetch_assoc($qid = -1) {
		if ($qid != -1) $this->qid = $qid;
		return mysql_fetch_assoc($qid);
	}

	public function fetch($sql) {
		$qid = $this->query($sql);
		while ($out[] = $this->fetch_assoc($qid));
		array_pop($out);
		$this->free($qid);
		return $out;
	}

	public function getRows($table, $where = '', $order = '', $limit = '', $groupby = '') {
		if (!empty($where)) {
			$where = 'WHERE ' . $where;
		}

		if (!empty($order)) {
			$order = 'ORDER BY ' . $order;
		}

		if (!empty($groupby)) {
			$groupby = 'GROUP BY ' . $groupby;
		}
		
		if (!empty($limit)) {
			$limit = 'LIMIT ' . $limit;
		}
		
		$out = $this->fetch("SELECT * FROM `{$table}` $where $order $groupby $limit;");
		return $limit == 'LIMIT 1' ? $out[0] : $out;
	}

	public function getField($table, $field, $where) {
		$qid = $this->query("SELECT `{$field}` FROM `{$table}` WHERE {$where};");
		$row = $this->fetch_assoc($qid);
		return $row[$field];
	}

	public function rowCount($table, $where = '') {
		return count($this->getRows($table, $where));
	}

	public function update($table, $data, $where, $safe = true) {
		$q = "UPDATE `{$table}` SET ";

		foreach($data as $key => $val) {
			$tmp = $safe ? $this->esc($val) : $val;

			if(strtolower($val) == 'null') {
				$q.= "`{$key}` = NULL, ";
			} elseif(strtolower($val) == 'now()') {
				$q.= "`{$key}` = NOW(), ";
			} else {
				if (!$safe) {
					$q.= "`{$key}`={$tmp}, ";
				} else {
					$q.= "`{$key}`='{$tmp}', ";
				}
			}
		}

		return $this->query(rtrim($q, ', ') . " WHERE {$where};");
	}

	public function insert($table, $data) {
		$q = "INSERT INTO `{$table}` ";
		$v = $n = '';

		foreach($data as $key=>$val) {
			$n.= "`{$key}`, ";
			$tmp = $this->esc($val);

			if(strtolower($val) == 'null') {
				$v.= 'NULL, ';
			} elseif(strtolower($val) == 'now()') {
				$v.= 'NOW(), ';
			} else {
				$v.= "'{$tmp}', ";
			}
		}
		unset($tmp);

		$q .= '(' . rtrim($n, ', ') . ') VALUES (' . rtrim($v, ', ') . ');';
		return $this->query($q) ? $this->getInsertID() : false;
	}

	private function free($qid = -1) {
		if ($qid != -1) $this->qid = $qid;
		return mysql_free_result($qid);
	}
	
	public function error() {
		return mysql_error($this->lid);
	}

	public function close() {
		return mysql_close($this->lid);
	}
	
	public function getInsertID() {
		return mysql_insert_id($this->lid);
	}
	
	public function getLink() {
		return $this->lid;
	}
	
	public function getAffectedRows() {
		return $this->affrows;
	}

	public function esc($str) {
		if(get_magic_quotes_runtime()) $str = stripslashes($str);
		return mysql_real_escape_string($str, $this->lid);
	}

	public function filter($str) {
		return $str;
	}

	private function trigger_error($msg) {
		die(System::log($msg));
	}

	private function format($string, $args) {
		array_shift($args);
		$len = strlen($string);
		$sql_query = "";
		$args_i = 0;

		for($i = 0; $i < $len; $i++) {
			if($string[$i] == "%") {
				$char = $string[$i + 1];
				$i++;

				switch($char) {
					case "%":
						$sql_query .= $char;
						break;
					case "u": /* int */
						$sql_query .= "'" . intval($args[$args_i]) . "'";
						break;
					case "s": /* string */
						$sql_query .= "'" . $this->esc($args[$args_i]) . "'";
						break;
					case "x": /* hex */
						$sql_query .= "'" . dechex($args[$args_i]) . "'";
						break;
				    case "v": /* variable */
				    	$var = $args[$args_i];
                        if (is_numeric($var)) {
                        	$sql_query .= intval($var);
                        } else {
                            $sql_query .= $this->esc($var);
                        }
                        break;
				}

				if($char != "x") $args_i++;
			} else $sql_query .= $string[$i];
		}

		return $sql_query;
	}

	public function queryf($sql) {
		$args = func_get_args();
		return $this->query($this->format($sql, $args));
	}

	public function fetchf($sql) {
		$args = func_get_args();
		return $this->fetch($this->format($sql, $args));
	}
}