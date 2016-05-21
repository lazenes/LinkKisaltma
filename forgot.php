<?php
/*******************************************************************************
 * Forgot password page
 *
 * @created     02/28/2011
 * @modified    02/01/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

require_once 'global.php';
$CODE = $_GET['c']; $pass_changed = false;

if ($CODE && isset($_POST['change_pass'])) {
	$npass = md5(trim($_POST['npass']));
	$cpass = md5(trim($_POST['cpass']));
	if (strlen($npass) < 6 || strlen($cpass) < 6) {
		$H_TEMPLATE['ERRORS'][] = 'Your password must be atleast 6 characters';
	} else if ($npass != $cpass) {
		$H_TEMPLATE['ERRORS'][] = 'Your passwords do not match!';
	} else if ($uid = System::getDB()->getField($_GLOBAL['TABLES']['USERS'], 'id', "`code`='{$CODE}'")) {
		$pass_changed = true;
		System::getDB()->update($_GLOBAL['TABLES']['USERS'], array('pass' => $npass), "`code`='{$CODE}'");
	} else {
		$H_TEMPLATE['ERRORS'][] = 'Your request code is incorrect. Please follow the link sent to your email.';
	}
}

if (isset($_POST['forgot_sub'])) {
	$eml = $_POST['forgot_email'];
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $eml)) {
		$H_TEMPLATE['ERRORS'][] = 'Please enter a valid email address';
	} else if ($data = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], "`email`='{$eml}'", '', '1')) {
		$msg = "Hi {$data['name']}!<br /><br />"
			   . "Please use the following link in order to update your password:<br /><br />"
			   . "<a href=\"" . SITE_LOCATION . "forgot.php?c={$data['code']}\">"
			   . "Click here to change your password</a><br /><br />"
			   . "Regards,<br />" . SITE_NAME;
		System::sendEmail(SITE_REPLY_EMAIL, SITE_REPLY_EMAIL, $eml, 'Forgot Password', $msg);
		$H_TEMPLATE['MESSAGES'][] = 'An email has been sent with a link to change your password';
	} else {
		$H_TEMPLATE['ERRORS'][] = 'We could not find your email in our records';
	}
}

site_header();

if ($pass_changed) { /* $pass_changed:if {{{ */
?>
<div class="account_verify" style="text-align:center;float:none;">
	<h3 style="text-align:center;float:none;width:auto;padding-left:0;">Your account password has been changed!</h3>
	<div class="field_opts" style="text-align:center;float:none;">
		<a href="index.php" style="float:none;">You may now login to your account with your new password.</a>
	</div>
</div>
<?php
} /* }}} $pass_changed:if */

else if ($CODE) { /* $CODE:else {{{ */
?>
<div class="account_verify">
	<h3 style="float:none;width:auto;display:block;margin-bototm:10px;">Change your password:</h3>
	<form action="?c=<?php echo $CODE; ?>" method="POST">
		<div class="fields">
			<h3 style="font-size:14px;width:135px;margin-top:5px;">New Password:</h3>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="password" name="npass" value="<?php echo $_POST['npass']; ?>" style="min-width:280px;" />
			</div>
			<div class="nspr inneri right"></div>
			<div style="clear:both;"></div>
			<h3 style="font-size:14px;width:135px;margin-top:5px;">Confirm Password:</h3>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="password" name="cpass" value="<?php echo $_POST['cpass']; ?>" style="min-width:280px;" />
			</div>
			<div class="nspr inneri right"></div>
			<div style="clear:both;"></div>
			<br />
			<div class="nspr inneri left nblu"></div>
			<div class="nspr inneri bg nblu">
				<input type="submit" name="change_pass" value="Change Pass" />
			</div>
			<div class="nspr inneri right nblu"></div>
		</div>
	</form>
</div>
<div style="clear:both;"></div>
<br />
<script type="text/javascript">
	$(document).ready(function() {
		$('div.account_verify div.nspr.inneri.nblu').hover(function(){$('input',this).fadeTo(400,1);},
													       function(){$('input',this).fadeTo(400,.7);});
	});
</script>
<?php
} /* }}} $CODE:else */

else {
?>
<div class="account_verify">
	<form action="" method="POST">
		<h3>Forgot Password:</h3>
		<div class="fields">
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="forgot_email" value="<?php echo $_POST['forgot_email']; ?>" style="min-width:280px;" />
			</div>
			<div class="nspr inneri right"></div>
			<div class="nspr inneri left nblu"></div>
			<div class="nspr inneri bg nblu">
				<input type="submit" name="forgot_sub" value="Submit" />
			</div>
			<div class="nspr inneri right nblu"></div>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('div.account_verify input[name="forgot_email"]').watermark('your email used at signup');
		$('div.account_verify div.nspr.inneri.nblu').hover(function(){$('input',this).fadeTo(400,1);},
													       function(){$('input',this).fadeTo(400,.7);});
	});
</script>
<?php
}
site_footer();
?>