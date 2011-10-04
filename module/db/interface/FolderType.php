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
 * Generic folder type database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_FolderType {
	/**
	 * Returns the list of folder types (array with id=>name format)
	 *
	 * @return array
	 */
	public function getFolderTypeList();

	/**
	 * Deletes an object type relation item
	 *
	 * @param integer $folderId
	 * @param integer $objectId
	 */
	public function deleteRelItem($folderId, $objectId);

	/**
	 * Inserts an object type relation item
	 *
	 * @param integer $folderId
	 * @param integer $objectId
	 * @return string
	 */
	public function insertRelItem($folderId, $objectId);

	/**
	 * Loads an object relation item
	 *
	 * @param integer $folderId
	 * @param integer $objectId
	 * @return array
	 */
	public function loadRelItem($folderId, $objectId);

	/**
	 * Returns the list of object types related to the given folder type (array with id=>name format)
	 *
	 * @return array
	 */
	public function getRelList($folderId);
}
?>