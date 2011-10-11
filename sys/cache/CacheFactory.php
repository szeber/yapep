<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 410 $
 */

/**
 * Cache factory
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 410 $
 */

class sys_cache_CacheFactory {

    /**
     * Stores the cache instances
     *
     * @var array
     */
    protected static $caches;

    /**
     * The config instance
     *
     * @var sys_IApplicationConfiguration
     */
    protected static $config;

    /**
     * Returns a cache backend specified by the cache ID
     *
     * @param string $cacheId
     *
     * @return sys_cache_backend_ICacheBackend
     */
    public static function getCache($cacheId) {
        if (!isset(self::$caches[$cacheId])) {
            self::$caches[$cacheId] = self::makeCache($cacheId);
        }
        return self::$caches[$cacheId];
    }

    /**
     * Creates a cache backend with the configuration specified by the cache ID
     *
     * @param string $cacheId
     *
     * @return sys_cache_backend_ICacheBackend
     */
    protected static function makeCache($cacheId) {
        if (is_null(self::$config)) {
            self::$config = sys_ApplicationConfiguration::getInstance();
        }
        $cacheConfig = self::$config->getCache($cacheId);
        if ($cacheConfig) {
            // selected cache backend
            switch($cacheConfig['type']) {
                case 'memcache':
                    $cacheOptions = array('memcacheProjectPrefix' => self::$config->getOption('memcacheProjectPrefix'));
                    return new sys_cache_backend_Memcache($cacheConfig, $cacheId, $cacheOptions);
                    break;
            }
        } else {
            return new sys_cache_backend_File(array(), $cacheId);
        }
    }

    /**
     * Sets the configuration instance
     *
     * @param sys_IApplicationConfiguration $config
     */
    public static function setConfig(sys_IApplicationConfiguration $config) {
        self::$config = $config;
    }
}