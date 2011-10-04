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
 * generic object type database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_ObjectType extends module_db_DbModule implements module_db_interface_ObjectType,
    module_db_interface_Admin
{

    /**
     * Returns an object type by it's short name
     *
     * @param string $typeName
     * @return array
     */
    public function getObjectTypeByShortName ($typeName)
    {
        return $this->conn->selectFirst(array(
            'table'=>'object_type_data',
            'where'=>'short_name='.$this->conn->quote($typeName),
        ));
    }

    /**
     * Returns all object type's admin handler that have one set
     *
     */
    public function getObjectTypeAdmins ()
    {
        return $this->conn->select(array(
            'table'=>'object_type_data',
            'fields'=>'id, admin_class',
            'where'=>'admin_class IS NOT NULL',
        ));
    }

    /**
     * Returns the listed column data by an object type id
     *
     * @param integer $id
     */
    public function getListColumnsByObjectTypeId ($id)
    {
        return $this->conn->select(array(
            'table'=>'object_type_column_data',
            'where'=>'object_type_id='.(int)$id,
            'orderBy'=>'column_number ASC',
        ));
    }

    /**
     * Returns the list of object types (array with id=>name format)
     *
     * @return array
     */
    public function getObjectTypeList ()
    {
        return $this->getBasicIdSelectList('object_type_data');
    }

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        $this->basicDelete('object_type_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('object_type_data', $itemData);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        $data = $this->basicLoad('object_type_data', $itemId);
        if (count($data)) {
            $data['Columns'] = $this->conn->select(array(
                'table'=>'object_type_column_data',
                'where'=>'object_type_id='.$data['id'],
            ));
        }
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
        return $this->basicUpdate('object_type_data', $itemId, $itemData);
    }

    /**
     * Returns the list of columns for a given object type (array with id=>title format)
     *
     * @param integer $typeId
     * @return array
     */
    public function getObjectTypeColumnList ($typeId)
    {
        return $this->getBasicIdSelectList('object_type_column_data', 'id', 'title',
            'object_type_id = ' . (int) $typeId);
    }

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteColumnItem ($itemId)
    {
        $this->basicDelete('object_type_column_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertColumnItem ($itemData)
    {
        $data = $this->conn->getDefaultRecord('object_type_column_data');
        $this->modifyData($data, $itemData);
        $data['object_type_id'] = $itemData['object_type_id'];
        if ($this->conn->insert('object_type_column_data', $data)) {
            return $data['id'];
        }
        return $this->conn->getLastError();
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadColumnItem ($itemId)
    {
        return $this->basicLoad('object_type_column_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::updateItem()
     *
     * @param integer $itemId
     * @param array $itemData
     * @return string
     */
    public function updateColumnItem ($itemId, $itemData)
    {
        return $this->basicUpdate('object_type_column_data', $itemId, $itemData,
            array('object_type_id'));
    }

    /**
     * @see module_db_interface_ObjectType::getAllObjectTypes()
     *
     * @return array
     */
    public function getAllObjectTypes ()
    {
        return $this->conn->select(array(
            'table'=>'object_type_data',
            'orderBy'=>'name ASC',
        ));
    }

    /**
     * @see module_db_interface_ObjectType::getObjectTypesByIds()
     *
     * @param array $objectTypeIds
     */
    public function getObjectTypesByIds ($objectTypeIds)
    {
        foreach ($objectTypeIds as $key=>$val) {
            if (!(int)$val) {
                unset($objectTypeIds[$key]);
            } else {
                $objectTypeIds[$key] = (int)$val;
            }
        }
        if (!count($objectTypeIds)) {
            return array();
        }
        return $this->conn->select(array(
            'table'=>'object_type_data',
            'where'=>'id IN ('.implode(', ', $objectTypeIds).')',
            'orderBy'=>'name ASC',
        ));
    }

    /**
     * Returns the data for all objec types used in the site except for the ones that have their short names listed in $igoreTypes
     *
     * @param array $ignoreTypes
     * @return array
     */
    public function getUsedObjectTypes ($ignoreTypes = array(), $onlyUsed = true)
    {
        foreach ($ignoreTypes as &$val) {
            $val = $this->conn->quote(trim($val));
        }
        $query = array(
            'table'=>'object_type_data t',
            'fields'=>'DISTINCT t.*',
        );
        if (count($ignoreTypes)) {
            $query['where'] = 't.short_name NOT IN (' . implode(', ',
                $ignoreTypes) . ')';
        }
        if ($onlyUsed) {
            $query['table'] .= ' JOIN object_data o ON o.object_type_id=t.id';
        }
        return $this->conn->select($query);
    }
}
?>