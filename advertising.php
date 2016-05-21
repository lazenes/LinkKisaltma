<?php
require_once 'global.php';

$H_TEMPLATE['TITLE'] = 'Advertising Rates';
site_header();

if (isset($_POST['package_submit'])) {
	System::verifyAccess('advertising.php');

	/** Quick Utils {{{ */
	function get_package($id) {
		global $_GLOBAL;
		return System::getDB()->getField($_GLOBAL['TABLES']['PACKAGES'], 'name', "`id`='{$id}'");
	}
	function get_price($id) {
		global $_GLOBAL;
		return System::getDB()->getField($_GLOBAL['TABLES']['PACKAGES'], 'price', "`id`='{$id}'");
	}
	/** }}} Quick Utils */

	$package_submit = true;
	$advert_type = $_POST['advert_type'] == '2' ? 'Banner' : 'Interstitial';
	$package = array();
	$subtotal = 0;
	foreach ($_POST['pkg'] as $k=>$v) {
		if ($v) {
			$package[$k] = $v;
			$subtotal += get_price($k) * $v;
		}
	}

	if ($subtotal < 5) {
		$F_TEMPLATE['ERRORS'][] = 'You must purchase atleast $5.00 worth of traffic';
		$package_submit = false;
	}
}

if (isset($package_submit) && $package_submit === true) {
	if (isset($_POST['advert_sub'])) {
		$advert_sub = $_POST['advert_sub'];
		$website_name = $_POST['website_name'];
		$website_url = $_POST['website_url'];
		$website_banner = $_POST['website_banner'];
		$daily_budget = floatval($_POST['daily_budget']);
		$terms = $_POST['terms'];
		
		if (!$package || !$advert_type) {
			$F_TEMPLATE['ERRORS'][] = 'There was an error with the form, please make sure you fill everything out';
		} else if (!$website_name) {
			$F_TEMPLATE['ERRORS'][] = 'Please enter a website name';
		} else if (!Utilities::validateURL($website_url)) {
			$F_TEMPLATE['ERRORS'][] = 'Please enter a valid URL for the website';
		} else if ($advert_type == 'Banner' && !Utilities::validateURL($website_banner)) {
			$F_TEMPLATE['ERRORS'][] = 'Please enter a valid URL for the banner';
		} else if (!$daily_budget && $_POST['daily_budget'] != '0') {
			$F_TEMPLATE['ERRORS'][] = 'Please enter a numeric value for your daily budget';
		} else if (!$terms) {
			$F_TEMPLATE['ERRORS'][] = 'You must agree to the terms and conditions in order to proceed';
		} else {
			$views_left = 0;
			$packages = '';
			$subtotal = 0;
			foreach ($package as $k=>$v) {
				$packages .= $k . ',' . $v . ';';
				$views_left += $v * 1000;
				$subtotal += get_price($k) * $v;
			}
			
			$db   = System::getDB();
			$data = array('uid' => $_UID,
						  'website_name' => $website_name,
						  'website_url' => $website_url,
						  'daily_budget' => $daily_budget,
						  'advert_type' => $advert_type,
						  'created' => date('Y-m-d h:m:s'),
						  'views_left' => $views_left,
						  'status' => '1',
						  'packages' => $packages
			);
			if ($advert_type == 'Banner') $data['website_banner'] = $website_banner;
			$cid = $db->insert($_GLOBAL['TABLES']['CAMPAIGNS'], $data);
			if ($advert_sub == 'paypal') {
				$a = array('cmd' => '_xclick',
						   'business' => SITE_PAYPAL_EMAIL,
						   'currency_code' => SITE_CURRENCY,
						   'item_name' => '[Campaign ID: ' . $cid . '] ' . $website_name,
						   'amount' => $subtotal,
						   'notify_url' => SITE_PAYPAL_CALLBACK_URL,
						   'tax_rate' => Utilities::getVAT($subtotal),
						   'custom' => serialize(array('cid'=>$cid)),
						   'image_url' => SITE_PAYPAL_CHECKOUT_LOGO,
						   'cpp_ headerback_ color' => '2687be',
						   'no_shipping' => '1',
						   'return' => SITE_LOCATION . '?done',
						   'cancel_return' => SITE_LOCATION . '?cancelled'
				);
				$u = 'https://www.paypal.com/cgi-bin/webscr?' . Utilities::arrayToURI($a);
				System::redirect($u);
			}
		}
	}
?>
<div class="user_content">
	<p>Please complete the form below to continue creating your campaign.</p>
	<p>
		You can see an example of how our advertising looks
		<a href="<?php echo SITE_LOCATION; ?>1">here</a>.
	</p>
	<form action="" method="POST">
		<input type="hidden" name="advert_type" value="<?php echo $_POST['advert_type']; ?>" />
		<?php
			foreach ($package as $k=>$v) {
				echo "<input type='hidden' name='pkg[{$k}]' value='{$v}' />";
			}
		?>
		<input type="hidden" name="package_submit" value="<?php echo $_POST['package_submit']; ?>" />
		<table width="100%" style="color:#000000;font-size:13px;">
			<tbody>
				<tr>
					<td>Website Name:</td>
					<td><input type="text" name="website_name" size="30" value="<?php echo $_POST['website_name']; ?>" /></td>
					<td style="color:orange;">
						<em>(only for internal use)</em>
					</td>
				</tr>
				<tr>
					<td>Website URL:</td>
					<td><input type="text" name="website_url" size="45" 
					value="<?php echo $_POST['website_url'] ? $_POST['website_url'] : 'http://'; ?>" /></td>
					<td style="color:orange;">
						<em>(must comply with our <a href="advertising.php#rules" target="_blank">rules</a>)</em>
					</td>
				</tr>
				<?php if ($advert_type == 'Banner') { ?>
				<tr>
					<td>Website Banner:</td>
					<td><input type="text" name="website_banner" size="45" 
					value="<?php echo $_POST['website_banner'] ? $_POST['website_banner'] : 'http://'; ?>" /></td>
					<td style="color:orange;">
						<em>(must comply with our <a href="advertising.php#rules" target="_blank">rules</a>)</em>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td>
						Max. Daily Budget:
						<div style="float:right;">$</div>
					</td>
					<td><input type="text" name="daily_budget" size="10" 
					value="<?php echo $_POST['daily_budget'] ? $_POST['daily_budget'] : '0'; ?>" /></td>
					<td style="color:orange;">
						<em>(enter 0 for no budget)</em>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<div style="background-color:#f9fea0;font-size:12px;border:1px solid #CCC;padding:9px;">
							<strong>*</strong>
							We recommend to set a daily budget to spread the traffic out over 24 hours.
						<div>
					</td>
				</tr>
				<tr>
					<td>Terms & Conditions:</td>
					<td>
						<input type="checkbox" name="terms" value="1" />
						I agree to the <a href="terms.php" target="_blank">terms and conditions</a>
					</td>
					<td></td>
				</tr>
			</tbody>
		</table>
		<div style="height:45px;">&nbsp;</div>
		<table cellspacing="1" class="tablesorter order_table" align="center">
			<thead>
				<tr>
					<th>Order Description</th>
					<th align="right">Quantity</th>
					<th align="right">Rate</th>
					<th align="right">Amount</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$desc  = $advert_type . ' traffic for';
					$total = 0;
					foreach ($package as $k=>$v) {
						$p = get_price($k);
						echo '<tr>';
						echo '<td>' . $desc . ' ' . get_package($k) . '</td>';
						echo '<td align="right">' . number_format($v * 1000) . '</td>';
						echo '<td align="right">$' . number_format($p / 1000, 5, '.', ',') . '</td>';
						echo '<td align="right">$' . money_format('%i', $p * $v) . '</td>';
						echo '</tr>';
						$total += $p * $v;
					}
					$VAT = Utilities::getVAT($total);
				?>
			</tbody>
			<tfoot>
				<tr style="color:#111;">
					<td colspan="3" align="right">Total:</td>
					<td align="right"><?php echo money_format('$%i', $total); ?></td>
				</tr>
				<tr style="color:#111;">
					<td colspan="3" align="right">VAT (20% Tax):</td>
					<td align="right"><?php echo money_format('$%i', $VAT); ?></td>
				</tr>
				<tr style="color:#111;font-weight:bold;">
					<td colspan="3" align="right">Subtotal:</td>
					<td align="right"><?php echo money_format('$%i', $VAT + $total); ?></td>
				</tr>
			</tfoot>
		</table>
		<div style="height:30px;">&nbsp;</div>
		<div style="background-color:#f9fea0;font-size:12px;border:1px solid #CCC;padding:9px;">
			<strong>*</strong>
			If your PayPal email address is different to your <?php echo SITE_NAME; ?> email address, you will be sent an confirmation email after you have placed your order for fraud prevention.
		</div>
		<div style="height:10px;">&nbsp;</div>
		<input style="margin-right:15px;" type="image" src="images/paypal_sub.gif" border="0" alt="Add" title="Pay by PayPal" name="advert_sub" value="paypal" />
	</form>
	</form>
</div>
<?php
} else {
	$_isAdvertiser = System::getUser()->loggedIn() == 2;
	$_AT = $_GET['at'] == '2' ? 'Banner' : 'Interstitial';
	$advert_packages = System::getDB()->getRows($_GLOBAL['TABLES']['PACKAGES'],
												"`price`>0 AND `advert_type`='{$_AT}'", 
												'`zorder`, `price` DESC');
?>
<div class="user_content">
	<h3>
		<a href="?" style="color:#000000;text-decoration:none;<?php if ($_AT=='Interstitial') echo 'text-decoration:underline;'; ?>">
			Interstitial Advertising
		</a>
		&nbsp;/&nbsp;
		<a href="?at=2" style="color:#000000;text-decoration:none;<?php if ($_AT=='Banner') echo 'text-decoration:underline;"'; ?>">
			Leaderboard Banner Advertising
		</a>
	</h3>
	<p style="font-size:13px;">
		Please find our advertising rate table below. Each visitor you will purchase from <?php echo SITE_NAME; ?> will meet the following criteria:
		<br />
		<ul>
			<li>Unique within a 24 hour time frame</li>
			<li>They will have JavaScript enabled</li>
			<li>They will have Cookies enabled</li>
			<li>They will have Flash enabled</li>
			<li>Must view your website for at least 5 seconds</li>
			<li>Anonymous proxy filtered against a massive daily updated list</li>
		</ul>
		You may receive traffic that does not meet this criteria, but you will never be charged for it. For example visitors that only stay on your site for 2 seconds - the traffic is yours, but you won't be charged for it.
	</p>
	<form id="packages" action="?at=<?php echo $_GET['at']; ?>" method="POST">
	<input type="hidden" name="advert_type" value="<?php echo ($_GET['at']!='2'?'1':'2'); ?>" />
	<table cellspacing="1" class="tablesorter advertising_table">
		<thead>
			<tr>
				<th colspan="2">Package Description / Country</th>
				<th align="right">Price/1,000</th>
				<?php if ($_isAdvertiser) { ?>
				<th align="right">Purchase</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
				$last_price = '';
				foreach ($advert_packages as $p) {
					if ($p['code'] && $last_price && $last_price != $p['price'] && !preg_match('#[0-9]#',$p['code'])) {
						echo '<tr style="border:0;background:transparent;"><td colspan="';
						echo ($_isAdvertiser ? '4' : '3') . '">&nbsp;</td></tr>';
					}
					echo '<tr>';
					echo '<td width="75%"' . (!$p['code']||preg_match('#[0-9]#',$p['code'])?' colspan="2"':'');
					echo ">{$p['name']}" . ($p['special'] 
											? "<div class='special'>{$p['special']}</div>"
											: '') . '</td>';
					if ($p['code']&&!preg_match('#[0-9]#',$p['code'])) echo "<td>{$p['code']}</td>";
					echo "<td align='right' width='10%'>\$<span>{$p['price']}</span></td>";
					if ($_isAdvertiser) {
						echo "<td align='right' width='15%'>";
						echo "<input type='text' size='5' name='pkg[{$p['id']}]' value='' /> k</td>";
					}
					echo '</tr>';
					$last_price = $p['price'];
				}
			
			if ($_isAdvertiser) { ?>
			<tr style="border:0;background:transparent;"><td colspan="4">&nbsp;</td></tr>
			<tr>
				<td colspan="3">Enter Coupon Code (optional):</td>
				<td align='right'><input type='text' size='10' name='coupon_code' value='' /></td>
			</tr>
			<tr style="border:0;background:transparent;"><td colspan="4">&nbsp;</td></tr>
			<tr class="submit">
				<td colspan="4" align="center">
					<p>
						You have ordered <span id="ordered_num">0</span> visitors for a total
						of $<span id="ordered_price">0.00</span>
					</p>
					<input type='submit' name='package_submit' value='Click here to enter your site details' />
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	</form>
	<p style="font-size:13px;">
		<em>Your order may be subject to VAT if your country of residence is in the European Union (EU).</em><br />
		There is a minimum PayPal deposit of $5.00 and all sites/banners are allowed, except when they contain:
		<ul>
			<li>Frame breaking script</li>
			<li>Popup any windows on entry or exit of any kind</li>
			<li>Automatically attempt to download software or change any user settings</li>
			<li>Adult or Pornographic related</li>
			<li>Hate, Bigotry, and/or Intolerance</li>
			<li>Warez or Software Piracy related</li>
			<li>Music Piracy Related</li>
			<li>Hacking Related</li>
			<li>Anything related to illegal activity</li>
			<li>An <?php echo SITE_NAME; ?> link, you cannot advertise <?php echo SITE_NAME; ?>
			links on <?php echo SITE_NAME; ?></li>
		</ul>		
		Please <a href="contact.php">contact us</a> first if you are unsure whether your advertising would be suitable.
	</p>
</div>
<?php if ($_isAdvertiser) { ?>
<script type="text/javascript">
	var addCommas = function(nStr) {
		nStr += '';
		x = nStr.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	};
	$(document).ready(function() {
		$('table.advertising_table input[name^="pkg"]').keydown(function(k) {
			var w = k.which;
			return (w == 8 || w == 13 || w == 46 || w == 37 || w == 39) || (w >= 48 && w <= 57) || (w >= 96 && w <= 105);
		}).bind('change keyup', function() {
			var slf = $(this);
			var p = slf.parent().parent();
			if (slf.val() != '') {
				if (!p.hasClass('selected')) p.addClass('selected');
			} else if (p.hasClass('selected')) p.removeClass('selected');
			
			var ordered_num = 0, ordered_price = 0.00;
			$('table.advertising_table tr.selected').each(function(k,v) {
				var p = $('span', v).text();
				var q = $('input[type="text"]', v).val();
				ordered_num += (q * 1000);
				ordered_price += q * p;
			});
			$('span#ordered_num').text(addCommas(ordered_num));
			$('span#ordered_price').text(addCommas(ordered_price.toFixed(2)));
		});
		
		$('form#packages"').submit(function() {
			var ordered_num = 0, ordered_price = 0.00;
			$('table.advertising_table tr.selected').each(function(k,v) {
				var p = $('span', v).text();
				var q = $('input[type="text"]', v).val();
				ordered_num += (q * 1000);
				ordered_price += q * p;
			});
			console.log(parseInt(ordered_price));
			if (ordered_price < 5) {
				alert('You must purchase atleast $5.00 worth of traffic');
				return false;
			}
			return true;
		});
	});
</script>
<?php 
	}
}
site_footer();
?>