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
 * Module parameter Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_ModuleParam extends module_db_DoctrineDbModule implements module_db_interface_ModuleParam, module_db_interface_Admin {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('CmsModuleParamData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('CmsModuleParamData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM CmsModuleParamData p LEFT JOIN p.Values v WHERE p.id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('CmsModuleParamData', $itemId, $itemData);
	}

	/**
	 * Returns the list of module parameters (array with id=>description (name) format)
	 *
	 * @param integer $moduleId
	 * @return array
	 */
	public function getModuleParamList($moduleId) {
		$data = $this->conn->query('SELECT id, description, name FROM CmsModuleParamData WHERE module_id = ? ORDER BY description ASC, name ASC', array((int)$moduleId));
		foreach($data as $item) {
			$funcs[$item['id']] = $item['description'].' ('.$item['name'].')';
		}
		return $funcs;
	}

	/**
	 * @see module_db_interface_ModuleParam::importParam()
	 *
	 * @param array $param
	 */

	public function importParam(array $param) {
		if(!is_array($param)) {
			return;
		}
		$paramRecord = $this->conn->queryOne('FROM CmsModuleParamData p LEFT JOIN p.Values pv WHERE p.module_id=? AND p.name=?', array((int)$param['module_id'], $param['name']));
		if (!$paramRecord || !count($paramRecord)) {
			$paramRecord = new CmsModuleParamData();
			$paramRecord['module_id'] = $param['module_id'];
			$paramRecord['name'] = $param['name'];
		} else {
			unset($param['name'], $param['module_id']);
		}
		foreach($param as $key=>$val) {
			if ('Values' == $key || !$paramRecord->contains($key)) {
				continue;
			}
			$paramRecord[$key] = $val;
		}
		$paramRecord->save();
		foreach($paramRecord['Values'] as $value) {
			if (!isset($param['Values'][$value['value']])) {
				continue;
			}
			$value['description'] = $param['Values'][$value['value']];
			unset($param['Values'][$value['value']]);
		}
		$paramRecord['Values']->save();
		foreach($param['Values'] as $newValue=>$newDescription) {
			$valueRecord = new CmsModuleParamValueData();
			$valueRecord['module_param_id'] = $paramRecord['id'];
			$valueRecord['value'] = $newValue;
			$valueRecord['description'] = $newDescription;
			$valueRecord->save();
		}
	}
}
?>