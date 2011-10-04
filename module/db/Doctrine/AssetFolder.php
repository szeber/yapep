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
 * Asset folder Doctrine database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_AssetFolder extends module_db_DoctrineDbModule implements module_db_interface_Admin, module_db_interface_AssetFolder {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete ('AssetFolderData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
	    return $this->basicLoad('AssetFolderData', $itemId);
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
			$data = $this->conn->queryOne ('FROM AssetFolderData WHERE id = ?', array ((int) $itemId));
			if (!is_object ($data)) {
				return 'Unable to update item because it was not found';
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
			$ignoreFields = array ('docpath');
			$data = new AssetFolderData ();
			if ($itemData ['parent_id']) {
				$parentData = $this->conn->queryOne ('SELECT docpath FROM AssetFolderData WHERE id = ?', array ((int) $itemData ['parent_id']));
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
	 * Updates the docpath for a folder's subfolders
	 *
	 * @param integer $folderId
	 * @param string $newDocpath
	 */
	protected function updateSubfolderDocpath($folderId, $docpath) {
		$folders = $this->conn->query ('FROM AssetFolderData WHERE parent_id = ?', array ($folderId));
		if (!count ($folders)) {
			return;
		}
		foreach ( $folders as $folder ) {
			$folder ['docpath'] = $docpath . '/' . $folder ['short'];
			$folder->save ();
			$this->updateSubfolderDocpath ($folder ['id'], $folder ['docpath']);
		}
	}

}
?>