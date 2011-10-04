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
 * Database module base class
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class module_db_DbModule
{

    /**
     * @var sys_db_Database
     */
    protected $conn;

    /**
     * Constructor
     *
     * @param string $dbConnection
     */
    public function __construct ($dbConnection = 'site')
    {
        $conn = sys_LibFactory::getDbConnection($dbConnection);
        if (! ($conn instanceof sys_db_Database)) {
            throw new sys_exception_DatabaseException(
                'Bad connection type provided!');
        }
        $this->conn = $conn;
    }

    /**
     * Modifies fields in the $data array with fields from $saveData ignoring fields specified in the $ignoreFields array
     *
     * @param array $data
     * @param array$saveData
     * @param array $ignoreFields
     * @return array
     */
    protected function modifyData (array &$data, array $saveData, array $ignoreFields = array())
    {
        $ignoreFields += array('id' , 'created_at' , 'updated_at' , 'creater' ,
        'updater' , 'object_type_id');
        foreach ($data as $field => $val) {
            if (in_array($field, $ignoreFields)) {
                continue;
            }
            if (array_key_exists($field, $saveData)) {
                $data[$field] = $saveData[$field];
            }
        }
        return $data;
    }

    /**
     * Returns the object type ID for a short name. Returns null if it's not found.
     *
     * @return integer
     */
    protected function getObjectTypeIdByShortName ($shortName = 'folder')
    {
        $objectTypeHandler = getPersistClass('ObjectType');
        $typeData = $objectTypeHandler->getObjectTypeByShortName($shortName);
        return $typeData['id'];
    }

    /**
     * Loads an item for administration
     *
     * @param string $tableName
     * @param integer $itemId
     * @return array
     */
    protected function basicLoad ($tableName, $itemId)
    {
        return $this->conn->selectFirst(
            array('table' => $tableName , 'where' => 'id=' . (int) $itemId));
    }

    /**
     * Basic item inserting method for administration
     *
     * @see module_db_interface_Admin::insertItem()
     *
     * @param string $tableName The name of the table to work on (Doctrine model class name)
     * @param array $itemData Associative array with the values to insert (name=>value)
     * @param array $ignoreFields Array containing the name of the fields not to be touched
     * @param integer $objectTypeId
     * @return string Empty string on success, or the error message on failure
     */
    protected function basicInsert ($tableName, $itemData, $ignoreFields = array(), $objectTypeId = null)
    {
        $data = $this->conn->getDefaultRecord($tableName);
        if ($objectTypeId && array_key_exists('object_type_id', $data)) {
            $data['object_type_id'] = $objectTypeId;
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        $this->conn->insert($tableName, $data);
        $schema = $this->conn->getTableSchema($tableName);
        if (isset($data['id'])) {
            return $data['id'];
        }
        return $this->conn->getLastInsertId();
    }

    /**
     * Basic item deleting method for administration
     *
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param string $tableName The name of the table to work on (Doctrine model class name)
     * @param integer $itemID The ID of the item to be deleted
     * @return boolean
     */
    protected function basicDelete ($tableName, $itemId)
    {
        return $this->conn->delete($tableName, 'id=' . (int) $itemId, 1);
    }

    /**
     * Basic item updating method for administration
     *
     * @see module_db_interface_Admin::updateItem()
     *
     * @param string $tableName The name of the table to work on (Doctrine model class name)
     * @param integer $itemID The ID of the item to be deleted
     * @param array $itemData Associative array with the values to insert (name=>value)
     * @param array $ignoreFields Array containing the name of the fields not to be touched
     * @return string Empty string on success, or the error message on failure
     */
    protected function basicUpdate ($tableName, $itemId, $itemData, $ignoreFields = array())
    {
        $data = $this->conn->selectFirst(
            array('table' => $tableName , 'field' => '*' ,
            'where' => 'id=' . (int) $itemId));
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->update(
            array('table' => $tableName , 'where' => 'id=' . (int) $itemId ,
            'limit' => 1), $itemData)) {
            return '';
        } else {
            return $this->conn->getLastError();
        }
    }

    /**
     * Generates an associative array that can be used for ID Select inputs
     *
     * @param string $tableName The name of the table to work from (Doctrine model class name)
     * @param string $idField The name of the field that should be used as the array's key. Defaults to 'id'
     * @param string $nameField Optional. The name of the field that should be used as the array's value. Defaults to 'name'
     * @param string $filter Optional. Any filtering that should be done as a valid WHERE clause without the WHERE keyword
     * @return array
     */
    protected function getBasicIdSelectList ($tableName, $idField = 'id', $nameField = 'name', $filter = '')
    {
        if ('' != $filter) {
            $filter = ' ' . $filter;
        }
        $data = $this->conn->select(
            array('table' => $tableName ,
            'fields' => $idField . ',' . $nameField , 'where' => $filter ,
            'orderBy' => $nameField . ' ASC'));
        $list = array();
        foreach ($data as $item) {
            $list[$item[$idField]] = $item[$nameField];
        }
        return $list;
    }

    /**
     * Returns the number of records matching the given filters in the given table
     *
     * @see module_db_interface_AdminList::getListResultCount()
     *
     * @param string $tableName
     * @param array $filter
     * @return integer
     */
    protected function getBasicListCount ($tableName, $filter = null)
    {
        $filters = '1=1';
        if (is_array($filter) && count($filter)) {
            $schema = $this->conn->getTableSchema($tableName);
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name]) && ('' !== $val)) {
                    $filters .= ' AND ' . $name . ' LIKE '.$this->conn->quote($val.'%');
                }
            }
        }
        $count = $this->conn->selectFirst(array('table'=>$tableName, 'fields'=>'COUNT(*) AS itemCount', 'where'=>$filters));
        return $count['itemCount'];
    }

    /**
     * Returns the records specified by tablename,limit, offset, filter
     *
     * @see module_db_interface_AdminList::listItems()
     *
     * @param string $tableName
     * @param integer $limit
     * @param integer $offset
     * @param array $filter
     * @param string $orderBy
     * @param string $orderDir
     * @return array
     */
    protected function getBasicList ($tableName, $limit = null, $offset = null, $filter = null, $orderBy = null,
        $orderDir = null)
    {
        $filters = '1=1';
        $schema = $this->conn->getTableSchema($tableName);
        if (is_array($filter) && count($filter)) {
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name]) && ('' !== $val)) {
                    $filters .= ' AND ' . $name . ' LIKE '.$this->conn->quote($val.'%');
                }
            }
        }
        $queryArr = array('table'=>$tableName, 'fields'=>'*', 'where'=>$filters);
        if ((int)$limit) {
            $queryArr['limit'] = (int)$limit;
            if ((int)$offset < 0) {
                $offset = 0;
            }
            $queryArr['offset'] = (int)$offset;
        }
        if (!is_null($orderBy)) {
            if (!isset($schema['columns'][$orderBy])) {
                $orderBy = 'name';
            }
            if (isset($schema['columns'][$orderBy])) {
                switch($orderDir) {
                    case 'desc':
                    case '-':
                        $queryArr['orderBy'] = $orderBy.' DESC';
                        break;
                    default:
                        $queryArr['orderBy'] = $orderBy.' ASC';
                        break;
                }
            }
        }
        return $this->conn->select($queryArr);
    }

    /**
     * Returns an extra DQL WHERE segment that includes folders in $includeFolders in a document search
     *
     * @param array $includeFolders
     * @return string
     */
    protected function makeDocIncludeExtra ($includeFolders)
    {
        $extra = '';
        if (count($includeFolders)) {
            $ids = array();
            $docpaths = array();
            foreach ($includeFolders as $folder) {
                if (! $folder) {
                    continue;
                }
                if (is_numeric($folder)) {
                    $ids[] = (int) $folder;
                } else {
                    $docpaths[] = $this->conn->quote(
                        trim(
                            $folder));
                }
            }
            if (count($ids)) {
                $extra .= ' OR f.id IN (' . implode(', ',
                    $ids) . ')';
            }
            if (count($docpaths)) {
                $extra .= ' OR f.docpath IN (' . implode(
                    ', ', $docpaths) . ')';
            }
        }
        return $extra;
    }

    /**
     * Returns an extra DQL WHERE segment that excludes folders in $excludeFolders from a document search
     *
     * @param array $excludeFolders
     * @return string
     */
    protected function makeDocExcludeExtra ($excludeFolders)
    {
        $extra = '';
        if (count($excludeFolders)) {
            $ids = array();
            $docpaths = array();
            foreach ($excludeFolders as $folder) {
                if (is_numeric($folder)) {
                    $ids[] = (int) $folder;
                } else {
                    $docpaths[] = $this->conn->quote(
                        trim(
                            $folder));
                }
            }
            if (count($ids)) {
                $extra .= ' AND f.id NOT IN (' . implode(
                    ', ', $ids) . ')';
            }
            if (count($docpaths)) {
                $extra .= ' AND f.docpath NOT IN (' . implode(
                    ', ', $docpaths) . ')';
            }
        }
        return $extra;
    }

    /**
     * Returns a DQL WHERE segment that filters out inactive documents
     *
     * @param boolean $inactive
     * @param string $docPrefix
     * @return string
     */
    protected function makeDocInactiveExtra ($inactive, $docPrefix = 'd.')
    {
        $extra = '';
        if (! $inactive) {
            $extra .= " AND " . $docPrefix . "status = " . module_db_interface_Doc::STATUS_ACTIVE .
                 " AND " . $docPrefix . "start_date <= NOW() AND " .
                 $docPrefix . "end_date >= NOW()";
        }
        return $extra;
    }

    protected function getMappableFields(array $tableNames) {
        $fields = array();
        foreach($tableNames as $alias=>$table) {
            $fields[$alias] = array('alias'=>array(), 'sql'=>array());
            $schema = $this->conn->getTableSchema($table);
            foreach($schema['columns'] as $columnName=>$columnData) {
                $columnAlias = $alias.'__'.$columnName;
                $fields[$alias]['alias'][$columnName] = $columnAlias;
                $fields[$alias]['sql'][$columnName] = $this->conn->quoteField($alias)
                    .'.'.$this->conn->quoteField($columnName)
                    .' AS '.$this->conn->quoteField($columnAlias);
            }
        }
        return $fields;
    }

    protected function getDataFromMappedFields(array $fieldMap, array $data, $dataIndexField = '') {
        $fieldMap = $this->convertFieldMap($fieldMap);
        $converted = array();
        foreach($data as $rowNum=>$row) {
            if ($dataIndexField) {
                $rowNum = $row[$dataIndexField];
            }
            $converted[$rowNum] = $this->doGetRowFromMappedFields($fieldMap, $row);;
        }
        return $converted;
    }

    protected function getRowFromMappedFields(array $fieldMap, array $row) {
/*        if (!count($row)) {
            return array();
        }*/
        $fieldMap = $this->convertFieldMap($fieldMap);
        return $this->doGetRowFromMappedFields($fieldMap, $row);
    }

    private function doGetRowFromMappedFields(array $fieldMap, array $row) {
        $converted = array();
        foreach($fieldMap as $alias=>$map) {
            if (!is_array($converted[$map['table']])) {
                $converted[$map['table']] = array();
            }
            if (isset($row[$alias])) {
                $converted[$map['table']][$map['field']] = $row[$alias];
            } else {
                $converted[$map['table']][$map['field']] = null;
            }
            if (array_key_exists($alias, $row)) {
                unset($row[$alias]);
            }
        }
        if (count($row)) {
            $converted['__other'] = $row;
        }
        return $converted;
    }

    protected function convertFieldMap(array $fieldMap) {
        $converted = array();
        foreach ($fieldMap as $table=>$fields) {
            foreach($fields['alias'] as $field=>$alias) {
                $converted[$alias] = array('table'=>$table, 'field'=>$field);
            }
        }
        return $converted;
    }

    protected function getFieldListFromMap(array $fieldMap) {
        $fields = array();
        foreach($fieldMap as $table) {
            $fields [] = implode(', ', $table['sql']);
        }
        return implode(', ', $fields);
    }

}
?>