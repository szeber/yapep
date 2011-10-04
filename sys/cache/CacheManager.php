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
 * Cache manager interface
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_cache_CacheManager {

	/**
	 * Clears the cache
	 *
	 */
	public function clearCache();

	/**
	 * Reads all information for the cache, and creates it
	 *
	 */
	public function recreateCache();

	/**
	 * Checks if this cache is enabled
	 *
	 * @return boolean True if it's enabled, false if not
	 */
	public function cacheEnabled();

}
?>