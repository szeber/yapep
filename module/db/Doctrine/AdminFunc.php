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
 * Admin functions Doctrine database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_AdminFunc extends module_db_DoctrineDbModule implements module_db_interface_AdminFunc, module_db_interface_Admin, module_db_interface_AdminList {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('AdminFuncData', $itemId);
	}
	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('AdminFuncData', $itemData, array(), $this->getObjectTypeIdByShortName('admin'));
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne ( 'FROM AdminFuncData WHERE id = ?', array (( int ) $itemId ) );
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('AdminFuncData', $itemId, $itemData);
	}

	/**
	 * @see module_db_interface_AdminList::getListResultCount()
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return array
	 */
	public function getListResultCount($localeId, $folder = null, $subFolders = false, $filter = null) {
		return $this->getBasicListCount('AdminFuncData', $filter);
	}

	/**
	 * @see module_db_interface_AdminList::listItems()
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return array
	 */
	public function listItems($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false, $filter = null, $orderBy = null, $orderDir = null) {
		return $this->getBasicList('AdminFuncData', $limit, $offset, $filter, $orderBy, $orderDir);
	}

	/**
	 * Returns the list of admin functions (array with id=>name format)
	 *
	 * @return array
	 */
	public function getAdminFuncList() {
		return $this->getBasicIdSelectList('AdminFuncData');
	}

}
?>