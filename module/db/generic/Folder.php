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
 * Folder generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Folder extends module_db_DbModule implements module_db_interface_Folder,
    module_db_interface_Admin
{

    protected static $folderDatas = array();

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('folder_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        $folder = $this->conn->selectFirst(
            array('table' => 'folder_data' ,
            'where' => 'id=' . (int) $itemId));
        $fieldMap = $this->getMappableFields(
            array('r' => 'cms_page_object_rel' , 'p' => 'cms_page_data' ,
            't' => 'cms_theme_data'));
        $query = array(
        'table' => 'cms_page_object_rel r JOIN cms_page_data p ON r.page_id=p.id' . ' JOIN cms_theme_data t ON t.id=r.theme_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'r.object_id=' . (int) $itemId);
        $data = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $folder['Pages'] = array();
        foreach ($data as $row) {
            $row['r']['Page'] = $row['p'];
            $row['r']['Theme'] = $row['t'];
            $folder['Pages'][] = $row['r'];
        }
        return $folder;
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
        $data = $this->conn->selectFirst(
            array('table' => 'folder_data' ,
            'where' => 'id=' . (int) $itemId));
        if (! count($data)) {
            return 'Unable to update item because it was not found';
        }
        if (isset($itemData['parent_id']) && (int) $itemData['parent_id'] != (int) $data['parent_id']) {
            if ($itemData['parent_id'] != 0) {
                $parentData = $this->conn->selectFirst(
                    array(
                    'table' => 'folder_data' ,
                    'where' => 'id=' . (int) $itemData['parent_id']));
            }
            if ($itemData['parent_id'] == 0 || count($parentData)) {
                if ($itemData['parent_id'] == 0) {
                    unset($data['parent_id']);
                    $data['docpath'] = $data['short'];
                } else {
                    $data['parent_id'] = $itemData['parent_id'];
                    $data['docpath'] = $parentData['docpath'] .
                         '/' .
                         $data['short'];
                }
                $this->conn->update(
                    array(
                    'table' => 'folder_data' ,
                    'where' => 'id=' . $data['id']),
                    $itemData);
                $this->updateSubfolderDocpath(
                    $data['id'],
                    $data['docpath']);
            }
        }
        if (isset($itemData['short']) && (string) $itemData['short'] != (string) $data['short']) {
            $data['short'] = $itemData['short'];
            if ($data['parent_id']) {
                $parentData = $this->conn->selectFirst(
                    array(
                    'table' => 'folder_data' ,
                    'where' => 'id=' . (int) $itemData['parent_id']));
                $data['docpath'] = $parentData['docpath'].'/'.$itemData['short'];
            } else {
                $data['docpath'] = $itemData['short'];
            }
            $newDocpath = substr($data['docpath'], 0,
                (- 1 * strlen($data['short']))) . $itemData['short'];
            $data['docpath'] = $newDocpath;
            $this->conn->update(
                array(
                'table' => 'folder_data' ,
                'where' => 'id=' . $data['id']),
                $itemData);
            $this->updateSubfolderDocpath($data['id'], $newDocpath);
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->update(
            array('table' => 'folder_data' , 'where' => 'id=' . $data['id']),
            $data)) {
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
        $data = $this->conn->getDefaultRecord('folder_data');
        $data['object_type_id'] = $this->getObjectTypeIdByShortName('folder');
        if ($itemData['parent_id']) {
            $parentData = $this->conn->selectFirst(
                array('table' => 'folder_data' ,
                'fields' => 'docpath' ,
                'where' => 'id=' . (int) $itemData['parent_id']));
            $data['docpath'] = $parentData['docpath'] . '/' . $itemData['short'];
        } else {
            $data['docpath'] = $itemData['short'];
            $ignoreFields[] = 'parent_id';
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->insert('folder_data', $data)) {
            return $data['id'];
        } else {
            return $this->conn->getLastError();
        }
    }

    /**
     * Returns the folder information for a given docpath and language
     *
     * @param integer $localeId
     * @param string $docPath
     * @return array
     */
    public function getFolderByDocPath ($localeId, $docPath)
    {
        if (isset(self::$folderDatas[$localeId][$docPath])) {
            return self::$folderDatas[$localeId][$docPath];
        }
        $data = $this->conn->selectFirst(
            array('table' => 'folder_data' ,
            'where' => 'docpath=' . $this->conn->quote($docPath) . ' AND (locale_id=' .
                 (int) $localeId . ' OR locale_id IS NULL)'));
        if (! $data) {
            self::$folderDatas[$localeId][$docPath] = array();
            return array();
        }
        self::$folderDatas[$localeId][$docPath] = $data;
        return $data;
    }

    /**
     * Returns the list of subfolders of a given folder
     *
     * @param integer $folderId
     * @return array
     */
    public function getSubfoldersByFolderId ($folderId)
    {
        return $this->conn->select(
            array('table' => 'folder_data' ,
            'where' => 'visible=' . $this->conn->getBool(true) . ' AND parent_id=' .
                 (int) $folderId ,
                'orderBy' => 'folder_name ASC'));
    }

    /**
     * Returns folders for a given language id
     *
     * @param integer $localeId
     * @return array
     */
    public function getFoldersByLocaleId ($localeId)
    {
        $data = $this->conn->select(
            array('table' => 'folder_data' ,
            'where' => 'parent_id IS NULL AND (locale_id IS NULL OR locale_id = ' .
                 (int) $localeId . ')' ,
                'orderBy' => 'folder_order, short'));
        return $this->extendFolderData($data);
    }

    /**
     * Adds the images and pages to the array containing folder information
     *
     * @param array $data
     * @return array
     */
    protected function extendFolderData (array $data)
    {
        if (! count($data)) {
            return $data;
        }
        $ids = array();
        foreach ($data as $row) {
            $ids[] = $row['id'];
        }
        $ids = implode(', ', $ids);
        $tmp = $this->conn->select(
            array('table' => 'folder_image_data' ,
            'where' => "folder_id IN ($ids)"));
        $imageData = array();
        foreach ($tmp as $row) {
            if (! is_array($imageData[$row['folder_id']])) {
                $imageData[$row['folder_id']] = array(
                $row);
            } else {
                $imageData[$row['folder_id']][] = $row;
            }
        }
        $tmp = $this->conn->select(
            array('table' => 'cms_page_object_rel' ,
            'where' => "object_id IN ($ids)"));
        $pageData = array();
        foreach ($tmp as $row) {
            if (! is_array($pageData[$row['object_id']])) {
                $pageData[$row['object_id']] = array(
                $row);
            } else {
                $pageData[$row['object_id']][] = $row;
            }
        }
        foreach ($data as $key => $row) {
            if (isset($pageData[$row['id']])) {
                $data[$key]['Pages'] = $pageData[$row['id']];
            } else {
                $data[$key]['Pages'] = array();
            }
            if (isset($imageData[$row['id']])) {
                $data[$key]['Images'] = $imageData[$row['id']];
            } else {
                $data[$key]['Images'] = array();
            }
        }
        return $data;
    }

    /**
     * Returns child folders for a given folder id
     *
     * @param integer $folderId
     * @return array
     */
    public function getFoldersByParentFolderId ($folderId)
    {
        $data = $this->conn->select(
            array('table' => 'folder_data' ,
            'where' => 'parent_id=' . (int) $folderId ,
            'orderBy' => 'folder_order, short'));
        return $this->extendFolderData($data);
    }

    /**
     * Returns specified type child folders for a given folder id
     *
     * @param integer $parentId
     * @param array $typeIds
     * @return array
     */
    public function getFoldersByParentAndType ($parentId, $typeIds)
    {
        if (is_array($typeIds)) {
            $tmp = array();
            foreach ($typeIds as $val) {
                if ((int) $val) {
                    $tmp[] = (int) $val;
                }
            }
            $typeIds = implode(', ', $tmp);
        }
        if (is_null($parentId)) {
            return $this->conn->select(
                array('table' => 'folder_data' ,
                'where' => 'visible=' . $this->conn->getBool(
                    true) . ' AND parent_id IS NULL AND folder_type_id IN (' .
                     $typeIds . ')' ,
                    'orderBy' => 'folder_order, name'));
        }
        return $this->conn->select(
            array('table' => 'folder_data' ,
            'where' => 'visible=' . $this->conn->getBool(true) . ' AND parent_id=' .
                 (int) $parentId . ' AND folder_type_id IN (' .
                 $typeIds . ')' ,
                'orderBy' => 'folder_order, name'));
    }

    /**
     * Returns all folders for a given language
     *
     * @param integer $localeId
     * @return array
     */
    public function getAllFoldersByLocaleId ($localeId)
    {
        $data = $this->conn->select(
            array('table' => 'folder_data' ,
            'where' => 'locale_id IS NULL OR locale_id = ' . (int) $localeId ,
            'orderBy' => 'folder_order, short'));
        return $this->extendFolderData($data);
    }

    /**
     * Returns all folder types
     *
     * @return array
     */
    public function getFolderTypes ()
    {
        return $this->conn->select(
            array('table' => 'folder_type_data' ,
            'orderBy' => 'description ASC'));
    }

    /**
     * Returns a folder by it's ID
     *
     * @param integer $id
     * @return array
     */
    public function getFullFolderInfoById ($id)
    {
        $fieldMap = $this->getMappableFields(
            array('f' => 'folder_data' , 'ft' => 'folder_type_data'));
        $query = array(
            'table' => 'folder_data f JOIN folder_type_data ft ON ft.id=f.folder_type_id' ,
            'fields' => $this->getFieldListFromMap($fieldMap) ,
            'where' => 'f.id=' . (int) $id);
        $data = $this->getRowFromMappedFields($fieldMap, $this->conn->selectFirst($query));
        if (! count($data)) {
            return array();
        }
        $data['f']['FolderType'] = $data['ft'];
        $data = $data['f'];
        $otData = $this->conn->select(
            array(
            'table' => 'folder_type_object_type_rel r JOIN object_type_data ot ON ot.id=r.object_type_id' ,
            'fields' => 'ot.*' ,
            'where' => 'r.folder_type_id ='.$data['FolderType']['id'],
        ));
        $otIds = array();
        foreach ($otData as $row) {
            $otIds[] = $row['id'];
        }
        if (count($otIds)) {
            $otIds = implode(', ', $otIds);
            $tmp = $this->conn->select(
                array('table' => 'object_type_column_data' ,
                'where' => "object_type_id IN ($otIds)",
                'orderBy'=>'column_number ASC'));
        } else {
            $tmp = array();
        }
        $columnData = array();
        foreach ($tmp as $row) {
            if (! isset($columnData[$row['object_type_id']])) {
                $columnData[$row['object_type_id']] = array(
                $row);
            } else {
                $columnData[$row['object_type_id']][] = $row;
            }
        }
        foreach($otData as $key=>$row) {
            if (isset($columnData[$row['id']])) {
                $otData[$key]['Columns'] = $columnData[$row['id']];
            } else {
                $otData[$key]['Columns'] = array();
            }
        }
        $data['FolderType']['ObjectTypes'] = $otData;
        return $data;
    }

    /**
     * Updates the docpath for a folder's subfolders
     *
     * @param integer $folderId
     * @param string $newDocpath
     */
    public function updateSubfolderDocpath ($folderId, $docpath)
    {
        $folders = $this->conn->select(
            array('table' => 'folder_data' ,
            'where' => 'parent_id=' . (int) $folderId));
        if (! count($folders)) {
            return;
        }
        foreach ($folders as $folder) {
            $folder['docpath'] = $docpath . '/' . $folder['short'];
            $this->conn->update(
                array('table' => 'folder_data' ,
                'where' => 'id=' . $folder['id']),
                $folder);
            $this->updateSubfolderDocpath($folder['id'],
                $folder['docpath']);
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
    public function saveFolderPage ($type, $folderId, $pageId)
    {
        $pageData = $this->conn->selectFirst(
            array('table' => 'cms_page_data' ,
            'where' => 'id=' . (int) $pageId));
        if (! count($pageData)) {
            return 'Page not found';
        }
        $folderPage = $this->conn->selectFirst(
            array('table' => 'cms_page_object_rel' ,
            'where' => 'object_id=' . (int) $folderId . ' AND relation_type=' .
                 $this->conn->quote($type) . ' AND theme_id=' .
                 $pageData['theme_id']));
        if (count($folderPage)) {
            if ($folderPage['page_id'] != (int) $pageId) {
                $folderPage['page_id'] = (int) $pageId;
                if (! $this->conn->update(
                    array(
                    'table' => 'cms_page_object_rel' ,
                    'where' => 'id=' . $folderPage['id']),
                    $folderPage)) {
                    return $this->conn->getLastError();
                }
            }
        } else {
            $folderPage = array();
            $folderPage['object_id'] = (int) $folderId;
            $folderPage['page_id'] = (int) $pageId;
            $folderPage['theme_id'] = (int) $pageData['theme_id'];
            $folderPage['relation_type'] = (int) $type;
            if (! $this->conn->insert('cms_page_object_rel', $folderPage)) {
                return $this->conn->getLastError();
            }
        }
        return '';
    }

    /**
     * Deletes all pages of the specified type for a given folder except the ones listed in the $dontDeletePages array
     *
     * @param string $type
     * @param integer $folderId
     * @param array $dontDeletePages
     * @return string Empty string if successful, the errormessage otherwise
     */
    public function deleteFolderPages ($type, $folderId, $dontDeletePages = array())
    {
        $extra = '';
        if (count($dontDeletePages)) {
            $extra = ' AND page_id NOT IN (' . implode(', ',
                $dontDeletePages) . ')';
        }
        if ($this->conn->delete('cms_page_object_rel',
            'relation_type=' . $this->conn->quote($type) . ' AND object_id=' .
                 (int) $folderId . $extra)) {
                return '';
        }
        return $this->conn->getLastError();
    }
}
?>