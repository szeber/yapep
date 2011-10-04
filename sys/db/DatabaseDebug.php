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
 * Database debug class
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_DatabaseDebug {

	/**
	 *
	 * @var string
	 */
	private $query;

	/**
	 *
	 * @var boolean
	 */
	private $success;

	/**
	 *
	 * @var integer
	 */
	private $rows;

	/**
	 *
	 * @var integer
	 */
	private $errorCode;

	/**
	 *
	 * @var string
	 */
	private $errorMessage;

	/**
	 *
	 * @var integer
	 */
	private $limit;

	/**
	 *
	 * @var integer
	 */
	private $offset;

	/**
	 *
	 * @var integer
	 */
	private $cache;

	/**
	 *
	 * @var boolean
	 */
	private $cacheHit;

	/**
	 *
	 * @var float
	 */
	private $time;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Returns the caching time for the query. 0 if no caching is used
	 *
	 * @return integer
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * Returns the limit used in the query
	 *
	 * @return integer
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * Returns the offset used in the query
	 *
	 * @return integer
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * Returns the error code for the query
	 *
	 * @return integer
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}

	/**
	 * Returns the error message for the query
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * Returns the query string
	 *
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Returns the number of affected or returned rows for the query
	 *
	 * @return integer
	 */
	public function getRows() {
		return $this->rows;
	}

	/**
	 * Returns true if the query was returned from cache
	 *
	 * @return boolean
	 */
	public function getCacheHit() {
		return $this->cacheHit;
	}

	/**
	 * Returns the query execution time
	 *
	 * @return float
	 */
	public function getTime($formatted = true) {
		if (!$formatted) {
			return $this->time;
		}
		return number_format ($this->time * 1000, 4);
	}

	/**
	 * Returns if the query was successful
	 *
	 * @return string
	 */
	public function getSuccess($bool = false) {
		if ($bool) {
			return $this->success;
		}
		if ($this->success) {
			return _ ('SQL OK');
		}
		return _ ('SQL FAIL');
	}

	/**
	 * Returns all debug information as an array
	 *
	 * @return array
	 */
	public function getDebugInfo() {
		return array ('query' => $this->getQuery (), 'success' => $this->getSuccess (), 'rows' => $this->getRows (), 'errorCode' => $this->getErrorCode (), 'errorMessage' => $this->getErrorMessage (), 'limit' => $this->getLimit (), 'offset' => $this->getOffset (), 'cache' => $this->getCache (), 'cacheHit' => $this->getCacheHit (), 'time' => $this->getTime ());
	}

	/**
	 * Returns a the formatted query string
	 *
	 * @return string
	 */
	public function getFormattedQuery($html = false) {
		return self::formatQuery ($this->query, $html);
	}

	/**
	 * Formats a query string and returns it
	 *
	 * @param string $query
	 * @return string
	 */
	static public function formatQuery($query, $html = false) {
		if ($html) {
			$replace = '"\n<span class=\\"sql_kw\\">".strtoupper(\'$1\')."</span>"';
		} else {
			$replace = '"\n".strtoupper(\'$1\')';
		}
		$query = preg_replace ('/(?<=^|\W)((off)?set|select|from|(left|inner)? *join|where|group by|having|order by|limit|insert into|values|update|delete from)(?=$|\W)/ei', $replace, trim ($query));
		if ("\n" == substr ($query, 0, 1)) {
			$query = substr ($query, 1);
		}
		if ($html) {
			$query = str_replace ("\n", "<br />\n", $query);
		}
		return $query;
	}

	/**
	 *
	 * @param string $query
	 * @param boolean $success
	 * @param integer $rows
	 * @param integer $limit
	 * @param integer $offset
	 * @param integer $errorCode
	 * @param string $errorMessage
	 * @param integer $cache
	 * @param boolean $cacheHit
	 * @param float $time
	 */
	public function __construct($query, $success, $rows, $limit = -1, $offset = -1, $errorCode = 0, $errorMessage = '', $cache = 0, $cacheHit = false, $time = 0, $params = array()) {
		$this->query = $query;
		$this->success = $success;
		$this->rows = $rows;
		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->cache = $cache;
		$this->cacheHit = $cacheHit;
		$this->time = $time;
		$this->params = $params;
	}
}

?>