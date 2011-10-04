<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Module parameter database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_ModuleParam {

	const TYPE_TEXT = 1;
	const TYPE_SELECT = 2;
	const TYPE_CHECK = 3;
	const TYPE_DOC = 4;
	const TYPE_FOLDER = 5;
	const TYPE_DOC_LIST = 6;
	const TYPE_FOLDER_LIST = 7;
	const TYPE_LONG_TEXT = 8;


	/**
	 * Returns the list of module parameters (array with id=>description (name) format)
	 *
	 * @param integer $moduleId
	 * @return array
	 */
	public function getModuleParamList($moduleId);

	/**
	 * Inserts or updates a param with it's values
	 *
	 * @param array $param
	 */
	public function importParam(array $param);
}
?>