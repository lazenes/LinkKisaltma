<?php
require_once 'global.php';
System::verifyAccess();

$H_TEMPLATE['TITLE'] = 'Withdraw';

site_header();

$_ae = System::getUser()->get('available_earning');
$_pa = System::getUser()->get('payment_processor');
$_pe = System::getUser()->get('payment_email');
?>
<div class="user_content">
	<p><strong>Available Earnings:</strong> $<?php echo number_format($_ae, 2); ?></p>
	<p>
		Your earnings will be automatically paid to you on the next payment date 
		(Friday, <?php echo date('d\t\h F Y', strtotime("next Friday")); ?>),
		when you have reached a total of $5.00 or more.
	</p>
	<p>Please ensure your PayPal email is correct below, otherwise you cannot be paid.</p>
	<p>
		<span style="display:inline-block;width:125px;font-weight:bold;">Payment Processor:</span>
		<?php echo (!$_pa ? 'currently not set' : $_pa); ?>
		<br />
		<span style="display:inline-block;width:125px;font-weight:bold;">Payment Email:</span>
		<?php echo (!$_pe ? 'currently not set' : $_pe); ?>
	</p>
	<p>
		To change your withdrawal information please update your
		<a href="account.php" class="uc">Account Details</a>.
	</p>
</div>
<?php site_footer(); ?>