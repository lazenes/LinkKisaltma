<?php
require_once 'global.php';

$H_TEMPLATE['TITLE'] = 'Frequently Asked Questions';
site_header();
?>
<div class="user_content" style="font-weight:normal;">
	<b style="font-size: 15px;">Publishers</b><br>
	<a class="uc" href="#1a"><b>How does this work?</b></a><br>
	<a class="uc" href="#2a"><b>How much can I earn?</b></a><br>
	<a class="uc" href="#3a"><b>How do I get paid?</b></a><br>
	<a class="uc" href="#4a"><b>Can I click on my own links?</b></a><br>
	<a class="uc" href="#5a"><b>Will my shortened links ever expire?</b></a><br>
	<a class="uc" href="#6a"><b>Can I receive credit for one person clicking on a link, multiple times in one day?</b></a><br>
	<a class="uc" href="#7a"><b>Can I ask people to click on my links to earn money?</b></a><br>
	<a class="uc" href="#8a"><b>Am I allowed to open my <?php echo SITE_NAME; ?> links in a popup or automated redirect?</b></a><br>
	<a class="uc" href="#9a"><b>Can I link to adult material? Or use an <?php echo SITE_NAME; ?> link on an adult site?</b></a><br><br>
	<b style="font-size: 15px;">Advertisers</b><br>
	<a class="uc" href="#1b"><b>How does this work?</b></a><br>
	<a class="uc" href="#2b"><b>How much does it cost?</b></a><br>
	<a class="uc" href="#3b"><b>How are ads targeted?</b></a><br>
	<a class="uc" href="#4b"><b>Can I advertise on <?php echo SITE_NAME; ?>?</b></a><br><br>
	<br><br>
	<h2>Publishers</h2>
	<br>
	<a class="uc" name="1a"></a><b>How does this work?</b><br>
	<?php echo SITE_NAME; ?> is an innovative service that allows you to earn money from each visitor to your shortened links.  How this works is actually quite simple.  First, you shorten your links with our site, and then you post them or send them to people as you usually would with any shortened link.  We handle the rest by showing an ad before the shortened link loads.  Each ad that is shown earns you money!<br><br>
	<a class="uc" name="2a"></a><b>How much can I earn?</b><br>
	This all depends on how many people click on your links.  Earning potential is limitless, and grows even furthur with our referral program which pays 20% of all of your referrals' earnings for LIFE!<br><br>
	<a class="uc" name="2a"></a><b>Will I get paid for every single visitor?</b><br>
	You get paid for every visitor that views an advert. If we do not have a matching advertiser for the visitor's country, your shortened link will always work - but a non paying default ad will be shown. The default ad is the <?php echo SITE_NAME; ?> homepage with your referral ID automatically assigned, so you could be referring <?php echo SITE_NAME; ?> users without even knowing it.<br><br>
	<a class="uc" name="3a"></a><b>How do I get paid?</b><br>
	Currently our methods of payment are PayPal. Your earnings are automatically paid out on the first Monday of each month once you have earned $5.00 USD or $20.00 if your account was registered before the 1st July 2009.<br><br>
	<a class="uc" name="4a"></a><b>Can I click on my own links?</b><br>
	You may click on your own <?php echo SITE_NAME; ?> links 1 time to test them. You may not create links solely to 'test'.<br><br>
	<a class="uc" name="5a"></a><b>Will my shortened links ever expire?</b><br>
	Currently shortened links have no expiry date. This may change in the future but plenty of notice will be given.<br><br>
	<a class="uc" name="6a"></a><b>Can I receive credit for one person clicking on a link multiple times?</b><br>
	You may do, it depends how many advertisers we have on the system at that time and also other factors relating to fraud prevention. We aim for the visitor to be credited at least once in a 24 hour period.<br><br>
	<a class="uc" name="7a"></a><b>Can I ask people to click on my links to earn money?</b><br>
	No, the user must click on your <?php echo SITE_NAME; ?> links because they wish to get to the destination website - not because they are asked or forced to.<br><br>
	<a class="uc" name="8a"></a><b>Am I allowed to open my <?php echo SITE_NAME; ?> links in a popup or automated redirect?</b><br>
	This is not allowed. The only legitimate way to open an <?php echo SITE_NAME; ?> is with a mouse click, on the actual link.<br><br>
	<a class="uc" name="9a"></a><b>Can I link to adult material? Or use an <?php echo SITE_NAME; ?> link on an adult site?</b><br>
	You may not use <?php echo SITE_NAME; ?> on any adult sites.<br><br>
	<br>
	<h2>Advertisers</h2>
	<br>
	<a class="uc" name="1b"></a><b>How does this work?</b><br>
	<?php echo SITE_NAME; ?> is a quite simple, yet innovative service that allows you to get targeted visitors to your site.  All of our ads are full page ads that show before shortened links load.  The process is simple: someone clicks on one of our user's shortened links and your ad (which is actually your entire site or landing page) is loaded first, then the visitor can stay on your site, bookmark it, or choose to skip the ad and go to the shortened link.<br><br>
	<a class="uc" name="2b"></a><b>How much does it cost?</b><br>
	The minimum deposit is just $5.00 and get unique visits to your site from just $0.50 per 1000 visitors!<br><br>
	<a class="uc" name="3b"></a><b>How are ads targeted?</b><br>
	We can target ads by country.<br><br>
	<a class="uc" name="4b"></a><b>Can I advertise on <?php echo SITE_NAME; ?>?</b><br>
	Please view our <a class="uc" href="advertising.php">advertising section</a> for more information.<br><br>
</div>
<?php site_footer(); ?>