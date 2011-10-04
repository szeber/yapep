<?php
interface test_helper_IDbCallback {

	/**
	 * Delete callback
	 *
	 * @param string $table
	 * @param string $where
	 * @param integer $limit
	 * @return boolean
	 */
	public function dbDelete($table, $where, $limit);

	/**
	 * Execute callback
	 *
	 * @param string $cmd
	 * @param integer $cache
	 * @param integer $limit
	 * @param integer $offset
	 * @return mixed
	 */
	public function dbExecute($cmd, $cache, $limit, $offset);

	/**
	 * Insert callback
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $values
	 * @return boolean
	 */
	public function dbInsert($table, $fields, $values);

	/**
	 * Select callback
	 *
	 * @param string $table
	 * @param string $fields
	 * @param string $where
	 * @param string $order_by
	 * @param string $more
	 * @param integer $limit
	 * @param integer $offset
	 * @param integer $cache
	 * @return array
	 */
	public function dbSelect($table, $fields, $where, $order_by, $more, $limit, $offset, $cache);

	/**
	 * Update callback
	 *
	 * @param string $table
	 * @param string $set
	 * @param string $where
	 * @param integer $limit
	 * @return boolean
	 */
	public function dbUpdate($table, $set, $where, $limit);
}
?>