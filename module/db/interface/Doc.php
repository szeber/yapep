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
 * Document database module interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Doc {

	const TYPE_NAME = 2;

	const TYPE_NEWEST = 1;

	const TYPE_RANDOM = 0;

	const STATUS_ACTIVE = 1;

	const STATUS_INACTIVE = 0;

	const REL_TAG = 101;

	/**
	 * Returns a document's data by its language, path and name
	 *
	 * @param integer $localeId
	 * @param string $docpath
	 * @param string $docname
	 * @param boolean $inactive
	 * @return array If true, returns the document even if it's inactive
	 */
	public function getDocByDocPath($localeId, $docpath, $docname, $inactive = false);

	/**
	 * Returns a document's data by its ID
	 *
	 * @param integer $docid
	 * @param boolean $inactive
	 * @return array If true, returns the document even if it's inactive
	 */
	public function getDocByDocId($docid, $inactive = false);

	/**
	 * Returns the object type's data
	 *
	 * @param string $objectType
	 * @return array
	 */
	public function getObjectTypeData($objectType);

	/**
	 * Returns documents from the specified folder
	 *
	 * @param integer $localeId
	 * @param string $docPath
	 * @param array $objectTypes
	 * @param integer $queryType
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $inactive
	 * @return array
	 */
	public function getDocFromFolder($localeId, $docPath, $objectTypes, $queryType, $limit = 1, $offset = 0, $inactive = false);

	/**
	 * Returns the number of documents from the specified folder
	 *
	 * @param integer $localeId
	 * @param string $docPath
	 * @param array $objectTypes
	 * @param boolean $inactive
	 * @return array
	 */
	public function getDocCountFromFolder($localeId, $docPath, $objectTypes, $inactive = false);

	/**
	 * Returns documents based on their relation to another object
	 *
	 * @param integer $relationType
	 * @param integer $relObjectId
	 * @param array $objectTypes
	 * @param integer $queryType
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $inactive
	 * @return integer
	 */
	public function getDocsByRelation($relationType, $relObjectId, $objectTypes, $queryType, $limit=1, $offset = 0, $inactive = false);

	/**
	 * Returns the number of documents based on their relation to another object
	 *
	 * @param integer $relationType
	 * @param array $relObjectIds
	 * @param array $objectTypes
	 * @param boolean $inactive
	 * @return integer
	 */
	public function getDocCountByRelation($relationType, $relObjectIds, $objectTypes, $inactive = false);

	/**
	 * Finds the first valid version of the given docname for the given folder.
	 *
	 * Checks if the given docname exists in the given folder that doesn't have the ID specified in $excludeId.
	 * If it's free, it returns the string, otherwise it appends a counter to the end and counts until it finds the first free docname,
	 *
	 * @param integer $localeId
	 * @param string $docName
	 * @param integer $folderId
	 * @param integer $excludeId
	 */
	public function findValidDocname($localeId, $docName, $folderId, $excludeId = 0);

	/**
	 * Moves all documents from a folder to another
	 *
	 * This method does NOT check if the target ID is  a valid folder ID!
	 *
	 * @param integer $localeId
	 * @param integer $srcFolder
	 * @param integer $trgtFolder
	 */
	public function moveFolderDocs($localeId, $srcFolder, $trgtFolder);

	/**
	 * Returns the latest documents from a folder or a set of folders
	 *
	 * @param integer $localeId
	 * @param array $folderIds List of folder IDs. Also accepts an integer if only one folder is required
	 * @param integer $limit
	 * @return array
	 */
	public function getLatestDocs($localeId, $folderIds, $limit);
}
?>