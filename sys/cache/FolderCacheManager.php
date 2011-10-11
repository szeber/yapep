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
 * Folder cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_FolderCacheManager extends sys_cache_BaseCacheManager {

	const WITHOUT_SUBFOLDER=0;
	const WITH_SUBFOLDER=1;
	const WITH_SUBFOLDER_RECURSIVE=2;

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
	 * Database
	 *
	 * @var module_db_generic_Page
	 */
	protected $db;

	/**
	 * @see sys_cache_FileCacheManager::doRecreateCache()
	 *
	 */
	protected function doRecreateCache() {
		self::$cacheData=array();
		$locales=$this->queryLocales();
		foreach($locales as $val) {
			$folder=new sys_cache_FolderCacheItem(null);
			$folder->locale_id=$val['id'];
			$folder->loadFolderData();
			self::$cacheData[$val['id']]=$folder;
		}
		$this->backend->set($this->cacheKey, self::$cacheData);
	}


	/**
	 * Runs a database query to get all the languages for the site, and returns them
	 *
	 * @return array
	 */
	private function queryLocales() {
		$langdb=getPersistClass('LangLocale');
		return $langdb->getLocales();
	}

	/**
	 * Returns a database access object
	 *
	 * @return module_db_UserAuth
	 */
	protected function getDb() {
		$this->db = getPersistClass('Folder');
	}

	/**
	 * @see sys_cache_CacheManagerBase::setCacheFile()
	 *
	 */
	protected function setCacheKey() {
		$this->cacheKey = 'folderCache';
	}

	/**
	 * @see sys_cache_CacheManagerBase::loadCacheData()
	 */
	protected function loadCacheData() {
		if (is_null(self::$cacheData) && $this->cacheEnabled()) {
			self::$cacheData=$this->backend->get($this->cacheKey);
		}
	}

	/**
	 * Returns a folder by it's document path and language from cache
	 *
	 * @param integer $lang
	 * @param array $pathArr
	 * @return array
	 */
	public function getFolder($lang, &$pathArr, $withSubfolders=self::WITHOUT_SUBFOLDER) {
		if (!reset($pathArr)) {
			array_shift($pathArr);
		}
		if ($this->cacheEnabled()) {
			return $this->getCachedFolder($lang, $pathArr, $withSubfolders);
		}
		return $this->getDbFolder($lang, $pathArr, $withSubfolders);
	}

	/**
	 * Returns a folder by it's document path and language from cache
	 *
	 * @param string $lang
	 * @param array $pathArr
	 * @return array
	 */
	private function getCachedFolder($lang, &$pathArr, $withSubfolders=self::WITHOUT_SUBFOLDER) {
		if (!isset(self::$cacheData[$lang])) {
			throw new sys_exception_SiteException('404 Not found', 404);
		}
		return self::$cacheData[$lang]->getFolder($pathArr, $withSubfolders);
	}

	/**
	 * Returns a folder by it's document path and language from cache
	 *
	 * @param string $lang
	 * @param array $pathArr
	 * @return array
	 */
	private function getDbFolder($lang, &$pathArr, $withSubfolders=self::WITHOUT_SUBFOLDER) {
		//FIXME IMPLEMENT
		throw new Exception('NOT IMPLEMENTED YET!');
	}

}
?>