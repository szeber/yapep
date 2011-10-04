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
 * Doctrine asset database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Asset extends module_db_DoctrineDbModule implements module_db_interface_Asset, module_db_interface_AdminList  {

	/**
	 * @see module_db_interface_Asset::getFolderInfoById()
	 *
	 * @param integer $folderId
	 */
	public function getFolderInfoById($folderId) {
		return $this->conn->queryOne('FROM AssetFolderData WHERE id = ?', array((int)$folderId));
	}

	/**
	 * @see module_db_interface_Asset::getAssetFolders()
	 *
	 * @param integer $typeId
	 * @return array
	 */
	public function getAllFoldersByTypeId($typeId) {
		return $this->conn->query('FROM AssetFolderData f INNER JOIN f.Assets a WHERE a.asset_type_id = ?', array($typeId));
	}

	/**
	 * @see module_db_interface_Asset::getAssetTypes()
	 *
	 * @return array
	 */
	public function getTypes() {
		return $this->conn->query('FROM AssetTypeData');
	}

	/**
	 * @see module_db_interface_Asset::getAllFolders()
	 *
	 * @return array
	 */
	public function getAllFolders() {
		return $this->conn->query('FROM AssetFolderData f ORDER BY name ASC');
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
		$filters = ' WHERE folder_id = ?';
		$filterArr = array((int)$folder);
		if (is_array($filter) && count($filter)) {
			$rec = new AssetData();
			foreach($filter as $name=>$val) {
				if ($rec->contains($name)) {
					if ($name == 'asset_type_id') {
						$filters .= ' AND '.$name.' = ?';
						$filterArr[] = $val;
					} else {
						$filters .= ' AND '.$name.' LIKE ?';
						$filterArr[] = $val.'%';
					}
				}
			}
		}
		$count = $this->conn->queryOne('SELECT COUNT(id) as itemCount FROM AssetData'.$filters, $filterArr);
		return $count['itemCount'];

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
		$filters = ' WHERE d.folder_id = ?';
		$filterArr = array((int)$folder);
		if (is_array($filter) && count($filter)) {
			$rec = new AssetData();
			foreach($filter as $name=>$val) {
				if ($rec->contains($name)) {
					if ($name == 'asset_type_id') {
						$filters .= ' AND '.$name.' = ?';
						$filterArr[] = $val;
					} else {
						$filters .= ' AND '.$name.' LIKE ?';
						$filterArr[] = $val.'%';
					}
				}
			}
		}
		$extra = '';
		if ((int)$limit) {
			if (!is_null($orderBy)) {
				$obj = new AssetData();
				if (!isset($obj[$orderBy])) {
					$orderBy = 'name';
				}
				$extra .= ' ORDER BY '.$orderBy;
				switch (strtolower($orderDir)) {
					case 'desc':
					case '-':
						$extra .= ' DESC';
						break;
					default:
						$extra .= ' ASC';
						break;
				}
			}
			$extra .= ' LIMIT '.(int)$limit;
			if ((int)$offset<0) {
				$offset = 0;
			}
			$extra .= ' OFFSET '.(int)$offset;
		}
		$data = $this->conn->query('SELECT d.*, s.name AS type FROM AssetData d LEFT JOIN d.Subtype s'.$filters.$extra, $filterArr);
		return $data;
	}

	/**
	 * @see module_db_interface_Asset::saveVideoToQueue()
	 *
	 * @param integer $assetId
	 * @param string $srcFile
	 * @param string $dstFile
	 */
	public function saveVideoToQueue($assetId, $srcFile, $dstFile) {
		$video = new VideoQueueData();
		$video['asset_id'] = (int)$assetId;
		$video['source_file'] = $srcFile;
		$video['destination_file'] = $dstFile;
		$video->save();
	}

	/**
	 * @see module_db_interface_Asset::getQueuedVideos()
	 *
	 * @return array
	 */
	public function getQueuedVideos() {
		return $this->normalizeResults($this->conn->query('FROM VideoQueueData ORDER BY id'));
	}

	/**
	 * @see module_db_interface_Asset::removeVideoFromQueue()
	 *
	 * @param integer $queueId
	 */
	public function removeVideoFromQueue($queueId) {
		$this->conn->query('DELETE FROM VideoQueueData WHERE id = ?', array((int)$queueId));
	}

	/**
	 * @see module_db_interface_Asset::getFolderByDocpath()
	 *
	 * @param string $docpath
	 * @return array
	 */
	public function getFolderByDocpath($docpath) {
		return $this->normalizeResults($this->conn->queryOne('FROM AssetFolderData WHERE docpath = ?', array($docpath)));
	}

	/**
	 * @see module_db_interface_Asset::addAssetTypeData()
	 *
	 * @param array $assets
	 */
	public function addAssetTypeData($assets) {
		if (!is_array($assets)) {
			return $assets;
		}
		$types = array();
		$subtypes = array();
		foreach($assets as &$asset) {
			if ($asset['asset_type_id']) {
				if (!is_array($types[$asset['asset_type_id']])) {
					$types [$asset['asset_type_id']] = $this->normalizeResults($this->conn->queryOne('FROM AssetTypeData WHERE id=?', array((int)$asset['asset_type_id'])));
					$asset ['Type'] = $types[$asset['asset_type_id']];
				} else {
					$asset ['Type'] = $types[$asset['asset_type_id']];
				}
			}
			if ($asset['asset_subtype_id']) {
				if (!is_array($subtypes[$asset['asset_subtype_id']])) {
					$subtypes [$asset['asset_subtype_id']] = $this->normalizeResults($this->conn->queryOne('FROM AssetSubtypeData WHERE id=?', array((int)$asset['asset_subtype_id'])));
					$asset ['Subtype'] = $subtypes[$asset['asset_subtype_id']];
				} else {
					$asset ['Subtype'] = $subtypes[$asset['asset_subtype_id']];
				}
			}
		}
		return $assets;
	}

	/**
	 * @see module_db_interface_Asset::getAssetSubtypeByExt()
	 *
	 * @param integer $assetType
	 * @param string $extension
	 * @return array
	 */
	public function getAssetSubtypeByExt($assetType, $extension) {
		$data = $this->normalizeResults($this->conn->queryOne('FROM AssetSubtypeData WHERE asset_type_id = ? AND extension = ?', array((int)$assetType, $extension)));
		if (!count($data)) {
			return $this->normalizeResults($this->conn->queryOne('FROM AssetSubtypeData WHERE asset_type_id = ? AND is_default = ?', array((int)$assetType, true)));
		}
		return $data;
	}

	/**
	 * @see module_db_interface_Asset::createFolder()
	 *
	 * @param integer $parentId
	 * @param string $name
	 * @param string $short
	 * @return integer
	 */
	public function createFolder($parentId, $name, $short) {
		$parent = $this->conn->queryOne('FROM AssetFolderData WHERE id=?', array((int)$parentId));
		if (!$parent || !count($parent)) {
			return null;
		}
		$folder = new AssetFolderData();
		$folder['parent_id'] = $parentId;
		$folder['name'] = $name;
		$folder['short'] = $short;
		$folder['docpath'] = $parent['docpath'].'/'.$short;
		$folder->save();
		return $folder['id'];
	}

	/**
	 * @see module_db_interface_Asset::deleteResizeItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteResizeItem($itemId) {
		return $this->basicDelete('AssetResizerData', $itemId);
	}

	/**
	 * @see module_db_interface_Asset::insertResizeItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertResizeItem($itemData) {
		return $this->basicInsert('AssetResizerData', $itemData);
	}

	/**
	 * @see module_db_interface_Asset::loadResizeItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadResizeItem($itemId) {
		return $this->basicLoad('AssetResizerData', $itemId);
	}

	/**
	 * @see module_db_interface_Asset::updateResizeItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateResizeItem($itemId, $itemData) {
		return $this->basicUpdate('AssetResizerData', $itemId, $itemData);
	}

	/**
	 * @see module_db_interface_Asset::getResizeList()
	 *
	 * @return array
	 */
	public function getResizeList() {
		return $this->getBasicIdSelectList('AssetResizerData');
	}

	/**
	 * @see module_db_interface_Asset::getResizers()
	 *
	 */
	public function getResizers() {
		return $this->normalizeResults($this->conn->query('FROM AssetResizerData ORDER BY name ASC'));
	}

}
?>