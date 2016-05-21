<?php
require_once 'global.php';
System::verifyAccess('index.php', false);

$H_TEMPLATE['TITLE'] = 'Update Your Account Details';
site_header();

if (isset($_POST['edit_sub'])) {
	$reqs = array('name','addr1','city_town','zipcode','country','telephone');
	foreach ($reqs as $r) {
		if (!$_POST[$r]) {
			$F_TEMPLATE['ERRORS'][] = 'Please complete all required fields!';
			break;
		}
	}
	
	if (!$F_TEMPLATE['ERRORS']) {
		$all = array('addr2', 'state_county', 'acc_type', 'username', 'cpass', 'npass', 'rpass',
					 'payment_processor', 'payment_email');
		$all = array_merge($reqs, $all);
		$val = array();
		foreach ($all as $v) {
			if ($v == 'addr2' || $v == 'addr1') {
				$val['address'] = serialize(array($_POST['addr1'], $_POST['addr2']));
			} else if ($_POST[$v]) {
				$val[$v] = $_POST[$v];
			}
		}
	}
	
	if (!$F_TEMPLATE['ERRORS']) {
		if ($val['npass']) {
			if (!$val['cpass']) {
				$F_TEMPLATE['ERRORS'][] = 'You must enter your current password in order to change it';
			} else if ($val['npass'] != $val['rpass']) {
				$F_TEMPLATE['ERRORS'][] = 'Your new passwords do not match';
			} else if (System::getUser()->get('pass') != md5($val['cpass'])) {
				$F_TEMPLATE['ERRORS'][] = 'Your current password is incorrect';
			} else {
				$val['pass'] = md5($val['npass']);
			}
		} else if ($val['payment_email']) {
			if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $val['payment_email'])) {
				$F_TEMPLATE['ERRORS'][] = 'Please enter a valid payment email';
			}
		}
	}
	
	if (!$F_TEMPLATE['ERRORS']) {
		unset($val['cpass'], $val['npass'], $val['rpass']);
		if (System::getUser()->update($val)) {
			$_MESSAGE = 'Your account has been updated';
		} else {
			$F_TEMPLATE['ERRORS'][] = 'There was a database error and we were unable to update our records. '
									  . 'Please try again.';
			System::log(System::getDB()->error());
		}
	}
}

$user = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], "`id`='{$_UID}'", '', '1');
$address = unserialize($user['address']);

if (isset($_POST)) {
	foreach ($_POST as $k=>$v) {
		if (isset($user[$k]) && $v) {
			$user[$k] = $v;
		}
	}
	if ($_POST['addr1']) $address[0] = $_POST['addr1'];
	if ($_POST['addr2']) $address[1] = $_POST['addr2'];
}
?>
<div class="user_content">
	<?php 
		if ($_MESSAGE != '') {
			echo '<div class="message_box">';
			echo $_MESSAGE;
			echo '</div>';
		} else if ($_GET['err'] != '') {
			echo '<div class="error_box">';
			echo $_GET['err'];
			echo '</div>';
		}
	?>
	<p>
		In order to maintain our records, we require the following information from you.
	</p>
	<p>
		Please complete the form with your real and accurate data, all records are checked manually.
		We reserve the right to verify your account details via telephone and/or postal address
		before any payments are made or advertising campaigns started.
	</p>
	<p>
		Any incomplete or fraudulent data submitted will result in the termination of your account.
	</p>
	<br />
	<form action="" method="POST" class="account_form">
		<div>
			<span>Real Name:</span>
			<span><input type="text" name="name" value="<?php echo $user['name']; ?>" /></span>
			<div class="req">*</div>
		</div>
		<div>
			<span>Address Line 1:</span>
			<span><input type="text" name="addr1" size="30" value="<?php echo $address[0]; ?>" /></span>
			<div class="req">*</div>
		</div>
		<div>
			<span>Address Line 2:</span>
			<span><input type="text" name="addr2" size="30" value="<?php echo $address[1]; ?>" /></span>
		</div>
		<div>
			<span>City / Town:</span>
			<span><input type="text" name="city_town" value="<?php echo $user['city_town']; ?>" /></span>
			<div class="req">*</div>
		</div>
		<div>
			<span>State / County:</span>
			<span><input type="text" name="state_county" value="<?php echo $user['state_county']; ?>" /></span>
		</div>
		<div>
			<span>Zipcode:</span>
			<span><input type="text" name="zipcode" size="10" value="<?php echo $user['zipcode']; ?>" /></span>
			<div class="req">*</div>
		</div>
		<div>
			<span>Country:</span>
			<span>
				<select name="country">
					<?php
						include_once ROOT_PATH . '/inc/geoip/geoip.php';
						$geoip = new GeoIP();
						foreach ($geoip->GEOIP_COUNTRY_NAMES as $country) {
							echo "<option value='{$country}'";
							if ($user['country'] == $country) echo ' selected';
							echo ">{$country}</option>";
						}
					?>
				</select>
			</span>
			<div class="req">*</div>
		</div>
		<div>
			<span>Telephone:</span>
			<span>+ <input type="text" name="telephone" value="<?php echo $user['telephone']; ?>" /></span>
			<div class="req">*</div>
		</div>
		<div>
			<span style="margin-top:1px;">Account Type:</span>
			<span>
				<input type="radio" name="acc_type" value="Personal"<?php if ($user['acc_type']=='Personal') echo ' checked' ?> />
				Personal
				&nbsp;&nbsp;
				<input type="radio" name="acc_type" value="Business"<?php if ($user['acc_type']=='Business') echo ' checked' ?> />
				Business
			</span>
		</div>
		<div>
			<span>Username:</span>
			<span>
				<?php
					if ($user['username']) echo $user['username'];
					else echo '<input type="text" name="username" value="" />';
				?>
			</span>
		</div>
		<p style="color:blue;">
			You may update your password here if you wish, it's good practice to change it regularly.
		<p>
		<div>
			<span>Current Password:</span>
			<span><input type="password" name="cpass" value="" /></span>
		</div>
		<div>
			<span>New Password:</span>
			<span><input type="password" name="npass" value="" /></span>
		</div>
		<div>
			<span>Retype Password:</span>
			<span><input type="password" name="rpass" value="" /></span>
		</div>
		<p style="color:blue;">
			Please review your withdrawal information. You can leave this blank if you wish.
		<p>
		<div>
			<span>Payment Processor:</span>
			<span>
				<select name="payment_processor">
					<option value="paypal"<?php if($user['payment_processor']=='paypal') echo ' selected'; ?>>PayPal</option>
				</select>
			</span>
		</div>
		<div>
			<span>Withdrawal Email:</span>
			<span><input type="text" name="payment_email" value="<?php echo $user['payment_email']; ?>" /></span>
		</div>
		<div>
			<span><input type="submit" name="edit_sub" value="Update" style="font-size:15px;padding:10px 30px;" /></span>
		</div>
		<div style="clear:both;"></div>
	</form>
</div>
<?php site_footer(); ?>