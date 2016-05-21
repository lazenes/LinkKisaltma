<?php
/*******************************************************************************
 * Initiate global usage
 *
 * @created     02/01/2011
 * @modified    02/26/2011
 * @program     URL Shortener
 * @author      Nadeem Syed <nsyed19@gmail.com>
 ******************************************************************************/
 
//error_reporting(E_ALL ^ (E_NOTICE ^ E_WARNING));

define('GLOBAL_PHP_INCLUDED', true);
define('__EXEC_START__', microtime());
define('ROOT_PATH', dirname(__FILE__));

require_once ROOT_PATH . '/inc/const.inc.php';
require_once ROOT_PATH . '/inc/database.class.php';

System::setDB(new MySQL(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB));
if ($config = System::getDB()->getRows($_GLOBAL['TABLES']['CONFIG'])) {
	foreach ($config as $c) define($c['key'], $c['value']);
}

require_once ROOT_PATH . '/inc/utilities.class.php';
require_once ROOT_PATH . '/inc/system.class.php';
require_once ROOT_PATH . '/inc/user.class.php';
require_once ROOT_PATH . '/template.php';

$_UID = $_COOKIE['UID'] ? $_COOKIE['UID'] : 0;

System::setUser(new User($_GLOBAL['TABLES']['USERS'], $_UID, System::getDB()));

$H_TEMPLATE['PAGE_TITLE'] = SITE_PAGE_TITLE;