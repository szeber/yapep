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
 * Cms_module Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Module extends module_db_DoctrineDbModule implements module_db_interface_Module, module_db_interface_Admin  {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('CmsModuleData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('CmsModuleData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM CmsModuleData m LEFT JOIN m.Params p WHERE m.id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('CmsModuleData', $itemId, $itemData);
	}

	/**
	 * Returns the list of modules (array with id=>name format)
	 *
	 * @return array
	 */
	public function getModuleList() {
		return $this->getBasicIdSelectList('CmsModuleData');
	}

	/**
	 * Returns module datas
	 *
	 * @return array
	 */
	public function getModuleData() {
		return $this->conn->query ('FROM CmsModuleData m LEFT JOIN m.Params p');
	}

	/**
	 * Returns module params for given module
	 *
	 * @param integer $moduleId
	 * @return array
	 */
	public function getModuleParamData($moduleId) {
		return $this->conn->query ('FROM CmsModuleParamData WHERE module_id = ?', array ($moduleId));
	}

	/**
	 * Returns parameter value options for a given parameter
	 *
	 * @param integer $paramId
	 * @return array
	 */
	public function getModuleParamValuesForParam($paramId) {
		return $this->conn->query('FROM CmsModuleParamValueData WHERE module_param_id = ? ORDER BY id ASC', array($paramId));
	}

	/**
	 * Returns a module's data by it's name
	 *
	 * @param string $name
	 */
	public function getModuleByName($name) {
		return $this->conn->queryOne('FROM CmsModuleData m LEFT JOIN m.Params p LEFT JOIN p.Values v WHERE m.name = ?', array($name));
	}

	/**
	 * Returns a module's data
	 *
	 * @param integer $moduleId
	 * @return array
	 */
	public function getModule($moduleId) {
		return $this->conn->queryOne('FROM CmsModuleData m LEFT JOIN m.Params p LEFT JOIN p.Values v WHERE m.id = ?', array($moduleId));
	}

}
?>