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
 * Generic debug listener
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_DatabaseDebugListener implements sys_db_Listener {

	/**
	 * @var sys_Debugger
	 */
	private $debugger;

	/**
	 * Temporarily stores the pending queries
	 *
	 * @var array
	 */
	private $pendingQueries = array ();

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->debugger = sys_Debugger::getInstance ();
	}

	/**
	 * @see sys_db_Listener::afterQuery()
	 *
	 * @param mixed $event
	 */
	public function afterQuery($event) {
		$query = $this->pendingQueries [$event ['query']];
		unset ($this->pendingQueries [$query ['query']]);
		$query ['time'] = microtime (true) - $query ['startTime'];
		$debug = new sys_db_DatabaseDebug ($query ['query'], $event ['success'], $event ['rows'], $query ['limit'], $query ['offset'], $event ['errorCode'], $event ['errorMessage'], $event ['cache'], $event ['cacheHit'], $query ['time']);
		$this->debugger->addQuery ($debug);
        if (!$event['success']) {
            $data = array(
                'POST'      => $_POST,
                'GET'       => $_GET,
                'trace'     => debug_backtrace(false),
                'query'     => $event['query'],
                'error'     => $event ['errorMessage'],
            );
            sys_Log::log(sys_Log::LEVEL_ERROR, 'Database', 'QueryError', 'Query: "'.$event['query'].'" Error: "'.$event ['errorMessage'].'"', $data);
        }
	}

	/**
	 * @see sys_db_Listener::beforeQuery()
	 *
	 * @param mixed $event
	 */
	public function beforeQuery($event) {
		$query = array ();
		$query ['startTime'] = microtime (true);
		$query ['query'] = $event ['query'];
		$query ['limit'] = $event ['limit'];
		$query ['offset'] = $event ['offset'];
		$this->pendingQueries [$query ['query']] = $query;
	}
}
?>