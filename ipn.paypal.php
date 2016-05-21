<?php
require_once 'global.php';

$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);

$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
$custom = $_POST['custom'];

if ($fp) {
	$db = System::getDB();
	fputs($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets($fp, 1024);
		if (strcmp($res, "VERIFIED") == 0 && $payment_status == 'Completed') {
			$table = $_GLOBAL['TABLES']['TRANSACTIONS'];
			$error = false;
			
			if ($db->getRows($table, "`txnid`='{$txn_id}'")) {
				$error = 'Attempt to resubmit transaction';
			} else if ($receiver_email != SITE_PAYPAL_EMAIL) {
				$error = 'Payment did not go to: ' . SITE_PAYPAL_EMAIL;
			} else if ($c = $db->getRows($_GLOBAL['TABLES']['CAMPAIGNS'], "`id`='{$custom}'", '', '1')) {
				$data = array('userid' => $c['uid'],
							  'email' => $payer_email,
							  'ptype' => 'paypal',
							  'txnid' => $txn_id,
							  'status' => 'Completed',
							  'subtotal' => $payment_amount,
							  'packages' => $c['packages']
				);
				$db->insert($table, $data);
				$db->update($_GLOBAL['TABLES']['CAMPAIGNS'], array('status'=>'2'), "`id`='{$custom}'");
			} else {
				$error = 'Payment Attempt: ' . var_export($_POST, true);
			}
		} else {
			$error = 'Payment Attempt: ' . $payment_status;
		}
		//else if (strcmp($res, "INVALID") == 0) { }
	}
	fclose($fp);
}