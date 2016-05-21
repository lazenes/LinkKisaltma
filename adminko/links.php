<?php
if (!defined('FROM_ADMIN') || FROM_ADMIN !== true) die();

$_SEARCHQ = $_POST['sq'] ? $_POST['sq'] : '';

$items = array('id', 'long_url', 'title', 'user');
$sqfor = $_POST['sqin'] != '' ? array($_POST['sqin']) : $items;
$where = '';
foreach ($sqfor as $s) $where .= "`{$s}` LIKE '%{$_SEARCHQ}%' OR ";
$where = trim(rtrim($where, 'OR '));

$links = System::getDB()->getRows($_GLOBAL['TABLES']['LINKS'], $where, '`post_date` DESC');
?>
<div class="user_content">
	<h2 style="margin-bottom:10px;font-weight:normal;">
		Searching For:
		<strong><?php echo ($_POST['sq']?'"'.$_POST['sq'].'"':'Nothing Yet'); ?></strong>
		<?php echo ($_POST['sqin']?" in \"<strong>{$_POST['sqin']}</strong>\"":''); ?>
		<div style="float: right; font-weight: normal;">
			<form action="?links" method="POST" style="margin:0px;">
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
	<table cellspacing="1" class="tablesorter link_list">
		<thead>
			<tr>
				<th width="7%">ID</th>
				<th width="36%">Website Address</th>
				<th width="10%">Advert Type</th>
				<th width="10%">Views</th>
				<th width="10%">Earnings</th>
				<th width="7%">User</th>
				<th width="10%">Posted</th>
				<th width="10%">Action</th>
			</tr>
		</thead>
		<tbody>
			<?php
				if (!empty($links)) {
					foreach ($links as $l) {
						echo "<tr id='l{$l['id']}'>";
						echo "<td>{$l['id']}</td>";
						
						echo "<td>";
						echo "<a href='{$l['short_url']}' class='short'>{$l['short_url']}</a>";
						echo "<br />";
						echo "<span id='rtitle' style='display:" . ($l['title']?"block;":"hidden;");
						echo "'><em>{$l['title']}</em></span>";
						echo "<span id='long'><a href='{$l['long_url']}' class='long'>";
						echo "<em>{$l['long_url']}</em></a></span></td>";
						
						echo "<td id='adtype'>{$l['adtype']}</td>";
						echo "<td>{$l['views']}</td>";
						echo "<td>\${$l['earned']}</td>";
						echo "<td id='user'>{$l['user']}</td>";
						echo "<td>" . date('Y-m-d', strtotime($l['post_date'])) . "</td>";
						
						echo "<td align='center'><a href='#' id='edit'><img src='../images/tsp_edit.png' border='0' /></a>";
						echo "<span>&nbsp;&nbsp;</span>";
						echo "<a href='#' id='rem'><img src='../images/tsp_del.png' border='0' /></a></td>";
						echo '</tr>';
					}
				} else {
					echo '<tr>';
					echo '<td colspan="8"><em>No links found, or no search query entered.</em></td>';
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
	<?php if (!empty($links)) { ?>
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
			$('table.link_list').tablesorter({widthFixed: true, headers: {0: {sorter: false}}})
								.tablesorterPager({container: $("div.links_pager")});
			$('table.link_list tr[id^="l"] a#edit').click(function() {
				var _e = $(this).parent().parent();
				var _t = _e.attr('id').substr(1);
				var _u = $('td#user',_e).text();
				var spanrtitle = $('span#rtitle',_e), spanlong = $('span#long',_e), 
					tdadtype = $('td#adtype',_e), _tdlast = $('td:last',_e);
				var adtype = tdadtype.text();
				spanrtitle.css('display', 'block')
					.html('<span style="width:50px;display:inline-block;font-size:12px;">Title:</span><input type="text" name="title" size="30" value="'+spanrtitle.text()+'" />');
				spanlong.html('<span style="width:50px;display:inline-block;font-size:12px;">Website:</span><input type="text" name="website" size="30" value="'+spanlong.text()+'" />');
				tdadtype.html('<select name="adtype">'
							  + '<option value="1"'+(adtype=='Interstitial'?' selected':'')+'>Interstitial</option>'
							  + '<option value="2"'+(adtype=='Top Banner'?' selected':'')+'>Top Banner</option>'
							  + '<option value="3"'+(adtype=='None'?' selected':'')+'>None</option>'
							  + '</select>');
				var edit_btn = $('<input type="submit" value="Edit" />');
				_tdlast.children().hide();
				_tdlast.append(edit_btn);
				edit_btn.click(function() {
					var tmp = edit_btn.val();
					edit_btn.val('Updating...').attr('readonly', true);
					var website = $('input[name="website"]',_e).val(), title = $('input[name="title"]',_e).val(), 
						adtype = $('select[name="adtype"]',_e).val();
					$.get('../ajax.php',{opt:'edit_link',args:{_uid_:_u,lid:_t,website:website,title:title,adtype:adtype}},
						function(r) {
							var j = eval('(' + r + ')');
							if (j.error) {
								edit_btn.val('Edit').attr('readonly', false);
								alert(j.error);
							} else if (j.message) {
								spanrtitle.html('<em>' + title + '</em>');
								spanlong.html(website);
								tdadtype.html(adtype==1?'Interstitial':(adtype==2?'Top Banner':'None'));
								edit_btn.remove();
								_tdlast.children().show();
							} else {
								alert('[0x1el02] Error Communicating With Server - Please try again');
							}
						}
					);
					return false;
				});
				return false;
				return false;
			});
			$('table.link_list tr[id^="l"] a#rem').click(function() {
				var e = $(this).parent().parent();
				var t = e.attr('id').substr(1);
				var u = $('td#user',e).text();
				if (confirm('Are you sure you want to delete this link?')) {
					$.get('../ajax.php',{opt:'rem_link',args:{_uid_:u,lid:t}},
						function(r) {
							var j = eval('(' + r + ')');
							if (j.error) {
								alert(j.error);
							} else if (j.message) {
								e.remove();
							} else {
								alert('[0x1rl01] Error Communicating With Server - Please try again');
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