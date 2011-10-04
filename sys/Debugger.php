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
     *
     * @var array
     */
    private $unhandledLogs = array();

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
     *
     * @var array
     */
    private $sysLogs = array();

	/**
	 * Module debug information
	 *
	 * @var array
	 */
	private $moduleDebug=array();

    /**
     *
     * @var FirePHP
     */
    private $firePhp;

    /**
     *
     * @var integer
     */
	private $startTime;

	/**
	 * Constructor
	 *
	 */
	protected function __construct() {
		self::$INSTANCE = $this;
		$this->db=sys_LibFactory::getDbConnection('site');
        require_once(LIB_DIR.'FirePHPCore/FirePHP.class.php');
        $this->firePhp = FirePHP::getInstance(true);
	}

	/**
	 * Returns the singleton instance
	 *
	 * @return sys_Debugger
	 */
	public static function getInstance() {
		if (empty(self::$INSTANCE)) {
			sys_ApplicationConfiguration::getInstance();
			if (DEBUGGING || (isset($_SESSION['debug_enable']) && defined('ADMIN_PREVIEW') && ADMIN_PREVIEW)) {
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
		self::$INSTANCE->moduleDebug[]=array('info'=>$moduleInfo, 'args'=>$args, 'smarty'=>$smartyVars, 'queries'=>self::$INSTANCE->getUnhandledQueries(), 'logs' => self::$INSTANCE->getUnhandledLogs(), 'cached'=>$cached);
		self::$INSTANCE->clearUnhandledQueries();
        self::$INSTANCE->clearUnhandledLogs();
	}

	public function getUnhandledQueries() {
		return $this->unhandledQueries;
	}

    public function getUnhandledLogs() {
        return $this->unhandledLogs;
    }

	public function clearUnhandledQueries() {
		$this->unhandledQueries=array();
	}

    public function clearUnhandledLogs() {
        $this->unhandledLogs = array();
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
	 * Transmits the debug information
	 */
	public function getDebugInfo() {
        foreach($this->errorList as $error) {
            $this->firePhp->error($error);
        }
        $this->firePhp->table('System queries', $this->getFirePhpQueryTable($this->sysQueries));
        $this->addSystemQueries();
        if (is_array($this->sysLogs) && count($this->sysLogs)) {
            $this->firePhp->table('System logs', $this->getFirePhpLogTable($this->sysLogs));
        }
        $this->firePhp->group('Module information', array('Collapsed'=>true));
        foreach($this->moduleDebug as $module) {
            if ($module['cached']) {
                $this->firePhp->group($module['info']['name'].' ('.$module['info']['description'].') Cached', array('Collapsed'=>true, 'Color'=>'#00FF00'));
            } else {
                $this->firePhp->group($module['info']['name'].' ('.$module['info']['description'].')', array('Collapsed'=>true, 'Color'=>'#0000FF'));
            }
            $this->firePhp->table('Module info', $this->getFirePhpDataTable($module['info']));
            $this->firePhp->table('Module args', $this->getFirePhpDataTable($module['args']));
            $this->firePhp->table('Smarty variables', $this->getFirePhpDataTable($module['smarty']));
            $this->firePhp->table('Database queries', $this->getFirePhpQueryTable($module['queries']));
            if (is_array($module['logs']) && count($module['logs'])) {
                $this->firePhp->table('LogData', $this->getFirePhpLogTable($module['logs']));
            }
            $this->firePhp->groupEnd();
        }
        $this->firePhp->groupEnd();
        $files = $this->getIncludedFiles();
        $this->firePhp->group('Included files', array('Collapsed'=>true));
        foreach($files as $file) {
            $this->firePhp->log($file);
        }
        $this->firePhp->groupEnd();
        $this->firePhp->info('Page loaded in '.round((microtime(true) - $this->startTime)*1000).' ms.');
        $queryInfo = $this->getQueryInfo();
        $this->firePhp->info($queryInfo['count'].' queries run ('.$queryInfo['cacheCount'].' cached) in '.$queryInfo['timeFormat'].' ms.');
        $this->firePhp->info('Average query execution time: '.$queryInfo['avgTimeFormat'].' ms.');
        $this->firePhp->info('IP: '.$_SERVER['REMOTE_ADDR']);
        $this->firePhp->info('Peak memory usage: '.(memory_get_peak_usage(true)/1024).' KiB');
	}

    /**
	 * Transmits the admin debug information
	 */
	public function getAdminDebugInfo($module, $receivedXml, $sentXml) {
        foreach($this->errorList as $error) {
            $this->firePhp->error($error);
        }
        $this->firePhp->info('Module used: '.$module);
        $this->firePhp->info(array('Received'=>$receivedXml, 'Sent'=>$sentXml), 'XMLs');
        $this->addSystemQueries();
        $this->firePhp->table('System queries', $this->getFirePhpQueryTable($this->sysQueries));
        if (is_array($this->sysLogs) && count($this->sysLogs)) {
            $this->firePhp->table('System logs', $this->getFirePhpLogTable($this->sysLogs));
        }
        $files = $this->getIncludedFiles();
        $this->firePhp->group('Included files', array('Collapsed'=>true));
        foreach($files as $file) {
            $this->firePhp->log($file);
        }
        $this->firePhp->groupEnd();
        $this->firePhp->info('Page loaded in '.round((microtime(true) - $this->startTime)*1000).' ms.');
        $queryInfo = $this->getQueryInfo();
        $this->firePhp->info($queryInfo['count'].' queries run ('.$queryInfo['cacheCount'].' cached) in '.$queryInfo['timeFormat'].' ms.');
        $this->firePhp->info('Average query execution time: '.$queryInfo['avgTimeFormat'].' ms.');
        $this->firePhp->info('IP: '.$_SERVER['REMOTE_ADDR']);
        $this->firePhp->info('Peak memory usage: '.(memory_get_peak_usage(true)/1024).' KiB');
	}


    private function getFirePhpQueryTable($queries) {
        $table = array(
            array(
                'SQL',
                'Result',
                'Info',
                'Rows',
                'Time',
                'Cached'
            )
        );
        foreach($queries as $query) {
            $tmp = array($query->getFormattedQuery(), $query->getSuccess(), $query->getErrorMessage());
            $tmp2 = $query->getRows();
            if ($tmp2 > -1) {
                $tmp [] = $tmp2;
            } else {
                $tmp[] = '';
            }
            $tmp[] = $query->getTime();
            if ($query->getCacheHit()) {
                $tmp[] = 'CACHED';
            } else {
                $tmp [] = '';
            }
            $table[] = $tmp;
        }
        return $table;
    }

    private function getFirePhpLogTable($logs) {
        $table = array(
            array(
                'Level',
                'Source',
                'Type',
                'Description',
                'Data',
                'User ID'
            )
        );
        return array_merge($table, $logs);
    }

    private function getFirePhpDataTable(array $data) {
        $table = array(array('name', 'value'));
        foreach($data as $key=>$val) {
            $table [] = array($key, $val);
        }
        return $table;
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
        $this->sysLogs += $this->unhandledLogs;
		$this->unhandledQueries=array();
        $this->unhandledLogs=array();
	}

	/**
	 * Adds a new query to the unhandled query list
	 *
	 * @param sys_db_DatabaseDebug $debug
	 */
	public function addQuery(sys_db_DatabaseDebug $debug) {
		$this->unhandledQueries[]=$debug;
	}

    public function addLog($level, $source, $type = null, $description = '', $data = null, $userId = null) {
        $this->unhandledLogs[] = array(
            'level'=>$level,
            'source'=>$source,
            'type'=>$type,
            'description'=>$description,
            'data'=>$data,
            'userId'=>$userId,
        );
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

    /**
     * Returns the FirePHP instance
     *
     * @return FirePHP
     */
    public function getFirePhp() {
        return $this->firePhp;
    }

    public static function debug($message) {
		if (!self::$INSTANCE) {
			self::getInstance();
		}
        self::$INSTANCE->firePhp->log($message);
    }
}

?>