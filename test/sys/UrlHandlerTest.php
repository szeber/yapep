<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname (dirname (dirname (__FILE__))) . '/system/paths.php';
require_once SYS_PATH . 'sys/autoload.php';
require_once SYS_PATH . 'sys/utility_funcs.php';
require_once SYS_PATH . 'test/helper/TestAutoload.php';

/**
 * sys_UrlHandler test case.
 */
class sys_UrlHandlerTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var sys_UrlHandler
	 */
	private $sys_UrlHandler;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();

		// TODO Auto-generated sys_UrlHandlerTest::setUp()


//		$this->sys_UrlHandler = new sys_UrlHandler(/* parameters */);

	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated sys_UrlHandlerTest::tearDown()


//		$this->sys_UrlHandler = null;

		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {	// TODO Auto-generated constructor
	}

	/**
	 * Tests sys_UrlHandler->__construct()
	 */
	public function test__construct() {
		// TODO Auto-generated sys_UrlHandlerTest->test__construct()
		$this->markTestIncomplete ("__construct test not implemented");

		$this->sys_UrlHandler->__construct(/* parameters */);

	}

	/**
	 * Tests sys_UrlHandler->getFolderInfo()
	 */
	public function testGetFolderInfo() {
		// TODO Auto-generated sys_UrlHandlerTest->testGetFolderInfo()
		$this->markTestIncomplete ("getFolderInfo test not implemented");

		$this->sys_UrlHandler->getFolderInfo(/* parameters */);

	}

}

