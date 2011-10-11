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
 * Cache backend interface
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 410 $
 */
interface sys_cache_ICacheBackend {
    /**
     * Constructor
     *
     * @param array  $config      Configuration data
     * @param string $cacheName   Name of the cache setup
     * @param array  $options     Other options
     */
    public function __construct(array $config, $cacheName, array $options = array());

    /**
     * Stores data in the cache under the specified key
     *
     * @param string $key
     * @param mixed  $data
     * @param string $facility
     * @param int    $ttl     The expiration time of the data in seconds (if supported by the backend)
     */
    public function set($key, $data, $facility = '', $ttl = 0);

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @param string $key
     * @param string $facility
     *
     * @return mixed
     */
    public function get($key, $facility = '');

    /**
     * Deltes the cache data specified by the key
     *
     * @param string $key
     * @param string $facility
     */
    public function delete($key, $facility = '');
}