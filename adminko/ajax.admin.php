<?php
/*******************************************************************************
 * Several ajax related functions
 *
 * @created     02/26/2011
 * @modified    04/26/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

define('AJAX_ADMIN_PHP_INCLUDED', true);

require_once '../global.php';
$OPT = $_GET['opt'];
$RET = array('error' => false, 'message' => false);

if ($OPT == 'get_report') {
	$rg_j['month'] = $_GET['args']['month'];
	$rg_j['year'] = $_GET['args']['year'];

	$links_data = System::getDB()->getRows($_GLOBAL['TABLES']['ANALYZER'], 
										   "month(date)='{$rg_j['month']}' AND "
										   . "year(date)='{$rg_j['year']}'", 
										   '`date` ASC');

	$visitors = $cpm = $earned = 0;
	$rg_data = array();
	foreach ($links_data as $ld) {
		$date = explode('-', $ld['date']);
		if (!$rg_data[(int)$date[2]]) $rg_data[(int)$date[2]] = 0;
		$rg_data[(int)$date[2]]++;
		$visitors++;
		$earned += $ld['earned'];
	}
	if ($earned) $cpm = $earned / ($visitors / 1000);
	
	$rg_j['x_max'] = cal_days_in_month(CAL_GREGORIAN, $rg_j['month'], $rg_j['year']);
	$rg_j['y_max'] = $rg_data ? max(10, ceil(max($rg_data) / 5) * 5) : 10;

	$rg_data_str = '';
	$rgj_xmax = $rg_j['month'] == date('m') && $rg_j['year'] == date('Y') ? date('d') : $rg_j['x_max'];
	for ($i = 1; $i <= $rgj_xmax; $i++) {
		$v = $rg_data[$i] ? $rg_data[$i] : 0;
		$rg_data_str .= "[{$i},{$v}],";
	}
	$rg_data_str = rtrim($rg_data_str, ',');
	$RET['message'] = array('data'=>$rg_data_str,'x_max'=>$rg_j['x_max'],'y_max'=>$rg_j['y_max'],
							'visitors'=>number_format($visitors),'cpm'=>number_format($cpm,2),
							'earned'=>number_format($earned,5));
} else if ($OPT == 'get_t20_cr') {
	$month = $_GET['args']['month'];
	$year = $_GET['args']['year'];
	
	$links_data = System::getDB()->getRows($_GLOBAL['TABLES']['ANALYZER'], 
										   "month(date)='{$month}' AND year(date)='{$year}'");

	include_once '../inc/geoip/geoip.php';
	function ns_geoip_country($c) {
		$geo = new GeoIP();
		return $geo->GEOIP_COUNTRY_NAMES[$geo->GEOIP_COUNTRY_CODE_TO_NUMBER[$c]];
	}
	
	$tc_data = array();
	$tr_data = array();
	foreach ($links_data as $ld) {
		if ($ld['country'] && count($tc_data) < 20) {
			if (!$tc_data[$ld['country']]) {
				$tc_data[$ld['country']] = array('country' => ns_geoip_country($ld['country']));
			}
			if (!$tc_data[$ld['country']]['hits']) $tc_data[$ld['country']]['hits'] = 0;
			$tc_data[$ld['country']]['hits']++;
			$tc_data[$ld['country']]['earned'] += $ld['earned'];
		}
		
		if ($ld['referrer'] && count($tr_data) < 20) {
			if (!$tr_data[$ld['referrer']]) $tr_data[$ld['referrer']] = array();
			if (!$tr_data[$ld['referrer']]['hits']) $tr_data[$ld['referrer']]['hits'] = 0;
			$tr_data[$ld['referrer']]['hits']++;
		}
		
		if (count($tc_data) + count($tr_data) == 40) break;
	}
	
	function hits($a, $b) { return ($a['hits'] > $b['hits']) ? -1 : 1; }
	uasort($tc_data, 'hits');
	uasort($tr_data, 'hits');
	$RET['message'] = array('tc_data' => $tc_data, 'tr_data' => $tr_data);
} else if ($OPT == 'edit_campaign') {
	$cid = intval($_GET['args']['cid']);
	$_uid = intval($_GET['args']['_uid']);
	$website_name = $_GET['args']['website_name'];
	$website_url = $_GET['args']['website_url'];
	$website_banner = $_GET['args']['website_banner'];
	$daily_budget = $_GET['args']['daily_budget'];
	$cstatus = $_GET['args']['cstatus'] > 1 && $_GET['args']['cstatus'] <= 3 ? $_GET['args']['cstatus'] : 1;
	
	if (!($cid > 0) || !($_uid > 0)) {
		$RET['error'] = 'There was an error retrieving link or user ID. Please refresh the page to correct this message';
	} else {
		$db    = System::getDB();
		if ($db->rowCount($_GLOBAL['TABLES']['CAMPAIGNS'], "`id`='{$cid}' AND `uid`='{$_uid}'")) {
			$db->update($_GLOBAL['TABLES']['CAMPAIGNS'], 
						array('website_name'=>$website_name,
								'website_url'=>$website_url,
								'website_banner'=>$website_banner,
								'daily_budget'=>$daily_budget,
								'status'=>$cstatus), 
								"`id`='{$cid}' AND `uid`='{$_uid}'");
			$RET['message'] = 'Updated!';
		} else {
			$RET['error'] = 'The campaign your trying to edit does not exist!';
		}
	}
} else if ($OPT == 'rem_campaign') {
	$cid = $_GET['args']['cid'];
	$_uid = intval($_GET['args']['_uid']);
	if (!($cid > 0) || !($_uid > 0)) {
		$RET['error'] = 'There was an error retrieving link or user ID. Please refresh the page to correct this message';
	} else {
		$db    = System::getDB();
		if ($db->rowCount($_GLOBAL['TABLES']['CAMPAIGNS'], "`id`='{$cid}' AND `uid`='{$_uid}'")) {
			$db->query("DELETE FROM `{$_GLOBAL['TABLES']['CAMPAIGNS']}` WHERE `id`='{$cid}' AND `uid`='{$_uid}';");
			$RET['message'] = 'Removed!';
		} else {
			$RET['error'] = 'The campaign your trying to edit does not exist!';
		}
	}
}

die(json_encode($RET));