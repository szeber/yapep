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
 * System configuration cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_SysConfigCacheManager extends sys_cache_FileCacheManager {

	/**
	 * Cache data
	 *
	 * @var array
	 */
	private static $cacheData = null;

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {
		return true;
	}

	/**
	 * @see sys_cache_FileCacheManager::doRecreateCache()
	 *
	 */
	protected function doRecreateCache() {
		$variables=$this->db->getSysVariables();
		$configData=array();
		$defines='';
		foreach($variables as $val) {
			if (is_object($va)) {
				$val=$val->toArray();
			}
			$defines .= "define(".var_export($val['name'],true).", ".var_export($val['value'],true).");\n";
			$configData[$val['name']] = $val['value'];
		}
		self::$cacheData = $configData;
		file_put_contents($this->cacheFile, "<?php\n$defines\$cache = ".var_export(self::$cacheData, true).";\n?>");
	}

	/**
	 * Returns a database access object
	 *
	 * @return module_db_UserAuth
	 */
	protected function getDb() {
		$this->db = getPersistClass('SysConfig');
	}

	/**
	 * @see sys_cache_FileCacheManager::loadCacheData()
	 *
	 */
	protected function loadCacheData() {
		if (is_null(self::$cacheData) && $this->cacheEnabled() && file_exists($this->cacheFile)) {
			include($this->cacheFile);
			self::$cacheData=$cache;
		}
	}

	/**
	 * @see sys_cache_FileCacheManager::setCacheFile()
	 *
	 */
	protected function setCacheFile() {
		$this->cacheFile = CACHE_DIR.'cms/sysConfigCache.php';
	}

}
?>