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
 * Object type database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_ObjectType {

	/**
	 * Returns an object type by it's short name
	 *
	 * @param string $typeName
	 * @return array
	 */
	public function getObjectTypeByShortName($typeName);

	/**
	 * Returns all object type's admin handler that have one set
	 *
	 */
	public function getObjectTypeAdmins();

	/**
	 * Returns the listed column data by an object type id
	 *
	 * @param integer $id
	 */
	public function getListColumnsByObjectTypeId($id);

	/**
	 * Returns the list of object types (array with id=>name format)
	 *
	 * @return array
	 */
	public function getObjectTypeList();

	/**
	 * Returns the list of columns for a given object type (array with id=>title format)
	 *
	 * @param integer $typeId
	 * @return array
	 */
	public function getObjectTypeColumnList($typeId);
	/**
	 * Deletes a column
	 *
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteColumnItem($itemId);

	/**
	 * Inserts a column
	 *
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertColumnItem($itemData);

	/**
	 * Loads a column
	 *
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadColumnItem($itemId);

	/**
	 * Updates a column
	 *
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateColumnItem($itemId, $itemData);

	/**
	 * Returns all object types in the database
	 *
	 * @return array
	 */
	public function getAllObjectTypes();

	/**
	 * Returns object type information for the given object type ids
	 *
	 * @param array $objectTypeIds
	 */
	public function getObjectTypesByIds($objectTypeIds);

	/**
	 * Returns the data for all objec types used in the site except for the ones that have their short names listed in $igoreTypes
	 *
	 * @param array $ignoreTypes
	 * @return array
	 */
	public function getUsedObjectTypes($ignoreTypes = array());
}
?>