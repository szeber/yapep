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
abstract class module_db_DoctrineDbModule
{

    /**
     * @var Doctrine_Connection
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
        if (! ($conn instanceof Doctrine_Connection)) {
            throw new sys_exception_DatabaseException(
                'Bad connection type provided!');
        }
        $this->conn = $conn;
    }

    /**
     * Modifies fields in the $data object with fields from $saveData ignoring fields specified in the $ignoreFields array
     *
     * @param Doctrine_Record $data
     * @param array$saveData
     * @param array $ignoreFields
     */
    protected function modifyData (Doctrine_Record $data, $saveData, $ignoreFields = array())
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
        return $this->normalizeResults(
            $this->conn->queryOne('FROM ' . $tableName . ' WHERE id = ?',
                array((int) $itemId)));
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
        try {
            $data = new $tableName();
            if ($objectTypeId && isset($data['object_type_id'])) {
                $data['object_type_id'] = $objectTypeId;
            }
            $this->modifyData($data, $itemData, $ignoreFields);
            $data->save();
            return $data['id'];
        } catch (Doctrine_Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Basic item deleting method for administration
     *
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param string $tableName The name of the table to work on (Doctrine model class name)
     * @param integer $itemID The ID of the item to be deleted
     */
    protected function basicDelete ($tableName, $itemId)
    {
        $data = $this->conn->queryOne('FROM ' . $tableName . ' WHERE id = ?',
            array((int) $itemId));
        if (is_object($data)) {
            $data->delete();
            return true;
        }
        return false;
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
        try {
            $data = $this->conn->queryOne(
                'FROM ' . $tableName . ' WHERE id = ?',
                array((int) $itemId));
            $this->modifyData($data, $itemData, $ignoreFields);
            $data->save();
            return '';
        } catch (Doctrine_Exception $e) {
            return $e->getMessage();
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
            $filter = ' WHERE ' . $filter;
        }
        $data = $this->conn->query(
            'SELECT ' . $idField . ', ' . $nameField . ' FROM ' . $tableName .
                 $filter . ' ORDER BY ' . $nameField . ' ASC');
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
        $filterArr = array();
        if (is_array($filter) && count($filter)) {
            $rec = new $tableName();
            foreach ($filter as $name => $val) {
                if ($rec->contains($name) && ('' !== $val)) {
                    $filters .= ' AND ' .
                         $name .
                         ' LIKE ?';
                    $filterArr[] = $val .
                         '%';
                }
            }
        }
        $count = $this->conn->queryOne(
            'SELECT COUNT(id) as itemCount FROM ' . $tableName . ' WHERE ' .
                 $filters, $filterArr);
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
        $filterArr = array();
        $extra = '';
        if (is_array($filter) && count($filter)) {
            $rec = new $tableName();
            foreach ($filter as $name => $val) {
                if ($rec->contains($name) && ('' !== $val)) {
                    $filters .= ' AND ' .
                         $name .
                         ' LIKE ?';
                    $filterArr[] = $val .
                         '%';
                }
            }
        }
        if ((int) $limit) {
            if (! is_null($orderBy)) {
                $obj = new $tableName();
                if (! isset($obj[$orderBy])) {
                    $orderBy = 'name';
                }
                if (isset($obj[$orderBy])) {
                    $extra .= ' ORDER BY ' .
                         $orderBy;
                    switch (strtolower(
                        $orderDir)) {
                        case 'desc':
                        case '-':
                            $extra .= ' DESC';
                            break;
                        default:
                            $extra .= ' ASC';
                            break;
                    }
                }
            }
            $extra .= ' LIMIT ' . (int) $limit;
            if ((int) $offset < 0) {
                $offset = 0;
            }
            $extra .= ' OFFSET ' . (int) $offset;
        }
        return $this->normalizeResults(
            $this->conn->query(
                'FROM ' . $tableName . ' WHERE ' . $filters .
                     $extra, $filterArr));
    }

    /**
     * Returns a normalized version of $data
     *
     * This method should be run on all query results that are supposed to return an array
     *
     * @param mixed $data
     * @return array
     */
    protected function normalizeResults ($data)
    {
        if (! $data) {
            return array();
        }
        if (! is_object($data)) {
            return $data;
        }
        if ($data instanceof Doctrine_Collection) {
            return $data->toArray(true);
        }
        return $data->toArray();
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
}
?>