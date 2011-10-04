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
 * generic folder type database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_FolderType extends module_db_DbModule implements module_db_interface_FolderType,
    module_db_interface_Admin
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('folder_type_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('folder_type_data', $itemData);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        $data = $this->conn->selectFirst(
            array('table' => 'folder_type_data' ,
            'where' => 'id=' . (int) $itemId));
        if (! count($data)) {
            return array();
        }
        $data['ObjectTypes'] = $this->conn->select(
            array(
            'table' => 'folder_type_object_type_rel r JOIN object_type_data ot ON ot.id=r.object_type_id' ,
            'fields' => 'ot.*' ,
            'where' => 'r.folder_type_id=' . $data['id']));
        return $data;
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
        return $this->basicUpdate('folder_type_data', $itemId, $itemData);
    }

    /**
     * Returns the list of folder types (array with id=>name format)
     *
     * @return array
     */
    public function getFolderTypeList ()
    {
        return $this->getBasicIdSelectList('folder_type_data');
    }

    /**
     * Deletes an object type relation item
     *
     * @param integer $folderId
     * @param integer $objectId
     */
    public function deleteRelItem ($folderId, $objectId)
    {
        $this->conn->delete('folder_type_object_type_rel',
            'folder_type_id=' . (int) $folderId . ' AND object_type_id=' .
                 (int) $objectId);
    }

    /**
     * Inserts an object type relation item
     *
     * @param integer $folderId
     * @param integer $objectId
     * @return string
     */
    public function insertRelItem ($folderId, $objectId)
    {
        $data = array();
        $data['folder_type_id'] = $folderId;
        $data['object_type_id'] = $objectId;
        if ($this->conn->insert('folder_type_object_type_rel', $data)) {
            return $data['object_type_id'];
        }
        return $this->conn->getLastError();
    }

    /**
     * Loads an object relation item
     *
     * @param integer $folderId
     * @param integer $objectId
     * @return array
     */
    public function loadRelItem ($folderId, $objectId)
    {
        return $this->conn->selectFirst(
            array('table' => 'folder_type_objet_type_rel' ,
            'where' => 'folder_type_id=' . (int) $folderId . ' AND object_type_id=' .
                 (int) $objectId));
    }

    /**
     * Returns the list of object types related to the given folder type (array with id=>name format)
     *
     * @return array
     */
    public function getRelList ($folderId)
    {
        $query = array(
        'table' => 'folder_type_object_type_rel r JOIN object_type_data o ON o.id=r.object_type_id' ,
        'fields' => 'o.name, o.id' , 'where' => 'r.folder_type_id=' . (int) $folderId ,
        'orderBy' => 'o.name ASC');
        $data = $this->conn->select($query);
        $list = array();
        foreach ($data as $item) {
            $list[$item['id']] = $item['name'];
        }
        return $list;

    }
}
?>