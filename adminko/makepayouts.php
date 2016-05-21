<?php
if (!defined('FROM_ADMIN') || FROM_ADMIN !== true) die();

if ($_GET['makepay'] == '1') {
	$environment = 'live';	// or 'beta-sandbox' or 'live'

	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The API method name
	 * @param	string	The POST Message fields in &name=value pair format
	 * @return	array	Parsed HTTP Response body
	 */
	function PPHttpPost($methodName_, $nvpStr_) {
		global $environment;

		// Set up your API credentials, PayPal end point, and API version.
		$API_UserName = urlencode('brashone_api1.hotmail.com');
		$API_Password = urlencode('YVMLCALYMDR2H8ZB');
		$API_Signature = urlencode('AAEZu7vCECq9dDiTni0MNPflP3ZPAB4BZKwpBv5DA2usEAs7s01ddhJR');
		$API_Endpoint = "https://api-3t.paypal.com/nvp";
		if("sandbox" === $environment || "beta-sandbox" === $environment) {
			$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
		}
		$version = urlencode('51.0');

		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		// Get response from the server.
		$httpResponse = curl_exec($ch);

		if(!$httpResponse) {
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}

		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);

		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}

		return $httpParsedResponseAr;
	}

	// Set request-specific fields.
	$emailSubject = urlencode(SITE_NAME . ' Payout');
	$receiverType = urlencode('EmailAddress');
	$currency = urlencode('USD');

	// Add request-specific fields to the request string.
	$nvpStr="&EMAILSUBJECT=$emailSubject&RECEIVERTYPE=$receiverType&CURRENCYCODE=$currency";

	$pu = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], 
								   '`available_earning`>=5',
								   '`available_earning` DESC');
							   
	$receiversArray = array();

	for($i = 0; $i < count($pu); $i++) {
		$receiversArray[] = array(
			'email' => $pu[$i]['payment_email'],
			'amount' => $pu[$i]['available_earning'],
			'uniqueID' => $pu[$i]['id']
		);
	}

	foreach($receiversArray as $i => $receiverData) {
		$receiverEmail = urlencode($receiverData['email']);
		$amount = urlencode($receiverData['amount']);
		$uniqueID = urlencode($receiverData['uniqueID']);
		$nvpStr .= "&L_EMAIL$i=$receiverEmail&L_AMT$i=$amount&L_UNIQUEID$i=$uniqueID&L_NOTE$i=";
	}

	// Execute the API operation; see the PPHttpPost function above.
	$httpParsedResponseAr = PPHttpPost('MassPay', $nvpStr);

	if( "SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) 
		|| "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
		$db = System::getDB();
		$date = date('Y-m-d h:m:s');
		foreach($receiversArray as $r) {
			$db->insert($_GLOBAL['TABLES']['PAYOUTS_MADE'],
						array('date' => $date, 'payee' => $r['email'], 'amount' => $r['amount'])
			);
			$db->update($_GLOBAL['TABLES']['USERS'], array('available_earning' => '0'),
						"`id`='{$r['id']}'");
		}
		echo '<div class="message_box">';
		echo 'Payouts have successfully been sent!';
		echo '</div>';
	} else  {
		echo '<div class="error_box">';
		exit('MassPay failed: ' . urldecode($httpParsedResponseAr['L_LONGMESSAGE0']));
		//print_r($httpParsedResponseAr, true) . var_export($nvpStr, true));
		echo '</div>';
	}
}

$lp = System::getDB()->getRows($_GLOBAL['TABLES']['PAYOUTS_MADE'], '', '`date` DESC', '1');
$pu = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], 
							   '`available_earning`>=5',
							   '`available_earning` DESC');
$total = 0;							   
$pubs = array();
foreach($pu as $p) {
	$pubs[] = $p;
	$total += $p['available_earning'];
}

$today = date('Y-m-d h:m:s');
?>
<div class="user_content" style="text-align:center;">
	<h2 style="font-weight:normal;text-align:center;margin:0;color:#cc8f0e;">
	<strong>Total Payout To Make:</strong> $<?php echo number_format($total, 5); ?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<strong>Last Payout:</strong> <?php echo (!$lp['date']?'None':$lp['date']); ?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<strong>Today's Date:</strong> <?php echo $today; ?>
	</h2>
	<?php if ($_GET['do'] != '1') { ?>
	<h2 style="text-align:center;margin:15px 0 0;">
		<a href="index.php?mp&do=1" style="color:#eda310;padding:5px;background-color:#FFFFFF;">
			MAKE PAYOUT FOR THIS WEEK
		</a>
	</h2>
	<?php } ?>
</div>
<?php
if ($_GET['do'] == '1') {
?>
<div class="user_content">
	<?php if ($_GET['makepay'] == '1') { ?>
	<h1>
		PAID OUT!
	</h1>
	<?php } else { ?>
	<h1 style="font-weight:normal;color:red;text-align:center;">
		Are you sure you want to make a total payout of:
		<strong>$<?php echo number_format($total, 5); ?></strong>?
		<br />
		<br />
		<a href="index.php?mp&do=1&makepay=1" style="color:green;padding:5px;background-color:#FFFFFF;">
			I AM SURE I WANT TO MAKE A PAYOUT OF $<?php echo number_format($total, 5); ?>
		</a>
	</h1>
	<?php } ?>
</div>
<?php
} else {
?>
<div class="user_content" >
	<h2>Publishers To Pay</h2>
	<table cellspacing="1" class="tablesorter link_list" style="width:90%;margin:0 auto;" align="center">
		<thead>
			<tr>
				<th width="7%">ID</th>
				<th>Name</th>
				<th>Email</th>
				<th>Payment Email</th>
				<th>Amount</th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($pubs as $p) {
					echo '<tr>';
					echo "<td>{$p['id']}</td>";
					echo "<td>{$p['name']}</td>";
					echo "<td>{$p['email']}</td>";
					echo "<td>{$p['payment_email']}</td>";
					echo "<td>\${$p['available_earning']}</td>";
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
	<div class="links_pager">
		<form>
			<div style="padding-top:3px;float:left;display:inline-block;">
				<a href="#" class="first"><img src="../images/tsp_first.png" border="0" /></a>
				<a href="#" class="prev"><img src="../images/tsp_prev.png" border="0" /></a>
			</div>
			<div style="padding:3px 3px 0 3px;float:left;display:inline-block;">
				<strong>Page:</strong> <span class="pagedisplay"></span>
			</div>
			<div style="padding-top:3px;float:left;display:inline-block;">
				<a href="#" class="next"><img src="../images/tsp_next.png" border="0" /></a>
				<a href="#" class="last"><img src="../images/tsp_last.png" border="0" /></a>
			</div>
			<input type="hidden" class="pagesize" value="50" />
		</form>
		<div style="clear:both;"></div>
	</div>
</div>
<script type="text/javascript">
	(function() {
		$('table.link_list').tablesorter({widthFixed: true})
							.tablesorterPager({container: $("div.links_pager")});
	})();
</script>
<?php } ?>