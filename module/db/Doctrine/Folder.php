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
 * Folder Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Folder extends module_db_DoctrineDbModule implements module_db_interface_Folder, module_db_interface_Admin  {

	protected static $folderDatas = array();

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete ('FolderData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne ('FROM FolderData f LEFT JOIN f.Pages p LEFT JOIN p.Theme LEFT JOIN p.Page WHERE f.id = ?', array ($itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		try {
			$ignoreFields = array ('id', 'docpath', 'short', 'parent_id');
			$data = $this->conn->queryOne ('FROM FolderData WHERE id = ?', array ((int) $itemId));
			if (!is_object ($data)) {
				return 'Unable to update item because it was not found';
			}
			if (isset($itemData['parent_id']) && (int)$itemData['parent_id'] != (int)$data['parent_id']) {
				if ($itemData['parent_id'] != 0) {
					$parentData = $this->conn->queryOne('FROM FolderData WHERE id = ?', array((int)$itemData['parent_id']));
				}
				if ($itemData['parent_id'] == 0 || count($parentData)) {
					if ($itemData['parent_id'] == 0) {
						$data['parent_id'] = null;
						$data['docpath'] = $data['short'];
					} else {
						$data['parent_id'] = $itemData['parent_id'];
						$data['docpath'] = $parentData['docpath'].'/'.$data['short'];
					}
					$data->save();
					$this->updateSubfolderDocpath($data['id'], $data['docpath']);
				}
			}
			if (isset ($itemData ['short']) && (string) $itemData ['short'] != (string) $data ['short']) {
				$newDocpath = substr ($data ['docpath'], 0, (-1 * strlen ($data ['short']))) . $itemData ['short'];
				$data ['short'] = $itemData ['short'];
				$data ['docpath'] = $newDocpath;
				$this->updateSubfolderDocpath ($data ['id'], $newDocpath);
			}
			$this->modifyData ($data, $itemData, $ignoreFields);
			$data->save ();
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		try {
			$objectTypeHandler = getPersistClass ('ObjectType');
			$ignoreFields = array ('docpath');
			$data = new FolderData ();
			$data ['object_type_id'] = $this->getObjectTypeIdByShortName ('folder');
			if ($itemData ['parent_id']) {
				$parentData = $this->conn->queryOne ('SELECT docpath FROM FolderData WHERE id = ?', array ((int) $itemData ['parent_id']));
				$data ['docpath'] = $parentData ['docpath'] . '/' . $itemData ['short'];
			} else {
				$data ['docpath'] = $itemData ['short'];
				$ignoreFields [] = 'parent_id';
			}
			$this->modifyData ($data, $itemData, $ignoreFields);
			$data->save ();
			return $data ['id'];
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * Returns the folder information for a given docpath and language
	 *
	 * @param integer $localeId
	 * @param string $docPath
	 * @return array
	 */
	public function getFolderByDocPath($localeId, $docPath) {
		if (isset(self::$folderDatas[$localeId][$docPath])) {
			return self::$folderDatas[$localeId][$docPath];
		}
		$data = $this->conn->queryOne ('FROM FolderData WHERE docpath=? AND (locale_id=? OR locale_id IS NULL)', array ($docPath, $localeId));
		if (!$data) {
			self::$folderDatas[$localeId][$docPath] = array();
			return array();
		}
		self::$folderDatas[$localeId][$docPath] = $data;
		return $data->toArray();
	}

	/**
	 * Returns the list of subfolders of a given folder
	 *
	 * @param integer $folderId
	 * @return array
	 */
	public function getSubfoldersByFolderId($folderId) {
		return $this->conn->query ('FROM FolderData WHERE visible = true AND parent_id=? ORDER BY folder_name ASC', array ($folderId));
	}

	/**
	 * Returns folders for a given language id
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getFoldersByLocaleId($localeId) {
		return $this->conn->query ('FROM FolderData f LEFT JOIN f.Images i LEFT JOIN f.Pages p WHERE parent_id IS NULL AND (locale_id IS NULL OR locale_id = ?) ORDER BY folder_order, short', array ($localeId));
	}

	/**
	 * Returns child folders for a given folder id
	 *
	 * @param integer $folderId
	 * @return array
	 */
	public function getFoldersByParentFolderId($folderId) {
		return $this->conn->query ('FROM FolderData f LEFT JOIN f.Images i LEFT JOIN f.Pages p WHERE parent_id = ? ORDER BY folder_order, short', array ($folderId));
	}

	/**
	 * Returns specified type child folders for a given folder id
	 *
	 * @param integer $parentId
	 * @param array $typeIds
	 * @return array
	 */
	public function getFoldersByParentAndType($parentId, $typeIds) {
		if (is_array ($typeIds)) {
			$tmp = array ();
			foreach ( $typeIds as $val ) {
				if ((int) $val) {
					$tmp [] = (int) $val;
				}
			}
			$typeIds = implode (', ', $tmp);
		}
		if (is_null ($parentId)) {
			return $this->conn->query ('FROM FolderData WHERE visible = true AND parent_id IS NULL AND folder_type_id IN ( ' . $typeIds . ' ) ORDER BY folder_order, name');
		}
		return $this->conn->query ('FROM FolderData WHERE visible = true AND parent_id = ? AND folder_type_id IN ( ' . $typeIds . ' ) ORDER BY folder_order, name', array ($parentId));
	}

	/**
	 * Returns all folders for a given language
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getAllFoldersByLocaleId($localeId) {
		return $this->conn->query ('FROM FolderData f LEFT JOIN f.Images i LEFT JOIN f.Pages p WHERE locale_id = ? OR locale_id IS NULL ORDER BY folder_order, short', array ($localeId));
	}

	/**
	 * Returns all folder types
	 *
	 * @return array
	 */
	public function getFolderTypes() {
		return $this->conn->query ('FROM FolderTypeData ORDER BY description ASC');
	}

	/**
	 * Returns a folder by it's ID
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFullFolderInfoById($id) {
		return $this->normalizeResults($this->conn->queryOne ('FROM FolderData f INNER JOIN f.FolderType ft LEFT JOIN ft.ObjectTypes ot LEFT JOIN ot.Columns WHERE f.id=?', array ($id)));
	}

	/**
	 * Updates the docpath for a folder's subfolders
	 *
	 * @param integer $folderId
	 * @param string $newDocpath
	 */
	public function updateSubfolderDocpath($folderId, $docpath) {
		$folders = $this->conn->query ('FROM FolderData WHERE parent_id = ?', array ($folderId));
		if (!count ($folders)) {
			return;
		}
		foreach ( $folders as $folder ) {
			$folder ['docpath'] = $docpath . '/' . $folder ['short'];
			$folder->save ();
			$this->updateSubfolderDocpath ($folder ['id'], $folder ['docpath']);
		}
	}

	/**
	 * Saves a folder and page relation
	 *
	 * @param string $type
	 * @param integer $folderId
	 * @param integer $pageId
	 * @return string Empty string if successful, the errormessage otherwise
	 */
	public function saveFolderPage($type, $folderId, $pageId) {
		$pageData = $this->conn->queryOne ('FROM CmsPageData WHERE id = ?', array ((int) $pageId));
		if (!is_object ($pageData)) {
			return 'Page not found';
		}
		$folderPage = $this->conn->queryOne ('FROM CmsPageObjectRel WHERE object_id = ? AND relation_type = ? AND theme_id = ?', array ((int) $folderId, (string) $type, (int) $pageData ['theme_id']));
		try {
			if (is_object ($folderPage)) {
				if ($folderPage ['page_id'] != (int) $pageId) {
					$folderPage ['page_id'] = (int) $pageId;
					$folderPage->save ();
				}
			} else {
				$folderPage = new CmsPageObjectRel ();
				$folderPage ['object_id'] = (int) $folderId;
				$folderPage ['page_id'] = (int) $pageId;
				$folderPage ['theme_id'] = (int) $pageData ['theme_id'];
				$folderPage ['relation_type'] = (int) $type;
				$folderPage->save ();
			}
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * Deletes all pages of the specified type for a given folder except the ones listed in the $dontDeletePages array
	 *
	 * @param string $type
	 * @param integer $folderId
	 * @param array $dontDeletePages
	 * @return string Empty string if successful, the errormessage otherwise
	 */
	public function deleteFolderPages($type, $folderId, $dontDeletePages = array()) {
		try {
			$extra = '';
			if (count ($dontDeletePages)) {
				$extra = ' AND page_id NOT IN (' . implode (', ', $dontDeletePages) . ')';
			}
			$this->conn->query ('DELETE FROM CmsPageObjectRel WHERE relation_type = ? AND object_id = ?' . $extra, array ((string) $type, (int) $folderId));
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}
}
?>