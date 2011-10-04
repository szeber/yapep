<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname (dirname (dirname (__FILE__))) . '/system/paths.php';
require_once SYS_PATH . 'sys/autoload.php';
require_once dirname(dirname(__FILE__)).'/mock/ApplicationConfigurationMock.php';
require_once dirname(dirname(__FILE__)).'/helper/TestDatabase.php';

/**
 * sys_LibFactory test case.
 */
class sys_LibFactoryTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var mock_ApplicationConfigurationMock
	 */
	private $config;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		sys_LibFactory::setConfig(new mock_ApplicationConfigurationMock($this));
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
		$this->config = null;
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {}

	/**
	 * Tests sys_LibFactory::getDbConnection()
	 */
	public function testGetDbConnectionReturnsDbInstance() {
		$this->assertType('sys_db_TestDatabase', sys_LibFactory::getDbConnection('site'));
	}

	public function testGetDbConnectionReturnsReferenceForSameConnection() {
		$this->assertSame(sys_LibFactory::getDbConnection('site'), sys_LibFactory::getDbConnection('site'));
	}

	public function testGetDbConnectionReturnsDifferentObjectForDifferentConnection() {
		$this->assertNotSame(sys_LibFactory::getDbConnection('site'), sys_LibFactory::getDbConnection('site2'));
	}

	/**
	 * Tests sys_LibFactory::getMailer()
	 */
	public function testGetMailerReturnsPhpMailer() {
		$this->assertType ('PHPMailer', sys_LibFactory::getMailer (), 'Returns PHPMailer instance');
	}

	public function testGetMailerReturnsClonedObject() {
		$this->assertNotSame (sys_LibFactory::getMailer (), sys_LibFactory::getMailer (), 'Returns cloned PHPMailer');
	}

	/**
	 * Tests sys_LibFactory::getSmarty()
	 */
	public function testGetSmartyReturnsSmartyInstance() {
		$this->assertType ('Smarty', sys_LibFactory::getSmarty (), 'Returns Smarty instance');
	}

	public function testGetSmartyReturnsClonedObject() {
		$this->assertNotSame (sys_LibFactory::getSmarty (), sys_LibFactory::getSmarty (), 'Returns only copies');
	}

	/**
	 * Tests sys_LibFactory::getSmartyProto()
	 */
	public function testGetSmartyProtoReturnsSmartyInstance() {
		$this->assertType ('Smarty', sys_LibFactory::getSmartyProto (), 'Returns Smarty instance');
	}

	public function testGetSmartyProtoReturnsPrototypeOfSmarty() {
		$this->assertSame (sys_LibFactory::getSmartyProto (), sys_LibFactory::getSmartyProto (), 'Returns the prototype Smarty instance');
	}
}