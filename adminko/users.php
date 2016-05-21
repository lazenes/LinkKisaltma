<?php
if (!defined('FROM_ADMIN') || FROM_ADMIN !== true) die();

$_EDIT = intval($_GET['e']);

if ($_EDIT > 0) {
	if (isset($_POST['edit_sub'])) {
		$reqs = array('name','addr1','city_town','zipcode','country','telephone');
		$all  = array('addr2', 'state_county', 'acc_type', 'username',  'payment_processor',
					  'payment_email', 'available_earning', 'atype', 'astatus', 'ga_code', 'notes');
		$all = array_merge($reqs, $all);
		$val = array();
		foreach ($all as $v) {
			if ($v == 'addr2' || $v == 'addr1') {
				$val['address'] = serialize(array($_POST['addr1'], $_POST['addr2']));
			} else if ($_POST[$v]) {
				$val[$v] = $_POST[$v];
			}
		}
		
		if (!System::getDB()->rowCount($_GLOBAL['TABLES']['USERS'], "`id`='{$_EDIT}'")) {
			$_ERROR = 'That user does not exist!';
		} else if ($val['payment_email']) {
			if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $val['payment_email'])) {
				$_ERROR = 'Please enter a valid payment email';
			}
		}
		
		if ($val['available_earning']) {
			if (!is_numeric($val['available_earning'])) {
				$_ERROR = 'Please enter a valid account balance';
			}
		}
		
		if (!$_ERROR) {
			if (System::getDB()->update($_GLOBAL['TABLES']['USERS'], $val, "`id`='{$_EDIT}'")) {
				$_MESSAGE = 'User has been updated!';
			} else {
				$_ERROR = 'There was a database error and we were unable to update our records. '
						  . 'Please try again.';
				System::log(System::getDB()->error());
			}
		}
	}
	
	$user = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], "`id`='{$_EDIT}'", '', '1');
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
	<h2>Editing User: #<?php echo $_EDIT; ?></h2>
	<?php 
		if ($_MESSAGE != '') {
			echo '<div style="font-weight:bold;background-color:#aaff56;padding:7px 15px;text-align:center;">';
			echo $_MESSAGE;
			echo '</div>';
		} else if ($_ERROR != '') {
			echo '<div style="font-weight:bold;background-color:#ff5656;padding:7px 15px;text-align:center;">';
			echo $_ERROR;
			echo '</div>';
		}
	?>
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
					echo '<input type="text" name="username" value="' . $user['username'] . '"';
					if ($user['username']) echo ' readonly="" ';
					echo '/>';
				?>
			</span>
		</div>
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
		---
		<div>
			<span>Account Balance:</span>
			<span>$ <input type="text" name="available_earning" value="<?php echo $user['available_earning']; ?>" /></span>
		</div>
		<div>
			<span>Account Type:</span>
			<span>
				<select name="atype">
					<option value="Shrinker"<?php echo ($user['atype']=='Shrinker'?' selected':''); ?>>Shrinker</option>
					<option value="Advertiser"<?php echo ($user['atype']!='Shrinker'?' selected':''); ?>>Advertiser</option>
				</select>
			</span>
		</div>
		<div>
			<span>Account Status:</span>
			<span>
				<select name="astatus">
					<option value="0"<?php echo ($user['astatus']==0?' selected':''); ?>>Unverified</option>
					<option value="1"<?php echo ($user['astatus']==1?' selected':''); ?>>Verified</option>
					<option value="2"<?php echo ($user['astatus']==2?' selected':''); ?>>Banned</option>
				</select>
			</span>
		</div>
		<div>
			<span>Analytics Code:</span>
			<span><input type="text" name="ga_code" value="<?php echo $user['ga_code']; ?>" /></span>
		</div>
		<div>
			<span>Notes:</span>
			<span>
				<textarea name="notes" rows="5" cols="35"><?php echo $user['notes']; ?></textarea>
			</span>
		</div>
		<div>
			<span><input type="submit" name="edit_sub" value="Update" style="font-size:15px;padding:10px 30px;" /></span>
		</div>
		<div style="clear:both;"></div>
	</form>
</div>
<?php
} else {
	$_SEARCHQ = $_POST['sq'] ? $_POST['sq'] : '';

	$items = array('id', 'name', 'email', 'username', 'zipcode', 'telephone');
	$sqfor = $_POST['sqin'] != '' ? array($_POST['sqin']) : $items;
	$where = '';
	foreach ($sqfor as $s) $where .= "`{$s}` LIKE '%{$_SEARCHQ}%' OR ";
	$where = trim(rtrim($where, 'OR '));

	$users = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], $where, '`name`');
?>
<div class="user_content">
	<h2 style="margin-bottom:10px;font-weight:normal;">
		Searching For:
		<strong><?php echo ($_POST['sq']?'"'.$_POST['sq'].'"':'Nothing Yet'); ?></strong>
		<?php echo ($_POST['sqin']?" in \"<strong>{$_POST['sqin']}</strong>\"":'');?>
		<div style="float: right; font-weight: normal;">
			<form action="?users" method="POST" style="margin:0px;">
				<input type="text" class="search_box" name="sq" value="<?php echo $_POST['sq']; ?>" size="15" />
				<select name="sqin" class="search_select">
					<option value="">All</option>
					<?php
						foreach ($items as $i) {
							echo "<option value='{$i}'";
							if ($_POST['sqin'] == $i) echo ' selected';
							echo '>' . ucwords($i) . '</option>';
						}
					?>
				</select>
				<input type="submit" class="search_button" value="Search" />
			</form>
		</div>
		<div style="clear:both;"></div>
	</h2>
	<table cellspacing="1" class="tablesorter user_list">
		<thead>
			<tr>
				<th width="5%">ID</th>
				<th>Name</th>
				<th>Email</th>
				<th>Country</th>
				<th>Status</th>
				<th>Type</th>
				<th>Balance</th>
				<th>Last Seen</th>
			</tr>
		</thead>
		<tbody>
			<?php
				if (!empty($users)) {
					foreach ($users as $u) {
						echo "<tr id='u{$u['id']}'>";
						echo "<td>{$u['id']}</td>";
						echo "<td>{$u['name']}</td>";
						echo "<td>{$u['email']}</td>";
						echo "<td>{$u['country']}</td>";
						echo "<td>" . ($u['astatus']==1?'Verified':($u['astatus']==2?'Banned':'Unverified')) . "</td>";
						echo "<td>{$u['atype']}</td>";
						echo "<td>\${$u['available_earning']}</td>";
						echo "<td>" . date('Y-m-d', strtotime($u['activity'])) . "</td>";
						echo '</tr>';
					}
				} else {
					echo '<tr>';
					echo '<td colspan="8"><em>No users found, or no search query entered.</em></td>';
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
	<?php if (!empty($users)) { ?>
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
	<script type="text/javascript">
		(function() {
			$('table.user_list').tablesorter({widthFixed: true,
												   headers: { 
													   0: {sorter: false}
												   }})
									 .tablesorterPager({container: $("div.links_pager")});
			$('table.user_list tr[id^="u"]').hover(function() {
				$(this).toggleClass('selected');
			}).click(function() {
				top.location.href = 'index.php?users&e=' + $(this).attr('id').substr(1);
				return false;
			});
		})();
	</script>
	<?php } ?>
</div>
<?php } ?>