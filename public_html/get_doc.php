<?php
/**
 * Normal page display script
 *
 * @package YAPEP
 */

/**
 * Includes
 */
require_once ('../system/paths.php');
require_once (SYS_PATH . 'sys/session.php');
require_once (SYS_PATH . 'sys/autoload.php');
require_once (SYS_PATH . 'sys/utility_funcs.php');

/**
 * Load and display the page
 */
try {
	sys_Debugger::startTimer ();
	removeMagicQuotes ();
	$ph = new sys_PageManager ();
	$ph->preparePage ();
	$ph->renderPage ();
} catch ( Exception $e ) {
	if (DEBUGGING) {
		echo 'ERROR: ' . $e->getMessage ();
	} else {
		$handler = new sys_ErrorHandler ();
		$handler->handleError (500);
	}
}
?>