<?php
	if(extension_loaded('zlib')) {
		ob_start('ob_gzhandler');
	} 
	header("content-type: text/css");
	header("cache-control: must-revalidate");
	header("expires: " . gmdate("D, d M Y H:i:s", time() + 864000) . " GMT");
	
	function gradient($start, $end, $bg = false) {
		$bgc = 'background-color:#' . ($bg?$bg:$start) . ';';
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			return $bgc;//$bgc . "filter: progid:DXImageTransform.Microsoft.gradient("
				   //. "startColorstr=#{$start}, endColorstr=#{$end});";
		} else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') && !strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
			return "background: -moz-linear-gradient(top , #{$start}, #{$end});";
		}
		
		return $bgc . "background: -webkit-gradient(linear, left top, left bottom,"
			   . " from({$start}), to({$end}));";
	}
	
	function rgba($r, $g, $b, $a) {
		if (isset($_GET['msie'])) {
			$r = dechex($r); $g = dechex($g); $b = dechex($b); $a = dechex($a * 255);
			if (strlen($r) < 2) $r = '0' . $r; if (strlen($g) < 2) $g = '0' . $g;
			if (strlen($b) < 2) $b = '0' . $b; if (strlen($a) < 2) $a = '0' . $a;
			$hex = $a . $r . $g . $b;
			return  'background: transparent;' .
					"-ms-filter: \"progid:DXImageTransform.Microsoft.gradient(startColorstr=#{$hex},endColorstr=#{$hex})\";" . /* IE8 */
					"filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#{$hex},endColorstr=#{$hex});" .   /* IE6 & 7 */
					'zoom: 1;';
		}
		
		return "background-color: rgba({$r},{$g},{$b},{$a});";
	}

	function radius($r) {
		return "-moz-border-radius:{$r};-webkit-border-radius:{$r};border-radius:{$r};";
	}
	
	function opacity($o) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			return 'filter:alpha(opacity=' . $o . ')';
		}
		return 'opacity:' . ($o / 100) . ';';
	}
	
	function user_select($s) {
		return "-webkit-user-select:{$s};-khtml-user-select:{$s};-moz-user-select:{$s};"
			   . "-o-user-select:{$s};user-select:{$s};";
	}
	
	$css = <<< CSS
body {
	color: #FEFEFE;
	font-family: 'Lucida Grande', Arial, Sans-Serif;
	margin: 0;
	padding: 0;
	background: #195F97 url('../images/main_bg.jpg') repeat-x fixed;
}

div.main_ctr {
	margin: 0 auto;
	padding: 6px 10px;
	min-width: 760px;
	width: 850px;
	text-align: center;
}

div.footer {
	color: #021C30;
	font-size: 12px;
	margin: 0 5px 10px 5px;
}

div.footer a {
	color: #021C30;
	text-decoration: none;
	padding: 0 3px;
}

div.footer a:hover { text-decoration: underline; }

div.logo {
	width: 140px;
	height: 109px;
	margin: 10px auto 5px auto;
	background: url('../images/logo.png') no-repeat;
}
div.logo a { height: 109px; display: block; }

div.mainhr {
	background-color: #60BFFF;
	border: none 0;
	border-bottom: 1px solid #0A4875;
	width: 100%;
	height: 1px;
	opacity(75);
}

div.error_box {
	font-weight: bold;
	color: #280505;
	background-color: #FF5656;
	padding: 9px 18px;
	text-align: center;
	font-weight: 14px;
	text-shadow: #ffa5a5 0px 1px 1px;
	gradient(FF5656,ff2b2b,FF5656);
	radius(10px);
}

div.message_box {
	font-weight: bold;
	color: #1a2b06;
	background-color: #b0ff56;
	padding: 9px 18px;
	text-align: center;
	font-weight: 14px;
	text-shadow: #edffd8 0px 1px 1px;
	gradient(b0ff56,9ef738,b0ff56);
	radius(10px);
}

div.main_intro {
	min-width: 200px;
	min-height: 270px;
	width: 41%;
	display: inline-block;
	line-height: 2em;
	text-align: left;
	font-size: 10.2pt;
	margin: 15px;
	padding: 10px 15px;
	color: #111111;
	radius(20px);
}

div.main_intro.left {
	float: left;
	border: 4px solid #e59202;
	gradient(f3ca0e,e59202,f3ca0e);
}

div.main_intro.right {
	float: right;
	border: 4px solid #2791ce;
	gradient(75c5f6,2791ce,75c5f6);
}

div.main_intro h2 {
	text-align: center;
	margin: 10px auto;
	font-family: 'Calibri', Lucida Grande;
}

div.navigation {
	float: right;
	font-size: 14px;
	font-weight: 600;
	margin: 79px 5px 0 15px;
	font-family: 'Tahoma', Verdana;
}

div.navigation a {
	margin: 0 3px;
	radius(25px);
	color: #FFFFFF;
	padding: 5px 15px;
	text-decoration: none;
	text-shadow: #083C60 1px 1px 3px;
	-moz-box-shadow: 0px 1px 3px #0d5f96;
	-webkit-box-shadow: 0px 1px 3px #0d5f96;
	/*box-shadow: 0px 1px 3px #0d5f96;*/
	gradient(4EADED,3B8DDB,3B8DDB);
}

div.navigation a:hover,
div.navigation a.selected {
	gradient(52B5F9,4098Ef,52B5F9);
}

div.navigation a:active {
	gradient(4098EF,52B5F9,4098EF);
}

div.user_login,
div.account_verify {
	padding: 5px 0;
	height: 100px;
}

div.user_login h3,
div.account_verify h3,
div.join_page h3 {
	float: left;
	display: inline-block;
	width: 200px;
	padding-left: 25px;
	margin-bottom: 0;
	text-align: left;
	text-shadow: #083C60 1px 2px 3px;
}

div.user_login div.fields,
div.account_verify div.fields {
	float: left;
	display: inline-block;
	padding: 15px 15px 0 15px;
}

div.user_login div.fields div.nspr.left,
div.account_verify div.fields div.nspr.left {
	margin-left: 20px;
}

div.user_login div.fields input[type="text"],
div.user_login div.fields input[type="password"],
div.account_verify div.fields input[type="text"],
div.join_page div.field input[type="text"],
div.join_page div.field input[type="password"] {
	min-width: 150px;
	font-size: 16px;
	color: #083C60;
	padding-top: 5px;
}

div.user_login div.fields input[type="submit"],
div.account_verify div.fields input[type="submit"],
div.join_page input[type="submit"] {
	min-width: 75px;
	opacity(70);
}

div.user_login div.field_opts,
div.account_verify div.field_opts {
	float: left;
	font-size: 12px;
	text-align: left;
	padding: 5px 25px;
	text-shadow: #083C60 1px 1px 1px;
}

div.user_login div.field_opts a,
div.account_verify div.field_opts a {
	float: left;
	width: 200px;
	color: #EEEEEE;
	text-decoration: none;
}

div.join_page {
	display: none;
	text-align: left;
	padding: 3px 15px;
	background: #195F97 url('../images/main_bg.jpg') repeat-x fixed;
	radius(6px);
}

div.join_page div.load01 {
	margin-top: 4px;
	font-size: 12px;
	display: inline-block;
	text-shadow: #083C60 1px 1px 3px;
}

div.simplemodal-container a.btn_close {
	color: #60BFFF;
	left: 82%;
	top: 9px;
	font-size: 10px;
	font-weight: bold;
	position: absolute;
	text-decoration: none;
	text-shadow: #0A4875 1px 1px 1px;
}

div.join_page h3 {
	float: none;
	padding: 0;
	margin-bottom: 10px;
}

div.join_page div.field {
	padding: 5px 0;
	clear: both;
}

div.join_page div.field div.field_name {
	float: left;
	padding: 6px;
	width: 125px;
	font-size: 14px;
	text-shadow: #083C60 1px 1px 1px;
}

div.join_page div.field input[type="text"],
div.join_page div.field input[type="password"] {
	width: 200px;
}

div.joinbtn,
div.ml_options {
	font-weight: bold;
	font-size: 16px;
	line-height: 2em;
	color: #082338;
	color: rgba(119, 89, 0, 0.8);
	text-shadow: #FFE15E 1px 1px 0px;
	user-select(none);
}
div.joinbtn div.nspr.outeri,
div.ml_options div.nspr.outeri {
	cursor: pointer;
}

div.joinbtn div.nspr.outeri.bg.over,
div.ml_options div.nspr.outeri.bg.over {
	text-shadow: #D6B224 1px 1px 0px; 
}

span.do_another {
	cursor: pointer;
	font-size: 12px;
	padding: 4px 10px;
	margin: 4px;
	font-size: 12px;
	font-weight: bold;
	float: right;
	text-decoration: none;
	color: #393939;
	text-shadow: #EEEEEE 1px 1px 0px;
	user-select(none);
	gradient(D9D9D9,CCCCCC,D9D9D9);
	radius(9px);
}

span.do_another:hover { gradient(CCCCCC,D9D9D9,CCCCCC); }

div.shrink div.nspr.inneri.bg.nblu, 
div.joinbtn div.nspr.outeri.bg,
div.ml_options div.nspr.outeri.bg {
	padding-left: 10px;
	padding-right: 10px;
}

div.shrink, div.joinbtn {
	display: inline-block;
	padding: 15px 0;
}

div.ml_options {
	display: inline-block;
}

div.ml_options_box {
	float: right;
	margin: 0 10px;
	padding: 10px 15px;
	font-size: 14px;
	text-align: left;
	gradient(166fab,125d8e,125d8e);
	radius(0 0 5px 5px);
}

div.ml_options_box input,
div.ml_options_box select {
	padding: 3px 3px;
}

div.ml_options_box.tools {
	float: none;
	color: #EEEEEE;
	radius(5px);
}

div.ml_options_box.links {
	float: none;
	padding: 7px 14px;
	margin: 10px;
	gradient(3aa2e8,166fab,3aa2e8);
	radius(5px);
}

div.ml_options_box.links a.l {
	color: #9edbff;
	font-size: 16px;
	margin: 0 0 3px 0;
	display: block;
	text-decoration: none;
}

div.ml_options_box.links a.s {
	color: #EEEEEE;
	font-size: 14px;
	display: block;
	text-decoration: none;
}

div.ml_options_box.links a:hover.l,
div.ml_options_box.links a:hover.s {
	text-decoration: underline;
}

div.tools_shrink_box {
	margin: 10px 10px 0 10px;
	padding: 4px;
	gradient(f3ca0e,ffa602,f3ca0e);
	radius(5px);
}

input.search_box,
select.search_select,
div.tools_shrink_box textarea {
	padding: 4px 8px;
	border: 1px solid CCCCCC;
	outline: 0;
	radius(15px);
	color: #444444;
	gradient(FFFFFF,CCCCCC,FFFFFF);
}

div.tools_shrink_box textarea {
	width: 100%;
	height: 150px;
	border: 0;
	radius(5px);
}

input.search_button {
	padding: 4px 8px;
	border: 1px solid 50b6f8;
	outline: 0;
	font-weight: bold;
	radius(15px);
	gradient(76c6f7,50b6f8,76c6f7);
}

input.search_button:active {
	gradient(50b6f8,76c6f7,50b6f8);
}

input.search_box:focus,
select.search_select:focus {
	color: #222222;
	border: 1px solid 999999;
}

input.mass_shrink {
	margin: 10px 10px 0 10px;
	padding: 12px 24px;
	border: 2px solid #50b6f8;
	font-weight: bold;
	font-size: 15px;
	gradient(76c6f7,50b6f8,76c6f7);
	radius(6px);
}

div.shrink input.shrinker,
div.user_login div.fields input,
div.account_verify div.fields input,
div.join_page input {
	background: transparent;
	border: 0;
	outline: 0;
}

div.shrink input[type="text"].shrinker {
	color: #775700;
	color: rgba(119, 89, 0, 0.7);
	height: 28px;
	padding: 1px 0;
	float: left;
	font-size: 22px;
	min-width: 400px;
}

div.shrink input[type="submit"].shrinker,
div.user_login div.fields input[type="submit"],
div.account_verify div.fields input[type="submit"],
div.join_page input[type="submit"] {
	color: #2C1F0E;
	height: 31px;
	font-weight: bold;
	font-size: 10px;
	cursor: pointer;
	text-shadow: #FFE15E 1px 1px 0px;
}

h2.page_title {
	padding-left: 5px;
	margin: 10px 0;
	text-align: left;
	text-shadow: #083C60 1px 2px 3px;
}

div.user_content {
	width: auto;
	margin: 5px 2px 10px 2px;
	padding: 10px 15px;
	text-align: left;
	color: #09212D;
	font-size: 12px;
	gradient(FFFFFF,DDDDDD,FFFFFF);
	radius(9px);
}

div.user_content h2 {
	padding-left: 0;
	margin: 0 0 10px 0;
	text-align: left;
	color: #feb300;
	font-size: 18px;
	text-shadow: #FFFFFF 0px 1px 1px;
}

a.uc { text-decoration: none; color: #166fab; }
a.uc:hover { text-decoration: underline; }

table.tablesorter {
	radius(6px);
}

table.tablesorter thead tr th:first-child { radius(6px 0 0 0); }
table.tablesorter thead tr th:last-child { radius(0 6px 0 0); }

table.links_table { font-size: 12px; }
table.links_table a,
table.link_list a { text-decoration: none; color: #166fab; }
table.links_table a.short, 
table.link_list a.short { font-weight: bold; font-size: 110%; }
table.links_table a.long, 
table.link_list a.long { color: #000000; font-size: 90%; }
table.links_table a:hover,
table.link_list a:hover { text-decoration: underline; }

table.campaign_list { }

div.campaign_box {
	background-color: #cde7fe;
	gradient(cde7fe,b8dcfc,cde7fe);
	radius(3px);
	border: 1px solid #b8dcfc;
}

div.campaign_box a { color: blue; text-decoration: none; }
div.campaign_box a.selected { font-weight: bold; }

div.campaign_filter {
	float: right;
	padding: 0 15px;
	font-size: 11px;
	display: inline-block;
}

div.campaign_filter a { color: #000000; }

div.campaign_type { padding: 10px 13px; font-size: 110%; }

table.advertising_table {
	width: 85%;
	margin: 0 auto;
}

table.advertising_table tbody td {
	padding: 3px 10px;
	vertical-align: middle;
}

table.advertising_table tbody tr.selected td {
	gradient(b4eaa8,9bdb8d,b4eaa8);
}

table.advertising_table tbody tr.submit td {
	padding: 10px;
	font-weight: bold;
	font-size: 13px;
	gradient(f5c910,f8a80b,f8a80b);
}

table.advertising_table tbody tr.submit td input[type="submit"] {
	padding: 6px 12px;
	border: 1px solid #76c6f7;
	gradient(76c6f7,50b6f8,76c6f7);
	radius(6px);
}

table.advertising_table div.special {
	float: right;
	color: red;
	font-weight: bold;
}

table.order_table {
	width: 85%;
	margin: 0 auto;
}

table.order_table tfoot td {
	background-color: #e6efee;
	padding: 5px;
	color: #333333;
}

table.report_info { width: 100%; margin: 10px auto 0 auto; border: 1px solid #2584BA; font-size: 13px; }
table.report_info td { padding: 4px 8px; gradient(2584BA,185f97,2584BA); text-align: center; color: #EEEEEE; }
table.report_info td:first-child { gradient(185f97,134d7a,185f97); color: #FFFFFF; }
table.report_info td:last-child { font-weight: bold; }

form.account_form div,
form.account_form input,
form.account_form select {
	font-size: 13px;
	margin-bottom: 5px;
}

form.account_form input, form.account_form select { padding: 5px; }

form.account_form div span:first-child {
	float: left;
	width: 200px;
	margin-top: 9px;
}

form.account_form div div.req {
	color: red;
	display: inline;
	font-size: 15px;
}

form.account_form span.captcha { float: left; }

form.account_form span.captcha div,
form.account_form span.captcha input,
form.account_form span.captcha select { font-size: auto; margin: auto; }
form.account_form div span#recaptcha_instructions_image {
	float: none;
	width: auto;
	margin: auto;
}

form.settings_form div,
form.settings_form input,
form.settings_form select {
	font-size: 12px;
	margin-bottom: 5px;
	padding: 5px;
	radius(5px);
}

form.settings_form input, form.settings_form select { border: 1px solid #CCCCCC; min-width: 400px; width: 100%; }

form.settings_form div span:first-child {
	float: left;
	width: 250px;
	margin-top: 6px;
}

form.settings_form div:nth-child(even) { background-color: #d7eaf4; radius(5px); }

input.nblu {
	padding: 6px 12px;
	border: 1px solid #76c6f7;
	gradient(76c6f7,50b6f8,76c6f7);
	radius(6px);
}

table.user_list tbody tr.selected td,
table.link_list tbody tr.selected td {
	cursor: pointer;
	gradient(76c6f7,50b6f8,76c6f7);
}


div.nspr { background-image: url('../images/nspr.png'); }
div.nspr.outeri, div.nspr.inneri { display: inline-block; float: left; }
div.nspr.outeri { height: 42px;  }
div.nspr.outeri.bg { background-position: 0px -42px; background-repeat: repeat-x; height: 36px;  padding: 6px 1px 0 0; }
div.nspr.outeri.bg.over { background-position: 0px -84px; }
div.nspr.outeri.left, div.nspr.outeri.right { background-repeat: no-repeat; width: 6px; }
div.nspr.outeri.left { background-position: 0px 0px;  }
div.nspr.outeri.left.over { background-position: -11px 0px;  }
div.nspr.outeri.right { background-position: -6px 0px;  }
div.nspr.outeri.right.over { background-position: -17px 0px;  }
div.nspr.inneri { height: 32px; }
div.nspr.inneri.bg { background-position: 0px -127px; background-repeat: repeat-x; }
div.nspr.inneri.bg.nblu { background-position: 0px -159px; }
div.nspr.inneri.left, div.nspr.inneri.right { background-repeat: no-repeat; width: 5px; }
div.nspr.inneri.left { background-position: -22px -1px;  }
div.nspr.inneri.left.nblu { background-position: -34px -1px; }
div.nspr.inneri.right { background-position: -28px -1px;  }
div.nspr.inneri.right.nblu { background-position: -40px -1px; }

/* fly */

div.fly_head {
	padding: 5px 15px;
	font-size: 18px;
	gradient(28587f,043156,043156);
}

div.fly_head_bottom {
	padding: 1px 25px;
	font-size: 13px;
	color: #000000;
	gradient(e7f3ff,b9dfff,b9dfff);
	border-bottom: 1px solid #28587f;
}

div.skip_btn {
	color: #000000;
	padding: 9px 25px;
	text-shadow: #FFFFFF 0px 1px 2px;
	gradient(f5c910,f8a80b,f8a80b);
	radius(19px);
}

div.skip_btn:hover {
	gradient(fcd741,f5c910,f5c910);
	cursor: pointer;
}

div.skip_btn a {
	text-decoration: none;
	color: #000000;
	font-weight: 600;
}
CSS;
	
	$css = preg_replace('/rgba\((\w+),(\w+),(\w+),(.*)\);/e', "rgba('$1','$2','$3','$4')", $css);
	$css = preg_replace('/gradient\((.*),(.*),(.*)\);/e', "gradient('$1','$2','$3')", $css);
	$css = preg_replace('/radius\((.*)\);/e', "radius('$1')", $css);
	$css = preg_replace('/opacity\((.*)\);/e', "opacity('$1')", $css);
	$css = preg_replace('/user-select\((.*)\);/e', "user_select('$1')", $css);
	
	$css = preg_replace('#\s+#', ' ', $css);
	$css = preg_replace('#/\*.*?\*/#s', '', $css);
	$css = str_replace('; ', ';', $css);
	$css = str_replace(': ', ':', $css);
	$css = str_replace(' {', '{', $css);
	$css = str_replace('{ ', '{', $css);
	$css = str_replace(', ', ',', $css);
	$css = str_replace('} ', '}', $css);
	$css = str_replace(';}', '}', $css);

	echo trim($css);
	if(extension_loaded('zlib')){ 
		ob_end_flush(); 
	} 
?>