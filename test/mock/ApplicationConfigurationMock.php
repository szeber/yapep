<?php
class mock_ApplicationConfigurationMock implements sys_IApplicationConfiguration {

	/**
	 * @var sys_ApplicationConfiguration
	 */
	protected $config;

	/**
	 * @var PHPUnit_Framework_TestCase
	 */
	protected $test;

	public function __construct($test) {
		$this->test = $test;
		$this->config = sys_ApplicationConfiguration::getInstance();
	}

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {}

	/**
	 * @see sys_cache_CacheManager::clearCache()
	 *
	 */
	public function clearCache() {}

	/**
	 * @see sys_cache_CacheManager::recreateCache()
	 *
	 */
	public function recreateCache() {}

	/**
	 * @see sys_IApplicationConfiguration::deleteProperty()
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function deleteProperty($name) {
		return $this->config->deleteProperty($name);
	}

	/**
	 * @see sys_IApplicationConfiguration::getDatabase()
	 *
	 * @param string $name
	 * @return array
	 */
	public function getDatabase($name) {
		return array ('connectionId' => $name, 'type' => 'test', 'server' => 'test', 'user' => 'test', 'password' => 'test', 'host' => 'localhost', 'dbName' => 'test', 'options' => '', 'charset' => 'utf8', 'dsn' => 'test://test:test@localhost/test');
	}

	/**
	 * @see sys_IApplicationConfiguration::getInstance()
	 *
	 * @return sys_IApplicationConfiguration
	 */
	public static function getInstance() {}

	/**
	 * @see sys_IApplicationConfiguration::getOption()
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getOption($name) {
		return $this->config->getOption($name);
	}

	/**
	 * @see sys_IApplicationConfiguration::getPath()
	 *
	 * @param string $name
	 * @return string
	 */
	public function getPath($name) {
		return $this->config->getPath($name);
	}

	/**
	 * @see sys_IApplicationConfiguration::getProperty()
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getProperty($name) {
		return $this->config->getProperty($name);
	}

	/**
	 * @see sys_IApplicationConfiguration::setProperty()
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setProperty($name, $value) {
		return $this->config->setProperty($name, $value);
	}

}
?>