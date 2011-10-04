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
 * Database handler for AdoDB
 *
 * @package	YAPEP
 * @subpackage Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_AdodbDatabase extends sys_db_Database {

	/**
	 * DB object
	 *
	 * @var ADOConnection
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
	 * The name of the table used for the last insert
	 *
	 * @var string
	 */
	protected $lastInsertTable = '';

	/**
	 *
	 * @see sys_db_Database::begin()
	 */
	public function begin() {
		$this->db->StartTrans ();
	}

	/**
	 *
	 * @see sys_db_Database::complete()
	 */
	public function complete() {
		$this->db->CompleteTrans ();
	}

	/**
	 *
	 * @param string $table
	 * @param string $where
	 * @param integer $limit
	 * @return boolean True on success, false on failure
	 * @see sys_db_Database::delete()
	 */
	public function delete($table, $where, $limit = false) {
		$query = "DELETE FROM $table WHERE $where";
		if ($limit) {
			$query .= ' LIMIT ' . (int) $limit;
		}
		if ($this->execute ($query, 0, $limit)) {
			$this->affectedRows = $this->db->Affected_Rows ();
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param string $cmd
	 * @param integer $cache
	 * @return ADORecordSet
	 * @see sys_db_Database::execute()
	 */
	public function execute($cmd, $cache = null, $limit = -1, $offset = -1) {
		foreach ( $this->listeners as $listener ) {
			$listener->beforeQuery (array ('query' => $cmd, 'limit' => $limit, 'offset' => $offset));
		}
		if (!$this->caching) {
			$cache = 0;
		} elseif (is_null ($cache)) {
			$cache = (int) $this->db->cacheSecs;
		} else {
			$cache = (int) $cache;
		}
		if ($cache) {
			$result = $this->db->CacheExecute ($cache, $cmd);
		} else {
			$startime = microtime (true);
			$result = $this->db->Execute ($cmd);
			$time = microtime (true) - $startime;
		}
		if ($result && is_object ($result)) {
			if (preg_match ('/^\s*select/i', $cmd)) {
				$recordCount = $result->RecordCount;
			} else {
				$recordCount = $this->db->Affected_Rows ();
			}
			foreach ( $this->listeners as $listener ) {
				$listener->afterQuery (array ('query' => $cmd, 'limit' => $limit, 'offset' => $offset, 'success' => true, 'rows' => $recordCount, 'limit' => $limit, 'offset' => $offset, 'errorCode' => 0, 'errorMessage' => $this->db->ErrorMsg (), 'cache' => $cache, 'cacheHit' => false));
			}
		} else {
			foreach ( $this->listeners as $listener ) {
				$listener->afterQuery (array ('query' => $cmd, 'limit' => $limit, 'offset' => $offset, 'success' => false, 'rows' => $recordCount, 'limit' => $limit, 'offset' => $offset, 'errorCode' => $this->db->ErrorNo (), 'errorMessage' => $this->db->ErrorMsg (), 'cache' => $cache, 'cacheHit' => false));
			}
		}
		return $result;
	}

	/**
	 *
	 * @see sys_db_Database::fail()
	 */
	public function fail() {
		return $this->db->FailTrans ();
	}

	/**
	 *
	 * @return integer
	 * @see sys_db_Database::getAffectedRows()
	 */
	public function getAffectedRows() {
		return $this->affectedRows;
	}

	/**
	 *
	 * @return integer
	 * @see sys_db_Database::getLastInsertId()
	 */
	public function getLastInsertId() {
		if (strstr ($this->db->databaseType, 'postgre')) {
			if (!$this->lastInsertTable) {
				return null;
			}
			$keys = $this->select ('information_schema.key_column_usage NATURAL JOIN information_schema.table_constraints', 'column_name', "constraint_type='PRIMARY KEY' and table_name=" . $this->quote ($this->lastInsertTable));
			foreach ( $keys as $val ) {
				$rs = $this->execute ('SELECT pg_catalog.currval(' . $this->db->quote ($this->lastInsertTable . '_' . $val [0] . '_seq') . ')');
				if (!$rs->EOF) {
					$id = $rs->fields [0];
					return $id;
				}
			}
			return null;
		} else {
			return $this->db->Insert_ID ();
		}
	}

	/**
	 *
	 * @return integer
	 * @see sys_db_Database::getNumRows()
	 */
	public function getNumRows() {
		return $this->numRows;
	}

	/**
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $values
	 * @return boolean True on success, false on failure
	 * @see sys_db_Database::insert()
	 */
	public function insert($table, $fields, $values) {
		$query = "INSERT INTO $table ($fields) VALUES ($values)";
		if ($this->execute ($query, 0)) {
			$this->lastInsertTable = $table;
			return true;
		}
		$this->lastInsertTable = $table;
		return false;
	}

	/**
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $where
	 * @param string $order_b
	 * @param string $more
	 * @param integer $limit
	 * @param integer $offset
	 * @param integer $cache
	 * @return array The results of the query
	 * @see sys_db_Database::select()
	 */
	public function select($table, $fields, $where = null, $order_by = null, $more = null, $limit = -1, $offset = -1, $cache = null) {
		if (!$this->caching) {
			$cache = 0;
		} elseif (is_null ($cache)) {
			$cache = (int) $this->db->cacheSecs;
		} else {
			$cache = (int) $cache;
		}
		$query = "SELECT $fields FROM $table";
		if (!is_null ($where)) {
			$query .= ' WHERE ' . $where;
		}
		if (!is_null ($more)) {
			$query .= ' ' . $more;
		}
		if (!is_null ($order_by)) {
			$query .= ' ORDER BY ' . $order_by;
		}
		$limit = (int) $limit;
		$offset = (int) $offset;
		foreach ( $this->listeners as $listener ) {
			$listener->beforeQuery (array ('query' => $query, 'limit' => $limit, 'offset' => $offset));
		}
		if ($cache) {
			$result = $this->db->CacheSelectLimit ($cache, $query, $limit, $offset);
		} else {
			$result = $this->db->SelectLimit ($query, $limit, $offset);
		}
		if ($result && is_object ($result)) {
			foreach ( $this->listeners as $listener ) {
				$listener->afterQuery (array ('query' => $query, 'limit' => $limit, 'offset' => $offset, 'success' => true, 'rows' => $result->RecordCount (), 'limit' => $limit, 'offset' => $offset, 'errorCode' => 0, 'errorMessage' => '', 'cache' => $cache, 'cacheHit' => false));
			}
			$data = $result->getArray ();
			$this->numRows = $result->RecordCount ();
			$result->Close ();
		} else {
			foreach ( $this->listeners as $listener ) {
				$listener->afterQuery (array ('query' => $query, 'limit' => $limit, 'offset' => $offset, 'success' => false, 'rows' => 0, 'limit' => $limit, 'offset' => $offset, 'errorCode' => $this->db->ErrorNo (), 'errorMessage' => $this->db->ErrorMsg (), 'cache' => $cache, 'cacheHit' => false));
			}
			$data = array ();
		}
		return $data;
	}

	/**
	 *
	 * @see sys_db_Database::setupConnection()
	 */
	public function setupConnection() {
		if (defined ('ADODB_PATH')) {
			require_once (ADODB_PATH . 'adodb_inc.php');
		} else {
			require_once (LIB_DIR . 'adodb/adodb.inc.php');
		}

		$this->db = NewADOConnection ($this->dbConfig ['dsn']);
		if (!($this->db instanceof ADOConnection)) {
			return;
		}
		$this->connected = true;
		$this->setupCharset ($this->dbConfig ['charset']);
		if (CACHING && $this->config->getOption ('dbCache')) {
			$this->caching = true;
			$this->db->cacheSecs = $config->getOption ('defaultDbCacheTime');
			global $ADODB_CACHE_DIR;
			$ADODB_CACHE_DIR = $config->getPath ('dbCacheDir');
		}
	}

	/**
	 * Sets the correct charset for the connection
	 *
	 * @param string $charset
	 */
	private function setupCharset($charset) {
		if (substr ($this->db->databaseType, 0, 5) == 'mysql') {
			$this->execute ("SET NAMES '$charset'");
		} else {
			$this->execute ("SET client_encoding = '$charset'");
		}
	}

	/**
	 *
	 * @param string $string
	 * @param boolean $escapeWildcards
	 * @return string
	 * @see sys_db_Database::quote()
	 */
	public function quote($string, $escapeWildcards = false) {
		if (is_null ($string)) {
			return 'NULL';
		}
		$string = $this->db->qstr ($string);
		if ($escapeWildcards) {
			$string = addcslashes ($string, '%_');
		}
		return $string;
	}

	/**
	 *
	 * @param string $string
	 * @param boolean $escapeWildcards
	 * @return string
	 * @see sys_db_Database escape()
	 */
	public function escape($string, $escapeWildcards = false) {
		$string = $this->db->escape ($string);
		if ($escapeWildcards) {
			$string = addcslashes ($string, '%_');
		}
		return $string;
	}

	/**
	 *
	 * @param string $table
	 * @param string $set
	 * @param string $where
	 * @param integer $limit
	 * @return boolean True on success, false on failure
	 * @see sys_db_Database::update()
	 */
	public function update($table, $set, $where = null, $limit = false) {
		$query = "UPDATE $table SET $set";
		if (!is_null ($where)) {
			$query .= " WHERE $where";
		}
		if ($limit) {
			$query .= ' LIMIT ' . (int) $limit;
		}
		if ($this->execute ($query, 0, $limit)) {
			$this->affectedRows = $this->db->Affected_Rows ();
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return string
	 * @see sys_db_Database::getType()
	 */
	public function getType() {
		$type = $this->db->databaseType;
		if ('mysql' == strtolower (substr ($type, 0, 5))) {
			$type = 'mysql';
		}
		return $type;
	}

}

?>
