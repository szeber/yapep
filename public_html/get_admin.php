<?php
/**
 * Admin page and XML display script
 *
 * @package YAPEP
 * @subpackage Admin
 */

/**
 * Includes
 */
require_once ('../system/paths.php');
require_once (SYS_PATH . 'sys/session.php');
require_once (SYS_PATH . 'sys/autoload.php');
require_once (SYS_PATH . 'sys/utility_funcs.php');

/**
 * Load and display the page or XML
 */
try {
	removeMagicQuotes();
	$manager = new sys_admin_AdminManager();
	$manager->getAdmin();
} catch (Exception $e) {
	echo 'ERROR: ' . $e->getMessage ();
}
?>