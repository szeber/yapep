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
 * Database cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_DbCacheManager extends sys_cache_DummyCacheManager {

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {
		return (CACHING && $this->config->getOption('dbCache'));
	}

	/**
	 * @see sys_cache_DummyCacheManager
	 *
	 * @return string
	 */
	protected function getCachePath() {
		return $this->config->getPath('dbCacheDir');
	}

	/**
	 * Returns the results of a cached query, or false if it's not cached
	 *
	 * @param string $sql
	 * @param array $params
	 * @param string $dir
	 * @param string $connection
	 * @return array The results of false if it's not found
	 */
	public static function getCachedQuery($sql, array $params, $dir, $connection) {
		$cacheFile=$dir.self::makeCacheName($sql, $params, $connection);
		if (!file_exists($cacheFile)) {
			return false;
		}
		include ($cacheFile);
		if (!$cacheData || $cacheData['expire']<time() || !is_array($cacheData['result'])) {
			unlink($cacheFile);
			return false;
		}
		if ($cacheData['query']!=$sql || $cacheData['params'] != $params || $cacheData['connection']!=$connection) {
			return false;
		}
		return $cacheData['result'];
	}

	/**
	 * Saves the specified query and it's results in the cache
	 *
	 * @param string $sql
	 * @param array $params
	 * @param array $results
	 * @param string $dir
	 * @param string $connection
	 * @param integer $timeout
	 * @return boolean True on success, false on failure
	 */
	public static function saveCachedQuery($sql, array $params, array $results, $dir, $connection, $timeout) {
		$cacheData=array('query'=>$sql, 'params'=>$params, 'connection'=>$connection, 'expire'=>(time()+$timeout), 'result'=>$results);
		$cacheInfo='<?php $cacheData='.var_export($cacheData, true).'; ?>';
		$cacheFile=$dir.self::makeCacheName($sql, $params, $connection);
		if (file_exists($cacheFile)) {
			unlink($cacheFile);
		} elseif (!is_dir(dirname($cacheFile))) {
			if (!mkdir(dirname($cacheFile), 0700, true)) {
				return false;
			}
		}
		return (bool)file_put_contents($cacheFile, $cacheInfo);
	}

	/**
	 * Generates a cache file name
	 *
	 * @param string $sql
	 * @param array $params
	 * @param string $connection
	 * @return string
	 */
	protected static function makeCacheName($sql, array $params, $connection) {
		$name=md5($sql.serialize($params).$connection);
		$dir1=substr($name, 0, 1);
		$dir2=substr($name, 1, 1);
		return $dir1.'/'.$dir2.'/'.$name;
	}

}
?>