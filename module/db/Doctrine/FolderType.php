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
 * Doctrine folder type database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_FolderType extends module_db_DoctrineDbModule implements module_db_interface_FolderType, module_db_interface_Admin  {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('FolderTypeData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('FolderTypeData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM FolderTypeData f LEFT JOIN f.ObjectTypes o WHERE f.id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('FolderTypeData', $itemId, $itemData);
	}

	/**
	 * Returns the list of folder types (array with id=>name format)
	 *
	 * @return array
	 */
	public function getFolderTypeList() {
		return $this->getBasicIdSelectList('FolderTypeData');
	}

	/**
	 * Deletes an object type relation item
	 *
	 * @param integer $folderId
	 * @param integer $objectId
	 */
	public function deleteRelItem($folderId, $objectId) {
		$data = $this->conn->queryOne('FROM FolderTypeObjectTypeRel WHERE folder_type_id = ? AND object_type_id = ?', array((int)$folderId, (int)$objectId));
		if(is_object($data)) {
			$data->delete();
		}

	}

	/**
	 * Inserts an object type relation item
	 *
	 * @param integer $folderId
	 * @param integer $objectId
	 * @return string
	 */
	public function insertRelItem($folderId, $objectId) {
		try {
			$data = new FolderTypeObjectTypeRel();
			$data['folder_type_id'] = $folderId;
			$data['object_type_id'] = $objectId;
			$data->save();
			return $data['object_type_id'];
		} catch (Doctrine_Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Loads an object relation item
	 *
	 * @param integer $folderId
	 * @param integer $objectId
	 * @return array
	 */
	public function loadRelItem($folderId, $objectId) {
		return $this->conn->queryOne('FROM FolderTypeObjectTypeRel WHERE folder_type_id = ? AND object_type_id = ?', array((int)$folderId, (int)$objectId));
	}

	/**
	 * Returns the list of object types related to the given folder type (array with id=>name format)
	 *
	 * @return array
	 */
	public function getRelList($folderId) {
		$data = $this->conn->query('SELECT r.object_type_id, o.name FROM FolderTypeObjectTypeRel r LEFT JOIN r.ObjectType o WHERE r.folder_type_id = ? ORDER BY o.name ASC', array((int)$folderId));
		foreach($data as $item) {
			$list[$item['object_type_id']] = $item['ObjectType']['name'];
		}
		return $list;

	}
}
?>