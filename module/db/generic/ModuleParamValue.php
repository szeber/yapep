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
 * Module parameter value generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_ModuleParamValue extends module_db_DbModule implements module_db_interface_ModuleParamValue, module_db_interface_Admin {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('cms_module_param_value_data', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('cms_module_param_value_data', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
	    return $this->basicLoad('cms_module_param_value_data', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('cms_module_param_value_data', $itemId, $itemData);
	}

	/**
	 * Returns the list of module parameter values (array with id=>description format)
	 *
	 * @param integer $moduleParamId
	 * @return array
	 */
	public function getModuleParamValueList($moduleParamId) {
		return $this->getBasicIdSelectList('cms_module_param_value_data', 'id', 'description', 'module_param_id='.(int)$moduleParamId);
	}
}
?>