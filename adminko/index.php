<?php
	require_once( '../global.php' );
	require_once( ROOT_PATH . '/inc/admin.class.php' );
	$H_TEMPLATE['PAGE_TITLE'] = 'ADMIN AREA';
	$_AID = ( $_COOKIE['ADMIN_ID'] ? $_COOKIE['ADMIN_ID'] : 0 );

	if (isset( $_REQUEST['logout'] )) {
		setcookie( 'ADMIN_ID', $_AID, time(  ) - 5400 );
		System::redirect( 'index.php?' );
	}

	if (isset( $_POST['adm_sub'] )) {
		$user = trim( $_POST['adm_user'] );
		$pass = md5( trim( $_POST['adm_pass'] ) );

		if (!( ( $user && trim( $_POST['adm_pass'] ) ))) {
			$F_TEMPLATE['ERRORS'][] = 'Please complete both fields in order to login';
		} else {
			if (!( System::getdb(  )->rowCount( $_GLOBAL['TABLES']['ADMINS'], '`user`=\'' . $user . '\' AND `pass`=\'' . $pass . '\'' ))) {
				$F_TEMPLATE['ERRORS'][] = 'The login information is incorrect.';
			} else {
				$uid = System::getdb(  )->getField( $_GLOBAL['TABLES']['ADMINS'], 'id', '`user`=\'' . $user . '\' AND `pass`=\'' . $pass . '\'' );
				System::getdb(  )->update( $_GLOBAL['TABLES']['ADMINS'], array( 'activity' => 'CURRENT_TIMESTAMP()' ), '`user`=\'' . $user . '\' AND `pass`=\'' . $pass . '\'', false );
				setcookie( 'ADMIN_ID', $uid, time(  ) + 3600 );
				System::redirect( 'index.php' . ( isset( $_REQUEST['gs'] ) ? '?gs' : '' ) );
			}
		}
	}

	$_ADMIN = new Admin( $_GLOBAL['TABLES'], $_AID, System::getdb(  ) );

	if ($_ADMIN->loggedIn(  )) {
		if (isset( $_GET['pr'] )) {
			$ifPagePR = true;
			$H_TEMPLATE['TITLE'] = 'Payout Rates';
		} else {
			if (isset( $_GET['mp'] )) {
				$ifPageMP = true;
				$H_TEMPLATE['TITLE'] = 'Make Publisher Payouts';
			} else {
				if (isset( $_GET['an'] )) {
					$ifPageAN = true;
					$H_TEMPLATE['TITLE'] = 'Manage Announcements';
				} else {
					if (isset( $_GET['ap'] )) {
						$ifPageAP = true;
						$H_TEMPLATE['TITLE'] = 'Advertising Prices';
					} else {
						if (isset( $_GET['gs'] )) {
							$ifPageGS = true;
							$H_TEMPLATE['TITLE'] = 'General Settings';
						} else {
							if (isset( $_GET['users'] )) {
								$ifPageUsers = true;
								$H_TEMPLATE['TITLE'] = 'Manage Users';
							} else {
								if (isset( $_GET['links'] )) {
									$ifPageLinks = true;
									$H_TEMPLATE['TITLE'] = 'Manage Links';
								} else {
									if (isset( $_GET['cs'] )) {
										$ifPageCS = true;
										$H_TEMPLATE['TITLE'] = 'Manage Campaigns';
									} else {
										$ifPageSS = true;
										$H_TEMPLATE['TITLE'] = 'Site Statistics';
									}
								}
							}
						}
					}
				}
			}
		}

		site_header( false, array( 'Home' => array( 'index.php?ss', $ifPageSS ), 'Manage Users' => array( 'index.php?users', $ifPageUsers ), 'Manage Links' => array( 'index.php?links', $ifPageLinks ), 'Manage Campaigns' => array( 'index.php?cs', $ifPageCS ), 'Announcements' => array( 'index.php?an', $ifPageAN ), 'General Settings' => array( 'index.php?gs', $ifPageGS ), 'Logout' => array( 'index.php?logout', false, true ), 'Make Payouts' => array( 'index.php?mp', $ifPagePR, true ), 'Payout Rates' => array( 'index.php?pr', $ifPagePR, true ), 'Advertising Prices' => array( 'index.php?ap', $ifPageAP, true ) ), true );

		if ($ifPageAN) {
			define( 'FROM_ADMIN', true );
			include( 'announcements.php' );
		} else {
			if ($ifPageMP) {
				define( 'FROM_ADMIN', true );
				include( 'makepayouts.php' );
			} else {
				if ($ifPageUsers) {
					define( 'FROM_ADMIN', true );
					include( 'users.php' );
				} else {
					if ($ifPageLinks) {
						define( 'FROM_ADMIN', true );
						include( 'links.php' );
					} else {
						if ($ifPageCS) {
							define( 'FROM_ADMIN', true );
							include( 'campaigns.php' );
						} else {
							if ($ifPageGS) {
								$config = System::getdb(  )->getRows( $_GLOBAL['TABLES']['CONFIG'] );

								if (isset( $_POST['settings_sub'] )) {
									for  ($i = 0; $i < count( $config ); ++$i) {
										if ($_POST[$config[$i]['key']]) {
											$config[$i]['value'] = $_POST[$config[$i]['key']];
											System::getdb(  )->update( $_GLOBAL['TABLES']['CONFIG'], array( 'value' => $config[$i]['value'] ), '`key`=\'' . $config[$i]['key'] . '\'' );
											continue;
										}
									}

									$_MESSAGE = 'Settings Updated!';

									if (!( ( isset( $_POST['nadmin_username'] ) && isset( $_POST['nadmin_password'] ) ))) {
										$cadmin_username = trim( $_POST['cadmin_username'] );
										$cadmin_password = md5( trim( $_POST['cadmin_password'] ) );
										$nadmin_username = trim( $_POST['nadmin_username'] );
										$nadmin_password = md5( trim( $_POST['nadmin_password'] ) );
										$radmin_password = md5( trim( $_POST['radmin_password'] ) );

										if (strlen( $_POST['nadmin_password'] ) < 3) {
											$_ERROR = 'Your new new password must be atleast 3 characters';
										} else {
											if ($nadmin_password != $radmin_password) {
												$_ERROR = 'Your new passwords do not match';
											} else {
												if (!( System::getdb(  )->rowCount( $_GLOBAL['TABLES']['ADMINS'], '`user`=\'' . $cadmin_username . '\' AND `pass`=\'' . $cadmin_password . '\'' ))) {
													$_ERROR = 'Your current information does not match!';
												} else {
													$nadmin_username = ( $nadmin_username ? $cadmin_username : $nadmin_username );
													$nadmin_password = ( $nadmin_password ? $cadmin_password : $nadmin_password );
													System::getdb(  )->update( $_GLOBAL['TABLES']['ADMINS'], array( 'user' => $nadmin_username, 'pass' => $nadmin_password ), '`user`=\'' . $cadmin_username . '\'' );
													$_MESSAGE .= '<br />Admin information has been updated! Your new username: ' . $nadmin_username;
												}
											}
										}
									}
								}

								echo '<div class="user_content">
	<h2>Update Settings</h2>
	';

								if ($_ERROR != '') {
									echo '<div class="error_box">';
									echo $_ERROR;
									echo '</div>';
								} else {
									if ($_MESSAGE != '') {
										echo '<div class="message_box">';
										echo $_MESSAGE;
										echo '</div>';
									}
								}

								echo '	<form action="" method="POST" class="settings_form">
		';

								foreach ($config as $c) {
									$key = ucwords( str_replace( '_', ' ', strtolower( $c['key'] ) ) );
									$value = stripcslashes( $c['value'] );
									echo '<div><span>';
									echo $key;
									echo ':</span><span><input type=\'text\' name=\'';
									echo $c['key'];
									echo '\' value=\'';
									echo $value;
									echo '\' /></span></div>';
								}

								echo '		<br />
		<div style="clear:both;background:none;"></div>
		<h2>Update Admin</h2>
		<div>
			<span>Current Username:</span>
			<span><input type=\'text\' name=\'cadmin_username\' value=\'';
								echo $_POST['cadmin_username'];
								echo '\' /></span>
		</div>
		<div>
			<span>Current Password:</span>
			<span><input type=\'password\' name=\'cadmin_password\' value=\'';
								echo $_POST['cadmin_password'];
								echo '\' /></span>
		</div>
		<div>
			<span>New Username:</span>
			<span><input type=\'text\' name=\'nadmin_username\' value=\'';
								echo $_POST['nadmin_username'];
								echo '\' /></span>
		</div>
		<div>
			<span>New Password:</span>
			<span><input type=\'password\' name=\'nadmin_password\' value=\'';
								echo $_POST['nadmin_password'];
								echo '\' /></span>
		</div>
		<div>
			<span>Retype Password:</span>
			<span><input type=\'password\' name=\'radmin_password\' value=\'';
								echo $_POST['radmin_password'];
								echo '\' /></span>
		</div>
		<input type="submit" name="settings_sub" value="Update" class="nblu" style="min-width:150px;width:200px;height:40px;" />
	</form>
</div>
';
							} else {
								if ($ifPageAP) {
									if (isset( $_POST['adsup_sub'] )) {
										foreach ($_POST as $v => $k) {
											if (!( isset( $_POST[$k] ))) {
												$tk = explode( '_', $k );

												if ($tk[0] == 'special') {
													System::getdb(  )->update( $_GLOBAL['TABLES']['PACKAGES'], array( 'special' => $_POST[$k] ), '`id`=\'' . $tk[1] . '\'' );
													continue;
												}

												if ($tk[0] == 'price') {
													if (0 <= intval( $_POST[$k] )) {
														System::getdb(  )->update( $_GLOBAL['TABLES']['PACKAGES'], array( 'price' => $_POST[$k] ), '`id`=\'' . $tk[1] . '\'' );
														continue;
													}

													continue;
												}

												continue;
											}
										}

										$_MESSAGE = 'Advertising Prices Updated!';
									}

									$_AT = ( $_GET['at'] != 2 ? 'Interstitial' : 'Banner' );
									$advert_packages = System::getdb(  )->getRows( $_GLOBAL['TABLES']['PACKAGES'], '`advert_type`=\'' . $_AT . '\'', '`zorder`, `price` DESC' );
									echo '<div class="user_content">
	<h2>Update Prices</h2>
	<h3>
		<a href="?ap" style="color:#000000;text-decoration:none;';

									if ($_AT == 'Interstitial') {
										echo 'text-decoration:underline;';
									}

									echo '">
			Interstitial Advertising
		</a>
		&nbsp;/&nbsp;
		<a href="?ap&at=2" style="color:#000000;text-decoration:none;';

									if ($_AT == 'Banner') {
										echo 'text-decoration:underline;"';
									}

									echo '">
			Leaderboard Banner Advertising
		</a>
	</h3>
	';

									if ($_MESSAGE != '') {
										echo '<div style="font-weight:bold;background-color:#aaff56;padding:7px 15px;text-align:center;">';
										echo $_MESSAGE;
										echo '</div>';
									}

									echo '	<form action="" method="POST" class="settings_form">
	';

									foreach ($advert_packages as $p) {
										echo '<div><span><small>[';
										echo $p['code'];
										echo ']</small> ';
										echo $p['name'];
										echo ':</span><span style=\'font-weight:normal;\'>Special: <input type=\'text\' name=\'special_';
										echo $p['id'];
										echo '\' value=\'';
										echo $p['special'];
										echo '\'style=\'min-width:0;width:200px;\' /></span><span style=\'float:right;font-weight:normal;\'>Price: $<input type=\'text\'name=\'price_';
										echo $p['id'];
										echo '\' value=\'';
										echo $p['price'];
										echo '\' style=\'min-width:0;width:150px;\' /></span></div>';
									}

									echo '	<input type="submit" name="adsup_sub" value="Update" class="nblu" style="min-width:150px;width:200px;height:40px;" />
	</form>
</div>
';
								} else {
									if ($ifPagePR) {
										if (isset( $_POST['payr_sub'] )) {
											foreach ($_POST as $v => $k) {
												if (!( intval( $_POST[$k] ) < 0)) {
													$tk = explode( '_', $k );

													if ($tk[0] == 'uint') {
														System::getdb(  )->update( $_GLOBAL['TABLES']['PAYOUTS'], array( 'u_interstitial' => $_POST[$k] ), '`id`=\'' . $tk[1] . '\'' );
														continue;
													}

													if ($tk[0] == 'utop') {
														System::getdb(  )->update( $_GLOBAL['TABLES']['PAYOUTS'], array( 'u_top_banner' => $_POST[$k] ), '`id`=\'' . $tk[1] . '\'' );
														continue;
													}

													if ($tk[0] == 'rint') {
														System::getdb(  )->update( $_GLOBAL['TABLES']['PAYOUTS'], array( 'r_interstitial' => $_POST[$k] ), '`id`=\'' . $tk[1] . '\'' );
														continue;
													}

													if ($tk[0] == 'rtop') {
														System::getdb(  )->update( $_GLOBAL['TABLES']['PAYOUTS'], array( 'r_top_banner' => $_POST[$k] ), '`id`=\'' . $tk[1] . '\'' );
														continue;
													}

													continue;
												}
											}

											$_MESSAGE = 'Payout Rates Updated!';
										}

										$payout_rates = System::getdb(  )->getRows( $_GLOBAL['TABLES']['PAYOUTS'], '', '`zorder`, `u_interstitial` DESC, `u_top_banner` DESC, `r_interstitial` DESC, `r_top_banner` DESC' );
										echo '<div class="user_content">
	<h2>Update Payout Rates</h2>
	';

										if ($_MESSAGE != '') {
											echo '<div style="font-weight:bold;background-color:#aaff56;padding:7px 15px;text-align:center;">';
											echo $_MESSAGE;
											echo '</div>';
										}

										echo '	<form action="" method="POST" class="settings_form">
	';

										foreach ($payout_rates as $p) {
											echo '<div><span><small>[';
											echo $p['code'];
											echo ']</small> ';
											echo $p['name'];
											echo ':</span><span style=\'font-weight:normal;margin-right:25px;\'>Unique Interstitial: $<input type=\'text\'name=\'uint_';
											echo $p['id'];
											echo '\' value=\'';
											echo $p['u_interstitial'];
											echo '\' style=\'min-width:0;width:65px;\' /></span><span style=\'font-weight:normal;\'>Raw Interstitial: $<input type=\'text\'name=\'rint_';
											echo $p['id'];
											echo '\' value=\'';
											echo $p['r_interstitial'];
											echo '\' style=\'min-width:0;width:65px;\' /></span><span style=\'float:right;font-weight:normal;\'>Raw Top Banner: $<input type=\'text\'name=\'rtop_';
											echo $p['id'];
											echo '\' value=\'';
											echo $p['r_top_banner'];
											echo '\' style=\'min-width:0;width:65px;\' /></span><span style=\'float:right;font-weight:normal;margin-right:25px;\'>Unique Top Banner: $<input type=\'text\'name=\'utop_';
											echo $p['id'];
											echo '\' value=\'';
											echo $p['u_top_banner'];
											echo '\' style=\'min-width:0;width:65px;\' /></span></div>';
										}

										echo '	<input type="submit" name="payr_sub" value="Update" class="nblu" style="min-width:150px;width:200px;height:40px;" />
	</form>
</div>
';
									} else {
										$rg_j['day'] = date( 'd' );
										$rg_j['month'] = date( 'm' );
										$rg_j['year'] = date( 'Y' );
										$query = 'SELECT COUNT(*) AS tc, SUM(earned) AS te FROM `' . $_GLOBAL['TABLES']['ANALYZER'] . '` WHERE ' . ( '`date`=\'' . $rg_j['year'] . '-' . $rg_j['month'] . '-' . $rg_j['day'] . '\' LIMIT 1' );
										$today_info = System::getdb(  )->fetch( $query );
										$today_info = $today_info[0];
										$query = 'SELECT COUNT(*) AS tc, SUM(earned) AS te FROM `' . $_GLOBAL['TABLES']['ANALYZER'] . '` LIMIT 1';
										$total_info = System::getdb(  )->fetch( $query );
										$total_info = $total_info[0];
										echo '<div class="user_content" style="text-align:center;">
	<h2 style="font-weight:normal;text-align:center;margin:0;color:#cc8f0e;">
	<strong>Today:</strong> ';
										echo $today_info['tc'];
										echo ' views, $';
										echo number_format( $today_info['te'], 5 );
										echo ' earned
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<strong>Total:</strong> ';
										echo $total_info['tc'];
										echo ' views, $';
										echo number_format( $total_info['te'], 5 );
										echo ' earned
	</h2>
</div>
<div class="user_content">
	<h2>
		<span id="report_date">';
										echo date( 'F Y' );
										echo '</span> Report
		<div style="float: right; font-weight: normal;">
			Choose Month:
			<select name="report_date">
				';
										$my = explode( '-', date( 'm-Y' ) );
										$m = 0;
										$y = 2011;
										$op = array(  );

										while (!( ( !( $m != (int)$my[0] ) && !( $y != (int)$my[1] ) ))) {
											if (++$m == 13) {
												$m = 1;
												++$y;
											}

											$op[] = '<option value=\'' . $m . '-' . $y . '\'>' . date( 'F Y', mktime( 0, 0, 0, $m, 1, $y ) ) . '</option>';
										}

										$op = array_reverse( $op );

										foreach ($op as $o) {
											echo $o;
										}

										echo '			</select>
		</div>
	</h2>
	<div id="report_graph" style="height:300px;margin:0 auto;"></div>
	<table cellspacing="2" class="report_info">
		<tr>
			<td><span id="report_date">';
										echo date( 'F Y' );
										echo '</span></td>
			<td>Total Visitors: <span id="report_info_visitors">0</span></td>
			<td>Avg. CPM: $<span id="report_info_cpm">0.00</span></td>
			<td>User Total Month Earned: $<span id="report_info_earned">0.0000</span></td>
		</tr>
	</table>
</div>
<div class="user_content">
	<div style="width:50%;float:left;">
		<h2>Top 20 Countries</h2>
		<table cellspacing="1" class="tablesorter top_countries" style="width:90%;">
			<thead>
				<tr>
					<th>Country</th>
					<th align="right">Views</th>
					<th align="right">Money Earned</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="3"><em>No data available</em></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div style="width:50%;float:left;">
		<h2>Top 20 Referrers</h2>
		<table cellspacing="1" class="tablesorter top_referrers" style="width:90%;">
			<thead>
				<tr>
					<th>Referrer</th>
					<th align="right">Views</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="2"><em>No data available</em></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div style="clear:both;"></div>
</div>
<script type="text/javascript">
';
										function get_js_01() {
											ob_start(  );
											echo '$(\'select[name="report_date"]\').change(function() {
	var rd = $(\'option:selected\', this).val().split(\'-\');
	var months = [\'January\',\'February\',\'March\',\'April\',\'May\',\'June\',\'July\',\'August\',\'September\',\'October\',\'November\',\'December\'];
	$("div#report_graph").html(\'<div class="load01"><img src="../images/load01.gif" border="0" /> &nbsp;&nbsp;Loading...</div>\');
	$(\'tbody\', \'table.top_countries\').html(\'<div class="load01"><img src="../images/load01.gif" border="0" /> &nbsp;&nbsp;Loading...</div>\');
	$(\'tbody\', \'table.top_referrers\').html(\'<div class="load01"><img src="../images/load01.gif" border="0" /> &nbsp;&nbsp;Loading...</div>\');
	$.get(\'ajax.admin.php\',{opt: \'get_report\', args:{month: rd[0], year: rd[1]}},
		function(r) {
			var j = eval(\'(\' + r + \')\');
			if (j.error) {
				alert(j.error);
			} else if (j.message) {
				$(\'span#report_date\').text(months[parseInt(rd[0] - 1)] + \' \' + rd[1]);
				$(\'span#report_info_visitors\').text(j.message.visitors || \'0\');
				$(\'span#report_info_cpm\').text(j.message.cpm || \'0.00\');
				$(\'span#report_info_earned\').text(j.message.earned || \'0.00000\');
				var jm_data = \'\';
				eval(\'jm_data = [\' + j.message.data + \'];\');
				$.plot($("div#report_graph"), [
					{ data: jm_data, lines: {show: true, fill: true} }
				], {
					xaxis: {
						ticks: j.message.x_max, min: 1, max: j.message.x_max,
						tickDecimals: 0
					},
					yaxis: { ticks: j.message.y_max, min: 0, max: j.message.y_max }
				});
			} else {
				alert(\'[0x1gr01] Error Communicating With Server - Please try again\');
			}
		}
	);
	$.get(\'ajax.admin.php\',{opt: \'get_t20_cr\', args:{month: rd[0], year: rd[1]}},
		function(r) {
			var j = eval(\'(\' + r + \')\');
			if (j.error) {
				alert(j.error);
			} else if (j.message) {
				var tc = j.message.tc_data, tc_html = \'\';
				var tr = j.message.tr_data, tr_html = \'\';
				$.each(tc, function(k,v) {
					tc_html += \'<tr>\';
					tc_html += \'<td>\' + v.country + \'</td>\';
					tc_html += \'<td align="right">\' + v.hits + \'</td>\';
					tc_html += \'<td align="right">$\' + parseFloat(v.earned).toFixed(5) + \'</td>\';
					tc_html += \'</tr>\';
				});
				$.each(tr, function(k,v) {
					tr_html += \'<tr>\';
					tr_html += \'<td>\' + k + \'</td>\';
					tr_html += \'<td align="right">\' + v.hits + \'</td>\';
					tr_html += \'</tr>\';
				});
				if (!tc_html) {
					tc_html += \'<tr>\';
					tc_html += \'<td colspan="3"><em>No data available</em></td>\';
					tc_html += \'</tr>\';
				}
				if (!tr_html) {
					tr_html += \'<tr>\';
					tr_html += \'<td colspan="2"><em>No data available</em></td>\';
					tr_html += \'</tr>\';
				}

				$(\'tbody\', \'table.top_countries\').html(tc_html);
				$(\'tbody\', \'table.top_referrers\').html(tr_html);
			} else {
				alert(\'[0x1gt01] Error Communicating With Server - Please try again\');
			}
		}
	);
});
$(\'select[name="report_date"]\').change();
';
											$c = ob_get_contents(  );
											ob_end_clean(  );
											return $c;
										}

										echo Utilities::jspack( get_js_01(  ) );
										echo '</script>
';
									}
								}
							}
						}
					}
				}
			}
		}
	} else {
		site_header( false, null, true );
		echo '<div class="mainhr"></div>
<div class="user_login" style="height:65px;">
	<form action="?';
		echo ( isset( $_REQUEST['gs'] ) ? 'gs' : '' );
		echo '" method="POST">
		<h3>Admin Login:</h3>
		<div class="fields">
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="adm_user" value="" />
			</div>
			<div class="nspr inneri right"></div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="password" name="adm_pass" value="" />
			</div>
			<div class="nspr inneri right"></div>
			<div class="nspr inneri left nblu"></div>
			<div class="nspr inneri bg nblu">
				<input type="submit" name="adm_sub" value="LOGIN" />
			</div>
			<div class="nspr inneri right nblu"></div>
		</div>
	</form>
</div>
<div class="mainhr"></div>
';
	}

	site_footer( false );
?>