<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Debugger class
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_Debugger {

	/**
	 * Singleton instance
	 *
	 * @var sys_Debugger
	 */
	private static $INSTANCE;

	/**
	 * Stores the unhandled queries
	 *
	 * @var array
	 */
	private $unhandledQueries=array();

	/**
	 * DB access
	 *
	 * @var sys_db_Database
	 */
	private $db;

	/**
	 * Errors happened
	 *
	 * @var boolean
	 */
	private $error=false;

	/**
	 * List of errors
	 *
	 * @var array
	 */
	private $errorList=array();

	/**
	 * List of system queries
	 *
	 * @var array
	 */
	private $sysQueries=array();

	/**
	 * Module debug information
	 *
	 * @var array
	 */
	private $moduleDebug=array();

	private $startTime;

	/**
	 * Constructor
	 *
	 */
	protected function __construct() {
		self::$INSTANCE = $this;
		$this->db=sys_LibFactory::getDbConnection('site');
	}

	/**
	 * Returns the singleton instance
	 *
	 * @return sys_Debugger
	 */
	public static function getInstance() {
		if (empty(self::$INSTANCE)) {
			sys_ApplicationConfiguration::getInstance();
			if (DEBUGGING) {
				new sys_Debugger();
			} else {
				new sys_DummyDebugger();
			}
		}
		return self::$INSTANCE;
	}

	/**
	 * Adds debug information for a module
	 *
	 * @param array $moduleInfo
	 * @param array $args
	 * @param array $smartyVars
	 * @param boolean $cached
	 */
	public static function addModuleDebugInfo($moduleInfo, $args, $smartyVars, $cached) {
		if (!self::$INSTANCE) {
			self::getInstance();
		}
		unset($smartyVars['argArr'], $smartyVars['moduleInfo'], $smartyVars['MODULE']);
		ksort($smartyVars);
		self::$INSTANCE->moduleDebug[]=array('info'=>$moduleInfo, 'args'=>$args, 'smarty'=>$smartyVars, 'queries'=>self::$INSTANCE->getUnhandledQueries(), 'cached'=>$cached);
		self::$INSTANCE->clearUnhandledQueries();
	}

	public function getUnhandledQueries() {
		return $this->unhandledQueries;
	}

	public function clearUnhandledQueries() {
		$this->unhandledQueries=array();
	}

	/**
	 * Sets an error
	 *
	 * @param string $message
	 */
	public function setError($message) {
		$this->error=true;
		$this->errorList[]=$message;
	}

	/**
	 * Returns the debug information
	 *
	 * @return string
	 */
	public function getDebugInfo() {
		$smarty = sys_LibFactory::getSmarty();
		$smarty->caching=false;
		$this->addSystemQueries();
		$smarty->assign('systemQueries', $this->sysQueries);
		$smarty->assign('queryInfo', $this->getQueryInfo());
		$smarty->assign('errors', $this->errorList);
		$smarty->assign('moduleDebug', $this->moduleDebug);
		$smarty->assign('loadTime', (microtime(true) - $this->startTime));
		$smarty->assign('ipAddr', $_SERVER['REMOTE_ADDR']);
		$smarty->assign('peakMem', memory_get_peak_usage(true));
		$smarty->assign('includedFiles', $this->getIncludedFiles());
		return $smarty->fetch('yapep:debug.tpl');
	}

	private function getIncludedFiles() {
		$config = sys_ApplicationConfiguration::getInstance();
		$files=get_included_files();
		foreach($files as $key=>$file) {
			if (strstr($file, $config->getPath('smartyCompileDir')) || strstr($file, $config->getPath('smartyCacheDir'))) {
				unset($files[$key]);
			}
		}
		return $files;
	}

	/**
	 * Returns the total number of queries ran
	 *
	 * @return integer
	 */
	private function getQueryInfo() {
		$failCount=0;
		$time=0;
		$cacheCount=0;
		$count=count($this->sysQueries);
		foreach($this->sysQueries as $query) {
			if (!$query->getSuccess(true)) {
				$failCount++;
			}
			if ($query->getCacheHit()) {
				$cacheCount++;
			}
			$time+=$query->getTime(false);
		}
		foreach($this->moduleDebug as $module) {
			$count += count($module['queries']);
			foreach($module['queries'] as $query) {
				if (!$query->getSuccess(true)) {
					$failCount++;
				}
				if ($query->getCacheHit()) {
					$cacheCount++;
				}
				$time+=$query->getTime(false);
			}
		}
		if ($failCount) {
			$this->setError($failCount.' database queries failed!');
		}
		if ($count-$cacheCount) {
			$avgTime=$time/$count;
            $avgTimeFormat=number_format(($time/($count-$cacheCount)*1000), 4);
		} else {
			$avgTime=0;
			$avgTimeFormat=0;
		}
		return array('count'=>$count, 'cacheCount'=>$cacheCount, 'time'=>$time, 'timeFormat'=>number_format(($time*1000), 4), 'avgTime'=>$avgTime, 'avgTimeFormat'=>$avgTimeFormat);
	}

	/**
	 * Adds the queries run so far to the system queries
	 *
	 */
	public function addSystemQueries() {
		$this->sysQueries += $this->unhandledQueries;
		$this->unhandledQueries=array();
	}

	/**
	 * Adds a new query to the unhandled query list
	 *
	 * @param sys_db_DatabaseDebug $debug
	 */
	public function addQuery(sys_db_DatabaseDebug $debug) {
		$this->unhandledQueries[]=$debug;
	}

	/**
	 * Starts the page load timer
	 *
	 */
	public static function startTimer() {
		if (!self::$INSTANCE) {
			self::getInstance();
		}
		self::$INSTANCE->startTime = microtime(true);
	}
}

/**
 * Dummy debugger class
 *
 * Overrides all public methods of the Debugger class to disable debugging
 *
 * @package YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.

 * @version	$Rev$
 */
class sys_DummyDebugger extends sys_Debugger {

	public static function addModuleDebugInfo($moduleInfo,$args,$smartyVars,$cached) {}

	public function getDebugInfo() {
		return '';
	}

	public function setError($message) {}

	public function addSystemQueries() {}
}
?>