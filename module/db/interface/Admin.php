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
 * Admin DB module interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Admin {

	/**
	 * Updates a document's information
	 *
	 * @param integer $itemId
	 * @param array $itemData The data to update
	 * @return string Empty on success, errormessage on failure
	 */
	public function updateItem($itemId, $itemData);

	/**
	 * Inserts a new document
	 *
	 * @param array $itemData
	 * @return string Empty on success, errormessage on failure
	 */
	public function insertItem($itemData);

	/**
	 * Loads the data for an item
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId);

	/**
	 * Deletes an item from the database
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId);
}
?>