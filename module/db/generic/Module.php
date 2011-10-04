<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Cms_module generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Module extends module_db_DbModule implements module_db_interface_Module,
    module_db_interface_Admin
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('cms_module_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('cms_module_data', $itemData);
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
            array('table' => 'cms_module_data' ,
            'where' => 'id=' . (int) $itemId));
        $data['Params'] = $this->conn->select(
            array('table' => 'cms_module_param_data' ,
            'where' => 'module_id=' . $data['id']));
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
        return $this->basicUpdate('cms_module_data', $itemId, $itemData);
    }

    /**
     * Returns the list of modules (array with id=>name format)
     *
     * @return array
     */
    public function getModuleList ()
    {
        return $this->getBasicIdSelectList('cms_module_data');
    }

    /**
     * Returns module datas
     *
     * @return array
     */
    public function getModuleData ()
    {
        $data = $this->conn->select(array('table' => 'cms_module_data'));
        $tmp = $this->conn->select(array('table' => 'cms_module_param_data'));
        $params = array();
        foreach ($tmp as $row) {
            if (! isset($params[$row['module_id']])) {
                $params[$row['module_id']] = array($row);
            } else {
                $params[$row['module_id']][] = $row;
            }
        }
        foreach ($data as $key => $row) {
            if (isset($params[$row['id']])) {
                $data[$key]['Params'] = $params[$row['id']];
            } else {
                $data[$key]['Params'] = array();
            }
        }
        return $data;
    }

    /**
     * Returns module params for given module
     *
     * @param integer $moduleId
     * @return array
     */
    public function getModuleParamData ($moduleId)
    {
        return $this->conn->select(
            array('table' => 'cms_module_param_data' ,
            'where' => 'module_id=' . (int) $moduleId));
    }

    /**
     * Returns parameter value options for a given parameter
     *
     * @param integer $paramId
     * @return array
     */
    public function getModuleParamValuesForParam ($paramId)
    {
        return $this->conn->select(
            array('table' => 'cms_module_param_value_data' ,
            'where' => 'module_param_id=' . (int) $paramId ,
            'orderBy' => 'id ASC'));
    }

    /**
     * Returns the full module information by the given filter
     *
     * @param string $where
     * @return array
     */
    protected function getFullModuleByFilter ($where)
    {
        $data = $this->conn->selectFirst(
            array('table' => 'cms_module_data' , 'where' => $where));
        if (! count($data)) {
            return array();
        }
        $params = $this->conn->select(array('table' => 'cms_module_param_data' ,
        'where'=>'module_id=' . $data['id']));
        if (! count($params)) {
            $data['Params'] = array();
            return $data;
        }
        $ids = array();
        foreach ($params as $row) {
            $ids[] = $row['id'];
        }
        $tmp = $this->conn->select(
            array('table' => 'cms_module_param_value_data' ,
            'where' => 'module_param_id IN (' . implode(', ',$ids) . ')'));
        $values = array();
        foreach ($tmp as $row) {
            if (! isset($values[$row['module_param_id']])) {
                $values[$row['module_param_id']] = array(
                $row);
            } else {
                $values[$row['module_param_id']][] = $row;
            }
        }
        foreach ($params as $key => $row) {
            if (isset($values[$row['id']])) {
                $params[$key]['Values'] = $values[$row['id']];
            } else {
                $params[$key]['Values'] = array();
            }
        }
        $data['Params'] = $params;
        return $data;
    }

    /**
     * Returns a module's data by it's name
     *
     * @param string $name
     */
    public function getModuleByName ($name)
    {
        return $this->getFullModuleByFilter('name=' . $this->conn->quote($name));
    }

    /**
     * Returns a module's data
     *
     * @param integer $moduleId
     * @return array
     */
    public function getModule ($moduleId)
    {
        return $this->getFullModuleByFilter('id=' . (int) $moduleId);
    }

}
?>