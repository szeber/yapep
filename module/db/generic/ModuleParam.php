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
 * Module parameter generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_ModuleParam extends module_db_DbModule implements module_db_interface_ModuleParam,
    module_db_interface_Admin
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('cms_module_param_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('cms_module_param_data', $itemData);
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
            array('table'=>'cms_module_param_data' , 'where' => 'id=' . (int) $itemId));
        $data['Values'] = $this->conn->select(
            array('table' => 'cms_module_param_value_data' ,
            'where' => 'module_param_id=' . $data['id']));
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
        return $this->basicUpdate('cms_module_param_data', $itemId, $itemData);
    }

    /**
     * Returns the list of module parameters (array with id=>description (name) format)
     *
     * @param integer $moduleId
     * @return array
     */
    public function getModuleParamList ($moduleId)
    {
        $data = $this->conn->select(
            array('table' => 'cms_module_param_data' ,
            'fields' => 'id, description, name' ,
            'where' => 'module_id=' . (int) $moduleId ,
            'orderby' => 'description ASC, name ASC'));
        foreach ($data as $item) {
            $funcs[$item['id']] = $item['description'] . ' (' . $item['name'] .
                 ')';
        }
        return $funcs;
    }

    /**
     * @see module_db_interface_ModuleParam::importParam()
     *
     * @param array $param
     */

    public function importParam (array $param)
    {
        if (! is_array($param)) {
            return;
        }
        $paramRecord = $this->conn->selectFirst(
            array('table' => 'cms_module_param_data' ,
            'where' => 'module_id=' . (int) $param['module_id'] . ' AND name=' .
                 $this->conn->quote($param['name'])));
        if (! $paramRecord || ! count($paramRecord)) {
            $paramRecord = $this->conn->getDefaultRecord('cms_module_param_data');
            $paramRecord['module_id'] = $param['module_id'];
            $paramRecord['name'] = $param['name'];
            $paramRecord['Values'] = array();
        } else {
            $paramRecord['Values'] = $this->conn->select(
                array(
                'table' => 'cms_module_param_value_data' ,
                'where' => 'module_param_id=' . $paramRecord['id']));
            unset($param['name'], $param['module_id']);
        }
        foreach ($param as $key => $val) {
            if ('Values' == $key || 'id' == $key || ! array_key_exists($key, $paramRecord)) {
                continue;
            }
            $paramRecord[$key] = $val;
        }
        if (!isset($paramRecord['id'])) {
            $this->conn->insert('cms_module_param_data', $paramRecord);
        } else {
            $this->conn->update(
                array('table' => 'cms_module_param_data' ,
                'where' => 'id=' . $paramRecord['id']),
                $paramRecord);
        }
        foreach ($paramRecord['Values'] as $value) {
            if (! isset($param['Values'][$value['value']])) {
                continue;
            }
            $value['description'] = $param['Values'][$value['value']];
            unset($param['Values'][$value['value']]);
            $this->conn->update(
                array(
                'table' => 'cms_module_param_value_data' ,
                'where' => 'id=' . $value['id']), $value);
        }
        foreach ($param['Values'] as $newValue => $newDescription) {
            $valueRecord = array();
            $valueRecord['module_param_id'] = $paramRecord['id'];
            $valueRecord['value'] = $newValue;
            $valueRecord['description'] = $newDescription;
            $this->conn->insert('cms_module_param_value_data',
                $valueRecord);
        }
    }
}
?>