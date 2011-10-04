<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname (dirname (dirname (__FILE__))) . '/system/paths.php';
require_once SYS_PATH . 'sys/autoload.php';
require_once SYS_PATH . 'sys/session.php';
require_once SYS_PATH . 'sys/utility_funcs.php';
require_once SYS_PATH . 'test/helper/TestAutoload.php';
require_once SYS_PATH . 'test/helper/TestDatabase.php';

/**
 * sys_ThemeManager test case.
 */
class sys_ThemeManagerTest extends PHPUnit_Framework_TestCase implements test_helper_IDbCallback {

	protected $selectResult;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		$this->selectResult = null;
		sys_db_TestDatabase::setCallbackHandler($this);
		sys_LibFactory::setConfig(new mock_ApplicationConfigurationMock($this));
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
		sys_db_TestDatabase::setCallbackHandler();
		sys_LibFactory::setConfig();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {}

	/**
	 * Tests sys_ThemeManager::changeTheme()
	 */
	public function testChangeTheme() {
		$this->selectResult = array(array('id'=>1));
		$origTheme = sys_ThemeManager::getTheme(new mock_ApplicationConfigurationMock($this));
		sys_ThemeManager::changeTheme($origTheme+1);
		$newTheme = sys_ThemeManager::getTheme(new mock_ApplicationConfigurationMock($this));
		$this->assertNotEquals($origTheme, $newTheme, 'The theme has been changed');
	}

	/**
	 * Tests sys_ThemeManager::getTheme()
	 */
	public function testGetTheme() {
		$this->selectResult = array(array('id'=>1));
		$theme = sys_ThemeManager::getTheme(new mock_ApplicationConfigurationMock($this));
		$this->assertType('integer', $theme, 'Theme ID is an integer');
		$this->assertGreaterThan(0, $theme, 'Theme ID is greater then 0');
	}

	/**
	 * @see test_helper_IDbCallback::dbDelete()
	 *
	 * @param string $table
	 * @param string $where
	 * @param integer $limit
	 * @return boolean
	 */
	public function dbDelete($table, $where, $limit) {}

	/**
	 * @see test_helper_IDbCallback::dbExecute()
	 *
	 * @param string $cmd
	 * @param integer $cache
	 * @param integer $limit
	 * @param integer $offset
	 * @return mixed
	 */
	public function dbExecute($cmd, $cache, $limit, $offset) {}

	/**
	 * @see test_helper_IDbCallback::dbInsert()
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $values
	 * @return boolean
	 */
	public function dbInsert($table, $fields, $values) {}

	/**
	 * @see test_helper_IDbCallback::dbSelect()
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $where
	 * @param string $order_by
	 * @param string $more
	 * @param integer $limit
	 * @param integer $offset
	 * @param integer $cache
	 * @return array
	 */
	public function dbSelect($table, $fields, $where, $order_by, $more, $limit, $offset, $cache) {
		return $this->selectResult;
	}

	/**
	 * @see test_helper_IDbCallback::dbUpdate()
	 *
	 * @param string $table
	 * @param string $set
	 * @param string $where
	 * @param integer $limit
	 * @return boolean
	 */
	public function dbUpdate($table, $set, $where, $limit) {}

}