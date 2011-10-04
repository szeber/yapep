<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Module database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Module {

	/**
	 * Returns data for all modules
	 *
	 * @return array
	 */
	public function getModuleData();

	/**
	 * Returns module params for given module
	 *
	 * @param integer $moduleId
	 * @return array
	 */
	public function getModuleParamData($moduleId);

	/**
	 * Returns a module's data
	 *
	 * @param integer $moduleId
	 * @return array
	 */
	public function getModule($moduleId);

	/**
	 * Returns the list of modules (array with id=>name format)
	 *
	 * @return array
	 */
	public function getModuleList();

	/**
	 * Returns parameter value options for a given parameter
	 *
	 * @param integer $paramId
	 * @return array
	 */
	public function getModuleParamValuesForParam($paramId);

	/**
	 * Returns a module's data by it's name
	 *
	 * @param string $name
	 */
	public function getModuleByName($name);
}
?>