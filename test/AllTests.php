<?php

require_once 'PHPUnit/Framework/TestSuite.php';
require_once dirname (dirname (__FILE__)) . '/system/paths.php';
require_once SYS_PATH . 'sys/autoload.php';
require_once SYS_PATH . 'test/helper/TestAutoload.php';

/**
 * Static test suite.
 */
class AllTests extends PHPUnit_Framework_TestSuite {

	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ('AllTests');

		$this->addTestSuite ('FrontendTests');

	}

	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

