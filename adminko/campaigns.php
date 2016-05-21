<?php
if (!defined('FROM_ADMIN') || FROM_ADMIN !== true) die();

$_SEARCHQ = $_POST['sq'] ? $_POST['sq'] : '';
$_CT = $_GET['ct'] == 2 ? 2 : 1;

$items = array('id', 'uid', 'website_name', 'website_url', 'created');
$sqfor = $_POST['sqin'] != '' ? array($_POST['sqin']) : $items;
$where = "`advert_type`='" . ($_CT == 2 ? 'Banner' : 'Interstitial') . "' AND (";
foreach ($sqfor as $s) $where .= "`{$s}` LIKE '%{$_SEARCHQ}%' OR ";
$where = trim(rtrim($where, 'OR ')) . ')';

$campaigns = System::getDB()->getRows($_GLOBAL['TABLES']['CAMPAIGNS'], $where, '`created` DESC');
?>
<div class="user_content">
	<h2 style="margin-bottom:10px;font-weight:normal;">
		Searching For:
		<strong><?php echo ($_POST['sq']?'"'.$_POST['sq'].'"':'Nothing Yet'); ?></strong>
		<?php echo ($_POST['sqin']?" in \"<strong>{$_POST['sqin']}</strong>\"":''); ?>
		<div style="float: right; font-weight: normal;">
			<form action="?cs&ct=<?php echo $_CT; ?>" method="POST" style="margin:0px;">
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
	<div class="campaign_box campaign_type">
		<a href="?cs&ct=1"<?php if($_CT!=2) echo ' class="selected"';?>>Interstitial Adverts</a>
		&nbsp;|&nbsp;
		<a href="?cs&ct=2"<?php if($_CT==2) echo ' class="selected"';?>>Banner Adverts</a>
	</div>
	<table cellspacing="1" class="tablesorter link_list">
		<thead>
			<tr>
				<th width="7%">ID</th>
				<th width="26%">Website Name</th>
				<th width="12%">Package</th>
				<th width="8%">Views Left</th>
				<th width="10%">Daily Budget</th>
				<th width="10%">Spent Today</th>
				<th width="7%">Status</th>
				<th width="7%">User</th>
				<th width="10%">Created</th>
				<th width="10%">Action</th>
			</tr>
		</thead>
		<tbody>
			<?php
				function get_package_name($id) {
					global $_GLOBAL;
					return System::getDB()->getField($_GLOBAL['TABLES']['PACKAGES'], 'name', "`id`='{$id}'");
				}
				function get_package_names($pkg) {
					$pkgs  = explode(';', $pkg);
					$names = '';
					foreach ($pkgs as $v) {
						$p = explode(',', $v);
						$names .= get_package_name($p[0]) . '<br />';
					}
					return rtrim($names, '<br />');
				}
				if (!empty($campaigns)) {
					foreach ($campaigns as $cl) {
						echo "<tr id='c{$cl['id']}'>";
						echo "<td>{$cl['id']}</td>";
						
						echo "<td><span id='website_name'>{$cl['website_name']}</span><br />";
						echo "<span id='website_url'><em>{$cl['website_url']}</em></span>";
						if ($_CT == 2) {
							echo "<br /><span id='website_banner'><em>{$cl['website_banner']}</em></span>";
						}
						echo "</td>";
						
						echo "<td>" . get_package_names($cl['packages']) . "</td>";
						echo '<td>' . number_format($cl['views_left']) . '</td>';
						echo "<td>\$<span id='daily_budget'>{$cl['daily_budget']}</span></td>";
						echo "<td>\${$cl['spent_today']}</td>";
						echo "<td align='center' id='cstatus'>" 
							 . ($cl['status'] == 2 ? 'Active'
								: ($cl['status'] == 3 ? 'Finished' : 'Pending'))
							 . "</td>";
						echo "<td id='user'>{$cl['uid']}</td>";
						echo "<td>" . date('Y-m-d', strtotime($cl['created'])) . "</td>";
						
						echo "<td align='center'><a href='#' id='edit'><img src='../images/tsp_edit.png' border='0' />";
						echo "</a><span>&nbsp;&nbsp;</span>";
						echo "<a href='#' id='rem'><img src='../images/tsp_del.png' border='0' /></a></td>";
						echo '</tr>';
					}
				} else {
					echo '<tr>';
					echo '<td colspan="10"><em>No campaigns found, or no search query entered.</em></td>';
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
	<?php if (!empty($campaigns)) { ?>
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
			$('table.link_list').tablesorter({widthFixed: true, headers: {0: {sorter: false},
																		  9: {sorter: false}}})
								.tablesorterPager({container: $("div.links_pager")});
			$('table.link_list tr[id^="c"] a#edit').click(function() {
				var _e = $(this).parent().parent();
				var _t = _e.attr('id').substr(1);
				var _u = $('td#user',_e).text();
				var website_name = $('span#website_name',_e), website_url = $('span#website_url',_e), 
					website_banner = $('span#website_banner',_e), daily_budget = $('span#daily_budget',_e), 
					cstatus = $('td#cstatus',_e), _tdlast = $('td:last',_e);
				website_name.html('<span style="width:45px;display:inline-block;font-size:12px;">Name:</span><input type="text" name="website_name" size="25" value="'+website_name.text()+'" />');
				website_url.css('display', 'block')
					.html('<span style="width:45px;display:inline-block;font-size:12px;">URL:</span><input type="text" name="website_url" size="25" value="'+website_url.text()+'" />');
				website_banner.html('<span style="width:45px;display:inline-block;font-size:12px;">Banner:</span><input type="text" name="website_banner" size="25" value="'+website_banner.text()+'" />');
				daily_budget.html('<input type="text" name="daily_budget" size="3" value="'+daily_budget.text()+'" />');
				cstatus.html('<select name="cstatus">'
							 + '<option value="1"'+(cstatus.text()=='Pending'?' selected':'')+'>Pending</option>'
							 + '<option value="2"'+(cstatus.text()=='Active'?' selected':'')+'>Active</option>'
							 + '<option value="3"'+(cstatus.text()=='Finished'?' selected':'')+'>Finished</option>'
							 + '</select>');
				var edit_btn = $('<input type="submit" value="Edit" />');
				_tdlast.children().hide();
				_tdlast.append(edit_btn);
				edit_btn.click(function() {
					var tmp = edit_btn.val();
					edit_btn.val('Updating...').attr('readonly', true);
					var name = $('input[name="website_name"]',_e).val(),
						url = $('input[name="website_url"]',_e).val(), 
						banner = $('input[name="website_banner"]',_e).val(), 
						budget = $('input[name="daily_budget"]',_e).val(), 
						status = $('select[name="cstatus"]',_e).val();
					$.get('ajax.admin.php',{opt:'edit_campaign',args:{_uid:_u,cid:_t,
																	website_name:name,
																	website_url:url,
																	website_banner:banner,
																	daily_budget:budget,
																	cstatus:status}},
						function(r) {
							var j = eval('(' + r + ')');
							if (j.error) {
								edit_btn.val('Edit').attr('readonly', false);
								alert(j.error);
							} else if (j.message) {
								website_name.html(name);
								website_url.html('<em>' + url + '</em>');
								website_banner.html('<em>' + banner + '</em>');
								daily_budget.html(budget);
								cstatus.html(status==2?'Active':(status==3?'Finished':'Pending'));
								edit_btn.remove();
								_tdlast.children().show();
							} else {
								alert('[0x1ec01] Error Communicating With Server - Please try again');
							}
						}
					);
					return false;
				});
				return false;
			});
			$('table.link_list tr[id^="c"] a#rem').click(function() {
				var e = $(this).parent().parent();
				var c = e.attr('id').substr(1);
				var u = $('td#user',e).text();
				if (confirm('Are you sure you want to delete this campaign?')) {
					$.get('ajax.admin.php',{opt:'rem_campaign',args:{_uid:u,cid:c}},
						function(r) {
							var j = eval('(' + r + ')');
							if (j.error) {
								alert(j.error);
							} else if (j.message) {
								e.remove();
							} else {
								alert('[0x1rc01] Error Communicating With Server - Please try again');
							}
						}
					);
				}
				return false;
			});
		})();
	</script>
	<?php } ?>
</div>