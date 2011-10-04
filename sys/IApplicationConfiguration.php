<?php
/**
 * This file is part of YAPEP.
 *
 * @package YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 8096 $
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
 * @version	$Rev: 8096 $
 */
interface sys_IApplicationConfiguration extends sys_cache_CacheManager {

	/**
	 * Returns the singleton instance
	 *
	 * @return sys_IApplicationConfiguration
	 */
	public static function getInstance();

	/**
	 * Saves a property to the configuration data
	 *
	 * @param string $name Name of the property
	 * @param mixed $value value of the property
	 */
	public function setProperty($name, $value);

	/**
	 * Reads a property previously set by setProperty from the configuration data
	 *
	 * @param string $name Name of the property
	 * @return mixed Value of the property or NULL if not found
	 */
	public function getProperty($name);

	/**
	 * Deletes a property previously set by setProperty
	 *
	 * @param string $name Name of the property
	 * @return boolean True on success, false if property is not found
	 */
	public function deleteProperty($name);

	/**
	 * Reads an option from the configuration data set by the XML file
	 *
	 * @param string $name
	 * @return mixed Value of the option or NULL if not found
	 */
	public function getOption($name);

	/**
	 * Returns a database's configuration information
	 *
	 * @param string $name Name of the database
	 * @return array The database configuration information
	 */
	public function getDatabase($name);

	/**
	 * Returns a path
	 *
	 * @param string $name The name of the path
	 * @return string The path or NULL if not found
	 */
	public function getPath($name);

    /**
     * Returns the names of all available database connections
     *
     * @return array
     */
    public function getDatabaseNames();
}
?>