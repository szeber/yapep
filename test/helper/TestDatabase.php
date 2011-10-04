<?php
class sys_db_TestDatabase extends sys_db_Database {

	static $callback = null;

	public static function setCallbackHandler(test_helper_IDbCallback $callback = null) {
		self::$callback = $callback;
	}

	/**
	 * @see sys_db_Database::begin()
	 *
	 */
	public function begin() {}

	/**
	 * @see sys_db_Database::complete()
	 *
	 */
	public function complete() {}

	/**
	 * @see sys_db_Database::delete()
	 *
	 * @param string $table
	 * @param string $where
	 * @param integer $limit
	 * @return boolean
	 */
	public function delete($table, $where, $limit = false) {
		if (self::$callback) {
			return self::$callback->dbDelete($table, $where, $limit);
		}
		return true;
	}

	/**
	 * @see sys_db_Database::escape()
	 *
	 * @param string $string
	 * @param boolean $escapeWildcards
	 * @return string
	 */
	public function escape($string, $escapeWildcards=false) {
		return addslashes($string);
	}

	/**
	 * @see sys_db_Database::execute()
	 *
	 * @param string $cmd
	 * @param integer $cache
	 * @param unknown_type $limit
	 * @param unknown_type $offset
	 * @return mixed
	 */
	public function execute($cmd, $cache = null, $limit=-1, $offset=-1) {
		if (self::$callback) {
			return self::$callback->dbExecute($cmd, $cache, $limit, $offset);
		}
		return array();
	}

	/**
	 * @see sys_db_Database::fail()
	 *
	 */
	public function fail() {}

	/**
	 * @see sys_db_Database::getAffectedRows()
	 *
	 * @return integer
	 */
	public function getAffectedRows() {}

	/**
	 * @see sys_db_Database::getLastInsertId()
	 *
	 * @return integer
	 */
	public function getLastInsertId() {}

	/**
	 * @see sys_db_Database::getNumRows()
	 *
	 * @return integer
	 */
	public function getNumRows() {}

	/**
	 * @see sys_db_Database::getType()
	 *
	 * @return string
	 */
	public function getType() {
		return 'test';
	}

	/**
	 * @see sys_db_Database::insert()
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $values
	 * @return boolean
	 */
	public function insert($table, $fields, $values) {
		if (self::$callback) {
			return self::$callback->dbInsert($table, $fields, $values);
		}
		return true;
	}

	/**
	 * @see sys_db_Database::select()
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $where
	 * @param unknown_type $order_by
	 * @param string $more
	 * @param integer $limit
	 * @param integer $offset
	 * @param integer $cache
	 * @return array
	 */
	public function select($table, $fields, $where = null, $order_by = null, $more = null, $limit = -1, $offset = -1, $cache = null) {
		if (self::$callback) {
			return self::$callback->dbSelect($table, $fields, $where, $order_by, $more, $limit, $offset, $cache);
		}
		return array();
	}

	/**
	 * @see sys_db_Database::setupConnection()
	 *
	 */
	public function setupConnection() {
		$this->connected = true;
	}

	/**
	 * @see sys_db_Database::update()
	 *
	 * @param string $table
	 * @param string $set
	 * @param string $where
	 * @param integer $limit
	 * @return boolean
	 */
	public function update($table, $set, $where = null, $limit = false) {
		if (self::$callback) {
			return self::$callback->dbUpdate($table, $set, $where, $limit);
		}
		return true;
	}

}
?>