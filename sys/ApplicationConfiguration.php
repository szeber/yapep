<?php
/**
 * This file is part of YAPEP.
 *
 * @package YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * ApplcationConfiguration class
 *
 * Singleton class to read, store and provide configuration data
 *
 * @package YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_ApplicationConfiguration implements sys_IApplicationConfiguration {

	/**
	 * Singleton instance
	 *
	 * @var sys_ApplicationConfiguration
	 */
	private static $INSTANCE = null;

	/**
	 * If set to true it disables the XML cache.
	 *
	 * @var boolean
	 */
	public static $disableCache = false;

	private $constants = array(

	);

	/**
	 * Configuration data
	 *
	 * @var array
	 */
	private $globalProperties = array(

	);

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {
		return true;
	}

	/**
	 * @see sys_cache_CacheManager::clearCache()
	 *
	 */
	public function clearCache() {
		unlink(CACHE_DIR . 'cms/applicationConfiguration.php', 'wb');
	}
	/**
	 * Singleton private constructor
	 *
	 */
	private function __construct() {
		$this->globalProperties['Properties'] = array(

		);
		$this->loadConfig();
	}

	/**
	 * Loads configuration data from cache or XML
	 *
	 */
	private function loadConfig() {
		if(!self::$disableCache && file_exists(CACHE_DIR . 'cms/applicationConfiguration.php') && filemtime(PROJECT_PATH . 'system/settings.xml') < filemtime(CACHE_DIR . 'cms/applicationConfiguration.php')) {
			include_once (CACHE_DIR . 'cms/applicationConfiguration.php');
			$this->globalProperties = $config;
			$this->constants = $constants;
		} else {
			$this->processXML();
		}
	}

	/**
	 * Creates or overwrites the configuration cache
	 *
	 * @return boolean True on success, false on failure
	 */
	public function recreateCache() {
		$this->processXML();
		return $this->saveCache();
	}

	/**
	 * Saves the configuration data to the cache file
	 *
	 * @return boolean True on success, false on failure
	 */
	protected function saveCache() {
		$this->createCacheDir();
		$file = fopen(CACHE_DIR . 'cms/applicationConfiguration.php', 'wb');
		if(!$file) {
			return false;
		}
		$content = "<?php\n";
		$constants = "\$constants=array(";
		foreach($this->constants as $key => $val) {
			$content .= "define('" . addslashes($key) . "', " . var_export($val, true) . ");\n";
			$constants .= "'" . addslashes($key) . "' => " . var_export($val, true) . ", ";
		}
		$constants .= ");\n";
		$content .= $constants;
		$props=$this->globalProperties;
		$props['Properties']=array();
		$content .= "\$config=" . var_export($props, true) . ";\n ?>";
		fwrite($file, $content);
		fclose($file);
		// The file could be chmodded but if it is, that could cause problems with some setups,
		// so if you want to secure the setup information just delete the cms directory,
		// and the system will create it chmodded to 0700.
		return true;
	}

	/**
	 * Reads contents of the XML configuration file and saves it in the cache
	 *
	 */
	private function processXML() {
		$xml = simplexml_load_file(PROJECT_PATH . 'system/settings.xml');
		$result = $xml->xpath("//Setup[@site='general']");
        $node = reset($result);
        if ($node instanceof SimpleXMLElement) {
            $this->loadElementsFromXML($node);
        }
        $result = $xml->xpath("//Setup[@site='".SITE."']");
        $node = reset($result);
        if ($node instanceof SimpleXMLElement) {
            $this->loadElementsFromXML($node);
        }
		if (!self::$disableCache) {
			$this->saveCache();
		}
	}

	/**
	 * Creates cache directory if it doesn't exist
	 *
	 */
	private function createCacheDir() {
		if(!is_dir(CACHE_DIR . '/cms')) {
			mkdir(CACHE_DIR . '/cms', 0700);
		}
	}

	/**
	 * Processes the XML file contents and saves it in the globalProerties array
	 *
	 * @param SimpleXMLElement $xml
	 */
	private function loadElementsFromXML(SimpleXMLElement &$xml) {
		if(isset($xml->Databases)) {
			foreach($xml->Databases->Database as $database) {
				$tmp = array(

				);
				foreach($database->attributes() as $key => $val) {
					$tmp[$key] = (string) $val;
				}
				$tmp['dsn'] = rawurlencode($tmp['server']) . '://' . rawurlencode($tmp['user']) . ':' . rawurlencode($tmp['password']);
				$tmp['dsn'] .= '@' . rawurlencode($tmp['host']) . '/' . rawurlencode($tmp['dbName']);
				if(!empty($tmp['options'])) {
					$tmp['dsn'] .= '?' . $tmp['options'];
				}
				$this->globalProperties['Databases'][$tmp['connectionId']] = $tmp;
			}
		}
		if(isset($xml->Caches)) {
			foreach($xml->Caches->Cache as $cache) {
				$tmp = array(

				);
				foreach($cache->attributes() as $key => $val) {
					$tmp[$key] = (string) $val;
				}
				$this->globalProperties['Caches'][$tmp['cacheId']] = $tmp;
			}
		}
		if(isset($xml->Options)) {
			foreach($xml->Options->Option as $option) {
				switch((string) $option['type']) {
					case 'bool':
					case 'boolean':
						if($option['value'] == 'true' || $option['value'] == 'TRUE' || $option['value'] == '1') {
							$this->globalProperties['Options'][(string) $option['name']] = true;
						} else {
							$this->globalProperties['Options'][(string) $option['name']] = false;
						}
						break;
					case 'array':
						$this->globalProperties['Options'][(string) $option['name']] = (array) $option['value'];
						break;
					case 'object':
						$this->globalProperties['Options'][(string) $option['name']] = (object) $option['value'];
						break;
					case 'binary':
					case 'string':
						$this->globalProperties['Options'][(string) $option['name']] = (string) $option['value'];
						break;
					case 'int':
					case 'integer':
						$this->globalProperties['Options'][(string) $option['name']] = (int) $option['value'];
						break;
					case 'float':
					case 'double':
					case 'real':
						$this->globalProperties['Options'][(string) $option['name']] = (float) $option['value'];
						break;
					default:
						throw new Exception('Bad type in option list in XML');
						break;
				}
				// check if we need to also define this as a constant
				if(isset($option['define']) && 'true' == $option['define'] && !defined(strtoupper((string) $option['name']))) {
					$this->defineConstant(strtoupper((string) $option['name']), $this->globalProperties['Options'][(string) $option['name']]);
				}
			}
		}
		if (isset($this->globalProperties['Options']['debugging'])) {
			$this->defineConstant('DEBUGGING', $this->globalProperties['Options']['debugging']);
		}
		if (isset($this->globalProperties['Options']['caching'])) {
			$this->defineConstant('CACHING', $this->globalProperties['Options']['caching']);
		}
		if(isset($xml->Paths)) {
			foreach($xml->Paths->Path as $path) {
				if(!isset($path['parent']) || (string) $path['parent'] == '') {
					$this->globalProperties['Paths'][(string) $path['name']] = (string) $path['value'];
				} else {
					$this->globalProperties['Paths'][(string) $path['name']] = constant((string) $path['parent']) . (string) $path['value'];

				}
				// check if we need to also define this as a constant
				if(isset($path['define']) && 'true' == $path['define'] && !defined(strtoupper((string) $path['name'].'_DIR'))) {
					$this->defineConstant(strtoupper((string) $path['name'].'_DIR'), $this->globalProperties['Paths'][(string) $path['name']]);
				}
			}
		}
		if (isset($this->globalProperties['Paths']['lib'])) {
			$this->defineConstant('LIB_DIR', $this->globalProperties['Paths']['lib']);
		}
	}

	/**
	 * Defines a constant
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	private function defineConstant($name, $value) {
		$this->constants[$name] = $value;

	    if (!defined($name)) {
	        define($name, $value);
	    }
	}

	/**
	 * Returns the singleton instance
	 *
	 * @return sys_ApplicationConfiguration
	 */
	public static function getInstance() {
		if(self::$INSTANCE == null) {
			self::$INSTANCE = new sys_ApplicationConfiguration();
		}

		return self::$INSTANCE;
	}

	/**
	 * Saves a property to the configuration data
	 *
	 * @param string $name Name of the property
	 * @param mixed $value value of the property
	 */
	public function setProperty($name, $value) {
		$this->globalProperties['Properties'][$name] = $value;
	}

	/**
	 * Reads a property previously set by setProperty from the configuration data
	 *
	 * @param string $name Name of the property
	 * @return mixed Value of the property or NULL if not found
	 */
	public function getProperty($name) {
		if(isset($this->globalProperties['Properties'][$name])) {
			return $this->globalProperties['Properties'][$name];
		}
		return null;
	}

	/**
	 * Deletes a property previously set by setProperty
	 *
	 * @param string $name Name of the property
	 * @return boolean True on success, false if property is not found
	 */
	public function deleteProperty($name) {
		if(isset($this->globalProperties['Properties'][$name])) {
			unset($this->globalProperties['Properties'][$name]);
			return true;
		}
		return false;
	}

	/**
	 * Reads an option from the configuration data set by the XML file
	 *
	 * @param string $name
	 * @return mixed Value of the option or NULL if not found
	 */
	public function getOption($name) {
		if(isset($this->globalProperties['Options'][$name])) {
			return $this->globalProperties['Options'][$name];
		}
		return null;
	}

	/**
	 * Returns a database's configuration information
	 *
	 * @param string $name Name of the database
	 * @return array The database configuration information
	 */
	public function getDatabase($name) {
		if(isset($this->globalProperties['Databases'][$name])) {
			return $this->globalProperties['Databases'][$name];
		}
		return null;
	}

	/**
	 * Returns a cache's configuration information
	 *
	 * @param string $name The name of the cache
	 * @return array|bool The cache's configuration information or FALSE if it's not set
	 */
	public function getCache($name) {
		if(isset($this->globalProperties['Caches'][$name])) {
			return $this->globalProperties['Caches'][$name];
		}
		return null;
	}


	/**
	 * Returns a path
	 *
	 * @param string $name The name of the path
	 * @return string The path or NULL if not found
	 */
	public function getPath($name) {
		if(isset($this->globalProperties['Paths'][$name])) {
			return $this->globalProperties['Paths'][$name];
		}
		return null;
	}

    /**
     * Returns the names of all available database connections
     *
     * @return array
     */
    public function getDatabaseNames() {
        return array_keys($this->globalProperties['Databases']);
    }
}
?>