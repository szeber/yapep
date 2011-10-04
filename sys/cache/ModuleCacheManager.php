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
 * Module cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_ModuleCacheManager extends sys_cache_FileCacheManager {

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
		$tmp = $this->db->getModuleData();
		$modules=array();
		foreach($tmp as $val) {
			$modules[$val['id']]=array('id'=>$val['id'],'name'=>$val['name'],'cache_type'=>$val['cache_type'],'cache_expire'=>$val['cache_expire']);

			$params=array();
			foreach($val['Params'] as $val2) {
				$params[$val2['name']]=array('name'=>$val2['name'], 'default_value'=>$val2['default_value'], 'default_is_variable'=>$val2['default_is_variable']);
			}
			$modules[$val['id']]['params']=$params;
		}
		self::$cacheData = $modules;
		file_put_contents($this->cacheFile, "<?php\n\$cache = ".var_export(self::$cacheData, true).";\n?>");
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
	 * Returns a database access object
	 *
	 * @return module_db_UserAuth
	 */
	protected function getDb() {
		$this->db = getPersistClass('Module');
	}

	/**
	 * @see sys_cache_FileCacheManager::setCacheFile()
	 *
	 */
	protected function setCacheFile() {
		$this->cacheFile = CACHE_DIR.'cms/moduleCache.php';
	}

	public function getModule($id) {
		return self::$cacheData[$id];
	}

}
?>