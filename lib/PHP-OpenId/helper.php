<?php

$path_extra = LIB_DIR . 'PHP-OpenId/';
$path = ini_get ('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set ('include_path', $path);

/**
 * Require the OpenID consumer code.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require the "file store" module, which we'll need to store
 * OpenID information.
 */
require_once "Auth/OpenID/FileStore.php";

/**
 * Require the Simple Registration extension API.
 */
require_once "Auth/OpenID/SReg.php";

/**
 * Require the PAPE extension module.
 */
require_once "Auth/OpenID/PAPE.php";

global $pape_policy_uris;
$pape_policy_uris = array (PAPE_AUTH_MULTI_FACTOR_PHYSICAL, PAPE_AUTH_MULTI_FACTOR, PAPE_AUTH_PHISHING_RESISTANT);

/**
 * This is where the example will store its OpenID information.
 * You should change this path if you want the example store to be
 * created elsewhere.  After you're done playing with the example
 * script, you'll have to remove this directory manually.
 *
 * @return Auth_OpenID_FileStore
 */
function getStore() {
	$store_path = "/tmp/openid_consumer_data";

	if (!file_exists ($store_path) && !mkdir ($store_path)) {
		return false;
	}

	return new Auth_OpenID_FileStore ($store_path);
}
/**
 * Create a consumer object using the store object created
 * earlier.
 *
 * @return Auth_OpenID_Consumer
 */
function &getConsumer() {
	$store = getStore ();
	if (!$store) {
		return false;
	}
	$consumer = new Auth_OpenID_Consumer ($store);
	return $consumer;
}

/**
 * Determines whether we use HTTP or HTTPS
 *
 * @return return
 */
function getScheme() {
	$scheme = 'http';
	if (isset ($_SERVER ['HTTPS']) and $_SERVER ['HTTPS'] == 'on') {
		$scheme .= 's';
	}
	return $scheme;
}

?>