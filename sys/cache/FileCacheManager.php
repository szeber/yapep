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
abstract class sys_cache_FileCacheManager implements sys_cache_CacheManager {

	/**
	 * Cache file's name
	 *
	 * @var string
	 */
	protected $cacheFile;

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
		unlink($this->cacheFile);
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
		$this->setCacheFile();
		$this->loadCacheData();
		$this->getDb();
	}

	/**
	 * Sets the cache file name
	 *
	 */
	abstract protected function setCacheFile();

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