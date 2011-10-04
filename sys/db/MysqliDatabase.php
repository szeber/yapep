<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Database handler for Mysqli
 *
 * @package	YAPEP
 * @subpackage Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_MysqliDatabase extends sys_db_Database
{

    /**
     * No transaction
     */
    const TRANS_NO_TRANS = 0;

    /**
     * Successful transaction
     */
    const TRANS_OK = 1;

    /**
     * Failed transaction
     */
    const TRANS_FAIL = 2;

    /**
     * DB object
     *
     * @var mysqli
     */
    public $db;

    /**
     * Caching enabled
     *
     * @var boolean
     */
    protected $caching = false;

    /**
     * Number of rows returned by SELECT
     *
     * @var integer
     */
    protected $numRows = 0;

    /**
     * Number of affected rows
     *
     * @var integer
     */
    protected $affectedRows = 0;

    /**
     * Transatction mode setting
     *
     * @var integer
     */
    protected $transactionMode = self::TRANS_NO_TRANS;

    /**
     * Cache time in seconds
     *
     * @var integer
     */
    protected $cacheSecs;

    /**
     * Cache directory
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Schema cache
     *
     * @var array
     */
    protected $_schemas = array();

    /**
     *
     * @see sys_db_Database::begin()
     */
    public function begin ()
    {
        if ($this->transactionMode == self::TRANS_NO_TRANS) {
            $this->db->autocommit(false);
            $this->execute('BEGIN', array(), 0);
            $this->transactionMode = self::TRANS_OK;
            return;
        }
    }

    /**
     *
     * @see sys_db_Database::complete()
     */
    public function complete ()
    {
        $result = false;
        switch ($this->transactionMode) {
            case self::TRANS_OK:
                $this->execute('COMMIT', array(), 0);
                $this->transactionMode = self::TRANS_NO_TRANS;
                $result = true;
                break;
            case self::TRANS_FAIL:
                $this->execute('ROLLBACK', array(), 0);
                $this->transactionMode = self::TRANS_NO_TRANS;
                break;
        }
        $this->db->autocommit(true);
        return $result;
    }

    /**
     *
     * @param string $cmd
     * @param array $params
     * @param integer $cache
     * @param integer $limit
     * @param integer $offset
     * @return mixed Array with the results, or true on success, false on failue
     * @see sys_db_Database::execute()
     */
    public function execute ($cmd, array $params = array(), $cache = null, $limit = -1, $offset = -1)
    {
        $origCmd = $cmd;
        if ((int) $limit > 0) {
            $cmd .= ' LIMIT ';
            if ((int) $offset > 0) {
                $cmd .= (int)$offset . ', ';
            }
            $cmd .= (int) $limit;
        }
        foreach ($this->listeners as $listener) {
            $listener->beforeQuery(
                array('query' => $cmd ,
                'limit' => $limit , 'offset' => $offset));
        }
        if (! $this->caching) {
            $cache = 0;
        } elseif (is_null($cache)) {
            $cache = (int) $this->cacheSecs;
        } else {
            $cache = (int) $cache;
        }
        if ($cache) {
            $result = sys_cache_DbCacheManager::getCachedQuery($cmd,
                $params,
                $this->cacheDir,
                $this->dbConfig['connectionId']);
            if ($result) {
                $recordCount = count($result);
                foreach ($this->listeners as $listener) {
                    $listener->afterQuery(
                        array(
                        'query' => $cmd ,
                        'params' => $params,
                        'limit' => $limit ,
                        'offset' => $offset ,
                        'success' => true ,
                        'rows' => $recordCount ,
                        'limit' => $limit ,
                        'offset' => $offset ,
                        'errorCode' => 0 ,
                        'errorMessage' => '' ,
                        'cache' => $cache ,
                        'cacheHit' => true));
                }
                $this->debugData[] = new sys_db_DatabaseDebug(
                    $origCmd, true,
                    $recordCount, $limit,
                    $offset, 0,
                    $this->db->info,
                    $cache, true, 0, $params);
                return $result;
            } else {
                $res = $this->doExecute($cmd, $params, $cache,
                    $limit, $offset);
                sys_cache_DbCacheManager::saveCachedQuery(
                    $cmd, $params, $res,
                    $this->cacheDir,
                    $this->dbConfig['connectionId'],
                    $cache);
                return $res;
            }
        }
        return $this->doExecute($cmd, $params, 0, $limit, $offset);
    }

    /**
     * Runs the query
     *
     * @param string $cmd
     * @param array $params
     * @param integer $cache
     * @param integer $limit
     * @param integer $offset
     * @return mixed Array with the results, or true on success, false on failue
     */
    protected function doExecute ($cmd, array $params, $cache, $limit = -1, $offset = -1)
    {
        $startTime = microtime(true);
        if (count($params)) {
            $stmt = $this->db->prepare($cmd);
            if (count($params) != $stmt->param_count) {
                throw new sys_exception_DatabaseException('Param count doesn\'t match provided parameters!', sys_exception_DatabaseException::ERR_INVALID_PARAM_COUNT);
            }
            $paramArr = array('');
            foreach($params as $param) {
                if (is_int($param)) {
                    $paramArr[0] .= 'i';
                } else if(is_string($param)) {
                    $paramArr[0] .= 's';
                } else {
                    $paramArr[0] .= 'd';
                }
                $paramArr[] = $param;
            }
            call_user_func_array(array($stmt, 'bind_param'), $paramArr);
            $res = $stmt->execute();
            $time = microtime(true) - $startTime;
            $meta = $stmt->result_metadata();
            if (false === $meta) {
                $result = $res;
                $recordCount = $stmt->affected_rows;
            } else {
                $stmt->store_result();
                $fields = $meta->fetch_fields();
                $dataArr = array();
                $fieldArr = array();
                foreach($fields as $fieldKey=>$field) {
                    $fieldArr[$fieldKey] = &$dataArr[$field->name];
                }
                call_user_func_array(array($stmt, 'bind_result'), $fieldArr);
                $result = array();
                while($stmt->fetch()) {
                    $row = array();
                    foreach($dataArr as $fieldName=>$field) {
                        $row[$fieldName] = $field;
                    }
                    $result[] = $row;
                }
                $recordCount = $stmt->num_rows;
                $stmt->free_result();
                $stmt->close();
            }
        } else {
            $res = $this->db->query($cmd);
            $time = microtime(true) - $startTime;
            if (is_object($res)) {
                $result = array();
                while ($tmp = $res->fetch_assoc()) {
                    $result[] = $tmp;
                }
                $recordCount = $res->num_rows;
            } else {
                $result = $res;
                $recordCount = $this->db->affected_rows;
            }
        }
        if ($res) {
            $success = true;
            $errorCode = 0;
            $errorMsg = $this->db->info;
            foreach ($this->listeners as $listener) {
                $listener->afterQuery(
                    array(
                    'query' => $cmd ,
                    'params' => $params,
                    'limit' => $limit ,
                    'offset' => $offset ,
                    'success' => true ,
                    'rows' => $recordCount ,
                    'limit' => $limit ,
                    'offset' => $offset ,
                    'errorCode' => 0 ,
                    'errorMessage' => $this->db->info ,
                    'cache' => $cache ,
                    'cacheHit' => false));
            }
        } else {
            $success = false;
            $recordCount = 0;
            $errorCode = $this->db->errno;
            $errorMsg = $this->db->error;
            $this->_lastError = $errorMsg;
            $this->_lastErrorCode = $errorCode;
            foreach ($this->listeners as $listener) {
                $listener->afterQuery(
                    array(
                    'query' => $cmd ,
                    'params' => $params,
                    'limit' => $limit ,
                    'offset' => $offset ,
                    'success' => false ,
                    'rows' => 0 ,
                    'limit' => $limit ,
                    'offset' => $offset ,
                    'errorCode' => $this->db->errno ,
                    'errorMessage' => $this->db->error ,
                    'cache' => $cache ,
                    'cacheHit' => false));
            }
            if ($this->transactionMode == self::TRANS_OK) {
                $this->transactionMode = self::TRANS_FAIL;
            }
        }
        $this->debugData[] = new sys_db_DatabaseDebug(
            $cmd, $success,
            $recordCount, $limit,
            $offset, $errorCode,
            $errorMsg,
            $cache, false, $time, $params);
        return $result;
    }

    /**
     *
     * @see sys_db_Database::fail()
     */
    public function fail ()
    {
        if ($this->transactionMode == self::TRANS_OK) {
            $this->transactionMode = self::TRANS_FAIL;
        }
    }

    /**
     *
     * @return integer
     * @see sys_db_Database::getAffectedRows()
     */
    public function getAffectedRows ()
    {
        return $this->affectedRows;
    }

    /**
     *
     * @return integer
     * @see sys_db_Database::getLastInsertId()
     */
    public function getLastInsertId ()
    {
        return $this->db->insert_id;
    }

    /**
     *
     * @return integer
     * @see sys_db_Database::getNumRows()
     */
    public function getNumRows ()
    {
        return $this->numRows;
    }

    /**
     *
     * @see sys_db_Database::setupConnection()
     */
    public function setupConnection ()
    {
        $this->db = new mysqli($this->dbConfig['host'], $this->dbConfig['user'],
            $this->dbConfig['password'], $this->dbConfig['dbName']);
        if (! ($this->db instanceof mysqli)) {
            return;
        }
        $this->connected = true;
        $this->execute("SET NAMES " . $this->quote($this->dbConfig['charset']), array(), 0);
        if (CACHING && $this->config->getOption('dbCache')) {
            $this->caching = true;
            $this->cacheSecs = $this->config->getOption('defaultDbCacheTime');
            $this->cacheDir = $this->config->getPath('dbCacheDir') . 'mysqli/';
        }
    }

    /**
     *
     * @param string $string
     * @param boolean $escapeWildcards
     * @return string
     * @see sys_db_Database::escape()
     */
    public function escape ($string, $escapeWildcards = false)
    {
        $string = $this->db->escape_string($string);
        if ($escapeWildcards) {
            $string = addcslashes($string, '%_');
        }
        return $string;
    }

    /**
     *
     * @return string
     * @see sys_db_Database::getType()
     */
    public function getType ()
    {
        return 'mysql';
    }

    /**
     * @see sys_db_Database::delete()
     *
     * @param string $table
     * @param string $where
     * @param integer $limit
     * @return boolean
     * @todo refactor to bound parameters
     */
    public function delete ($table, $where, $limit = -1)
    {
        if ($this->transactionMode == self::TRANS_NO_TRANS) {
            $this->begin();
            $result = $this->doDelete($table, $where, $limit);
            if (!$result) {
                $this->fail();
            }
            $this->complete();
        } else {
            $result = $this->doDelete($table, $where, $limit);
        }
        return $result;
    }

    /**
     * Performs the delete query
     *
     * @param strint $table
     * @param string $where
     * @param integer $limit
     * @return boolean
     */
    protected function doDelete($table, $where, $limit)
    {
        $cmd = "DELETE FROM $table WHERE $where";
        $schema = $this->getTableSchema($table);
        if (isset($schema['inheritance']['extendsTable'])) {
            $parentQuery = array('table'=>$table, 'where'=>$where, 'fields'=>reset($schema['primaryKey']), 'limit'=>$limit);
            $tmp = $this->select($parentQuery);
            $parentIds = array();
            if (!count($tmp)) {
                $this->affectedRows = 0;
                return true;
            }
            foreach($tmp as $id) {
                $parentIds[] = $this->quote(reset($id));
            }
        }
        if ($this->useListeners && is_array($schema['listeners'])) {
            foreach($schema['listeners'] as $listener) {
                if (!class_exists($listener)) {
                    throw new sys_exception_DatabaseException('Listener does not exist: '.$listener, sys_exception_DatabaseException::ERR_INVALID_LISTENER);
                }
                $listenerObj = new $listener();
                if (!($listenerObj instanceof sys_db_ModifyListener)) {
                    throw new sys_exception_DatabaseException('Invalid listener: '.$listener, sys_exception_DatabaseException::ERR_INVALID_LISTENER);
                }
                $listenerObj->onDelete($this, $table, $where, $limit);
            }
        }
        if ((int)$limit <= 0) {
            $limit = - 1;
        }
        if ($this->execute($cmd, array(), 0, $limit)) {
            $this->affectedRows = $this->db->affected_rows;
            if (isset($schema['inheritance']['extendsTable'])) {
                $result = $this->delete($schema['inheritance']['extendsTable'], reset($schema['primaryKey']).' IN ('.implode(', ', $parentIds).')', $limit);
                if (!$result) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @see sys_db_Database::insert()
     *
     * @param string $table
     * @param array $values
     * @param boolean $quoteVals
     * @return boolean
     */
    public function insert ($table, array &$values, $quoteVals = true)
    {
        if ($this->transactionMode == self::TRANS_NO_TRANS) {
            $this->begin();
            $result = $this->doInsert($table, $values, $quoteVals);
            if (!$result) {
                $this->fail();
            }
            $this->complete();
        } else {
            $result = $this->doInsert($table, $values, $quoteVals);
        }
        return $result;
    }

    /**
     * Performs the insert query
     *
     * @see sys_db_Database::insert()
     * @param string $table
     * @param array $values
     * @param boolean $quoteVals
     * @return boolean
     * @todo refactor to bound parameters
     */
    protected function doInsert($table, array &$values, $quoteVals)
    {
        $schema = $this->getTableSchema($table);
        $quotedValues = array();
        if ($quoteVals) {
            foreach($values as $field=>$value) {
                if (!isset($schema['columns'][$field]['type'])) {
                    continue;
                }
                $quotedValues[$field] = $this->getQuotedVal($value, $schema['columns'][$field]['type']);
            }
        } else {
            $quotedValues = $values;
        }
        if (isset($schema['inheritance']['extendsTable'])) {
            $result = $this->doInsert($schema['inheritance']['extendsTable'], $quotedValues, false);
            if (!$result) {
                return false;
            }
        }
        if ($this->useListeners && is_array($schema['listeners'])) {
            foreach($schema['listeners'] as $listener) {
                $listener = trim($listener);
                if (!class_exists($listener, true)) {
                    throw new sys_exception_DatabaseException('Listener does not exist: '.$listener, sys_exception_DatabaseException::ERR_INVALID_LISTENER);
                }
                $listenerObj = new $listener();
                if (!($listenerObj instanceof sys_db_ModifyListener)) {
                    throw new sys_exception_DatabaseException('Invalid listener: '.$listener, sys_exception_DatabaseException::ERR_INVALID_LISTENER);
                }
                $listenerObj->onInsert($this, $table, $quotedValues);
            }
        }
        $insertValues = array();
        foreach($quotedValues as $field=>$value) {
            if (isset($schema['columns'][$field])) {
                $insertValues[$field] = $value;
            }
        }
        $fieldsStr = implode(', ', array_keys($insertValues));
        $valuesStr = implode(', ', $insertValues);
        $cmd = 'INSERT INTO '.$table.'('.$fieldsStr.') VALUES ('.$valuesStr.')';
        if ($this->execute($cmd, array(), 0)) {
            $pkField = reset($schema['primaryKey']);
            if ((!isset($values[$pkField]) || 'NULL'==$values[$pkField]) && ('id' == $pkField || $schema['columns'][$pkField]['autoincrement'])) {
                $values[$pkField] = $this->getLastInsertId();
            }
            return true;
        }
        return false;
    }

    /**
     * @see sys_db_Database::select()
     *
     * @param array $query
     * @param array $params
     * @param integer $cache
     * @return array
     */
    public function select (array $query, array $params = array(), $cache = null)
    {
        $query = $this->makeSelectArr($query);
        $cmd = 'SELECT '.$query['fields'].' FROM '.$query['table'];
        if (! is_null($query['where'])) {
            $cmd .= ' WHERE ' . $query['where'];
        }
        if (! is_null($query['more'])) {
            $cmd .= ' ' . $query['more'];
        }
        if (! is_null($query['orderBy'])) {
            $cmd .= ' ORDER BY ' . $query['orderBy'];
        }
        return $this->execute($cmd, $params, $cache, $query['limit'], $query['offset']);
    }

    /**
     * @see sys_db_Database::update()
     *
     * @param array|string $table
     * @param array $values
     * @param boolean $quoteVals
     * @return boolean
     */
    public function update ($table, array &$values, $quoteVals = true)
    {
        if ($this->transactionMode == self::TRANS_NO_TRANS) {
            $this->begin();
            $result = $this->doUpdate($table, $values, $quoteVals);
            if (!$result) {
                $this->fail();
            }
            $this->complete();
        } else {
            $result = $this->doUpdate($table, $values, $quoteVals);
        }
        return $result;
    }

    /**
     * Performs the update query
     *
     * @see sys_db_Database::update()
     * @param array|string $table
     * @param array $values
     * @param boolean $quoteVals
     * @return boolean
     * @todo refactor to bound parameters
     */
    protected function doUpdate ($table, array &$values, $quoteVals)
    {
        $query = $this->makeUpdateArr($table);
        $cmd = 'UPDATE '.$query['table'].' SET ';
        $schema = $this->getTableSchema($query['table']);
        $quotedValues = array();
        if ($quoteVals) {
            foreach($values as $field=>$value) {
                if (!isset($schema['columns'][$field])) {
                    continue;
                }
                $quotedValues[$field] = $this->getQuotedVal($value, $schema['columns'][$field]['type']);
            }
        } else {
            $quotedValues = $values;
        }
        if (isset($schema['inheritance']['extendsTable'])) {
            $parentQuery = array('table'=>$query['table'], 'where'=>$query['where'], 'fields'=>reset($schema['primaryKey']), 'limit'=>$query['limit']);
            $tmp = $this->select($parentQuery);
            $ids = array();
            if (!count($tmp)) {
                $this->affectedRows = 0;
                return true;
            }
            foreach($tmp as $id) {
                $ids[] = $this->quote(reset($id));
            }
            $parentQuery = array('table'=>$schema['inheritance']['extendsTable'], 'where'=>reset($schema['primaryKey']).' IN ('.implode(', ', $ids).')', 'limit'=>$query['limit']);
            $result = $this->update($parentQuery, $quotedValues, false);
            if (!$result) {
                return false;
            }
        }
        if (!count($quotedValues)) {
            $this->affectedRows = 0;
            return true;
        }
        if ($this->useListeners && is_array($schema['listeners'])) {
            foreach($schema['listeners'] as $listener) {
                $listener = trim($listener);
                if (!class_exists($listener)) {
                    throw new sys_exception_DatabaseException('Listener does not exist: '.$listener, sys_exception_DatabaseException::ERR_INVALID_LISTENER);
                }
                $listenerObj = new $listener();
                if (!($listenerObj instanceof sys_db_ModifyListener)) {
                    throw new sys_exception_DatabaseException('Invalid listener: '.$listener, sys_exception_DatabaseException::ERR_INVALID_LISTENER);
                }
                $listenerObj->onUpdate($this, $table, $quotedValues);
            }
        }
        $set = array();
        foreach($quotedValues as $field=>$value) {
            if (!isset($schema['columns'][$field])) {
                continue;
            }
            if ($schema['columns']['autoincrement']) {
                continue;
            }
            $set[] = $field.'='.$value;
        }
        if (!count($set)) {
            $this->affectedRows = 0;
            return true;
        }
        $cmd .= implode(', ' , $set);
        if (! is_null($query['where'])) {
            $cmd .= ' WHERE '.$query['where'];
        }
        if ($this->execute($cmd, array(), 0, $query['limit'])) {
            $this->affectedRows = $this->db->affected_rows;
            return true;
        }
        return false;
    }

    /**
     * Returns the value quoted according to it's type
     *
     * @param mixed $value
     * @param string $type
     * @return integer|float|string
     */
    protected function getQuotedVal($value, $type) {
        // Missing doctrine types: enum, gzip
        if (is_null($value)) {
            return 'NULL';
        }
        switch($type) {
            case 'integer':
                $value = (int)$value;
                break;
            case 'boolean':
                $value = (int)(bool)$value;
                break;
            case 'decimal':
            case 'float':
            case 'double':
                // hack to fix locales that use a coma as a decimal
                $value = str_replace(',', '.', (float)$value);
                break;
            case 'array':
            case 'object':
                $value = serialize($value);
                // break intentionally left out, $value needs to be quoted/escaped
            case 'blob':
            case 'clob':
            case 'date':
            case 'time':
            case 'timestamp':
            case 'time':
            case 'date':
            case 'string':
            default:
                $value = $this->quote($value);
                break;
        }
        return $value;
    }

    /**
     * @see sys_db_Database::getFunc()
     *
     * @param string $funcName
     * @param array $funcParams
     * @throws sys_exception_DatabaseException
     * @return string
     */
    public function getFunc ($funcName, array $funcParams = array())
    {
        switch (strtoupper($funcName)) {
            case 'NOW':
                return 'NOW()';
            case 'CONCAT':
                if (!count($funcParams)) {
                    throw new sys_exception_DatabaseException('Missing params for CONCAT function', sys_exception_DatabaseException::ERR_FUNC_ERROR);
                }
                return 'CONCAT('.implode(', ', $funcParams).')';
            case 'COUNT':
                if (1 != count($funcParams)) {
                    throw new sys_exception_DatabaseException('The COUNT function requires exactly 1 parameter', sys_exception_DatabaseException::ERR_FUNC_ERROR);
                }
                return 'COUNT('.reset($funcParams).')';
            default:
                throw new sys_exception_DatabaseException('Unknown function: '.$funcName, sys_exception_DatabaseException::ERR_FUNC_ERROR);
                break;
        }
    }

    /**
     * @see sys_db_Database::getBool()
     *
     * @param boolean $bool
     * @return mixed
     */
    public function getBool ($bool)
    {
        return (int)(boolean)$bool;
    }

}

?>
