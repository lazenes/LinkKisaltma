<?php
/*******************************************************************************
 * Several ajax related functions
 *
 * @created     02/01/2011
 * @modified    04/26/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

define('AJAX_PHP_INCLUDED', true);

require_once 'global.php';
$OPT = $_GET['opt'];
$RET = array('error' => false, 'message' => false);

if ($OPT == 'shrink') {
	$surl = SITE_LOCATION;
	$url = urldecode($_GET['url']);
	$title = $_GET['args']['title'];
	$adtype = $_GET['args']['adtype'] > 1 && $_GET['args']['adtype'] <= 3 ? $_GET['args']['adtype'] : 1;
	eval('$badlist = array(' . stripcslashes(BAD_URL_LIST) . ');');
	$host = Utilities::validateURL($url);
	if (!$host) {
		$RET['error'] = 'Please enter a valid URL';
	} else if (in_array($host, $badlist)) {
		$RET['error'] = $host . ' URLs are not allowed!';
	} else {
		$short = '';
		$db    = System::getDB();
		$user  = System::getUser()->get('id');
		$user  = !$user ? 0 : $user;
		$adTL  = $adtype == 3 ? 'None' : ($adtype == 2 ? 'Top Banner' : 'Interstitial');
		if ($tmp = $db->getField($_GLOBAL['TABLES']['LINKS'], 'short_url', 
								 "`long_url`='{$url}' AND `adtype`='{$adTL}' AND `user`='{$user}'")) {
			$short = $tmp;
		} else {
			$data = array('long_url' => $url, 'title' => $title, 'adtype' => $adtype, 'user' => $user);
			$db->query('LOCK TABLES ' . $_GLOBAL['TABLES']['LINKS'] . ' WRITE;');
			$lid = $db->insert($_GLOBAL['TABLES']['LINKS'], $data);
			$db->query('UNLOCK TABLES');
			$short = $surl . Utilities::compressInt($lid);
			$db->update($_GLOBAL['TABLES']['LINKS'], array('short_url' => $short), "`id`='{$lid}'");
		}
		$RET['message'] = $short;
	}
} else if ($OPT == 'join') {
	$reqd = array('join_name', 'join_email', 'join_remail', 'join_pass', 'join_rpass', 'join_atype');
	$args = $_GET['args'];
	for($i = 0; $i < count($reqd); $i++) {
		if (!$args[$reqd[$i]] || empty($args[$reqd[$i]])) {
			$RET['error'] = 'Please fill out all the fields and then submit';
			$RET['error_trace'] = '$args[$reqd[$i]]: ' . $args[$reqd[$i]] . '; $reqd[$i]: ' . $reqd[$i];
			break;
		} else {
			$args[$reqd[$i]] = trim($args[$reqd[$i]]);
		}
	}
	
	$db = System::getDB();
	
	if (!$RET['error']) {
		if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $args['join_email'])) {
			$RET['error'] = 'Please enter a valid email address';
		} else if ($db->rowCount($_GLOBAL['TABLES']['USERS'], "email='{$args['join_email']}'")) {
			$RET['error'] = 'The email you\'ve entered already exists';
		} else if ($args['join_email'] != $args['join_remail']) {
			$RET['error'] = 'The emails you\'ve entered do not match';
		} else if (strlen($args['join_pass']) < 6) {
			$RET['error'] = 'The password must be atleast 6 characters long';
		} else if ($args['join_pass'] != $args['join_rpass']) {
			$RET['error'] = 'The passwords youve entered do not match';
		} else if ($args['join_atype'] != '1' && $args['join_atype'] != '2') {
			$RET['error'] = 'Please choose an account type';
			$RET['error_trace'] = '$args[join_atype]: ' . $args['join_atype'];
		}
	}
	
	if (!$RET['error']) {
		$data = array();
		$data['name']  = $args['join_name'];
		$data['email'] = $args['join_email'];
		$data['pass']  = md5($args['join_pass']);
		$data['code']  = md5($data['email'] . microtime());
		$data['atype'] = $args['join_atype'];
		if (intval($_COOKIE['REFERRER'])) $data['refid'] = intval($_COOKIE['REFERRER']);
		
		if ($db->insert($_GLOBAL['TABLES']['USERS'], $data)) {
			$RET['message'] = 'Registration Successful!';
			$msg = "Welcome {$data['name']}, to " . SITE_NAME . "!<br /><br />"
				   . "Please keep this email safe as it contains your login details "
				   . "for " . SITE_NAME . ".<br /><br />" . "<strong>Username:</strong> "
				   . "{$data['email']}<br /><strong>Password:</strong> <em>as chosen at signup."
				   . "</em><br /><strong>Confirmation Code:</strong> {$data['code']}<br /><br />"
				   . "<a href=\"" . SITE_LOCATION . "verify.php?c={$data['code']}\">"
				   . "Click this link to activate your account</a><br /><br />"
				   . "Regards,<br />" . SITE_NAME;
			System::sendEmail(SITE_REPLY_EMAIL, SITE_REPLY_EMAIL, $data['email'],
							  'Welcome to ' . SITE_NAME . '!', $msg);
		} else {
			$RET['error'] = 'A database error has occured while registering. Please try again '
							. 'later or contact the administrator if the problem persists.';
		}
	}
}

/* -> */
else if ($OPT == 'get_links') {
	$user_links = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], "`user`='{$_UID}'");
	$_rmsg = '';
	if ($user_links) {
		foreach ($user_links as $link) {
			$lu = $link['long_url'];
			if (strlen($lu) > 69) $lu = substr($lu, 0, 69) . '...';
			$_rmsg .= "<tr id='lid_{$link['id']}'>";
			$_rmsg .= "<td><a href='{$link['short_url']}' class='short'>{$link['short_url']}</a><br />";
			$_rmsg .= "<span id='rtitle' style='display:";
			$_rmsg .= $link['title'] ? "block;" : "hidden;";
			$_rmsg .= "'><em>{$link['title']}</em></span>";
			$_rmsg .= "<span id='long'><a href='{$link['long_url']}' class='long'><em>{$lu}</em></a></span></td>";
			$_rmsg .= "<td id='adtype'>{$link['adtype']}</td>";
			$_rmsg .= "<td>{$link['views']}</td>";
			$_rmsg .= "<td>\${$link['earned']}</td>";
			$_rmsg .= "<td align='center'><a href='#' id='edit'><img src='images/tsp_edit.png' border='0' /></a>";
			$_rmsg .= "<span>&nbsp;&nbsp;</span><a href='#' id='rem'><img src='images/tsp_del.png' border='0' /></a></td>";
			$_rmsg .= '</tr>';
		}
		$_lp = <<<HTML
<div class="links_pager">
	<form>
		<div style="padding-top:3px;float:left;display:inline-block;">
			<a href="#" class="first"><img src="images/tsp_first.png" border="0" /></a>
			<a href="#" class="prev"><img src="images/tsp_prev.png" border="0" /></a>
		</div>
		<div style="padding:3px 3px 0 3px;float:left;display:inline-block;">
			<strong>Page:</strong> <span class="pagedisplay"></span>
		</div>
		<div style="padding-top:3px;float:left;display:inline-block;">
			<a href="#" class="next"><img src="images/tsp_next.png" border="0" /></a>
			<a href="#" class="last"><img src="images/tsp_last.png" border="0" /></a>
		</div>
		<input type="hidden" class="pagesize" value="10" />
	</form>
	<div style="clear:both;"></div>
</div>
<div style="clear:both;"></div>
<script type="text/javascript">
HTML;
	$js = <<<JS
$(function() {
		$("table.links_table").tablesorter({widthFixed: true,
											headers: { 
												0: {sorter: false}, 2: {sorter: "integer"},
												3: {sorter: "currency"}, 4: {sorter: false}
											}})
							  .tablesorterPager({container: $("div.links_pager")});
		
	});
JS;
	$_lp .= Utilities::JSPack($js) . '</script>';
	} else {
		$_rmsg .= '<tr id="no_link">';
		$_rmsg .= '<td colspan="5"><em>You currently have no links</em></td>';
		$_rmsg .= '</tr>';
	}
	$RET['message'] = array('data'=>$_rmsg,'lp'=>$_lp);
} else if ($OPT == 'edit_link') {
	$lid = $_GET['args']['lid'];
	$url = $_GET['args']['website'];
	$title = $_GET['args']['title'];
	$adtype = $_GET['args']['adtype'] > 1 && $_GET['args']['adtype'] <= 3 ? $_GET['args']['adtype'] : 1;
	$_uid_ = isset($_GET['args']['_uid_']) ? intval($_GET['args']['_uid_']) : -1;
	$host = Utilities::validateURL($url);
	eval('$badlist = array(' . stripcslashes(BAD_URL_LIST) . ');');
	
	if (!$host) {
		$RET['error'] = 'Please enter a valid URL';
	} else if (!$lid || !($lid > 0)) {
		$RET['error'] = 'There was an error retrieving link ID. Please refresh the page to correct this message';
	} else if (in_array($host, $badlist)) {
		$RET['error'] = $host . ' URLs are not allowed!';
	} else {
		$db    = System::getDB();
		$user  = $_uid_ >= 0 ? $_uid_ : (!$_UID ? 0 : $_UID);
		if ($db->rowCount($_GLOBAL['TABLES']['LINKS'], "`id`='{$lid}' AND `user`='{$user}'")) {
			$db->update($_GLOBAL['TABLES']['LINKS'], 
						array('long_url'=>$url,'title'=>$title,'adtype'=>$adtype), 
						"`id`='{$lid}' AND `user`='{$user}'");
			$RET['message'] = 'Updated!';
		} else {
			$RET['error'] = 'The link your trying to edit does not exist!';
		}
	}
} else if ($OPT == 'rem_link') {
	$lid = $_GET['args']['lid'];
	$_uid_ = intval($_GET['args']['_uid_']);
	if (!$lid || !($lid > 0)) {
		$RET['error'] = 'There was an error retrieving link ID. Please refresh the page to correct this message';
	} else {
		$db    = System::getDB();
		$user  = $_uid_ > 0 ? $_uid_ : (!$_UID ? 0 : $_UID);
		if ($db->rowCount($_GLOBAL['TABLES']['LINKS'], "`id`='{$lid}' AND `user`='{$user}'")) {
			$db->query("DELETE FROM `{$_GLOBAL['TABLES']['LINKS']}` WHERE `id`='{$lid}' AND `user`='{$user}';");
			$RET['message'] = 'Removed!';
		} else {
			$RET['error'] = 'The link your trying to edit does not exist!';
		}
	}
} else if ($OPT == 'get_report') {
	System::verifyAccess();
	$rg_j['month'] = $_GET['args']['month'];
	$rg_j['year'] = $_GET['args']['year'];

	$links_data = System::getDB()->getRows($_GLOBAL['TABLES']['ANALYZER'], "`oid`='{$_UID}' AND "
										   . "month(date)='{$rg_j['month']}' AND "
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
	System::verifyAccess();
	$month = $_GET['args']['month'];
	$year = $_GET['args']['year'];
	
	$links_data = System::getDB()->getRows($_GLOBAL['TABLES']['ANALYZER'], "`oid`='{$_UID}' AND "
										   . "month(date)='{$month}' AND year(date)='{$year}'");

	include_once ROOT_PATH . '/inc/geoip/geoip.php';
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
}

die(json_encode($RET));