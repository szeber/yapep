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
 * Asset folder generic database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_AssetFolder extends module_db_DbModule implements module_db_interface_Admin,
    module_db_interface_AssetFolder
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('asset_folder_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        return $this->basicLoad('asset_folder_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::updateItem()
     *
     * @param integer $itemId
     * @param array $itemData
     * @return string
     */
    public function updateItem ($itemId, $itemData)
    {
        $ignoreFields = array('id' , 'docpath' , 'short' , 'parent_id');
        $data = $this->basicLoad('asset_folder_data', $itemId);
        if (! count($data)) {
            return 'Unable to update item because it was not found';
        }
        if (isset($itemData['short']) && (string) $itemData['short'] != (string) $data['short']) {
            $newDocpath = substr($data['docpath'], 0,
                (- 1 * strlen($data['short']))) . $itemData['short'];
            $data['short'] = $itemData['short'];
            $data['docpath'] = $newDocpath;
            $this->updateSubfolderDocpath($data['id'], $newDocpath);
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->update(
            array('table' => 'asset_folder_data' ,
                'where' => 'id=' . $data['id']), $data)) {
            return '';
        }
        return $this->conn->getLastError();
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        $ignoreFields = array('docpath');
        $data = $this->conn->getDefaultRecord('asset_folder_data');
        if ($itemData['parent_id']) {
            $parentData = $this->conn->selectFirst(
                array('table' => 'asset_folder_data' ,
                    'where' => 'id=' . (int) $itemData['parent_id']));
            $data['docpath'] = $parentData['docpath'] . '/' . $itemData['short'];
        } else {
            $data['docpath'] = $itemData['short'];
            $ignoreFields[] = 'parent_id';
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->insert('asset_folder_data', $data)) {
            return $data['id'];
        }
        return $this->conn->getLastError();
    }

    /**
     * Updates the docpath for a folder's subfolders
     *
     * @param integer $folderId
     * @param string $newDocpath
     */
    protected function updateSubfolderDocpath ($folderId, $docpath)
    {
        $folders = $this->conn->select(
            array('table' => 'asset_folder_data' ,
                'where' => 'parent_id=' . (int) $folderId));
        if (! count($folders)) {
            return;
        }
        foreach ($folders as $folder) {
            $folder['docpath'] = $docpath . '/' . $folder['short'];
            $this->conn->update(
                array('table' => 'asset_folder_data' ,
                    'where' => 'id=' . $folder['id']));
            $this->updateSubfolderDocpath($folder['id'],
                $folder['docpath']);
        }
    }

}
?>