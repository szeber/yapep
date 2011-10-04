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
 * Smarty cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_SmartyCacheManager extends sys_cache_DummyCacheManager {

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {
		return (CACHING && $this->config->getOption('smartyCache'));
	}

	/**
	 * @see sys_cache_DummyCacheManager
	 *
	 * @return string
	 */
	protected function getCachePath() {
		return $this->config->getPath('smartyCacheDir');
	}

}
?>