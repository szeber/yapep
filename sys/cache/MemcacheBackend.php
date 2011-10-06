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
class sys_cache_MemcacheBackend implements sys_cache_ICacheBackend {

    /**
     * The prefix to all keys
     *
     * @var string
     */
    protected $keyPrefix = '';

    /**
     * The memcache instance
     *
     * @var Memcache
     */
    protected $memcache;

    /**
     * Constructor
     *
     * @param array  $config      Configuration data
     * @param string $cacheName   Name of the cache setup
     * @param array  $options     Other options
     */
    public function __construct(array $config, $cacheName, array $options = array()) {
        $this->memcache = new Memcache();
        $this->memcache->connect($config['host'], $config['port']);
        $this->keyPrefix = $cacheName;
        if (isset($options['memcacheProjectPrefix']) && $options['memcacheProjectPrefix']) {
            $this->keyPrefix = $options['memcacheProjectPrefix'] . '.' . $this->keyPrefix;
        }
    }

    /**
     * Stores data in the cache under the specified key
     *
     * @param string $key
     * @param mixed  $data
     * @param string $facility
     * @param int    $ttl     The expiration time of the data in seconds (if supported by the backend)
     */
    public function set($key, $data, $facility = '', $ttl = 0) {
        $this->memcache->set($this->makeKey($key, $facility), $data, 0, $ttl);
    }

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @param string $key
     * @param string $facility
     *
     * @return mixed
     */
    public function get($key, $facility = '') {
        return $this->memcache->get($this->makeKey($key, $facility));
    }

    /**
     * Deltes the cache data specified by the key
     *
     * @param string $key
     * @param string $facility
     */
    public function delete($key, $facility = '') {
        $this->memcache->delete($this->makeKey($key, $facility), 0);
    }

    /**
     * Makes the key used to store the data
     *
     * @param string $key
     * @param string $facility
     *
     * @return string   The memcache key
     */
    protected function makeKey($key, $facility = '') {
        if ($facility) {
            $key = $facility . '.' . $key;
        }
        return $this->keyPrefix . '.' . $key;
    }
}