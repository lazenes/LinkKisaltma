<?php
/*******************************************************************************
 * Verify user email
 *
 * @created     02/05/2011
 * @modified    02/10/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

define('VERIFY_PHP_INCLUDED', true);
require_once 'global.php';
$CODE = !$_POST['acc_code'] ? $_GET['c'] : $_POST['acc_code']; $verified = false;

if ($CODE) {
	if ($uid = System::getDB()->getField($_GLOBAL['TABLES']['USERS'], 'id', "`code`='{$CODE}'")) {
		$verified = true;
		System::getDB()->update($_GLOBAL['TABLES']['USERS'], array('astatus' => '1'), "`code`='{$CODE}'");
	}
}

site_header();

if ($verified) { /* $verified:if {{{ */
?>
<div class="account_verify" style="text-align:center;float:none;">
	<h3 style="text-align:center;float:none;width:auto;padding-left:0;">Your account has been verified!</h3>
	<div class="field_opts" style="text-align:center;float:none;">
		<a href="index.php" style="float:none;">You may now login to your account normally.</a>
	</div>
</div>
<?php
} /* }}} $verified:if */

else { /* $verified:else {{{ */
?>
<div class="account_verify">
	<form action="" method="POST">
		<h3>Verify your account:</h3>
		<div class="fields">
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="acc_code" value="<?php echo $CODE; ?>" style="min-width:280px;" />
			</div>
			<div class="nspr inneri right"></div>
			<div class="nspr inneri left nblu"></div>
			<div class="nspr inneri bg nblu">
				<input type="submit" name="acc_sub" value="VERIFY" />
			</div>
			<div class="nspr inneri right nblu"></div>
		</div>
		<div class="field_opts">
			<a href="#">Didn't get your code?</a>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('div.account_verify input[name="acc_code"]').watermark('your verification code');
		$('div.account_verify div.nspr.inneri.nblu').hover(function(){$('input',this).fadeTo(400,1);},
													       function(){$('input',this).fadeTo(400,.7);});
	});
</script>
<?php
} /* }}} $verified:else */

site_footer();
?>