<?php
require_once 'global.php';
System::verifyAccess();

$isPagePStats = isset($_GET['pstats']);
$isPageAStats = isset($_GET['astats']);
$isPageBanners = isset($_GET['banners']);
$isPageCode = !$isPageBanners && !$isPagePStats && !$isPageAStats ? true : isset($_GET['refcode']);

if ($isPagePStats) {
	$H_TEMPLATE['TITLE'] = 'Publisher Referral Statistics';
} else if ($isPageAStats) {
	$H_TEMPLATE['TITLE'] = 'Advertiser Referral Statistics';
} else if ($isPageBanners) {
	$H_TEMPLATE['TITLE'] = 'Referral Banners';
} else {
	$H_TEMPLATE['TITLE'] = 'Referral Program - 20% Earnings FOR LIFE';
}

site_header(false,
			array(
				'Banners' => array('?banners', $isPageBanners),
				'Advertiser Stats' => array('?astats', $isPageAStats),
				'Publisher Stats' => array('?pstats', $isPagePStats),
				'Referral Code' => array('?refcode', $isPageCode)
			)
);

if ($isPageCode) {
?>
<div class="user_content">
	<p>
		The <?php echo SITE_NAME; ?> referral program is a great way to spread the word of this great service and to
		earn even more money with your short links! Refer friends and receive
	</p>
	<p>
		<strong>20% of their earnings for life!</strong>
	</p>
	<p>
		Just send them the link below, post it on your Twitter, post it on your website, or any
		other way without spamming (as this is against our Terms & Conditions).
	</p>
	<div style="background-color:#FFFFFF;border:1px solid #CCCCCC;padding:10px 15px;margin:20px 0;">
		<a href="<?php echo SITE_LOCATION; ?>?r=<?php echo $_UID; ?>" class="uc"><?php echo SITE_LOCATION; ?>?r=<?php echo $_UID; ?></a>
	</div>
	<p>
		<strong>Do you have webmaster traffic?</strong>
	</p>
	<p>
		Refer them to <?php echo SITE_NAME; ?> and earn 5% commission on every advertising order they make.
		Just use the same link above - a great way to boost your earnings, selling our quality
		traffic here is easy!
	</p>
</div>
<?php
} else if ($isPagePStats) {
	$publishers = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], "`refid`='{$_UID}' AND `atype`='Shrinker'");
	$earnings = 0;
	foreach ($publishers as $p) {
		$earnings += $p['ref_earned'];
	}
?>
<div class="user_content">
	<p><strong>Referrals:</strong> <?php echo count($publishers); ?></p>
	<p><strong>Earnings:</strong> $<?php echo number_format($earnings, 5, '.', ','); ?></p>
	<br />
	<table cellspacing="1" class="tablesorter referral_table">
		<thead>
			<tr>
				<th>Join Date</th>
				<th>Referral ID</th>
				<th>Earned</th>
				<th>Commission</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$total = 0;
				foreach ($publishers as $p) {
					$joined = explode(' ', $p['joined']);
					$earned = number_format($p['earned'], 5, '.', ',');
					$ref_earned = number_format($p['ref_earned'], 5, '.', ',');
					echo '<tr>';
					echo "<td>{$joined[0]}</td>";
					echo "<td>{$p['id']}</td>";
					echo "<td>\${$earned}</td>";
					echo "<td>\${$ref_earned}</td>";
					echo '</tr>';
					$total += $ref_earned;
				}
			?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="3" align="right">Total:</th>
				<th>$<?php echo number_format($total, 5, '.', ','); ?></th>
			</tr>
		</tfoot>
	</table>
	<?php if (count($publishers)) { ?>
	<div class="links_pager">
		<form>
			<div style="padding-top:3px;float:left;display:inline-block;">
				<a href="#" class="first"><img src="images/tsp_first.png" border="0" /></a>
				<a href="#" class="prev"><img src="images/tsp_prev.png" border="0" /></a>
			</div>
			<div style="padding:3px 3px 0 3px;float:left;display:inline-block;">
				<strong>Page:</strong> <span class="pagedisplay"></span>
			</div>
			<div style="padding-top:3px;float:left;display:inline-block;">
				<a href="#" class="next"><img src="images/tsp_next.png" border="0" /></a>
				<a href="#" class="last"><img src="images/tsp_last.png" border="0" /></a>
			</div>
			<input type="hidden" class="pagesize" value="50" />
		</form>
		<div style="clear:both;"></div>
	</div>
	<script type="text/javascript">
		(function() {
			$("table.referral_table").tablesorter({widthFixed: true,
												   headers: { 
													   0: {sorter: false}, 1: {sorter: false},
													   2: {sorter: false}, 3: {sorter: "integer"}
												   }})
									 .tablesorterPager({container: $("div.links_pager")});
		})();
	</script>
	<?php } ?>
</div>
<?php
} else if ($isPageAStats) {
	$advertisers = System::getDB()->getRows($_GLOBAL['TABLES']['USERS'], "`refid`='{$_UID}' AND `atype`='Advertiser'");
	$earnings = 0;
	foreach ($advertisers as $p) {
		$earnings += $p['ref_earned'];
	}
?>
<div class="user_content">
	<p><strong>Referrals:</strong> <?php echo count($advertisers); ?></p>
	<p>Campaign commissions are credited when they have a 'Finished' status.</p>
	<br />
	<table cellspacing="1" class="tablesorter referral_table">
		<thead>
			<tr>
				<th>Join Date</th>
				<th>Referral ID</th>
				<th>Earned</th>
				<th>Commission</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$total = 0;
				foreach ($advertisers as $p) {
					$joined = explode(' ', $p['joined']);
					$earned = number_format($p['earned'], 5, '.', ',');
					$ref_earned = number_format($p['ref_earned'], 5, '.', ',');
					echo '<tr>';
					echo "<td>{$joined[0]}</td>";
					echo "<td>{$p['id']}</td>";
					echo "<td>\${$earned}</td>";
					echo "<td>\${$ref_earned}</td>";
					echo '</tr>';
					$total += $ref_earned;
				}
			?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="3" align="right">Total:</th>
				<th>$<?php echo number_format($total, 5, '.', ','); ?></th>
			</tr>
		</tfoot>
	</table>
	<?php if (count($advertisers)) { ?>
	<div class="links_pager">
		<form>
			<div style="padding-top:3px;float:left;display:inline-block;">
				<a href="#" class="first"><img src="images/tsp_first.png" border="0" /></a>
				<a href="#" class="prev"><img src="images/tsp_prev.png" border="0" /></a>
			</div>
			<div style="padding:3px 3px 0 3px;float:left;display:inline-block;">
				<strong>Page:</strong> <span class="pagedisplay"></span>
			</div>
			<div style="padding-top:3px;float:left;display:inline-block;">
				<a href="#" class="next"><img src="images/tsp_next.png" border="0" /></a>
				<a href="#" class="last"><img src="images/tsp_last.png" border="0" /></a>
			</div>
			<input type="hidden" class="pagesize" value="50" />
		</form>
		<div style="clear:both;"></div>
	</div>
	<script type="text/javascript">
		(function() {
			$("table.referral_table").tablesorter({widthFixed: true,
												   headers: { 
													   0: {sorter: false}, 1: {sorter: false},
													   2: {sorter: false}, 3: {sorter: "integer"}
												   }})
									 .tablesorterPager({container: $("div.links_pager")});
		})();
	</script>
	<?php } ?>
</div>
<?php } else if ($isPageBanners) { ?>
<div class="user_content">
	
</div>
<?php } site_footer(); ?>