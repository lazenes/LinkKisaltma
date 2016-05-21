<?php
/*******************************************************************************
 * Link analytic system
 *
 * @created     02/06/2011
 * @modified    02/24/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/
 
define('ANALYZER_CLASS_PHP_INCLUDED', true); 
define('GEOIP_LOC', realpath(dirname(__FILE__)) . '/geoip/');
include_once GEOIP_LOC . 'geoip.php';
include_once '../global.php';

class Analyzer {
	protected $table = '';
	protected $db = null;
	protected $lid = 0;
	protected $oid = 0;
	protected $aid = 0;
	protected $cip = 0;
	protected $geo = null;
	
	protected $client = array(
		'ipaddr' => '0.0.0.0',
		'country' => '',
		'referrer' => ''
	);
	
	public function __construct($lid, $oid, $aid) {
		global $_GLOBAL;
		$this->table = $_GLOBAL['TABLES']['ANALYZER'];
		$this->db = System::getDB();
		$this->lid = $lid;
		$this->oid = $oid;
		$this->aid = $aid;
		$this->cip = mb_substr($this->ip(), 0, 15);
		$this->geo = geoip_open(GEOIP_LOC . 'GeoIP.dat', GEOIP_STANDARD);
	}
	
	public function __destruct() {
		geoip_close($this->geo);
	}
	
	public function _record($adtype = 'none', $view_time = 0) {
		global $_GLOBAL;
		if ($this->_isCrawler() || !$this->db || !$this->isValid()) return;
		$_u = $this->isUnique();
		
		$client['ipaddr'] = $this->cip;		
		$client['country'] = $this->country($client['ipaddr']);		
		$client['referrer'] = !getenv('HTTP_REFERER') ? '' : getenv('HTTP_REFERER');
		$client['referrer'] = mb_substr($this->_toUTF8($client['referrer']), 0, 255);
		try {
			$pu = parse_url($client['referrer']);
			/*if (strpos($pu['host'], SITE_LOCATION)) {
				$client['referrer'] = '';
			}*/
		} catch(Exception $e) { }
		
		$earned = 0;
		$adtype = strtolower($adtype);
		if ($adtype != 'none') {
			$earned = $this->getEarning(($_u ? 'u_' : 'r_') . $adtype, $client['country']);
			if ($adtype == 'top_banner') $earned *= .75;
			else if ($view_time < 6) $earned /= 6 - $view_time;
		}
		$data = array_merge($client, array('lid' => $this->lid, 'oid' => $this->oid, 'aid' => $this->aid,
										   'date' => date('Y-m-d'), 'earned' => $earned));
	
		$_p = false;
		if ($_u) {
			$_p = $this->db->insert($this->table, $data);
		} else {
			$_p = $this->db->update($this->table, array('earned' => 'earned+' . $earned), 
									"`lid`='{$this->lid}' AND `aid`='{$this->aid}' AND `ipaddr`='{$this->cip}' "
									. "AND `date`='" . date('Y-m-d') . "'", 
									false);
		}
		
		if ($_p) {
			$data = array('views' => 'views+1', 'earned' => 'earned+' . $earned);
			$data2 = array('available_earning' => 'available_earning+' . $earned);
			$this->db->update($_GLOBAL['TABLES']['LINKS'], $data, "`id`='{$this->lid}'", false);
			$data3  = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], "`id`='{$this->lid}'", '', '1');
			$this->db->update($_GLOBAL['TABLES']['USERS'], $data2, "`id`='{$data3['user']}'", false);
			if ($this->aid) {
				$c = $this->db->getRows($_GLOBAL['TABLES']['CAMPAIGNS'], "`id`='{$this->aid}'", '', '1');
				if ($c['spent_today'] < $c['daily_budget'] && $c['daily_budget'] != 0) {
					$cdat = array('views_left'=>'views_left-1', 'spent_today'=>'spent_today+' . $earned);
					if ($c['views_left'] <= 1) {
						$cdat['status'] = 3;
						$cdat['views_left'] = '0';
					}
					$this->db->update($_GLOBAL['TABLES']['CAMPAIGNS'], $cdat, "`id`='{$this->aid}'", false);
				}
			}
		} else {
			System::log($this->db->error());
		}
	}
	
	public function _recordClick() {
		global $_GLOBAL;
		if (!$this->isValid()) return false;
		$earned = $this->getEarning(($this->isUnique(true) ? 'u' : 'r') . '_top_banner', 
									$this->country($this->ip())) * .25;
		$cdat = array('total_clicks' => 'total_clicks+1', 'spent_today' => 'spent_today+' . $earned);
		$adat = array('earned' => 'earned+' . $earned, 
					  'banner_click' => '1');
		return $this->db->update($_GLOBAL['TABLES']['CAMPAIGNS'], $cdat, "`id`='{$this->aid}'", false)
			   && $this->db->update($this->table, $adat, "`id`='{$this->lid}'", false)
			   && System::getUser()->raiseEarning($earned);
	}
	
	public function isUnique($click = false) {
		$date = date('Y-m-d');
		$where = "`lid`='{$this->lid}' AND `aid`='{$this->aid}' AND `ipaddr`='{$this->cip}'" 
				 . ($click?" AND `banner_click`='1'":'');
		return !$this->db->rowCount($this->table, $where . " AND `date`='{$date}'");
	}
	
	public function isValid() {
		global $_GLOBAL;
		return $this->db->rowCount($_GLOBAL['TABLES']['LINKS'], "`id`='{$this->lid}' AND `user`='{$this->oid}'");
	}
	
	public function getEarning($adtype, $gccn) {
		global $_GLOBAL;
		$adtype = str_replace(' ', '_', strtolower($adtype));
		$pkgs = $this->db->getRows($_GLOBAL['TABLES']['PAYOUTS'], "`active`='1'");
		$prox = isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '';
		$eccn = $prox ? $pkgs[1] : $pkgs[0];
		if (!$prox) {
			for ($i = 0; $i < count($pkgs); $i++) {
				if ($pkgs[$i]['code'] == $gccn) {
					$eccn = $pkgs[$i];
					break;
				}
			}
		}
		return $eccn[$adtype] / 1000;
	}
	
	// ***
	
	private function _toUTF8($str) {
		$e = strtoupper(mb_detect_encoding($str));
		return $e == false || $e == 'UTF-8' || $e == 'ASCII' ? $str : iconv($e, 'UTF-8', $str);
	}

	// ***
	
	public function _isCrawler() {
		$ua = $_SERVER['HTTP_USER_AGENT'];
		$crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|' .
					'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
					'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby';
		return preg_match("/{$crawlers}/", $ua) > 0;
	}
	
	public function ip() {
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if (($ip == '127.0.0.1' || $ip == '::1' || $ip == $_SERVER['SERVER_ADDR'])
			&& isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
			$ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = $ips[0];
		}
		
		return $ip;
	}
	
	public function country($ip) {
		return geoip_country_code_by_addr($this->geo, $ip);
	}
	
	/*
	function browser() {
		$a = array('Windows', 'Mac', 'Linux', 'FreeBSD', 'DoCoMo', 'iPod', 'iPad', 'iPhone', 
				   'Android', 'Symbian', 'Nintendo', 'PlayStation');
		$a = $_SERVER['HTTP_USER_AGENT'];
		$b = get_browser($a, true);
		$o = '';
		
		foreach ($a as $b) {
			if (preg_match('/' . $b . '/', $u)) {
				$o = $b;
				break;
			}
		}
		
		return array('os' => $o, 'browser' => '' $b['parent']);
	}*/
}