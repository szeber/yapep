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
 * Dummy cache manager base
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_cache_DummyCacheManager implements sys_cache_CacheManager {

	/**
	 * The permissions for the directory if it's going to be recreated
	 *
	 * @var integer
	 */
	protected $dirMode = 0700;

	/**
	 *
	 * @var sys_IApplicationConfiguration
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param sys_IApplicationConfiguration $conifg
	 */
	public function __construct(sys_IApplicationConfiguration $config = null) {
		if (is_null($config)) {
			$this->config = sys_ApplicationConfiguration::getInstance();
		} else {
			$this->config = $config;
		}
	}

	/**
	 * @see sys_cache_CacheManager::clearCache()
	 *
	 */
	public function clearCache() {
		recursiveDelete($this->getCachePath(), false);
	}

	/**
	 * @see sys_cache_CacheManager::recreateCache()
	 *
	 */
	public function recreateCache() {
		$this->clearCache();
		if (!file_exists($this->getCachePath())) {
			mkdir($this->getCachePath(),$this->dirMode);
		}
	}

	/**
	 * Returns the path to the cache
	 *
	 * @return string
	 */
	abstract protected function getCachePath();

}
?>