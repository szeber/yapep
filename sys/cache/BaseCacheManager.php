<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * File base cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_cache_BaseCacheManager implements sys_cache_CacheManager {

	/**
	 * Cache file's name
	 *
	 * @var string
	 */
	protected $cacheKey;

	/**
	 * Cache backend
	 *
	 * @var sys_cache_backend_ICacheBackend
	 */
	protected $backend;

	/**
	 * Configuration data
	 *
	 * @var sys_IApplicationConfiguration
	 */
	protected $config;

	/**
	 * Database access
	 *
	 * @var module_db_DbModule
	 */
	protected $db;

	/**
	 * @see sys_cache_CacheManager::clearCache()
	 *
	 */
	public function clearCache() {
		$this->backend->delete($this->cacheKey);
		if ($this->cacheEnabled()) {
			$this->recreateCache();
		}

	}
	/**
	 * @see sys_cache_CacheManager::recreateCache()
	 *
	 */
	public function recreateCache() {
		if ($this->cacheEnabled()) {
			$this->doRecreateCache();
		}
	}

	/**
	 * Constructor
	 *
	 * @param sys_IApplicationConfiguration $config
	 */
	final public function __construct(sys_IApplicationConfiguration $config = null) {
		if(is_null($config)) {
			$this->config = sys_ApplicationConfiguration::getInstance();
		} else {
			$this->config = $config;
		}
		$this->setCacheKey();
		$this->backend = sys_cache_CacheFactory::getCache('system');
		$this->loadCacheData();
		$this->getDb();
	}

	/**
	 * Sets the cache key
	 *
	 */
	abstract protected function setCacheKey();

	/**
	 * Makes database connection
	 *
	 */
	abstract protected function getDb();

	/**
	 * Loads data stored in cache
	 *
	 */
	abstract protected function loadCacheData();

	/**
	 * Does the job of recreating the cache
	 *
	 * @see recreateCache()
	 */
	abstract protected function doRecreateCache();

}
?>