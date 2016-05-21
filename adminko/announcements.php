<?php
if (!defined('FROM_ADMIN') || FROM_ADMIN !== true) die();

if (isset($_POST['an_sub'])) {
	if ($msg = $_POST['an_msg']) {
		System::getDB()->insert($_GLOBAL['TABLES']['NEWS'], array('message' => $msg));
	}
}

if (isset($_REQUEST['r']) && ($r = intval($_REQUEST['r']))) {
	System::getDB()->query("DELETE FROM `{$_GLOBAL['TABLES']['NEWS']}` WHERE `id`='{$r}' LIMIT 1;");
}

$news = System::getDB()->getRows($_GLOBAL['TABLES']['NEWS'], '', '`date` DESC', '30');

echo '<div class="user_content"><h2>Announcements</h2>';
if ($news) {
	foreach ($news as $item) {
		echo '<div style="min-width:32px;float:left;">';
		echo "<a href='?an&r={$item['id']}'><img src='../images/tsp_del.png' border='0' /></a>";
		echo '</div><div style="min-width:75px;float:left;font-weight:bold;">';
		echo date('d/m/Y', strtotime($item['date'])) . '</div>';
		echo '<div style="float:left;">' . $item['message'] . '</div>';
		echo '<div style="clear:both;height:5px;"></div>';
	}
} else {
	echo '<em>None</em>';
}
echo '</div>';
?>
<div class="user_content">
	<h2>Post Announcement</h2>
	<form action="" method="POST" class="account_form">
		<textarea name="an_msg" rows="5" cols="35"></textarea>
		<div>
			<input type="submit" name="an_sub" value="Post Announcment" style="font-size:15px;padding:10px 30px;" />
		</div>
		<div style="clear:both;"></div>
	</form>
</div>
<script type="text/javascript">
	(function() {
		$('textarea[name="an_msg"]').watermark('Enter Announcement');
	})();
</script>