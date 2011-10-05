<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Abstract class for database access
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_db_Database
{

    /**
     * Database configuration
     *
     * @var array
     */
    protected $dbConfig;

    /**
     * Sets whether the table listeners are used
     *
     * @var boolean
     */
    protected $useListeners = true;

    /**
     * Enables or disables debugging
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * Debug information
     *
     * @var sys_db_DatabaseDebug[]
     */
    protected $debugData = array();

    /**
     * Set to true if a db connection is established
     *
     * @var boolean
     */
    protected $connected = false;

    /**
     * Array containing the listeners
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * The last error message
     *
     * @var string
     */
    protected $_lastError = '';

    /**
     * The last error code
     *
     * @var integer
     */
    protected $_lastErrorCode = 0;

    /**
     * @var sys_IApplicationConfiguration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param sys_IApplicationConfiguration $config
     * @param string $connection
     */
    final public function __construct (sys_IApplicationConfiguration $config, $connection)
    {
        $this->config = $config;
        $this->dbConfig = $config->getDatabase($connection);
        if (defined('DEBUGGING') && DEBUGGING) {
            $this->debug = true;
        }
        $this->setupConnection();
    }

    /**
     * Sets up the connection to the database. Also sets the $connected property
     *
     */
    abstract public function setupConnection ();

    /**
     * Runs a SELECT query on the database
     *
     * @param array $query
     * @param array $params
     * @param integer $cache The amount of seconds the query should be cached. Ignored if not supported by the engine. 0 to disable default db caching.
     * @return array The results of the query
     */
    abstract public function select (array $query, array $params = array(), $cache = null);

    /**
     * Runs a SELECT query on the database and returns the first result
     *
     * @see select
     *
     * @param array $query
     * @param array $params
     * @param integer $cache
     * @return array
     */
    public function selectFirst (array $query, array $params = array(), $cache = null)
    {
        $query['limit'] = 1;
        $query['offset'] = 0;
        $result = $this->select($query, $params, $cache);
        if (is_array($result) && count($result)) {
            return reset($result);
        }
        return $result;
    }

    /**
     * Runs an INSERT query on the database
     *
     * @param string $table
     * @param array $values
     * @param boolean $quoteVals
     * @return boolean True on success, false on failure
     */
    abstract public function insert ($table, array &$values, $quoteVals = true);

    /**
     * Runs an UPDATE query on the database
     *
     * @param array|string $table
     * @param array $values
     * @param boolean $quoteVals
     * @return boolean True on success, false on failure
     */
    abstract public function update ($table, array &$values, $quoteVals = true);

    /**
     * Runs a DELETE query on the database
     *
     * @param string $table
     * @param string $where
     * @param integer $limit
     * @return boolean True on success, false on failure
     */
    abstract public function delete ($table, $where, $limit = -1);

    /**
     * Executes a query on the database
     *
     * For portability reasons avoid using this function from client code.
     * The return value is highly dependant on the database implementation and the query's type!
     *
     * @param string $cmd
     * @param array $params
     * @param integer $cache
     * @param integer $limit
     * @param integer $offset
     * @return mixed
     */
    abstract public function execute ($cmd, array $params = array(), $cache = null, $limit = -1, $offset = -1);

    /**
     * Starts a transaction
     *
     */
    abstract public function begin ();

    /**
     * Fails a transaction
     *
     */
    abstract public function fail ();

    /**
     * Finishes a transaction.
     *
     * Automatically selects from rollback or commit is run depending on the success of
     * previous queries and whether te fail() method was called.
     *
     * @return boolean True on commit, false on rollback
     */
    abstract public function complete ();

    /**
     * Returns the last insert id
     *
     * @return integer
     */
    abstract public function getLastInsertId ();

    /**
     * Returns the number of rows received from the db by the last SELECT
     *
     * @return integer
     */
    abstract public function getNumRows ();

    /**
     * Returns the last affected rows
     *
     * @return integer
     */
    abstract public function getAffectedRows ();

    /**
     * Returns the SQL safe version of the string
     *
     * @param string $string
     * @param boolean $escapeWildcards
     * @return string
     */
    abstract public function escape ($string, $escapeWildcards = false);

    /**
     * Returns the SQL safe and quoted version of $string
     *
     * @param string $string
     * @param boolean $escapeWildcards
     * @return string
     */
    public function quote ($string, $escapeWildcards = false)
    {
        if (is_null($string)) {
            return 'NULL';
        }
        return "'" . $this->escape($string, $escapeWildcards) . "'";
    }

    /**
     * Returns the persistance layer type
     *
     * @return string
     */
    abstract public function getType ();

    /**
     * Returns true if the connection is established to the database
     *
     * @return booelan
     */
    public function getConnected ()
    {
        return $this->connected;
    }

    /**
     * Returns the debug information
     *
     * SQL commands, successes, returned/affected rows/insert_ids
     *
     * @return sys_db_databaseDebug[]
     */
    public function getDebug ()
    {
        return $this->debugData;
    }

    /**
     * Clears the debug information
     *
     */
    public function clearDebug ()
    {
        $this->debugData = array();
    }

    /**
     * Returns the last error message
     *
     * @return string
     */
    public function getLastError() {
        return $this->_lastError;
    }

    /**
     * Returns the last error code
     *
     * @return integer
     */
    public function getLastErrorCode() {
        return $this->_lastErrorCode;
    }

    /**
     * Attaches a listener to the active listeners
     *
     * @param sys_db_Listener $listener
     */
    public function addListener (sys_db_Listener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Detaches a listener from the active listeners. Returns true on success, false if the listener is not found
     *
     * @param sys_db_Listener $listener
     * @return boolean
     */
    public function removeListener (sys_db_Listener $listener)
    {
        $index = array_search($listener, $this->listeners, true);
        if (false === $index) {
            return false;
        }
        unset($this->listeners[$index]);
        return true;
    }

    /**
     * Returns an array containing all event listeners
     *
     * @return array
     */
    public function getListeners ()
    {
        return $this->listeners;
    }

    protected function makeSelectArr ($query)
    {
        if (isset($query['from']) && !isset($query['table'])) {
            $query['table'] = $query['from'];
        }
        if (empty($query['table'])) {
            throw new sys_exception_DatabaseException(
                'Table parameter is missing',
                sys_exception_DatabaseException::ERR_INVALID_SELECT_QUERY);
        }
        if (isset($query['field'])) {
            $query['fields'] = $query['field'];
        } else if (isset($query['what'])) {
            $query['fields'] = $query['what'];
        }
        if (isset($query['fields']) && is_array($query['fields'])) {
            $query['fields'] = implode(',', $query['fields']);
        }
        if (empty($query['fields'])) {
            $query['fields'] = '*';
        }
        if (empty($query['where'])) {
            $query['where'] = null;
        }
        if (empty($query['more'])) {
            $query['more'] = null;
        }
        if (!empty($query['order'])) {
            $query['orderBy'] = $query['order'];
        } else if (!empty($query['orderby'])) {
            $query['orderBy'] = $query['orderby'];
        }
        if (empty($query['orderBy'])) {
            $query['orderBy'] = null;
        }
        if (empty($query['limit'])) {
            $query['limit'] = -1;
        } else {
            $query['limit'] = (int) $query['limit'];
        }
        if (empty($query['offset'])) {
            $query['offset'] = -1;
        } else {
            $query['offset'] = (int) $query['offset'];
        }
        return $query;
    }

    protected function makeUpdateArr ($query)
    {
        if (! is_array($query)) {
            return array('table' => $query , 'limit' => false);
        }
        if (! $query['table']) {
            throw new sys_exception_DatabaseException(
                'Table parameter is missing',
                sys_exception_DatabaseException::ERR_INVALID_UPDATE_QUERY);
        }
        if (! isset($query['where'])) {
            $query['where'] = null;
        }
        if (! isset($query['limit'])) {
            $query['limit'] = - 1;
        }
        return $query;
    }

    /**
     * Returns the schema for table
     *
     * @param string $table
     * @return array
     */
    public function getTableSchema ($table)
    {
        if (preg_match('/[.\/ ]/', $table)) {
            throw new sys_exception_DatabaseException(
                'Invalid table name: ' . $table,
                sys_exception_DatabaseException::ERR_INVALID_TABLE_NAME);
        }
        if (isset($this->_schemas[$table])) {
            return $this->_schemas[$table];
        }
        if (! file_exists(CACHE_DIR . 'dbSchema/YAPEP/' . $table . '.php')) {
            throw new sys_exception_DatabaseException(
                'Table schema file not found: ' . $table,
                sys_exception_DatabaseException::ERR_SCHEMA_ERROR);
        }
        include (CACHE_DIR . 'dbSchema/YAPEP/' . $table . '.php');
        if (! is_array($tableData)) {
            throw new sys_exception_DatabaseException(
                'Invalid schema file: ' . $table,
                sys_exception_DatabaseException::ERR_SCHEMA_ERROR);
        }
        $this->_schemas[$table] = $tableData;
        return $tableData;
    }

    /**
     * Quotes all fields with the correct type of quoting for their types
     *
     * @param string $table
     * @param array $data
     */
    public function quoteData($table, array &$data) {
        $schema = $this->getTableSchema($table);
        foreach($data as $field=>$value) {
            if (!isset($schema['columns'][$field])) {
                continue;
            }
            $data[$field] = $this->getQuotedVal($value, $schema['columns'][$field]['type']);
        }
    }


    /**
     * Returns the value quoted according to it's type
     *
     * @param mixed $value
     * @param string $type
     * @return integer|float|string
     */
    abstract public function getQuotedVal($value, $type);


    /**
     * Returns an array that has all the fields of the given table as the keys with their default values as the value
     *
     * @param sting $table
     * @return array
     */
    public function getDefaultRecord($table) {
        $schema = $this->getTableSchema($table);
        $record = array();
        foreach($schema['columns'] as $columnName=>$column) {
            if (isset($column['default'])) {
                $record[$columnName] = $column['default'];
            } else {
                $record[$columnName] = null;
            }
        }
        return $record;
    }

    /**
     * Returns $field quoted so it can be safely used in the field list of a query
     *
     * @param string $field
     * @return string
     */
    public function quoteField($field) {
        return '`'.addcslashes($field, '`').'`';
    }

    /**
     * This should return the current database's version of the $funcName function
     *
     * @param string $funcName
     * @param array $funcParams
     * @throws sys_exception_DatabaseException
     * @return string
     */
    abstract public function getFunc ($funcName, array $funcParams = array());

    /**
     * Returns the boolean type appropriate for the database
     *
     * @param boolean $bool
     * @return mixed
     */
    abstract public function getBool ($bool);

    /**
     * Enables table listeners
     *
     */
    public function enableListeners() {
        $this->useListeners = true;
    }

    /**
     * Disables table listeners
     *
     */
    public function disableListeners() {
        $this->useListeners = false;
    }

    /**
     * Returns the currently used db configuration
     *
     * @return array
     */
    public function getDbConfig() {
        return $this->dbConfig;
    }
}