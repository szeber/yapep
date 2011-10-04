<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Generic administration Doctrine module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_GenericAdmin extends module_db_DoctrineDbModule implements module_db_interface_GenericAdmin {

	/**
	 * Stores the name of the used model class for the table
	 *
	 * @var array
	 */
	private $objectType;

	/**
	 * Checks if the model class is set, and throws an exception if it's not
	 *
	 */
	private function checkModelClass() {
		if (!$this->objectType) {
			throw new sys_exception_DatabaseException(_('Object type not set'), sys_exception_DatabaseException::ERR_OBJECT_TYPE_NOT_SET);
		}
	}

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		$this->checkModelClass();
		return $this->basicDelete($this->objectType['persist_class'], $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		$this->checkModelClass();
		return $this->basicInsert($this->objectType['persist_class'], $itemData, array(), $this->objectType['id']);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		$this->checkModelClass();
		return $this->basicLoad($this->objectType['persist_class'], $itemId);
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		$this->checkModelClass();
		return $this->basicUpdate($this->objectType['persist_class'], $itemId, $itemData);
	}

	/**
	 * @see module_db_interface_GenericAdmin::getList()
	 *
	 * @param string $type
	 * @param string $where
	 * @return array
	 */
	public function getList($type=null, $where = '') {
		if ('' != $where) {
			$where = ' WHERE '.$where;
		}
		if (is_null($type)) {
			$typeData = $objectType;
		} else {
			$typeData = $this->conn->queryOne('FROM ObjectTypeData WHERE short_name = ?', array($type));
			if (!$typeData) {
				throw new sys_exception_DatabaseException(_('Object type not found'), sys_exception_DatabaseException::ERR_OBJECT_TYPE_NOT_FOUND);
			}
		}
		$data = $this->conn->query('FROM '.$typeData['persist_class'].$where);
		if (!count($data)) {
			return array();
		}
		$tmp = $data[0];
		if (!$tmp->contains('name')) {
			throw new sys_exception_DatabaseException(_('No name field in the table'), sys_exception_DatabaseException::ERR_MISSING_FIELD);
		}
		$results = array();
		foreach($data as $val) {
			$results[$val['id']]=$val['name'];
		}
		asort($results);
		return $results;
	}

	/**
	 * @see module_db_interface_GenericAdmin::setObjType()
	 *
	 * @param string $type
	 */
	public function setObjType($type) {
		$data = $this->conn->queryOne('FROM ObjectTypeData WHERE short_name = ?', array($type));
		if (!$data) {
			throw new sys_exception_DatabaseException(_('Object type not found:'.$type), sys_exception_DatabaseException::ERR_OBJECT_TYPE_NOT_FOUND);
		}
		$this->objectType = $data->toArray();
	}

}
?>