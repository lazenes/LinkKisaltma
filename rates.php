<?php
require_once 'global.php';

$H_TEMPLATE['TITLE'] = 'Publisher Rates';
site_header();

$payout_rates = System::getDB()->getRows($_GLOBAL['TABLES']['PAYOUTS'], "`u_interstitial`>0 OR `u_top_banner`>0", 
										 '`zorder`, `u_interstitial` DESC, `u_top_banner` DESC, `r_interstitial` DESC, `r_top_banner` DESC');
?>
<div class="user_content">
	<p>
		The current average CPM (amount payable for 1000 visitors in a 24-hour period) for
		<strong><?php echo date('d\t\h F Y'); ?>.</strong>
	</p>
	<p>
		<em>Unique = amount payable for 1000 visitors in a 24-hour period, clicking multiple links.</em>
	</p>
	<p>
		<em>Raw = amount payable for 1000 link views in a 24-hour period.</em>
	</p>
	<br />
	<table cellspacing="1" class="tablesorter rates_table" style="width:90%;margin:0 auto;" align="center">
		<thead>
			<tr>
				<th width="60%">Country</th>
				<th align="center" colspan="2">Interstitials CPM</th>
				<th align="center" colspan="2">Top Banner CPM</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td width="60%"></td>
				<td align="center"><strong>Unique</strong></td>
				<td align="center" style="border-right:1px solid #CCC;"><strong>Raw</strong></td>
				<td align="center"><strong>Unique</strong></td>
				<td align="center"><strong>Raw</strong></td>
			</tr>
			<?php
				$last_price = '';
				for ($i = 0; $i < count($payout_rates); $i++) {
					if ($i == 4 || $payout_rates[$i]['id'] == 1) {
						echo '<tr style="border:0;background:transparent;"><td colspan="5">&nbsp;</td></tr>';
					}
					echo '<tr>';
					echo "<td>{$payout_rates[$i]['name']}</td>";
					echo '<td align="center">$' . number_format($payout_rates[$i]['u_interstitial'], 2) . '</td>';
					echo '<td align="center" style="border-right:1px solid #CCC;">$' . number_format($payout_rates[$i]['r_interstitial'], 2) . '</td>';
					echo '<td align="center">$' . number_format($payout_rates[$i]['u_top_banner'], 2) . '</td>';
					echo '<td align="center">$' . number_format($payout_rates[$i]['r_top_banner'], 2) . '</td>';
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
</div>
<?php site_footer(); ?>