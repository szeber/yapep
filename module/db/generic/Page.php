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
class module_db_generic_Page extends module_db_DbModule implements module_db_interface_Page, module_db_interface_Admin
{

    /**
     * Static array storing the default document ids
     *
     * @var array
     */
    private static $defaultDocIds = array();

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('cms_page_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        $ignoreFields = array('path');
        $data = $this->conn->getDefaultRecord('cms_page_data');
        if ($itemData['parent_id']) {
            $parentData = $this->conn->selectFirst(array(
                'table'=>'cms_page_data',
                'fields'=>'path',
                'where'=>'id='.(int)$itemData['parent_id'],
            ));
            $data['path'] = $parentData['path'] . '/' . $itemData['short_name'];
        } else {
            $data['path'] = $itemData['short_name'];
            $ignoreFields[] = 'parent_id';
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if (! $this->conn->insert('cms_page_data', $data)) {
            return $this->conn->getLastError();
        }
        if ($data['page_type'] == sys_PageManager::TYPE_DERIVED_PAGE && $data['parent_id']) {
            $parent = $this->conn->select(
                array('table' => 'cms_page_box_data' ,
                'where' => 'page_id=' . $data['parent_id']));
            foreach ($parent as $key => $box) {
                $parent[$key]['Params'] = $this->conn->select(
                    array(
                    'table' => 'cms_page_box_param_data' ,
                    'where' => 'page_box_id=' .
                         $box['id']));
            }
            if (count($parent)) {
                foreach ($parent as $parentBox) {
                    $box = array();
                    $box['page_id'] = $data['id'];
                    $box['parent_id'] = $parentBox['id'];
                    $box['boxplace_id'] = $parentBox['boxplace_id'];
                    $box['module_id'] = $parentBox['module_id'];
                    $box['name'] = $parentBox['name'];
                    $box['box_order'] = $parentBox['box_order'];
                    $box['active'] = $parentBox['active'];
                    $box['status'] = module_admin_page_Box::STATUS_ACTIVE_INHERITED |
                         module_admin_page_Box::STATUS_ORDER_INHERITED;
                    $this->conn->insert(
                        'cms_page_box_data',
                        $box);
                    foreach ($parentBox['Params'] as $parentParam) {
                        $param = array();
                        $param['parent_id'] = $parentParam['id'];
                        $param['module_param_id'] = $parentParam['module_param_id'];
                        $param['page_box_id'] = $box['id'];
                        $param['value'] = $parentParam['value'];
                        $param['is_var'] = $parentParam['is_var'];
                        $param['inherited'] = true;
                        $this->conn->insert(
                            'cms_page_box_param_data',
                            $param);
                    }
                }
            }
        }
        return $data['id'];
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
            array('table' => 'cms_page_data' ,
            'where' => 'id=' . (int) $itemId));
        $fieldMap = $this->getMappableFields(
            array('r' => 'cms_page_object_rel' , 'o' => 'object_data' ,
            'ot' => 'object_type_data'));
        $query = array(
        'table' => 'cms_page_object_rel r JOIN object_data o ON o.id=r.object_id JOIN object_type_data ot ON ot.id=o.object_type_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'r.page_id=' . $data['id']);
        $rels = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data['Rels'] = array();
        foreach ($rels as $rel) {
            $rel['o']['FullObject'] = $this->conn->selectFirst(
                array(
                'table' => sys_cache_DbSchema::makeTableName(
                    $rel['ot']['persist_class']) ,
                'where' => 'id=' . $rel['o']['id']));
            $rel['r']['Object'] = $rel['o'];
            $data['Rels'][] = $rel['r'];
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
        $ignoreFields = array('id' , 'path' , 'short_name' , 'parent_id');
        $data = $this->conn->selectFirst(
            array('table' => 'cms_page_data' ,
            'where' => 'id=' . (int) $itemId));
        if (! count($data)) {
            return 'Unable to update item because it was not found';
        }
        if (isset($itemData['short_name']) && (string) $itemData['short_name'] != (string) $data['short_name']) {
            $newPath = substr($data['path'], 0,
                (- 1 * strlen($data['short_name']))) . $itemData['short_name'];
            $data['short_name'] = $itemData['short_name'];
            $data['path'] = $newPath;
            $this->updateSubpagePath($data['id'], $newPath);
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->update(array('table'=>'cms_page_data' , 'where'=>'id=' . $data['id']), $data)) {
            return '';
        }
        return $this->conn->getLastError();
    }

    /**
     * Updates the path for a pages's subpages
     *
     * @param integer $pageId
     * @param string $path
     */
    public function updateSubpagePath ($pageId, $path)
    {
        $folders = $this->conn->select(
            array('table' => 'cms_page_data' ,
            'where' => 'parent_id=' . (int) $pageId));
        if (! count($folders)) {
            return;
        }
        foreach ($folders as $folder) {
            $folder['path'] = $path . '/' . $folder['short_name'];
            $this->conn->update(
                array('table' => 'cms_page_data' ,
                'where' => 'id=' . $folder['id']),
                $folder);
            $this->updateSubpagePath($folder['id'], $folder['path']);
        }
    }

    /**
     * Returns boxes for given cms_page_id and boxplace_id
     *
     * @param integer $pageId
     * @param integer $boxplaceId
     * @return array
     */
    public function getBoxesByPageId ($pageId, $boxplaceId)
    {
        $fieldMap = $this->getMappableFields(
            array('p' => 'cms_page_box_data' , 'm' => 'cms_module_data'));
        $query = array(
        'table' => 'cms_page_box_data p JOIN cms_module_data m ON m.id=p.module_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'p.page_id=' . (int) $pageId . ' AND p.boxplace_id=' . (int) $boxplaceId ,
        'orderBy' => 'p.box_order ASC, p.name ASC');
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data = array();
        foreach ($tmp as $row) {
            $row['p']['Module'] = $row['m'];
            $data[] = $row['p'];
        }
        return $data;
    }

    /**
     * Returns params for given cms page box
     *
     * @param integer $boxId
     * @return array
     */
    public function getBoxParamByBoxId ($boxId)
    {
        $fieldMap = $this->getMappableFields(
            array('p' => 'cms_page_box_param_data' ,
            'mp' => 'cms_module_param_data'));
        $query = array(
        'table' => 'cms_page_box_param_data p JOIN cms_module_param_data mp ON mp.id=p.module_param_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'p.page_box_id=' . (int) $boxId);
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data = array();
        foreach ($tmp as $row) {
            $row['p']['ModuleParam'] = $row['mp'];
            $data[] = $row['p'];
        }
        return $data;
    }

    /**
     * Returns pages and derived pages
     *
     * @return array
     */
    public function getPages ()
    {
        $fieldMap = $this->getMappableFields(
            array('p' => 'cms_page_data' , 't' => 'cms_template_data'));
        $query = array(
        'table' => 'cms_page_data p JOIN cms_template_data t ON t.id=p.template_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'p.page_type IN (' . sys_PageManager::TYPE_PAGE . ', ' . sys_PageManager::TYPE_DERIVED_PAGE .
             ')');
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data = array();
        foreach ($tmp as $row) {
            $row['p']['Template'] = $row['t'];
            $data[] = $row['p'];
        }
        return $data;
    }

    /**
     * Returns all pages, folders and derived pages by locale id
     *
     * @param integer $localeId
     */
    public function getPagesByLocaleId ($localeId)
    {
        return $this->conn->select(
            array('table' => 'cms_page_data' ,
            'where' => 'locale_id IS NULL OR locale_id=' . (int) $localeId ,
            'orderBy' => 'name ASC'));
    }

    /**
     * Returns boxplaces
     *
     * @return array
     */
    public function getBoxPlaces ()
    {
        return $this->conn->select(array('table' => 'cms_boxplace_data'));
    }

    /**
     * Returns the boxplaces for a given template
     *
     * @param integer $templateId
     * @return array
     */
    public function getBoxPlacesByTemplate ($templateId)
    {
        return $this->conn->select(
            array('table' => 'cms_boxplace_data' ,
            'where' => 'template_id=' . (int) $templateId));
    }

    /**
     * Returns the list of main page IDs for a given locale
     *
     * @param integer $localeId
     * @return array
     */
    public function getMainPageIdsForLocale ($localeId)
    {
        return $this->conn->select(
            array(
            'table' => 'cms_page_object_rel r JOIN cms_page_data p ON .p.id=r.page_id' ,
            'fields' => 'r.page_id, r.theme_id' ,
            'where' => 'r.relation_type=' . module_db_interface_Page::TYPE_MAIN_PAGE .
                 ' AND (p.locale_id = ' . (int) $localeId .
                 ' OR p.locale_id IS NULL)'));
    }

    /**
     * Returns the default document page IDs for a given locale
     *
     * @var integer $localeId
     * @return array
     */
    public function getDefaultDocPageIdsForLocale ($localeId)
    {
        if (empty(self::$defaultDocIds[$localeId])) {
            self::$defaultDocIds[$localeId] = $this->conn->select(
                array(
                'table' => 'cms_page_object_rel r JOIN cms_page_data p ON .p.id=r.page_id' ,
                'fields' => 'r.page_id, r.theme_id' ,
                'where' => 'r.relation_type=' . module_db_interface_Page::TYPE_DEFAULT_DOC .
                     ' AND (p.locale_id = ' .
                     (int) $localeId . ' OR p.locale_id IS NULL)'));
        }
        return self::$defaultDocIds[$localeId];
    }

    /**
     * Returns the specified page's data
     *
     * @param integer $pageId
     * @return array
     */
    public function getPageData ($pageId)
    {
        $fieldMap = $this->getMappableFields(
            array('p' => 'cms_page_data' , 't' => 'cms_template_data'));
        $query = array(
        'table' => 'cms_page_data p JOIN cms_template_data t ON t.id=p.template_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'p.id =' . (int) $pageId);
        $data = $this->getRowFromMappedFields($fieldMap,
            $this->conn->selectFirst($query));
        $data['p']['Template'] = $data['t'];
        return $data['p'];
    }

    /**
     * @see module_db_interface_Page::getBoxPlaceById()
     *
     * @param integer $boxplaceId
     * @return array
     */
    public function getBoxPlaceById ($boxplaceId)
    {
        return $this->conn->selectFirst(
            array('table' => 'cms_boxplace_data' ,
            'page_id=' . (int) $boxplaceId));
    }

    /**
     * @see module_db_interface_Page::addDefaultPageToTheme()
     *
     * @param integer $themeId
     * @param integer $pageId
     * @param integer $type
     * @return integer
     */
    public function addDefaultPageToTheme ($themeId, $pageId, $type)
    {
        if ($type != module_db_interface_Page::TYPE_DEFAULT_DOC && $type != module_db_interface_Page::TYPE_MAIN_PAGE) {
            return _('Bad type');
        }
        $rel = $this->conn->selectFirst(
            array('table' => 'cms_page_object_rel' ,
            'where' => 'theme_id=' . (int) $themeId . ' AND relation_type = ' .
                 (int) $type));
        if (! $rel) {
            $rel = $this->conn->getDefaultRecord('cms_page_object_rel');
            $rel['theme_id'] = (int) $themeId;
            $rel['relation_type'] = (int) $type;
            $rel['page_id'] = (int) $pageId;
            $this->conn->insert('cms_page_object_rel', $rel);
        } else {
            $rel['page_id'] = (int) $pageId;
            $this->conn->update(
                array('table' => 'cms_page_object_rel' ,
                'where' => 'id=' . $rel['id']), $rel);
        }
        return $rel['id'];
    }

    /**
     * @see module_db_interface_Page::getDefaultPageThemes()
     *
     * @param integer $pageId
     * @param integer $type
     */
    public function getDefaultPageThemes ($pageId, $type)
    {
        if ($type != module_db_interface_Page::TYPE_DEFAULT_DOC && $type != module_db_interface_Page::TYPE_MAIN_PAGE) {
            return array();
        }
        $fieldMap = $this->getMappableFields(
            array('r' => 'cms_page_object_rel' , 't' => 'cms_theme_data'));
        $query = array(
        'table' => 'cms_page_object_rel r JOIN cms_theme_data t ON t.id=r.theme_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'r.page_id=' . (int) $pageId . ' AND r.relation_type=' . (int) $type);
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data = array();
        foreach ($tmp as $row) {
            $row['r']['Theme'] = $row['t'];
            $data[] = $row['r'];
        }
        return $data;
    }

    /**
     * @see module_db_interface_Page::getObjectRelById()
     *
     * @param integer $relId
     * @return array
     */
    public function getObjectRelById ($relId)
    {
        return $this->selectFirst(
            array('table' => 'cms_page_object_rel' ,
            'where' => 'id=' . (int) $relId));
    }

    /**
     * @see module_db_interface_Page::deleteObjectRel()
     *
     * @param integer $relId
     */
    public function deleteObjectRel ($relId)
    {
        $this->conn->delete('cms_page_object_rel', 'id=' . (int) $relId);
    }

    /**
     * @see module_db_interface_Page::deletePageObjects()
     *
     * @param string $type
     * @param integer $pageId
     * @param array $dontDeleteObjects
     * @return string
     */
    public function deletePageObjects ($type, $pageId, $dontDeleteObjects = array())
    {
        $where = 'relation_type=' . (int) $type . ' AND page_id=' . (int) $pageId;
        if (count($dontDeleteObjects)) {
            $where .= ' AND object_id NOT IN (' . implode(', ',
                $dontDeleteObjects) . ')';
        }
        if ($this->conn->delete('cms_page_object_rel', $where)) {
            return '';
        }
        return $this->conn->getLastError();
    }

    /**
     * @see module_db_interface_Page::savePageObject()
     *
     * @param integer $type
     * @param integer $pageId
     * @param integer $objectId
     * @return boolean
     */
    public function savePageObject ($type, $pageId, $objectId)
    {
        $objectData = $this->conn->selectFirst(
            array('table' => 'object_data' ,
            'where' => 'id=' . (int) $objectId));
        if (! count($objectData)) {
            return 'Object not found';
        }
        $pageData = $this->conn->selectFirst(
            array('table' => 'cms_page_data' ,
            'where' => 'id=' . (int) $pageId));

        $objectPage = $this->conn->selectFirst(
            array('table' => 'cms_page_object_rel' ,
            'where' => 'object_id=' . (int) $objectId . ' AND relation_type=' .
                 (int) $type . ' AND theme_id=' . $pageData['theme_id']));
        if (count($objectPage)) {
            if ($objectPage['page_id'] != (int) $pageId) {
                $objectPage['page_id'] = (int) $pageId;
                if ($this->conn->update(
                    array(
                    'table' => 'cms_page_object_rel' ,
                    'where' => 'id=' . $objectPage['id']),
                    $objectPage)) {
                    return '';
                }
            }
        } else {
            $objectPage = $this->conn->getDefaultRecord('cms_page_object_rel');
            $objectPage['object_id'] = (int) $objectId;
            $objectPage['page_id'] = (int) $pageId;
            $objectPage['theme_id'] = (int) $pageData['theme_id'];
            $objectPage['relation_type'] = (int) $type;
            if ($this->conn->insert('cms_page_object_rel', $objectPage)) {
                return '';
            }
        }
        return $this->conn->getLastError();
    }

    /**
     * @see module_db_interface_Page::getPageIdForObject()
     *
     * @param integer $objectId
     * @param integer $themeId
     * @param integer $type
     * @return integer
     */
    public function getPageIdForObject ($objectId, $themeId, $type)
    {
        $data = $this->conn->selectFirst(array(
            'table'=>'cms_page_object_rel',
            'where'=>'object_id='.(int)$objectId.' AND theme_id='.(int)$themeId.' AND relation_type='.(int)$type
        ));
        if ($data && count($data)) {
            return $data['page_id'];
        }
        return null;
    }

}
?>