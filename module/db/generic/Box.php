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
 * generic page database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Box extends module_db_DbModule implements module_db_interface_Box
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('cms_page_box_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        $result = $this->basicInsert('cms_page_box_data', $itemData);
        if (! is_numeric($result)) {
            return $result;
        }
        $derivedPages = $this->conn->select(
            array('table' => 'cms_page_data' ,
            'where' => 'parent_id=' . (int) $itemData['page_id'] . ' AND page_type=' .
                 sys_PageManager::TYPE_DERIVED_PAGE));
        if (! count($derivedPages)) {
            return $result;
        }
        foreach ($derivedPages as $page) {
            $insertData = $itemData;
            $insertData['page_id'] = $page['id'];
            $insertData['parent_id'] = $result;
            $insertData['status'] = module_admin_page_Box::STATUS_ACTIVE_INHERITED |
                 module_admin_page_Box::STATUS_ORDER_INHERITED;
            $this->insertItem($insertData);
        }
        return $result;
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
            array('table' => 'cms_page_box_data' ,
            'where' => 'id=' . (int) $itemId));
        if (! $data || ! count($data)) {
            return array();
        }
        $moduleParams = $this->conn->select(
            array('table' => 'cms_module_param_data' ,
            'where' => 'module_id=' . $data['module_id']));
            $mpIds = array();
        foreach($moduleParams as $row) {
            $mpIds[] = $row['id'];
        }
        if (!count($mpIds)) {
            $tmp = array();
        } else {
            $tmp = $this->conn->select(array('table'=>'cms_module_param_value_data', 'where'=>'module_param_id IN ('.implode(', ', $mpIds).')'));
        }
        $values = array();
        foreach($tmp as $row) {
            if (!is_array($values[$row['module_param_id']])) {
                $values[$row['module_param_id']] = array($row);
            } else {
                $values[$row['module_param_id']][] = $row;
            }
        }
        foreach($moduleParams as $key=>$row) {
            if (!isset($values[$row['id']])) {
                $values[$row['id']] = array();
            }
            $moduleParams[$key]['Values'] = $values[$row['id']];
        }
        $data['ModuleParams'] = $moduleParams;
        $fieldMap = $this->getMappableFields(array('mp'=>'cms_module_param_data', 'p'=>'cms_page_box_param_data'));
        $query = array(
            'table'=>'cms_page_box_param_data p JOIN cms_module_param_data mp ON mp.id=p.module_param_id',
            'fields'=>$this->getFieldListFromMap($fieldMap),
            'where'=>'page_box_id='.$data['id'],
        );
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data['Params'] = array();
        foreach($tmp as $row) {
            $row['mp']['Values'] = $values[$row['mp']['id']];
            $row['p']['ModuleParam'] = $row['mp'];
            $data['Params'][] = $row['p'];
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
        $result = $this->basicUpdate('cms_page_box_data', $itemId, $itemData);
        $this->updateDerivedPages($itemId);
        return $result;
    }

    /**
     * Recursively updates a pages derived pages information
     *
     * @param integer $itemId
     */
    protected function updateDerivedPages ($itemId)
    {
        $parent = $this->conn->selectFirst(
            array('table' => 'cms_page_box_data' ,
            'where' => 'id=' . (int) $itemId));
        $derived = $this->conn->select(
            array('table' => 'cms_page_box_data' ,
            'where' => 'parent_id=' . (int) $itemId));
        if (! count($derived)) {
            return;
        }
        foreach ($derived as $page) {
            $changed = false;
            if ($page['status'] & module_admin_page_Box::STATUS_ACTIVE_INHERITED) {
                $page['active'] = $parent['active'];
                $changed = true;
            }
            if ($page['status'] & module_admin_page_Box::STATUS_ORDER_INHERITED) {
                $page['box_order'] = $parent['box_order'];
                $changed = true;
            }
            if ($changed) {
                $this->conn->update(
                    array(
                    'table' => 'cms_page_box_data' ,
                    'where' => 'id=' . $page['id']),
                    $page);
            }
        }
        foreach ($derived as $page) {
            $this->updateDerivedPages($page['id']);
        }
    }

    /**
     * Returns a box's data
     *
     * @param integer $boxId
     * @return array
     */
    public function getBox ($boxId)
    {
        $data = $this->conn->selectFirst(
            array('table' => 'cms_page_box_data' ,
            'where' => 'id=' . (int) $boxId));
        if (! $data || ! count($data)) {
            return;
        }
        $fieldMap = $this->getMappableFields(
            array('p' => 'cms_page_box_param_data' ,
            'mp' => 'cms_module_param_data'));
        $query = array(
        'table' => 'cms_page_box_param_data p LEFT JOIN cms_module_param_data mp ON p.module_param_id=mp.id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'p.page_box_id=' . (int) $boxId);
        $paramData = $this->getDataFromMappedFields($fieldMap,
            $this->conn->select($query));
        $data['Params'] = array();
        foreach ($paramData as $param) {
            $param['p']['ModuleParam'] = $param['mp'];
            $data['Params'][] = $param['p'];
        }
        return $data;
    }

    /**
     * @see module_db_interface_Box::updateBoxParams()
     *
     * @param integer $boxId
     * @param array $params
     * @param boolean $onlyInherited
     */
    public function updateBoxParams ($boxId, $params, $onlyInherited = false)
    {
        // TODO TEST!!!!!!
        $fieldMap = $this->getMappableFields(
            array('b' => 'cms_page_box_data' , 'm' => 'cms_module_data'));
        $query = array(
        'table' => 'cms_page_box_data b JOIN cms_module_data m ON m.id=b.module_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'b.id=' . (int) $boxId);
        $tmpData = $this->getRowFromMappedFields($fieldMap,
            $this->conn->selectFirst($query));
        $boxData = $tmpData['b'];
        $boxData['Module'] = $tmpData['m'];
        $boxData['ModuleParams'] = $this->conn->select(
            array('table' => 'cms_module_param_data' ,
            'where' => 'module_id=' . $boxData['module_id']));
        $fieldMap = $this->getMappableFields(
            array('p' => 'cms_page_box_param_data' ,
            'mp2' => 'cms_module_param_data'));
        $query = array(
        'table' => 'cms_page_box_param_data p JOIN cms_module_param_data mp2 ON mp2.id=p.module_param_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'p.page_box_id=' . $boxData['id']);
        $tmpData = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $boxData['Params'] = array();
        foreach ($tmpData as $row) {
            $row['p']['ModuleParam'] = $row['mp2'];
            $boxData['Params'][] = $row['p'];
        }

        if (is_null($params)) {
            $params = array();
        }
        $moduleParams = array();
        if ($boxData['parent_id']) {
            $inherited = true;
        }
        foreach ($boxData['ModuleParams'] as $param) {
            $moduleParams[$param['name']] = array('id' => $param['id'] ,
            'is_var' => $param['default_is_variable'] ,
            'value' => $param['default_value'] ,
            'inherited' => $inherited ,
            'allow_variable' => $param['allow_variable']);
        }
        foreach ($params as $name => $param) {
            if (! isset($moduleParams[$name])) {
                continue;
            }
            $moduleParams[$name]['value'] = $param['value'];
            $moduleParams[$name]['is_var'] = $moduleParams[$name]['allow_variable'] &&
                 $param['isVariable'];
            $moduleParams[$name]['inherited'] = $inherited && $param['useInherited'];
        }
        $savedParams = array();
        foreach ($boxData['Params'] as $param) {
            if (! isset($moduleParams[$param['ModuleParam']['name']])) {
                $this->conn->delete(
                    'cms_page_box_param_data',
                    'id=' . $param['id']);
                continue;
            }
            if ($onlyInherited && ! $param['inherited']) {
                if (! $params['parent_id'] && $inherited) {
                    $parentId = $this->conn->selectFirst(
                        array(
                        'table' => 'cms_page_box_param_data' ,
                        'fields' => 'id' ,
                        'where' => 'module_param_id=' .
                             $param['module_param_id'] .
                             ' AND page_box_id=' .
                             $boxData['parent_id']));
                    if (! $parentId || ! count(
                        $parentId)) {
                        $this->updateBoxParams(
                            $boxData['parent_id'],
                            array(),
                            true);
                        $parentId = $this->conn->selectFirst(
                            array(
                            'table' => 'cms_page_box_param_data' ,
                            'fields' => 'id' ,
                            'where' => 'module_param_id=' .
                                 $param['module_param_id'] .
                                 ' AND page_box_id=' .
                                 $boxData['parent_id']));
                    }
                    $param['parent_id'] = $parentId['id'];
                }
                $savedParams[] = $param['ModuleParam']['name'];
                continue;
            }
            $currentParam = $moduleParams[$param['ModuleParam']['name']];
            $param['is_var'] = $currentParam['is_var'];
            $param['inherited'] = $currentParam['inherited'];
            if ($param['inherited']) {
                if (! $param['parent_id']) {
                    $parentId = $this->conn->selectFirst(
                        array(
                        'table' => 'cms_page_box_param_data' ,
                        'fields' => 'id' ,
                        'where' => 'module_param_id=' .
                             $param['module_param_id'] .
                             ' AND page_box_id=' .
                             $boxData['parent_id']));
                    if (! $parentId || ! count(
                        $parentId)) {
                        $this->updateBoxParams(
                            $boxData['parent_id'],
                            array(),
                            true);
                        $parentId = $this->conn->selectFirst(
                            array(
                            'table' => 'cms_page_box_param_data' ,
                            'fields' => 'id' ,
                            'where' => 'module_param_id=' .
                                 $param['module_param_id'] .
                                 ' AND page_box_id=' .
                                 $boxData['parent_id']));
                    }
                    $param['parent_id'] = $parentId['id'];
                }
                $parentValue = $this->conn->selectFirst(
                    array(
                    'table' => 'cms_page_box_param_data' ,
                    'fields' => 'value, is_var' ,
                    'where' => 'id=' . $param['parent_id']));
                $currentParam['is_var'] = $parentValue['is_var'];
                $currentParam['value'] = $parentValue['value'];
            }
            $param['is_var'] = $currentParam['is_var'];
            $param['value'] = $currentParam['value'];
            $this->conn->update(
                array(
                'table' => 'cms_page_box_param_data' ,
                'where' => 'id=' . $param['id']), $param);
            $savedParams[] = $param['ModuleParam']['name'];
        }
        if (count($savedParams) < count($moduleParams)) {
            foreach ($moduleParams as $name => $param) {
                if (in_array($name, $savedParams)) {
                    continue;
                }
                $newParam = $this->conn->getDefaultRecord('cms_page_box_param_data');
                $newParam['module_param_id'] = $param['id'];
                $newParam['page_box_id'] = (int) $boxId;
                $newParam['value'] = $param['value'];
                $newParam['is_var'] = $param['is_var'];
                $newParam['inherited'] = $param['inherited'];
                if ($param['inherited']) {
                    $parentData = $this->conn->selectFirst(
                        array(
                        'table' => 'cms_page_box_param_data' ,
                        'where' => 'page_box_id=' .
                             $boxData['parent_id'] .
                             ' AND module_param_id=' .
                             $param['id']));
                    if (! $parentData || ! count(
                        $parentData)) {
                        $this->updateBoxParams(
                            $boxData['parent_id'],
                            array(),
                            true);
                        $parentData = $this->conn->selectFirst(
                            array(
                            'table' => 'cms_page_box_param_data' ,
                            'where' => 'page_box_id=' .
                                 $boxData['parent_id'] .
                                 ' AND module_param_id=' .
                                 $param['id']));
                    }
                    $newParam['parent_id'] = $parentData['id'];
                    $newParam['value'] = $parentData['value'];
                }
                $this->conn->insert(
                    'cms_page_box_param_data',
                    $newParam);
            }
        }
        $childBoxes = $this->conn->select(
            array('table' => 'cms_page_box_data' , 'field' => 'id' ,
            'where'=>'parent_id=' . (int) $boxId));
        foreach ($childBoxes as $child) {
            $this->updateBoxParams($child['id'], array(), true);
        }
    }
}
?>