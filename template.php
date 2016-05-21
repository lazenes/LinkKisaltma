<?php
$H_TEMPLATE = $F_TEMPLATE = array(
					'PAGE_TITLE' => '', 
					'TITLE' => '', 
				    'MESSAGES' => array(), 
				    'ERRORS' => array()
);

function join_page($ret = false) {
	$site_name = SITE_NAME;
	$html = <<< HTML
<div class="join_page">
	<form action="" method="POST" style="margin-bottom:10px;">
		<h3>{$site_name} Kayıt Ol</h3>
		<div class="mainhr" style="margin-bottom:5px;"></div>
		<div class="field">
			<div class="field_name">Ad Soyad:</div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="join_name" />
			</div>
			<div class="nspr inneri right"></div>
		</div>
		<div class="field">
			<div class="field_name">E-Mail:</div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="join_email" />
			</div>
			<div class="nspr inneri right"></div>
		</div>
		<div class="field">
			<div class="field_name">Tekrar E-Mail:</div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="join_remail" />
			</div>
			<div class="nspr inneri right"></div>
		</div>
		<div class="field">
			<div class="field_name">Şifre:</div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="password" name="join_pass" />
			</div>
			<div class="nspr inneri right"></div>
		</div>
		<div class="field">
			<div class="field_name">Tekrar Şifre:</div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="password" name="join_rpass" />
			</div>
			<div class="nspr inneri right"></div>
		</div>
		<div class="field">
			<div class="field_name">Hesap Türü:</div>
			<div class="field_name" style="font-size:12px;">
				<div>
					<span style="width:50px;">
						<input type="radio" name="join_atype" value="1" checked="yes" />
					</span>
					Link Kısaltma
					<div style="margin-left:25px;font-size:11px;">
						- Link Paylaşma
					</div>
				</div>
				<div>
					<span style="width:50px;">
						<input type="radio" name="join_atype" value="2" />
					</span>
					Reklam veren
					<div style="margin-left:25px;font-size:11px;">
						- reklamlarınız Yayınlansın
					</div>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>
		<div class="field" style="margin-top:10px;">
			<div class="nspr inneri left nblu"></div>
			<div class="nspr inneri bg nblu">
				<input type="submit" name="join_sub" value="KULLANIM KOŞULLARINI KABUL EDİYORUM VE ONAYLIYORUM!!" />
			</div>
			<div class="nspr inneri right nblu"></div>
		</div>
		<div style="clear:both;"></div>
	</form>
</div>
HTML;
	if ($ret) return $html;
	echo $html;
}

function announcements($ret = false) {
	global $_GLOBAL;
	$html = '<div class="user_content"><h2>Duyurular</h2>';
	if ($news = System::getDB()->getRows($_GLOBAL['TABLES']['NEWS'], '', '`date` DESC', '10')) {
		foreach ($news as $item) {
			$html .= '<div style="min-width:75px;float:left;font-weight:bold;">';
			$html .= date('d/m/Y', strtotime($item['date'])) . '</div>';
			$html .= '<div style="float:left;">' . $item['message'] . '</div>';
			$html .= '<div style="clear:both;height:5px;"></div>';
		}
	} else {
		$html .= '<em>none</em>';
	}
	$html .= '</div>';
	if ($ret) return $html;
	echo $html;
}

function site_header($show_shrinker = 0, $extra_links = null, $isAdmin = false, $cWidth = 85) {
	global $H_TEMPLATE;
	/* site_header() {{{ */
?>
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
	<title><?php echo $H_TEMPLATE['PAGE_TITLE']; ?></title>
	<meta name="keywords" content="short links, tinyurl, bitly, bit.ly, earn money, link advertising, tiny url, url shortener" />
	<meta name="description" content="Earn cash for each visitor to your shortened url links with <?php echo SITE_NAME; ?>!" />
	<meta name="robots" content="index,follow" />
	<meta name="copyright" content="Copyright 2011">
	<meta http-equiv="X-UA-Compatible" content="IE=8; IE=9" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_LOCATION; ?>css/jq.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_LOCATION; ?>css/style.css.php" />
	<!--[if lte IE 9]><script language="javascript" type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/excanvas.min.js"></script><![endif]-->
	<script type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/jquery.min.js"></script> 
	<script type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/jquery.libs.js"></script>
	<script type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/jquery.flot.min.js"></script>
	<script type="text/javascript" src="<?php echo SITE_LOCATION; ?>js/jquery.tablesorter.min.js"></script>
</head> 
<body> 
	<div class="main_ctr"<?php echo ($isAdmin?" style='width:{$cWidth}%;'":''); ?>>
		<div class="logo"<?php if ((System::getUser()->loggedIn()&&!$isAdmin)||is_array($extra_links)) echo ' style="float: left;"'; ?>>
			<a href="index.php"></a>
		</div>
		<?php if (System::getUser()->loggedIn() && !$isAdmin) { ?>
		<div class="navigation"<?php if (is_array($extra_links)) echo ' style="margin-top:39px;"'; ?>>
			<a href="index.php?">Anasayfa</a>
			<?php if (System::getUser()->loggedIn() == 2) { ?>
			<a href="advertising.php">Kampanya oluştur</a>
			<a href="referrals.php">Referaller</a>
			<?php } else { ?>
			<a href="referrals.php">Referaller</a>
			<a href="tools.php">Araçlar</a>
			<a href="withdraw.php">Ödemeler</a>
			<?php } ?>
			<a href="account.php">Hesabım</a>
			<a href="index.php?logout">Çıkışyap</a>
			<?php
				if (is_array($extra_links)) {
					echo '<br />';
					foreach ($extra_links as $k=>$v) {
						echo "<a href='{$v[0]}' style='float:right;margin:20px 8px;font-size:12px;'";
						if ($v[1]) echo " class='selected'";
						echo ">{$k}</a>";
					}
					$extra_links_handled = true;
				}
			?>
		</div>
		<?php
			}
			if (is_array($extra_links) && $extra_links_handled !== true) {
				echo '<div class="navigation">';
				$first_broker = false;
				foreach ($extra_links as $k=>$v) {
					if (isset($v[2]) && $v[2] && !$first_broker) {
						echo '<br /><br />';
						$first_broker = true;
					}
					echo "<a href='{$v[0]}' style='font-size:13px;";
					if (isset($v[2]) && $v[2]) echo 'float:right;';
					echo "'" . ($v[1] ? " class='selected'" : '') . ">{$k}</a>";
				}
				echo '</div>';
				echo '<div style="clear:both;"></div>';
			}
			
			echo '<div class="error_box" style="display:' 
				 . (!empty($H_TEMPLATE['ERRORS'])?'block':'none') . ';">';
			if (!empty($H_TEMPLATE['ERRORS'])) {
				if (count($H_TEMPLATE['ERRORS']) > 1) {
					echo '<div style="text-align:left;font-size:10px;margin-bottom:10px;">';
					echo count($H_TEMPLATE['ERRORS']) .  'Hatalar oluştu:</div>';
				}
				for ($i = 0; $i < count($H_TEMPLATE['ERRORS']); $i++) {
					echo $H_TEMPLATE['ERRORS'][$i];
					if ($i < count($H_TEMPLATE['ERRORS']) - 1) echo '<br />';
				}
			}
			echo '</div>';
			
			echo '<div class="message_box" style="display:' 
				 . (!empty($H_TEMPLATE['MESSAGES'])?'block':'none') . ';">';
			if (!empty($H_TEMPLATE['MESSAGES'])) {
				if (count($H_TEMPLATE['MESSAGES']) > 1) {
					echo '<div style="text-align:left;font-size:10px;margin-bottom:10px;">';
					echo count($H_TEMPLATE['MESSAGES']) .  ' MESAJLAR:</div>';
				}
				for ($i = 0; $i < count($H_TEMPLATE['MESSAGES']); $i++) {
					echo $H_TEMPLATE['MESSAGES'][$i];
					if ($i < count($H_TEMPLATE['MESSAGES']) - 1) echo '<br />';
				}
			}
			echo '</div>';
		?>
		<noscript>
			<div style="padding: 5px; background-color: red; color: #ffffff; text-align:center; margin-top: 10px; margin-bottom: 10px;">
			Tarayıcınız şu anda javascript desteklemiyor veya javascript kapattınız.
Bu site javascript olmadan çok sınırlı özelliğe sahip olacak .
javascript ile modern bir tarayıcı kullanın veya javascript seçeneğini etkinleştirin.
			</div>
		</noscript>
		<?php
			if (System::getUser()->loggedIn() != 2 && $show_shrinker) {
		?>
		<div class="shrink">
			<form action="" method="POST" style="margin:0;">
				<div class="nspr outeri left"></div>
				<div class="nspr outeri bg">
					<div class="nspr inneri left"></div>
					<div class="nspr inneri bg">
						<input type="text" name="adb_url" class="shrinker" value="<?php echo $adb_url; ?>" />
					</div>
					<div class="nspr inneri bg nblu">
						<input type="submit" name="adb_sub" class="shrinker" value="SHRINK!" />
					</div>
					<div class="nspr inneri right nblu"></div>
				</div>
				<?php
					if (System::getUser()->loggedIn() != 1) {
				?>
				<div class="nspr outeri right"></div>
				<?php
					} else if (System::getUser()->loggedIn() == 1) {
				?>
					<div class="ml_options">
						<div class="nspr outeri bg">Diğer Ayarlar</div>
						<div class="nspr outeri right"></div>
					</div>
					<div style="clear:both;"></div>
					<div class="ml_options_box">
						<span style="float:left;width:125px;margin-top:6px;">Reklam Türü :</span>
						<span style="float:left;">
							<select style="width:200px;" name="advert_type">
								<option value="1">Ust Katman($$$$$)</option>
								<option value="2">Çerçeveli ($$$)</option>
								<option value="3">Reklam Yok</option>
							</select>
						</span>
						<div style="clear:both;height:10px;"></div>
						<span style="float:left;width:125px;margin-top:6px;">Özel Ad:</span>
						<span style="float:left;">
							<input type="text" name="custom_name" maxlength="40" style="width:200px;" />
						</span>
					</div>
				<?php } ?>
			</form>
		</div>
		<?php	if (!System::getUser()->loggedIn()) { ?>
		<div class="joinbtn">
			<div class="nspr outeri left over"></div>
			<div class="nspr outeri bg over">KAYIT OL</div>
			<div class="nspr outeri right over"></div>
		</div>
		<?php
					join_page();
				}
			} else if (System::getUser()->loggedIn()) echo '<div style="clear:both"></div>';
		
			if ($H_TEMPLATE['TITLE']) {
				echo "<h2 class='page_title'>{$H_TEMPLATE['TITLE']}</h2>";
			}
		?>
<?php
} /* }}} site_header() */
	
function site_footer($show_copyright = true) {
	global $F_TEMPLATE;
	/* site_footer() {{{ */
	if ($show_copyright) {
?>
		<div class="footer">
			<span style="float:left;">
				&copy; Copyright 2011 <?php echo SITE_NAME; ?> v<?php echo SITE_VERSION; ?>
			</span>
			<span style="float:right;">
				<a href="advertising.php">Reklamveren Oranları</a> | 
				<a href="rates.php"> Yayıncı Oranları</a> |
				<a href="privacy.php"> Gizlilik</a> |
				<a href="terms.php">Şartlar</a> | 
				<a href="faq.php">SSS</a> | 
				<a href="contact.php">İletişim</a>
			</span>
			<div style="clear:both;"></div>
		</div>
	</div>
	<?php
	}
	if (!empty($F_TEMPLATE['ERRORS'])) {
		$errors = '';
		echo '<script type="text/javascript">';
		foreach ($F_TEMPLATE['ERRORS'] as $err) $errors .= $err . '\n';
		echo "alert('{$errors}');";
		echo '</script>';
	}
	?>
</body> 
</html>
<?php
} /* }}} site_footer() */
?>