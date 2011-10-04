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
 * generic doc database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Doc extends module_db_DbModule implements module_db_interface_Admin,
    module_db_interface_AdminList, module_db_interface_Doc
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        $this->basicDelete('doc_data', $itemId);
    }

    /**
     * @see module_db_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        if ($itemData['docObjectTypeName'] == 'docCopy') {
            $objectId = $itemData['object_id'];
            $objTypeId = $itemData['ref_object_type_id'];
        } else {
            $objTypeData = $this->conn->selectFirst(
                array('table' => 'object_type_data' ,
                'where' => 'name=' . $this->conn->quote(
                    $itemData['docObjectTypeName'])));
            $persistClass = $objTypeData['persist_class'];
            $objectId = $this->basicInsert(
                sys_cache_DbSchema::makeTableName(
                    $persistClass),
                $itemData, array(), $objTypeData['id']);
            $objTypeId = $objTypeData['id'];
        }
        $doc = array();
        $doc['object_type_id'] = $this->getObjectTypeIdByShortName('document');
        $doc['name'] = $itemData['doctitle'];
        $doc['object_id'] = $objectId;
        $doc['ref_object_type_id'] = $objTypeId;
        $doc['docname'] = $itemData['docname'];
        $doc['start_date'] = $itemData['start_date'];
        $doc['end_date'] = $itemData['end_date'];
        $doc['status'] = (int) $itemData['status'];
        $doc['folder_id'] = $itemData['docFolderId'];
        $doc['locale_id'] = $itemData['docLocale'];
        $this->conn->insert('doc_data', $doc);
        return $doc['id'];
    }

    /**
     * @see module_db_Admin::listItems()
     *
     * @param integer $localeId
     * @param integer $folder
     * @param integer $limit
     * @param integer $offset
     * @param boolean $subFolders
     * @param array $filter
     * @return array
     */
    public function listItems ($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false,
        $filter = null, $orderBy = null, $orderDir = null)
    {
        $fieldMap = $this->getMappableFields(
            array('d' => 'doc_data' , 'f' => 'folder_data'));
        $query = array('table' => 'doc_data d JOIN folder_data f ON f.id=d.folder_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'd.locale_id = ' . (int) $localeId);
        if ($subFolders) {
            $folderData = $this->basicLoad('folder_data', $folder);
            $query['where'] .= ' AND f.docpath LIKE ' . $this->conn->quote(
                $folderData['docpath'] . '%');
        } else {
            $query['where'] .= ' AND d.folder_id = ' . (int) $folder;
        }
        $schema = $this->conn->getTableSchema('doc_data');
        if (is_array($filter) && count($filter)) {
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         'd.'.$name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                }
            }
        }
        if (! is_null($orderBy)) {
            if (! isset($schema['columns'][$orderBy])) {
                $orderBy = 'd.name';
            }
            switch (strtolower($orderDir)) {
                case 'desc':
                case '-':
                    $query['orderBy'] .= 'd.'.$orderBy .
                         ' DESC';
                    break;
                default:
                    $query['orderBy'] .= 'd.'.$orderBy .
                         ' ASC';
                    break;
            }
        }
        if ((int) $limit) {
            $query['limit'] = (int) $limit;
            if ((int) $offset < 0) {
                $offset = 0;
            }
            $query['offset'] = (int) $offset;
        }
        $data = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $docs = array();
        foreach ($data as $row) {
            $row['d']['Folder'] = $row['f'];
            $docs[] = $row['d'];
        }
        return $docs;
    }

    /**
     * Returns the count of items that match the filters in a folder
     *
     * @param integer $localeId
     * @param integer $folder
     * @param boolean $subFolders
     * @param array $filter
     * @return integer
     */
    public function getListResultCount ($localeId, $folder = null, $subFolders = false, $filter = null)
    {
        $query = array('table' => 'doc_data d JOIN folder_data f ON f.id=d.folder_id' ,
        'fields' => $this->conn->getFunc('COUNT', array('d.id')) . ' AS itemCount' ,
        'where' => 'd.locale_id = ' . (int) $localeId);
        if ($subFolders) {
            $folderData = $this->basicLoad('folder_data', $folder);
            $query['where'] .= ' AND f.docpath LIKE ' . $this->conn->quote(
                $folderData['docpath'] . '%');
        } else {
            $query['where'] .= ' AND d.folder_id = ' . (int) $folder;
        }
        if (is_array($filter) && count($filter)) {
            $schema = $this->conn->getTableSchema('doc_data');
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         'd.'.$name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                }
            }
        }
        $count = $this->conn->selectFirst($query);
        return $count['itemCount'];
    }

    /**
     * @see module_db_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        $docData = $this->basicLoad('doc_data', $itemId);
        if (! count($docData)) {
            return array();
        }
        $docData['doctitle'] = $docData['name'];
        $docData['docFolderId'] = $docData['folder_id'];
        $otData = $this->conn->selectFirst(
            array('table' => 'object_type_data' ,
            'where' => 'id=' . $docData['ref_object_type_id']));
        $objData = $this->basicLoad(
            sys_cache_DbSchema::makeTableName($otData['persist_class']),
            $docData['object_id']);
        if ($objData) {
            foreach ($objData as $key => $val) {
                $docData[$key] = $val;
            }
        }
        return $docData;
    }

    /**
     * @see module_db_Admin::updateItem()
     *
     * @param integer $itemId
     * @param array $itemData
     * @return string
     */
    public function updateItem ($itemId, $itemData)
    {
        $doc = $this->conn->selectFirst(
            array('table' => 'doc_data' ,
            'where' => 'id=' . (int) $itemId));
        if (isset($itemData['doctitle']))
            $doc['name'] = $itemData['doctitle'];
        if (isset($itemData['docname']))
            $doc['docname'] = $itemData['docname'];
        if (isset($itemData['doctitle']))
            $doc['start_date'] = $itemData['start_date'];
        if (isset($itemData['end_date']))
            $doc['end_date'] = $itemData['end_date'];
        if (isset($itemData['status']))
            $doc['status'] = (int) $itemData['status'];
        if (isset($itemData['docfolder_id'])) {
            $doc['folder_id'] = $itemData['docfolder_id'];
        }
        if (! $this->conn->update(
            array('table' => 'doc_data' , 'where' => 'id=' . $doc['id']),
            $doc)) {
            return $this->conn->getLastError();
        }
        $otData = $this->conn->selectFirst(
            array('table' => 'object_type_data' ,
            'where' => 'id=' . $doc['ref_object_type_id']));
        return $this->basicUpdate(
            sys_cache_DbSchema::makeTableName($otData['persist_class']),
            $doc['object_id'], $itemData);
    }

    /**
     * Returns a document's data by its language, path and name
     *
     * @param integer $localeId
     * @param string $docpath
     * @param string $docname
     * @param boolean $inactive
     * @return array If true, returns the document even if it's inactive
     */
    public function getDocByDocPath ($localeId, $docpath, $docname, $inactive = false)
    {
        return $this->getDoc(
            '(f.locale_id IS NULL OR f.locale_id = ' . (int) $localeId . ') AND f.docpath = ' .
                 $this->conn->quote($docpath) . ' AND d.docname = ' .
                 $this->conn->quote($docname) . ' AND d.locale_id=' .
                 (int) $localeId, $inactive);
    }

    /**
     * Returns a document's data by its ID
     *
     * @param integer $docid
     * @param boolean $inactive
     * @return array If true, returns the document even if it's inactive
     */
    public function getDocByDocId ($docid, $inactive = false)
    {
        return $this->getDoc('d.id = ' . (int) $docid, $inactive);
    }

    /**
     * Returns the object type's data
     *
     * @param integer $objectType
     * @return array
     */
    public function getObjectTypeData ($objectType)
    {
        return $this->basicLoad('object_type_data', $objectType);
    }

    /**
     * Returns a doc's data
     *
     * @param string $filter SQL WHERE
     * @param array $params
     * @param boolean $inactive If true, returns the document even if it's inactive
     * @return array
     */
    protected function getDoc ($filter, $inactive = false)
    {
        $fieldMap = $this->getMappableFields(
            array('d' => 'doc_data' , 'o' => 'object_data' ,
            'f' => 'folder_data'));
        $query = array(
        'table' => 'doc_data d JOIN object_data o ON o.id=d.object_id JOIN folder_data f ON f.id=d.folder_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) , 'where' => $filter);
        $query['where'] .= $this->makeDocInactiveExtra($inactive);
        $docData = $this->getRowFromMappedFields($fieldMap, $this->conn->selectFirst($query));
        if (! $docData || !$docData['d']['id']) {
            return false;
        }
        $docData['d']['Object'] = $docData['o'];
        $docData['d']['Folder'] = $docData['f'];
        $docData = $docData['d'];
        $otData = $this->basicLoad('object_type_data',
            $docData['Object']['object_type_id']);
        $docData['FullObject'] = $this->basicLoad(
            sys_cache_DbSchema::makeTableName($otData['persist_class']),
            $docData['Object']['id']);
        return $docData;
    }

    /**
     * Returns a document from a folder
     *
     * @param integer $localeId
     * @param string $docPath
     * @param array $objectTypes
     * @param integer $queryType
     * @param integer $limit
     * @param integer $offset
     * @param boolean $inactive
     * @return array
     */
    public function getDocFromFolder ($localeId, $docPath, $objectTypes, $queryType, $limit = 1, $offset = 0,
        $inactive = false)
    {
        $where = '(f.locale_id IS NULL OR f.locale_id='.(int)$localeId.') AND (d.locale_id IS NULL OR d.locale_id='.(int)$localeId.') AND f.docpath=' . $this->conn->quote($docPath);
        return $this->getDocsByFilter($where, $objectTypes, $queryType, $limit, $offset,
            $inactive, true);
    }

    /**
     * @see module_db_interface_Doc::getDocsByRelation()
     *
     * @param integer $relationType
     * @param array $relObjectIds
     * @param array $objectTypes
     * @param integer $queryType
     * @param integer $limit
     * @param integer $offset
     * @param boolean $inactive
     * @return array
     */
    public function getDocsByRelation ($relationType, $relObjectIds, $objectTypes, $queryType,
        $limit = 1, $offset = 0, $inactive = false)
    {
        $where = 'd.id IN (' . implode(', ',
            $this->getDocRelIds($relationType, $relObjectIds)) . ')';
        return $this->getDocsByFilter($where, $objectTypes, $queryType, $limit, $offset,
            $inactive, false);
    }

    /**
     * @see module_db_interface_Doc::getDocCountByRelation()
     *
     * @param integer $relationType
     * @param integer $relObjectId
     * @param array $objectTypes
     * @param boolean $inactive
     * @return integer
     */
    public function getDocCountByRelation ($relationType, $relObjectId, $objectTypes, $inactive = false)
    {
        $where = 'd.id IN (' . implode(', ',
            $this->getDocRelIds($relationType, $relObjectId)) . ')';
        return $this->getDocsByFilter($where, $objectTypes, $inactive, false);
    }

    /**
     * @see module_db_interface_Doc::getDocCountFromFolder()
     *
     * @param itneger $localeId
     * @param unknown_type $docPath
     * @param unknown_type $objectTypes
     * @param unknown_type $inactive
     * @return integer
     */
    public function getDocCountFromFolder ($localeId, $docPath, $objectTypes, $inactive = false)
    {
        $where = '(f.locale_id IS NULL OR f.locale_id='.(int)$localeId.') AND (d.locale_id IS NULL OR d.locale_id='.(int)$localeId.') AND f.docpath=' . $this->conn->quote($docPath);
        return $this->getDocCountByFilter($where, $objectTypes, $inactive, false);
    }

    /**
     * @see module_db_interface_Doc::findValidDocname()
     *
     * @param string $docName
     * @param integer $folderId
     * @param integer $excludeId
     */
    public function findValidDocname ($localeId, $docName, $folderId, $excludeId = 0)
    {
        $docNameList = $this->conn->selectFirst(
            array('table' => 'doc_data' , 'fields' => 'docname' ,
            'where' => 'docname=' . $this->conn->quote($docName) . ' AND folder_id=' .
                 (int) $folderId . ' AND locale_id=' . (int) $localeId .
                 ' AND id<>' . (int) $excludeId));
        if (! $docNameList) {
            return $docName;
        }
        $docNameList = $this->conn->select(
            array('table' => 'doc_data' , 'fields' => 'docname' ,
            'where' => 'docname LIKE ' . $this->conn->quote(
                $docName . '-%') . ' AND folder_id=' . (int) $folderId .
                 ' AND locale_id=' . (int) $localeId . ' AND id<>' .
                 (int) $excludeId ,
                'orderBy' => 'docname ASC'));
        $docNameArr = array();
        foreach ($docNameList as $val) {
            $docNameArr[] = $val['docname'];
        }
        unset($docNameList);
        $counter = 2;
        while (in_array($docName . '-' . $counter, $docNameArr)) {
            $counter ++;
        }
        return $docName . '-' . $counter;
    }

    protected function addDocObjectTypeFilter ($objectTypes)
    {
        $where = '';
        if (is_array($objectTypes)) {
            $tmp = array();
            foreach ($objectTypes as $val) {
                if (is_numeric($val)) {
                    $tmp[] = (int) $val;
                } else {
                    $tmp[] = (int) $this->getObjectTypeIdByShortName(
                        $val);
                }
            }
            $where .= ' AND d.ref_object_type_id IN ( ' . implode(', ',
                $tmp) . ' )';
        } elseif ($objectTypes) {
            if (! is_numeric($objectTypes)) {
                $objectTypes = (int) $this->getObjectTypeIdByShortName(
                    $objectTypes);
            }
            $where .= ' AND d.ref_object_type_id IN ( ' . $objectTypes . ' )';
        }
        return $where;
    }

    protected function getDocsByFilter ($docWhere, $objectTypes, $queryType, $limit, $offset, $inactive,
        $getLinks)
    {
        $fieldMap = $this->getMappableFields(
            array('d' => 'doc_data' , 'o' => 'object_data' ,
            'ot' => 'object_type_data' , 'f' => 'folder_data'));
        $query = array(
        'table' => 'doc_data d JOIN object_data o ON o.id=d.object_id JOIN object_type_data ot ON o.object_type_id=ot.id JOIN folder_data f ON f.id=d.folder_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) , 'where' => $docWhere ,
        'limit' => (int) $limit , 'offset' => (int) $offset);
        $query['where'] .= $this->makeDocInactiveExtra($inactive);
        $query['where'] .= $this->addDocObjectTypeFilter($objectTypes);

        switch ($queryType) {
            case module_db_interface_Doc::TYPE_NAME:
                $query['orderBy'] = 'd.name ASC, d.id ASC';
                break;
            case module_db_interface_Doc::TYPE_RANDOM:
                $query['orderBy'] = 'RANDOM()';
                break;
            case module_db_interface_Doc::TYPE_NEWEST:
            default:
                $query['orderBy'] = 'd.start_date DESC, d.id ASC';
                $order2 = 'l.id DESC';
                break;
        }
        if (! $order2) {
            $order2 = $query['orderBy'];
        }
        $docData = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        if ($getLinks) {
            $fieldMap = $this->getMappableFields(
                array('d' => 'doc_data' ,
                'o' => 'object_data' ,
                'ot' => 'object_type_data' ,
                'df' => 'folder_data'));
            $query['table'] = 'doc_link_data l JOIN folder_data f ON l.folder_id=f.id ' .
                 'JOIN doc_data d ON d.id=l.doc_id JOIN folder_data df ON d.folder_id=df.id ' .
                 'JOIN object_data o ON o.id=d.object_id JOIN object_type_data ot ON ot.id=o.object_type_id';
            $query['fields'] = $this->getFieldListFromMap($fieldMap);
            $query['orderBy'] = $order2;
            $linkData = $this->getDataFromMappedFields($fieldMap,
                $this->conn->select($query));
        } else {
            $linkData = array();
        }
        if (! count($docData) && ! count($linkData)) {
            return array();
        }
        $docs = array();
        foreach ($docData as $doc) {
            $doc['d']['Folder'] = $doc['f'];
            $doc['d']['Object'] = $doc['o'];
            $doc['d']['FullObject'] = $this->conn->selectFirst(
                array(
                'table' => sys_cache_DbSchema::makeTableName(
                    $doc['ot']['persist_class']) ,
                'where' => 'id=' . $doc['o']['id']));
            $docs[] = $doc['d'];
        }
        foreach ($linkData as $doc) {
            $doc['d']['Folder'] = $doc['df'];
            $doc['d']['Object'] = $doc['o'];
            $doc['d']['FullObject'] = $this->conn->selectFirst(
                array(
                'table' => sys_cache_DbSchema::makeTableName(
                    $doc['ot']['persist_class']) ,
                'where' => 'id=' . $doc['o']['id']));
            $docs[] = $doc['d'];
        }
        return $docs;
    }

    protected function getDocRelIds ($relationType, $relObjectIds)
    {
        if (! is_array($relObjectIds)) {
            $relObjectIds = array($relObjectIds);
        }
        if (! count($relObjectIds)) {
            return array();
        }
        $ids = array();
        $where = 'IN (' . implode(', ', $relObjectIds) . ') AND r.relation_type = ' . (int) $relationType;
        $parentQuery = array('fields' => 'child_id' , 'table' => 'object_object_rel' ,
        'where' => 'parent_id=' . $where);
        $childQuery = array('fields' => 'child_id' , 'table' => 'object_object_rel' ,
        'where' => 'parent_id=' . $where);
        $tmp = array_merge($this->conn->select($parentQuery),
            $this->conn->select($childQuery));
        foreach ($tmp as $row) {
            $ids[] = reset($row);
        }
        return $ids;
    }

    protected function getDocCountByFilter ($docWhere, $objectTypes, $inactive, $getLinks)
    {
        $query = array(
        'table' => 'doc_data d JOIN object_data o ON o.id=d.object_id JOIN object_type_data ot ON o.object_type_id=ot.id JOIN folder_data f ON f.id=d.folder_id' ,
        'fields' => $this->conn->getFunc('COUNT', array('*')) . ' AS item_count' ,
        'where' => $docWhere);
        $query['where'] .= $this->makeDocInactiveExtra($inactive);
        $query['where'] .= $this->addDocObjectTypeFilter($objectTypes);

        $tmp = $this->conn->selectFirst($query);
        $itemCount = $tmp['item_count'];
        if ($getLinks) {
            $query['table'] = 'doc_link_data l JOIN folder_data f ON l.folder_id=f.id ' .
                 'JOIN doc_data d ON d.id=l.doc_id JOIN folder_data df ON d.folder_id=df.id ' .
                 'JOIN object_data o ON o.id=d.object_id JOIN object_type_data ot ON ot.id=o.object_type_id';
            $tmp = $this->conn->selectFirst($query);
            $itemCount += $tmp['item_count'];
        }
        return $itemCount;
    }

    /**
     * @see module_db_interface_Doc::moveFolderDocs()
     *
     * @param integer $localeId
     * @param integer $srcFolder
     * @param integer $trgtFolder
     */
    public function moveFolderDocs ($localeId, $srcFolder, $trgtFolder)
    {
        $data = $this->conn->select(
            array('table' => 'doc_data' ,
            'where' => 'folder_id = ' . (int) $srcFolder . ' AND locale_id = ' .
                 (int) $trgtFolder));
        foreach ($data as $doc) {
            $doc['folder_id'] = (int) $trgtFolder;
            $this->conn->update(
                array('table' => 'doc_data' ,
                'where' => 'id=' . $doc['id']), $doc);
        }
    }

    /**
     * @see module_db_interface_Doc::getLatestDocs()
     *
     * @param integer $localeId
     * @param array $folderIds
     * @param integer $limit
     * @return array
     */
    public function getLatestDocs ($localeId, $folderIds, $limit)
    {
        if (! is_array($folderIds)) {
            $folderIds = array($folderIds);
        }
        foreach ($folderIds as $key => &$folder) {
            $folder = (int) $folder;
            if (! $folder) {
                unset($folderIds[$key]);
            }
        }
        if (! count($folderIds)) {
            return array();
        }
        $fieldMap = $this->getMappableFields(
            array('d' => 'doc_data' , 'f' => 'folder_data' ,
            'ot' => 'object_type_data'));
        $extra = $this->makeDocInactiveExtra(false, 'd.');
        $query = array(
        'table' => 'doc_data d JOIN folder_data f ON f.id=d.folder_id JOIN object_type_data ot ON d.ref_object_type_id=.ot.id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'd.locale_id=' . (int) $localeId . ' AND d.folder_id IN (' . implode(
            ', ', $folderIds) . ')' . $extra ,
        'orderBy' => 'd.start_date DESC' , 'limit' => (int) $limit);
        $docData = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $fieldMap = $this->getMappableFields(
            array('d' => 'doc_data' , 'df' => 'folder_data' ,
            'ot' => 'object_type_data'));
        $query = array(
        'table' => 'doc_link_data l JOIN folder_data f ON f.id=l.folder_id JOIN doc_data d ON d.id=l.doc_id JOIN folder_data df ON df.id=d.folder_id JOIN object_type_data ot ON ot.id=d.ref_object_type_id' ,
        'fields' => $this->getFieldListFromMap($fieldMap) ,
        'where' => 'd.locale_id=' . (int) $localeId . ' AND l.folder_id IN (' . implode(
            ', ', $folderIds) . ')' . $extra , 'orderBy' => 'l.id DESC' ,
        'limit' => (int) $limit);
        $linkData = $this->getDataFromMappedFields($fieldMap,
            $this->conn->select($query));
        if (! $docData && ! $linkData) {
            return false;
        }
        $docs = array();
        foreach ($docData as $doc) {
            $doc['d']['FullObject'] = $this->basicLoad(
                sys_cache_DbSchema::makeTableName(
                    $doc['ot']['persist_class']),
                $doc['d']['object_id']);
            $doc['d']['Folder'] = $doc['f'];
            $docs[] = $doc['d'];
        }
        foreach ($linkData as $link) {
            $link['d']['FullObject'] = $this->basicLoad(
                sys_cache_DbSchema::makeTableName(
                    $doc['ot']['persist_class']),
                $doc['d']['object_id']);
            $link['d']['Folder'] = $link['df'];
            $link[] = $link['d'];
        }
        return $docs;
    }
}
?>