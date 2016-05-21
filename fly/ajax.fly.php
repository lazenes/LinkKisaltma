<?php
/*******************************************************************************
 * Several ajax related functions
 *
 * @created     02/16/2011
 * @modified    04/26/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

define('AJAX_FLY_PHP_INCLUDED', true);

require_once '../global.php';
$OPT  = $_GET['opt'];
$POPT = $_POST['opt'];
$RET  = array('error' => false, 'message' => false);

if ($POPT == 'check_log') {
	$lid = intval($_POST['args']['lid']);
	$oid = intval($_POST['args']['oid']);
	$vlo = SITE_SESSION_PREPEND . "view_{$lid}_{$oid}";
	if ($lid > 0 && $oid >= 0) {
		$view_time = 0;
		$cookie = $_COOKIE[$vlo] | false;
		if ($cookie && $cookie > 0) {
			$view_time = gettimeofday(true) - $cookie;
		} else {
			setcookie($vlo, gettimeofday(true));
		}

		if ($view_time > 5) $RET['message'] = array('url' => '#');
	}
} else if ($POPT == 'make_log') {
	$aid = intval($_POST['args']['aid']);
	$lid = intval($_POST['args']['lid']);
	$oid = intval($_POST['args']['oid']);
	$vlo = SITE_SESSION_PREPEND . "view_{$lid}_{$oid}";
	$cookie = $_COOKIE[$vlo] | false;
	$view_time = $cookie && $cookie > 5 ? gettimeofday(true) - $cookie : 0;
	if ($view_time > 5) {
		require_once ROOT_PATH . '/inc/analyzer.class.php';
		$analyzer = new Analyzer($lid, $oid, $aid);
		$adtype = System::getDB()->getField($_GLOBAL['TABLES']['LINKS'], 'adtype', "`id`='{$lid}'");
		$analyzer->_record($adtype, $view_time - 6);
		setcookie($vlo, 0);
		$RET['message'] = array('url'=>System::getDB()->getField($_GLOBAL['TABLES']['LINKS'], 
																 'long_url', "`id`='{$lid}'")
		);
	}
} else if ($POPT == 'click_log') {
	$aid = intval($_POST['args']['aid']);
	$lid = intval($_POST['args']['lid']);
	$oid = intval($_POST['args']['oid']);
	require_once ROOT_PATH . '/inc/analyzer.class.php';
	$analyzer = new Analyzer($lid, $oid, $aid);
	$RET['message'] = $analyzer->_recordClick();
}

die(json_encode($RET));