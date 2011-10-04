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
 * Doctrine object type database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_ObjectType extends module_db_DoctrineDbModule implements module_db_interface_ObjectType, module_db_interface_Admin  {

	/**
	 * Returns an object type by it's short name
	 *
	 * @param string $typeName
	 * @return array
	 */
	public function getObjectTypeByShortName($typeName) {
		return $this->normalizeResults($this->conn->queryOne ('FROM ObjectTypeData WHERE short_name = ?', array ($typeName)));
	}

	/**
	 * Returns all object type's admin handler that have one set
	 *
	 */
	public function getObjectTypeAdmins() {
		return $this->conn->query('SELECT id, admin_class FROM ObjectTypeData WHERE admin_class IS NOT NULL');
	}

	/**
	 * Returns the listed column data by an object type id
	 *
	 * @param integer $id
	 */
	public function getListColumnsByObjectTypeId($id) {
		return $this->normalizeResults($this->conn->query ('FROM ObjectTypeColumnData WHERE object_type_id = ? ORDER BY column_number ASC', array($id)));
	}

	/**
	 * Returns the list of object types (array with id=>name format)
	 *
	 * @return array
	 */
	public function getObjectTypeList() {
		return $this->getBasicIdSelectList('ObjectTypeData');
	}

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		$this->basicDelete('ObjectTypeData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('ObjectTypeData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM ObjectTypeData t LEFT JOIN t.Columns c WHERE t.id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('ObjectTypeData', $itemId, $itemData);
	}

	/**
	 * Returns the list of columns for a given object type (array with id=>title format)
	 *
	 * @param integer $typeId
	 * @return array
	 */
	public function getObjectTypeColumnList($typeId) {
		return $this->getBasicIdSelectList('ObjectTypeColumnData', 'id', 'title', 'object_type_id = '.(int)$typeId);
	}

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteColumnItem($itemId) {
		$this->basicDelete('ObjectTypeColumnData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertColumnItem($itemData) {
		try {
			$data = new ObjectTypeColumnData();
			$this->modifyData($data, $itemData);
			$data['object_type_id'] = $itemData['object_type_id'];
			$data->save();
			return $data['id'];
		} catch (Doctrine_Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadColumnItem($itemId) {
		return $this->conn->queryOne('FROM ObjectTypeColumnData WHERE id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateColumnItem($itemId, $itemData) {
		return $this->basicUpdate('ObjectTypeColumnData', $itemId, $itemData, array('object_type_id'));
	}

	/**
	 * @see module_db_interface_ObjectType::getAllObjectTypes()
	 *
	 * @return array
	 */
	public function getAllObjectTypes() {
		return $this->conn->query('FROM ObjectTypeData ORDER BY name ASC');
	}

	/**
	 * @see module_db_interface_ObjectType::getObjectTypesByIds()
	 *
	 * @param array $objectTypeIds
	 */
	public function getObjectTypesByIds($objectTypeIds) {
		foreach($objectTypeIds as &$val) {
			$val = $this->conn->quote(trim($val));
		}
		return $this->conn->query('FROM ObjectTypeData WHERE id IN ('.implode(', ', $objectTypeIds).') ORDER BY name ASC');
	}

	/**
	 * Returns the data for all objec types used in the site except for the ones that have their short names listed in $igoreTypes
	 *
	 * @param array $ignoreTypes
	 * @return array
	 */
	public function getUsedObjectTypes($ignoreTypes = array(), $onlyUsed = true) {
		foreach($ignoreTypes as &$val) {
			$val = $this->conn->quote(trim($val));
		}
		$where = '';
		if (count($ignoreTypes)) {
			$where = ' WHERE t.short_name NOT IN ('.implode(', ', $ignoreTypes).')';
		}
		$join = '';
		if ($onlyUsed) {
			$join .= ' INNER JOIN t.Objects';
		}
		return $this->conn->query('SELECT DISTINCT t.* FROM ObjectTypeData t'.$join.$where.' ORDER BY t.name ASC');
	}
}
?>