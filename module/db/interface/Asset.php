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
 * Asset database module interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Asset {

	const ASSET_TYPE_FILE = 1;

	const ASSET_TYPE_IMAGE = 2;

	const ASSET_TYPE_VIDEO = 3;


	/**
	 * Returns an array with all asset types
	 *
	 * @return array
	 */
	public function getTypes();

	/**
	 * Returns the folderlist for the specified type
	 *
	 * @param integer $typeId
	 * @return array
	 */
	public function getAllFoldersByTypeId($typeId);

	/**
	 * Returns the list of all asset folders
	 *
	 * @return array
	 */
	public function getAllFolders();

	/**
	 * Returns an array with the specified folder's
	 *
	 * @param integer $folderId
	 */
	public function getFolderInfoById($folderId);

	/**
	 * Adds a video to the converting queue
	 *
	 * @param integer $assetId
	 * @param string $srcFile
	 * @param string $dstFile
	 */
	public function saveVideoToQueue($assetId, $srcFile, $dstFile);

	/**
	 * Returns the list of videos queued for recoding
	 *
	 * @return array
	 */
	public function getQueuedVideos();

	/**
	 * Removes a video from the queue
	 *
	 * @param integer $queueId
	 */
	public function removeVideoFromQueue($queueId);

	/**
	 * Returns a folder by it's docpath
	 *
	 * @param string $docpath
	 * @return array
	 */
	public function getFolderByDocpath($docpath);

	/**
	 * Adds the asset type data to an array of assets
	 *
	 * @param array $assets
	 * @return $assets
	 */
	public function addAssetTypeData($assets);

	/**
	 * Returns the asset subtype data by it's asset type id and extension
	 *
	 * @param integer $assetType
	 * @param string $extension
	 * @return array
	 */
	public function getAssetSubtypeByExt($assetType, $extension);

	/**
	 * Creates an asset folder
	 *
	 * @param integer $parentId
	 * @param string $name
	 * @param string $short
	 * @return integer
	 */
	public function createFolder($parentId, $name, $short);

	/**
	 * Updates a resizer's information
	 *
	 * @param integer $itemId
	 * @param array $itemData The data to update
	 * @return string Empty on success, errormessage on failure
	 */
	public function updateResizeItem($itemId, $itemData);

	/**
	 * Inserts a new resizer
	 *
	 * @param array $itemData
	 * @return string Empty on success, errormessage on failure
	 */
	public function insertResizeItem($itemData);

	/**
	 * Loads the data for a resizer
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadResizeItem($itemId);

	/**
	 * Deletes a resizer from the database
	 *
	 * @param integer $itemId
	 */
	public function deleteResizeItem($itemId);

	/**
	 * Returns the list of resizers
	 *
	 * @return array
	 */
	public function getResizeList();

	/*
	 * Returns the list of all resizers
	 *
	 * @return array
	 */
	public function getResizers();

}
?>