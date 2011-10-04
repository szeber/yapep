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
 * Folder database inteface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Folder {

	/**
	 * Returns the folder information for a given docpath and language
	 *
	 * @param integer $localeId
	 * @param string $docpath
	 * @return array
	 */
	public function getFolderByDocPath($localeId, $docpath);

	/**
	 * Returns the list of subfolders of a given folder
	 *
	 * @param integer $folderId
	 * @return array
	 */
	public function getSubfoldersByFolderId($folderId);

	/**
	 * Returns folders for a given language id
	 *
	 * @param string $lang_id
	 * @return array
	 */
	public function getFoldersByLocaleId($localeId);

	/**
	 * Returns child folders for a given folder id
	 *
	 * @param integer $folderId
	 * @return array
	 */
	public function getFoldersByParentFolderId($folderId);

	/**
	 * Returns specified type child folders for a given folder id
	 *
	 * @param integer $parentId
	 * @param array $typeIds
	 * @return array
	 */
	public function getFoldersByParentAndType($parentId, $typeIds);

	/**
	 * Returns all folders for a given language
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getAllFoldersByLocaleId($localeId);

	/**
	 * Returns all folder types
	 *
	 * @return array
	 */
	public function getFolderTypes();

	/**
	 * Returns all information about a folder by it's ID
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFullFolderInfoById($id);

	/**
	 * Updates the docpath for a folder's subfolders
	 *
	 * @param integer $folderId
	 * @param string $newDocpath
	 */
	public function updateSubfolderDocpath($folderId, $docpath);

	/**
	 * Saves a folder and page relation
	 *
	 * @param string $type
	 * @param integer $folderId
	 * @param integer $pageId
	 * @return boolean
	 */
	public function saveFolderPage($type, $folderId, $pageId);

	/**
	 * Deletes all pages of the specified type for a given folder except the ones listed in the $dontDeletePages array
	 *
	 * @param string $type
	 * @param integer $folderId
	 * @param array $dontDeletePages
	 * @return string Empty string if successful, the errormessage otherwise
	 */
	public function deleteFolderPages($type, $folderId, $dontDeletePages = array());

}
?>