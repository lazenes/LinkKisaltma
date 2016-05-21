<?php
require_once 'global.php';
System::verifyAccess();

$isPageEasy = isset($_GET['easy']);
//$isPageAPI = isset($_GET['api']);
$isPageGA = isset($_GET['ga']);
$isPageFP = isset($_GET['fp']);
$isPageExport = isset($_GET['export']);
$isPageMass = !$isPageEasy && !$isPageGA && !$isPageFP && !$isPageExport ? true : isset($_GET['mass']);

if ($isPageEasy) {
	$H_TEMPLATE['TITLE'] = 'Easy URL LinkS';
} else if ($isPageGA) {
	$H_TEMPLATE['TITLE'] = 'Google Analytics';
} else if ($isPageFP) {
	$H_TEMPLATE['TITLE'] = 'Full Page Script';
} else if ($isPageExport) {
	$H_TEMPLATE['TITLE'] = 'Export Links and Statistics';
} else {
	$H_TEMPLATE['TITLE'] = 'Mass URL Shrinker';
}

/** EXPORT {{{ */
$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'] ? $_POST['date_end'] : date('m/').(date('d')-1).date('/Y');
if (isset($_POST['export_submit'])) {
	function pdate($date) {
		if (!preg_match('/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4,}/', $date)) return false;
		try {
			$p = explode('/', trim($date));
			$s = strtotime($date);
			$month = trim(date('m', $s));
			$day = trim(date('d', $s)); 
			$year = trim(date('Y', $s));
			return checkdate($month, $day, $year) ? $p : false;
		} catch (Exception $e) { return false; }
	}
	$start = pdate($date_start);
	$end = pdate($date_end);
	if (!$date_start || !$start) {
		$F_TEMPLATE['ERRORS'][] = 'Please enter a valid date format for the starting date';
	} else if (!$date_end || !$end) {
		$F_TEMPLATE['ERRORS'][] = 'Please enter a valid date format for the ending date';
	} else if (strtotime($date_start) > strtotime($date_end)) {
		$F_TEMPLATE['ERRORS'][] = 'Your end date must be greater than the start date';
	} else {
		$out = '';
		$end[1]++;
		$user = System::getUser()->get('id');
		$data = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], "`user`='{$user}'"
										 . " AND `post_date`>='{$start[2]}-{$start[0]}-{$start[1]}'"
										 . " AND `post_date`<='{$end[2]}-{$end[0]}-{$end[1]}'"
		);
		$out .= '"'.SITE_NAME.' Export '.date('m/d/Y h:m:sa').' for Account: '
				. System::getUser()->get('email') . ' ('.$date_start.' - '.$date_end.')",' . "\n";
		$out .= '"Link","Destination URL","Views","Earnings",' . "\n";
		System::log("`user`='{$user}'"
										 . " AND `post_date`>='{$start[2]}-{$start[0]}-{$start[1]}'"
										 . " AND `post_date`<='{$end[2]}-{$end[0]}-{$end[1]}'");
		foreach ($data as $d) {
			$out .= '"'.$d['short_url'].'","'.$d['long_url'].'","'.$d['views'].'","'.$d['earned'].'",' 
					. "\n";
		}
		
		$csvfilename = strtolower(SITE_NAME . '_' . str_replace('/', '', $date_start) . '-' 
								  . str_replace('/', '', $date_end)) . '.csv';
		header("Content-type: application/csv");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename = {$csvfilename}");
		header("Content-Length: " . strlen($out));
		die($out);
	}
}
/** }}} EXPORT */

site_header(false,
			array(
				'Export' => array('?export', $isPageExport),
				'Full Page Script' => array('?fp', $isPageFP),
				'Google Analytics' => array('?ga', $isPageGA),
				'Easy Link' => array('?easy', $isPageEasy),
				'Mass Shrinker' => array('?mass', $isPageMass)
			)
);

if ($isPageMass) {
if (isset($_POST['tools_sub'])) {
	$adtype = $_POST['adtype'] > 1 && $_POST['adtype'] <= 3 ? $_POST['adtype'] : 1;
	$list = explode("\n", trim($_POST['shrink_list']));
	$list = array_filter($list, 'trim');
	eval('$badul = array(' . stripcslashes(BAD_URL_LIST) . ');');
	$valid = array();
	for ($i = 0; $i < count($list); $i++) {
		$list[$i] = System::getDB()->esc(trim($list[$i]));
		$host = Utilities::validateURL($list[$i]);
		if (!$host) {
			$F_TEMPLATE['ERRORS'][] = 'Please make sure all your URLs are in valid format';
			break;
		} else if (in_array($host, $badul)) {
			$F_TEMPLATE['ERRORS'][] = $host . ' URLs are not allowed!';
			break;
		}
		$valid[] = $list[$i];
		if ($i >= 19) break;
	}
	
	if (count($list) < 1) {
		$F_TEMPLATE['ERRORS'][] = 'Please enter a few URLs to shrink';
	}
	
	if (!$F_TEMPLATE['ERRORS']) {
		$surl  = SITE_LOCATION;
		$db    = System::getDB();
		$user  = System::getUser()->get('id');
		$user  = !$user ? 0 : $user;
		$adTL  = $adtype == 3 ? 'None' : ($adtype == 2 ? 'Top Banner' : 'Interstitial');
		$shls  = array();
		for ($i = 0; $i < count($valid); $i++) {
			$url = $valid[$i];
			if ($tmp = $db->getField($_GLOBAL['TABLES']['LINKS'], 'short_url', 
									 "`long_url`='{$url}' AND `adtype`='{$adTL}' AND `user`='{$user}'")) {
				$short = $tmp;
			} else {
				$data = array('long_url' => $url, 'adtype' => $adtype, 'user' => $user);
				$db->query('LOCK TABLES ' . $_GLOBAL['TABLES']['LINKS'] . ' WRITE;');
				$lid = $db->insert($_GLOBAL['TABLES']['LINKS'], $data);
				$db->query('UNLOCK TABLES');
				$short = $surl . Utilities::compressInt($lid);
				$db->update($_GLOBAL['TABLES']['LINKS'], array('short_url' => $short), "`id`='{$lid}'");
			}
			$shls[] = array($short, $url);
		}
	}
}
?>
<div class="user_content">
	<p>
		Enter up to 20 URLs (one per line) to be shrunk and added to your account.
	</p>
	<p>
		<strong>
			NOTE: The 'Mass URL Shrinker' can be disabled for your account if abused.
			Only create links that you will use.
		</strong>
	</p>
	<?php 
	if (isset($shls)) {
		foreach ($shls as $s) {
			echo '<div class="ml_options_box links">';
			echo "<a href='{$s[1]}' class='l'>{$s[1]}</a>";
			echo "<a href='{$s[0]}' class='s'>{$s[0]}</a>";
			echo '</div>';
		}
	} else {
	?>
	<form action="?" method="POST">
		<div class="ml_options_box tools">
			<span style="float:left;width:125px;margin-top:6px;">Advertising Type:</span>
			<span>
				<select style="width:200px;" name="adtype">
					<option value="1">Interstitial Advert ($$$$$)</option>
					<option value="2">Framed Banner ($$$)</option>
					<option value="3">No Advert</option>
				</select>
			</span>
		</div>
		<div class="tools_shrink_box">
			<textarea name="shrink_list"><?php echo $_POST['shrink_list']; ?></textarea>
		</div>
		<input type="submit" name="tools_sub" class="mass_shrink" value="MASS URL SHRINK!" />
	</form>
	<?php } ?>
</div>
<?php
} else if ($isPageEasy) {
?>
<div class="user_content">
	<p>
		If you have no need for a short URL and you just want to make money from your blog or 
		website, here is the quickest and simplest possible way to earn with <?php echo SITE_NAME; ?>:
	</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<a href="<?php echo SITE_LOCATION.$_UID; ?>/www.google.com" class="uc">
			<?php echo SITE_LOCATION.$_UID; ?>/www.google.com
		</a>
	</div>
	<p>
		Replace "www.google.com" with whatever URL you wish the user to go to if they skip the advertisement.
	</p>
	<p>
		You do not need to even login to <?php echo SITE_NAME; ?> to use this and your account will be credited 
		in the same way as if you had shortened the link.
	</p>
	<p>
		If you want the Easy Link with the Banner advertising (less money, but less intrusive):
	</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<a href="<?php echo SITE_LOCATION.$_UID; ?>/banner/www.google.com" class="uc">
			<?php echo SITE_LOCATION.$_UID; ?>/banner/www.google.com
		</a>
	</div>
</div>
<?php
} else if ($isPageGA) {
	$_GA_CODE = !$_POST['ga_code'] ? System::getUser()->get('ga_code') : $_POST['ga_code'];
	if (isset($_POST['ga_submit'])) {
		if (!preg_match('/UA-[0-9]+-[0-9]/', $_GA_CODE)) {
			$F_TEMPLATE['ERRORS'][] = 'Please enter a valid tracker code';
		} else {
			System::getUser()->update(array('ga_code' => $_GA_CODE));
		}
	}
?>
<div class="user_content">
	<p>
		Ever wished you could have the same statistics that Analytics gives you, in a URL shortener?
	</p>
	<p>
		Well now you can. Just enter your Analytics tracker code below (in the format shown) and 
		you will be able to view all of the stats in your Google account.
	</p>
	<p>
		Please allow several hours for your Google account to be updated.
	</p>
	<form action="tools.php?ga"	method="POST">
		<input type="text" name="ga_code" value="<?php echo $_GA_CODE; ?>" size="10" style="padding:3px 9px;" />
		(UA-XXXXX-X)
		<br />
		<input type="submit" name="ga_submit" value="Submit" style="padding:5px 15px;" />
	</form>
</div>
<?php } else if ($isPageFP) { ?>
<div class="user_content">
	<p>
		If you have a website with 100's or 1000's of links you want to change over to <?php echo SITE_NAME; ?>
		then please use the script below.
	</p>
	<p>
		Simply copy-and-paste the code below on to your webpage or blog and the links will be 
		updated automatically - simple!
	</p>
	<p>
		You can add or remove any domains for the code that you use or your website.
	</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<code style="font-size:11px;">
			&lt;script type=&quot;text/javascript&quot; src=&quot;<?php echo SITE_LOCATION; ?>js/fp.js.php&quot;&gt;&lt;/script&gt;
			<br />
			&lt;script type=&quot;text/javascript&quot;&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;var _x3 = _x3 || new _nsurls();<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.push(['accountID', '1']);<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.push(['adType', 'int']);<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.push(['allowDomains', ['yoursite.com', 'example.com',]]);<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.run();<br />
			&lt;/script&gt;<br />
		</code>
	</div>
	<p>
		Or if you wish to change every link to <?php echo SITE_NAME; ?> on your website 
		(without stating exactly which domains) please use the following code.
	</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<code style="font-size:11px;">
			&lt;script type=&quot;text/javascript&quot; src=&quot;<?php echo SITE_LOCATION; ?>js/fp.js.php&quot;&gt;&lt;/script&gt;
			<br />
			&lt;script type=&quot;text/javascript&quot;&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;var _x3 = _x3 || new _nsurls();<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.push(['accountID', '1']);<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.push(['adType', 'int']);<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.push(['disallowDomains', ['google.com', 'example.com',]]);<br />
			&nbsp;&nbsp;&nbsp;&nbsp;_x3.run();<br />
			&lt;/script&gt;<br />
		</code>
	</div>
	<p>You can add as many domains to the exclusion list as you wish.</p>
	<p>
		If you want to use Full Page Script with the Banner advertising 
		(less money, but less intrusive) change the following line:
	</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<code style="font-size:11px;">_x3.push(['adType', 'int']);</code>
	</div>
	<p>to:</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<code style="font-size:11px;">_x3.push(['adType', 'banner']);</code>
	</div>
</div>
<?php
} else if ($isPageExport) {
?>
<div class="user_content">
	<p>Use the form below to export and download your <?php echo SITE_NAME; ?> account.</p>
	<p>This can then be opened in Microsoft Excel or most spreadsheet programs.</p>
	<p><em>Reporting until <?php echo date('m/').(date('d')-1).date('/Y'); ?> available.</em></p>
	<br />
	<form action="tools.php?export"	method="POST">
		<span style="width:150px;display:inline-block;">Date Start (dd/mm/yyyy):</span>
		<input type="text" name="date_start" value="<?php echo $date_start; ?>" size="10" style="padding:3px 9px;" />
		<br />
		<span style="width:150px;display:inline-block;">Date End (dd/mm/yyyy):</span>
		<input type="text" name="date_end" value="<?php echo $date_end; ?>" size="10" style="padding:3px 9px;" />
		<br />
		<input type="submit" name="export_submit" value="Download Export" style="padding:5px 15px;" />
	</form>
</div>
<?php } site_footer(); ?>