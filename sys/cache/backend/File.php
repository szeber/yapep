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
class sys_cache_backend_File implements sys_cache_backend_ICacheBackend {

    protected $cacheName;

    /**
     * Constructor
     *
     * @param array  $config      Configuration data
     * @param string $cacheName   Name of the cache setup
     * @param array  $options     Other options
     */
    public function __construct(array $config, $cacheName, array $options = array()) {
        $this->cacheName = $cacheName;
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
        $cacheData = array();
        if ($ttl > 0) {
            $cacheData['expiration'] = time() + $ttl;
        }
        $cacheData['data'] = $data;
        $fileName = $this->makeFileName($key, $facility);
        $fileBaseDir = dirname($fileName);
        if (!file_exists($fileBaseDir)) {
            mkdir($fileBaseDir, 0755, true);
        }
        file_put_contents($fileName, serialize($cacheData));
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
        $fileName = $this->makeFileName($key, $facility);
        if (!file_exists($fileName)) {
            return false;
        }
        $data = unserialize(file_get_contents($fileName));
        if (isset($data['expiration']) && time() < $data['expiration']) {
            unlink($fileName);
            return false;
        }
        return $data['data'];
    }

    /**
     * Deltes the cache data specified by the key
     *
     * @param string $key
     * @param string $facility
     */
    public function delete($key, $facility = '') {
        unlink($this->makeFileName($key, $facility));
    }

    /**
     * Makes the filename used to store the cache data
     *
     * @param string $key
     * @param string $facility
     *
     * @return string   The filename
     */
    protected function makeFileName($key, $facility = '') {
        if ($facility) {
            $key = $facility . '/' . $key;
        }
        return CACHE_DIR . $this->cacheName . '/' . $key . '.php';
    }

    /**
     * Returns if the backend is volatile, or stores data persistently.
     *
     * Used by the system caches, which will recache automatically, if the cache data is missing,
     * and the backend is volatile.
     *
     * @return bool
     */
    public function isVolatile() {
        // File based backend is persistent, we should not recache automatically if the data is missing.
        return false;
    }
}