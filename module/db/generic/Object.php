<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 12611 $
 */

/**
 * Object generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 12611 $
 */
class module_db_generic_Object extends module_db_DbModule implements module_db_interface_Object
{

    /**
     * @see module_db_interface_Object::getListCountForObjectType()
     *
     * @param integer $typeId
     * @param array $filter
     * @return integer
     */
    public function getListCountForObjectType ($typeId, $filter = null)
    {
        $typeData = $this->conn->selectFirst(
            array('table' => 'object_type_data' ,
            'where' => 'id=' . (int) $typeId));
        if (! $typeData['persist_class']) {
            return 0;
        }
        $tableName = sys_cache_DbSchema::makeTableName($typeData['persist_class']);
        $query = array('table' => $tableName ,
        'fields' => $this->conn->getFunc('COUNT', array('id')) . ' AS itemCount');
        $query['where'] = 'object_type_id = ' . (int) $typeId;
        if (is_array($filter) && count($filter)) {
            $schema = $this->conn->getTableSchema($tableName);
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name]) && ('' !==
                     $val)) {
                        $query['where'] .= ' AND ' .
                         $name .
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
     * @see module_db_interface_Object::getListForObjectType()
     *
     * @param integer $typeId
     * @param integer $limit
     * @param integer $offset
     * @param array $filter
     * @param string $orderBy
     * @param string $orderDir
     * @return array
     */
    public function getListForObjectType ($typeId, $limit = null, $offset = null, $filter = null, $orderBy = null,
        $orderDir = null)
    {
        $typeData = $this->conn->selectFirst(
            array('table' => 'object_type_data' ,
            'where' => 'id=' . (int) $typeId));
        if (! $typeData['persist_class']) {
            return array();
        }
        $tableName = sys_cache_DbSchema::makeTableName($typeData['persist_class']);
        $query = array('table' => $tableName);
        $query['where'] = 'object_type_id = ' . (int) $typeId;
        $schema = $this->conn->getTableSchema($tableName);
        if (is_array($filter) && count($filter)) {
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name]) && ('' !==
                     $val)) {
                        $query['where'] .= ' AND ' .
                         $name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                }
            }
        }
        if (! is_null($orderBy)) {
            if (! isset($schema['columns'][$orderBy])) {
                $orderBy = 'name';
            }
            switch (strtolower($orderDir)) {
                case 'desc':
                case '-':
                    $query['orderBy'] = $orderBy .
                         ' DESC';
                    break;
                default:
                    $query['orderBy'] = $orderBy .
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
        return $this->conn->select($query);
    }

    /**
     * @see module_db_interface_Object::getRelList()
     *
     * @param integer $objectId
     * @param integer $relationType
     * @return array
     */
    public function getRelList ($objectId, $relationType = null)
    {
        $extra = '';
        if (! is_null($relationType)) {
            $extra .= ' AND r.relation_type = ' . (int) $relationType;
        }
        $parents = $this->conn->select(
            array(
            'table' => 'object_object_rel r JOIN object_data o ON o.id=r.parent_id JOIN object_type_data ot ON ot.id=o.object_type_id' ,
            'fields' => 'ot.short_name, ot.persist_class, o.id' ,
            'where' => 'r.child_id=' . (int) $objectId . $extra,
            'orderBy' => 'r.id ASC',
        ));
        $children = $this->conn->select(
            array(
            'table' => 'object_object_rel r JOIN object_data o ON o.id=r.child_id JOIN object_type_data ot ON ot.id=o.object_type_id' ,
            'fields' => 'ot.short_name AS ot__short_name, ot.persist_class AS ot__persist_class, o.id AS o__id' ,
            'where' => 'r.parent_id=' . (int) $objectId . $extra,
            'orderBy' => 'r.id ASC',
        ));
        $parents = array_merge($parents, $children);
        $docFieldMap = $this->getMappableFields(
            array('d' => 'doc_data' , 'f' => 'folder_data'));
        $docFields = $this->getFieldListFromMap($docFieldMap);
        foreach ($parents as $val) {
            if ($val['ot__short_name'] == 'document') {
                $query = array(
                'table' => 'doc_data d JOIN folder_data f ON f.id=d.folder_id' ,
                'where' => 'd.id=' . $val['o__id'] . $this->makeDocInactiveExtra(
                    false) ,
                'fields' => $docFields);
                $item = $this->getRowFromMappedFields(
                    $docFieldMap,
                    $this->conn->selectFirst(
                        $query));
                $item['d']['Folder'] = $item['f'];
                $data[] = $item['d'];
            } else {
                $item = $this->conn->selectFirst(
                    array(
                    'table' => sys_cache_DbSchema::makeTableName(
                        $val['ot__persist_class']) ,
                    'where' => 'id=' . $val['o__id']));
                if (! count($item)) {
                    continue;
                }
                $data[] = $item;
            }
        }
        return $data;
    }

    /**
     * @see module_db_interface_Object::replaceObjectRels()
     *
     * @param integer $objectId
     * @param integer $relationType
     * @param unknown_type $rels
     * @return boolean
     */
    public function replaceObjectRels ($objectId, $relationType, $rels)
    {
        $parents = $this->conn->select(
            array('table' => 'object_object_rel' ,
            'where' => 'child_id=' . (int) $objectId . ' AND relation_type=' .
                 (int) $relationType));
        $children = $this->conn->select(
            array('table' => 'object_object_rel' ,
            'where' => 'parent_id=' . (int) $objectId . ' AND relation_type=' .
                 (int) $relationType));
        $currentRels = array();
        foreach ($parents as $val) {
            $currentRels[$val['parent_id']] = $val['id'];
        }
        foreach ($children as $val) {
            $currentRels[$val['child_id']] = $val['id'];
        }
        $savedRels = array();
        if (! is_array($rels)) {
            $rels = array();
        }
        foreach ($rels as $key => $val) {
            if (isset($currentRels[$key]) && ! in_array($key, $savedRels)) {
                $savedRels[] = $key;
                unset($currentRels[$key]);
            } else {
                $rel = array();
                $rel['parent_id'] = (int) $objectId;
                $rel['relation_type'] = (int) $relationType;
                $rel['child_id'] = (int) $key;
                $this->conn->insert('object_object_rel',
                    $rel);
                $savedRels[] = $key;
            }
        }
        if (count($currentRels)) {
            $this->conn->delete('object_object_rel',
                'id IN (' . implode(', ', $currentRels) .
                     ')');
        }
    }

    /**
     * @see module_db_interface_Object::getObjectById()
     *
     * @param integer $objectId
     * @return array
     */
    public function getObjectById ($objectId)
    {
        return $this->basicLoad('object_data', (int) $objectId);
    }

    /**
     * @see module_db_interface_Object::getFullObjectById()
     *
     * @param integer $objectId
     */
    public function getFullObjectById ($objectId)
    {
        $obj = $this->conn->selectFirst(
            array(
            'table' => 'object_data o JOIN object_type_data ot ON ot.id=o.object_type_id' ,
            'fields' => 'ot.short_name, ot.persist_class, o.id' ,
            'where' => 'o.id=' . (int) $objectId));
        if (! count($obj)) {
            return array();
        }
        return $this->basicLoad(sys_cache_DbSchema::makeTableName($obj['persist_class']),
            $objectId);
    }

}
?>