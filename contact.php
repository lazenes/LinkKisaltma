<?php
require_once 'global.php';
require_once 'inc/recaptchalib.php';

$H_TEMPLATE['TITLE'] = 'Contact Us';

$captcha_public_key = '6Lcrx8MSAAAAAGguOEnVxYr-L9kdtzffryYY4XgU';
$captcha_private_key = '6Lcrx8MSAAAAAPUjaSGvb-It8P8gEIRLaR3g_n4i';

$c_email = !$_POST['c_email'] ? $_GET['e'] : $_POST['c_email'];
$c_sub   = !$_POST['c_sub'] ? $_GET['s'] : $_POST['c_sub'];
$c_msg   = !$_POST['c_msg'] ? $_GET['m'] : $_POST['c_msg'];

if (isset($_POST['c_sub'])) {
	$resp = recaptcha_check_answer($captcha_private_key, $_SERVER["REMOTE_ADDR"],
								   $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
								
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $c_email)) {
		$H_TEMPLATE['ERRORS'][] = 'Please enter a valid email address';
	} else if (strlen($c_msg) < 50) {
		$H_TEMPLATE['ERRORS'][] = 'Your message must be atleast 50 characters long. Please try and be detailed.';
	} else if (!$resp->is_valid) {
		$H_TEMPLATE['ERRORS'][] = 'You failed the captcha.';
	} else {
		if (System::sendEmail($c_email, $c_email, SITE_SUPPORT_EMAIL, ucwords($c_sub), $c_msg)) {
			$H_TEMPLATE['MESSAGES'][] = 'Your email has been sent! Please allow upto 48-hours for a reply.';
		} else {
			$H_TEMPLATE['ERRORS'][] = 'An error occured while sending the email. Please try again.';
		}
	}
}

site_header();
?>
<div class="user_content" style="font-weight:normal;">
	<p>
		<?php echo SITE_NAME; ?> provides a URL shortening service that pays our members.
	</p>
	<p>
		We will not respond to any queries regarding downloading content protected by 
		an <?php echo SITE_NAME; ?> link or similar.
	</p>
	<p>
		At present we can only offer support in English, anything else will go unanswered.
	</p>
	<br />
	<form action="" method="POST" class="account_form">
		<div style="margin-bottom:0;">
			<span style="font-weight:bold;">Email:</span>
			<span><input type="text" name="c_email" value="<?php echo $c_email; ?>" /></span>
		</div>
		<div style="margin-bottom:0;">
			<span style="font-weight:bold;">Subject:</span>
			<span>
				<select name="c_sub">
					<option value="">Please choose a subject..</option>
					<option value="advertising"<?php echo ($c_sub=='advertising'?' selected':''); ?>>Advertising Question</option>    
					<option value="registration"<?php echo ($c_sub=='registration'?' selected':''); ?>>Registration Email has not Arrived</option>
					<option value="payout"<?php echo ($c_sub=='payout'?' selected':''); ?>>Payout Question</option>
					<option value="terms"<?php echo ($c_sub=='terms'?' selected':''); ?>>Reporting Terms &amp; Conditions Violation</option>
					<option value="no_adverts"<?php echo ($c_sub=='no_adverts'?' selected':''); ?>>No Advertisements are Shown</option>
					<option value="bug"<?php echo ($c_sub=='bug'?' selected':''); ?>>Bug Reporting</option>
					<option value="other"<?php echo ($c_sub=='other'?' selected':''); ?>>Other</option>
				</select>
			</span>
		</div>
		<div style="margin-bottom:0;">
			<span style="font-weight:bold;">Message:</span>
			<span>
				<textarea cols="48" rows="8" name="c_msg"><?php echo $c_msg; ?></textarea>
			</span>
		</div>
		<div style="margin-bottom:0;">
			<span style="font-weight:bold;">Captcha:</span>
			<span class="captcha">
				<?php echo recaptcha_get_html($captcha_public_key); ?>
			</span>
		</div>
		<div style="clear:both;"></div>
		<div>
			<span><input type="submit" name="c_sub" value="Send Email" class="nblu" style="padding:7px 14px;" /></span>
		</div>
		<div style="clear:both;"></div>
	</form>
</div>
<?php site_footer(); ?>