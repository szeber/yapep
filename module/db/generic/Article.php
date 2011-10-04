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
 * generic article object class
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Article extends module_db_DbModule implements module_db_interface_Article,
    module_db_interface_DocObject
{

    /**
     * @see module_db_interface_DocObject::getObjectByDocId()
     *
     * @param integer $docId
     * @param boolean $inactive
     */
    public function getObjectByDocId ($docId, $inactive = true)
    {
        $extra = '';
        if (! $inactive) {
            $extra .= " AND d.status = " . module_db_interface_Doc::STATUS_ACTIVE .
                 " AND d.start_date <= NOW() AND d.end_date >= NOW()";
        }
        $fieldMap = $this->getMappableFields(array('d' => 'doc_data' ,
        'f' => 'folder_data'));
        $query = array(
            'table' => 'doc_data d JOIN folder_data f ON f.id=d.folder_id' ,
            'fields' => $this->getFieldListFromMap($fieldMap) ,
            'where' => 'd.id=' . (int) $docId . $extra);
        $docData = $this->getRowFromMappedFields($fieldMap, $this->conn->selectFirst($query));
        $docData['d']['Folder'] = $docData['f'];
        $docData = $docData['d'];
        if (! $docData['id']) {
            return array();
        }
        $docData['FullObject'] = $this->conn->selectFirst(
            array('table' => 'article_data' ,
            'where' => 'id=' . $docData['object_id']));
        return $docData;
    }

    /**
     * @see module_db_interface_DocObject::findDoc()
     *
     * @param integer $localeId
     * @param string $startPath
     * @param string $text
     * @param array $includeFolders
     * @param array $excludeFolders
     * @param boolean $inactive
     * @return array
     */
    public function findDoc ($localeId, $startPath, $text, $limit = -1, $offset = 0,
        $includeFolders = array(), $excludeFolders = array(), $inactive = false)
    {
        $text = $this->conn->quote('%' . $text . '%');
        $query = array(
        'table' => 'doc_data d INNER JOIN folder_data f ON f.id=d.folder_id');
        $docFields = $this->getMappableFields(array('d' => 'doc_data' ,
        'f' => 'folder_data'));
        $query['fields'] = implode(', ', $docFields['d']['sql']) . ',' . implode(', ',
            $docFields['f']['sql']);
        $query['where'] = 'd.ref_object_type_id=' . $this->getObjectTypeIdByShortName(
            'article');
        $query['where'] .= ' AND (f.docpath LIKE ' . $this->conn->quote($startPath . '%');
        $query['where'] .= $this->makeDocIncludeExtra($includeFolders) . ')';
        $query['where'] .= ' AND (f.locale_id = ' . (int) $localeId . ' OR f.locale_id IS NULL)';
        $query['where'] .= $this->makeDocInactiveExtra($inactive) . $this->makeDocExcludeExtra(
            $excludeFolders);

        $docData = $this->conn->select($query);
        $docData = $this->getDataFromMappedFields($docFields, $docData, 'd__id');

        $objIds = array();
        $idMap = array();
        foreach ($docData as $key => $doc) {
            $objIds[] = $doc['d']['object_id'];
            if (! isset($idMap[$doc['d']['object_id']])) {
                $idMap[$doc['d']['object_id']] = array(
                $key);
            } else {
                $idMap[$doc['d']['object_id']][] = $key;
            }
            $docData[$key]['d']['Folder'] = $doc['f'];
        }
        $objIds = array_unique($objIds);
        $query = array(
        'table' => 'article_data a INNER JOIN doc_data d ON d.object_id=a.id' ,
        'fields' => 'a.*' , 'orderBy' => 'name ASC');
        $query['where'] = 'a.id IN (' . implode(', ', $objIds) . ')';
        $query['where'] .= " AND (d.name LIKE $text OR a.name LIKE $text OR a.lead LIKE $text OR a.content LIKE $text)";
        if ($limit >= 0) {
            $query['limit'] = (int) $limit;
            $query['offset'] = (int) $offset;
        }
        $articleData = $this->conn->select($query);

        foreach ($articleData as $key => $article) {
            $articleData[$key]['Docs'] = array();
            foreach ($idMap[$article['id']] as $docId) {
                $articleData[$key]['Docs'][] = $docData[$docId]['d'];
            }
        }

        return $articleData;
    }

    /**
     * @see module_db_interface_DocObject::getFindDocCount()
     *
     * @param integer $localeId
     * @param string $startPath
     * @param string $text
     * @param array $includeFolders
     * @param array $excludeFolders
     * @param unknown_type $inactive
     * @return integer
     */
    public function getFindDocCount ($localeId, $startPath, $text, $includeFolders = array(),
        $excludeFolders = array(), $inactive = false)
    {
        $text = $this->conn->quote('%' . $text . '%');
        $query = array(
        'table' => 'doc_data d INNER JOIN folder_data f ON f.id=d.folder_id');
        $query['fields'] = 'd.object_id';
        $query['where'] = 'd.ref_object_type_id=' . $this->getObjectTypeIdByShortName(
            'article');
        $query['where'] .= ' AND (f.docpath LIKE ' . $this->conn->quote($startPath . '%');
        $query['where'] .= $this->makeDocIncludeExtra($includeFolders) . ')';
        $query['where'] .= ' AND (f.locale_id = ' . (int) $localeId . ' OR f.locale_id IS NULL)';
        $query['where'] .= $this->makeDocInactiveExtra($inactive) . $this->makeDocExcludeExtra(
            $excludeFolders);

        $docData = $this->conn->select($query);

        $objIds = array();
        foreach ($docData as $doc) {
            $objIds[] = $doc['object_id'];
        }
        $objIds = array_unique($objIds);
        $query = array(
        'table' => 'article_data a INNER JOIN doc_data d ON d.object_id=a.id' ,
        'fields' => $this->conn->getFunc('COUNT', array('a.id')) . ' AS article_count');
        $query['where'] = 'a.id IN (' . implode(', ', $objIds) . ')';
        $query['where'] .= " AND (d.name LIKE $text OR a.name LIKE $text OR a.lead LIKE $text OR a.content LIKE $text)";
        $articleData = $this->conn->selectFirst($query);
        return $articleData['article_count'];
    }

    /**
     * @see module_db_interface_Article::getArticlesByIds()
     *
     * @param array $idList
     * @return array
     */
    public function getArticlesByIds (array $idList)
    {
        foreach ($idList as $key => $id) {
            if ((int) $id) {
                $idList[$key] = (int) $id;
            } else {
                unset($idList[$key]);
            }
        }
        if (! count($idList)) {
            return array();
        }
        $query = array('table' => 'doc_data d LEFT JOIN folder_data f ON f.id=d.folder_id');
        $query['where'] = 'd.ref_object_type_id=' . $this->getObjectTypeIdByShortName(
            'article');
        $query['where'] .= ' AND d.object_id IN (' . implode(',', $idList) . ')';
        $docFields = $this->getMappableFields(array('d' => 'doc_data' ,
        'f' => 'folder_data'));
        $query['fields'] = implode(', ', $docFields['d']['sql']) . ',' . implode(', ',
            $docFields['f']['sql']);
        $docData = $this->conn->select($query);
        $docData = $this->getDataFromMappedFields($docFields, $docData, 'd__id');

        $idMap = array();
        foreach ($docData as $key => $doc) {
            if (! isset($idMap[$doc['d']['object_id']])) {
                $idMap[$doc['d']['object_id']] = array(
                $key);
            } else {
                $idMap[$doc['d']['object_id']][] = $key;
            }
            $docData[$key]['d']['Folder'] = $doc['f'];
        }

        $query = array('table' => 'article_data' ,
        'where' => 'id IN (' . implode(',', $idList) . ')' , 'orderBy' => 'name ASC');

        $articleData = $this->conn->select($query);
        foreach ($articleData as $key => $article) {
            $articleData[$key]['Docs'] = array();
            foreach ($idMap[$article['id']] as $docId) {
                $articleData[$key]['Docs'][] = $docData[$docId]['d'];
            }
        }
        return $articleData;
    }

}
?>