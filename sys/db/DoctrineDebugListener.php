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
 * Doctrine debug listener
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_DoctrineDebugListener extends Doctrine_EventListener implements sys_db_Listener {

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
	 * @see sys_db_Listener::afterQuery()
	 *
	 * @param mixed $event
	 */
	public function afterQuery($event) {
		$query = $this->pendingQueries [$event->getQuery ()];
		if (!$query) {
			return;
		}
		unset ($this->pendingQueries [$query ['query']]);
		$query ['time'] = microtime (true) - $query ['startTime'];
		$invoker = $event->getInvoker ();
		$query ['rows'] = -1;
		$query ['errorCode'] = $invoker->errorCode ();
		$query ['errorMessage'] = implode (', ', $invoker->errorInfo ());
		$debug = new sys_db_DatabaseDebug ($query ['query'], !(int) $query ['errorCode'], $query ['rows'], -1, -1, $query ['errorCode'], $query ['errorMessage'], 0, false, $query ['time'], $query ['params']);
		$this->debugger->addQuery ($debug);
	}

	/**
	 * @see sys_db_Listener::beforeQuery()
	 *
	 * @param mixed $event
	 */
	public function beforeQuery($event) {
		$query = array ();
		$query ['startTime'] = microtime (true);
		$query ['query'] = $event->getQuery ();
		$query ['params'] = $event->getParams ();
		$this->pendingQueries [$query ['query']] = $query;
	}
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->debugger = sys_Debugger::getInstance ();
	}

	/**
	 *
	 * @param Doctrine_Event $event
	 * @see Doctrine_EventListener::preStmtExecute()
	 */
	public function preStmtExecute(Doctrine_Event $event) {
		$this->beforeQuery($event);
	}

	/**
	 *
	 * @param Doctrine_Event $event
	 * @see Doctrine_EventListener::postStmtExecute()
	 */
	public function postStmtExecute(Doctrine_Event $event) {
		$this->afterQuery($event);
	}

	/**
	 * @see Doctrine_EventListener::postQuery()
	 *
	 * @param Doctrine_Event $event
	 */
	public function postQuery(Doctrine_Event $event) {
		$this->afterQuery($event);
	}

	/**
	 * @see Doctrine_EventListener::preQuery()
	 *
	 * @param Doctrine_Event $event
	 */
	public function preQuery(Doctrine_Event $event) {
		$this->beforeQuery($event);
	}

	/**
	 * @see Doctrine_EventListener::postExec()
	 *
	 * @param Doctrine_Event $event
	 */
	public function postExec(Doctrine_Event $event) {
		$this->afterQuery($event);
	}

	/**
	 * @see Doctrine_EventListener::preExec()
	 *
	 * @param Doctrine_Event $event
	 */
	public function preExec(Doctrine_Event $event) {
		$this->beforeQuery($event);
	}

}
?>