<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11582 $
 */

/**
 * Generic administration generic module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11582 $
 */
class module_db_generic_GenericAdmin extends module_db_DbModule implements module_db_interface_GenericAdmin
{

    /**
     * Stores the name of the used model class for the table
     *
     * @var array
     */
    private $objectType;

    /**
     * Checks if the model class is set, and throws an exception if it's not
     *
     * @throws sys_exception_DatabaseException
     */
    private function checkModelType ()
    {
        if (! $this->objectType) {
            throw new sys_exception_DatabaseException(
                _('Object type not set'),
                sys_exception_DatabaseException::ERR_OBJECT_TYPE_NOT_SET);
        }
    }

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        $this->checkModelType();
        return $this->basicDelete($this->objectType['persist_class'], $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        $this->checkModelType();
        return $this->basicInsert($this->objectType['persist_class'], $itemData, array(),
            $this->objectType['id']);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        $this->checkModelType();
        return $this->basicLoad($this->objectType['persist_class'], $itemId);
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
        $this->checkModelType();
        return $this->basicUpdate($this->objectType['persist_class'], $itemId, $itemData);
    }

    /**
     * @see module_db_interface_GenericAdmin::getList()
     *
     * @param string $type
     * @param string $where
     * @return array
     */
    public function getList ($type = null, $where = '')
    {
        if (is_null($type)) {
            $typeData = $this->objectType;
        } else {
            $typeData = $this->conn->selectFirst(
                array('table' => 'object_type_data' ,
                'where' => 'short_name=' . $this->conn->quote(
                    $type)));
            if (! $typeData) {
                throw new sys_exception_DatabaseException(
                    _(
                        'Object type not found'),
                    sys_exception_DatabaseException::ERR_OBJECT_TYPE_NOT_FOUND);
            }
            $typeData['persist_class'] = sys_cache_DbSchema::makeTableName(
                $typeData['persist_class']);
        }
        $query = array('table' => $typeData['persist_class']);
        if ('' != $where) {
            $query['where'] = $where;
        }
        $data = $this->conn->select($query);
        if (! count($data)) {
            return array();
        }
        $tmp = reset($data);
        if (! isset($tmp['name'])) {
            throw new sys_exception_DatabaseException(
                _('No name field in the table'),
                sys_exception_DatabaseException::ERR_MISSING_FIELD);
        }
        $results = array();
        foreach ($data as $val) {
            $results[$val['id']] = $val['name'];
        }
        asort($results);
        return $results;
    }

    /**
     * @see module_db_interface_GenericAdmin::setObjType()
     *
     * @param string $type
     */
    public function setObjType ($type)
    {
        $data = $this->conn->selectFirst(
            array('table' => 'object_type_data' ,
            'where' => 'short_name=' . $this->conn->quote($type)));
        if (! $data) {
            throw new sys_exception_DatabaseException(
                _('Object type not found:' . $type),
                sys_exception_DatabaseException::ERR_OBJECT_TYPE_NOT_FOUND);
        }
        $data['persist_class'] = sys_cache_DbSchema::makeTableName(
            $data['persist_class']);
        $this->objectType = $data;
    }

}
?>