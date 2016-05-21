<?php
/*******************************************************************************
 * Constants file, contains all global defined constants
 *
 * @created     02/01/2011
 * @modified    04/26/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/

define('CONST_INC_PHP_INCLUDED', true);
 
/**
 * Config constants {{{
 */
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', '');
define('MYSQL_PASS', '');
define('MYSQL_DB', '');
define('TABLE_PREPEND', 'x3_');

define('SITE_VERSION', 		file_get_contents(dirname(__FILE__) . '/VERSION.NS'));
define('CURRENT_HOSTNAME', 	$_SERVER["SERVER_NAME"]);
/**
 * }}} Config constants
 */

/**
 * Table definitions {{{
 */
$_GLOBAL['TABLES'] = array(
	'ADMINS' 		=> TABLE_PREPEND . 'admins',
	'CONFIG' 		=> TABLE_PREPEND . 'config',
	'LINKS' 		=> TABLE_PREPEND . 'links',
	'USERS' 		=> TABLE_PREPEND . 'users',
	'NEWS'  		=> TABLE_PREPEND . 'news',
	'ANALYZER'  	=> TABLE_PREPEND . 'analyzer',
	'PACKAGES'  	=> TABLE_PREPEND . 'packages',
	'PAYOUTS'  		=> TABLE_PREPEND . 'payouts',
	'PAYOUTS_MADE'  => TABLE_PREPEND . 'payouts_made',
	'CAMPAIGNS'  	=> TABLE_PREPEND . 'campaigns',
	'TRANSACTIONS'  => TABLE_PREPEND . 'transactions'
);
/**
 * }}} Table definitions
 */