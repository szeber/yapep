<?php

class sys_Log {

    /**
     * Log level constants
     */
    const LEVEL_NONE = 0;
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_INFO = 4;
    const LEVEL_DEBUG = 8;

    /**
     *
     * @var sys_Log
     */
    protected static $INSTANCE;

    /**
     *
     * @var array
     */
    protected $loggers = array();

    /**
     * Constructor
     */
    protected function __construct() {
        $config = sys_ApplicationConfiguration::getInstance();
        $this->loggers = array();
        if(defined('DEBUGGING') && DEBUGGING) {
            $this->addLogger('debug', self::LEVEL_DEBUG, sys_log_Debug::getInstance());
        }
        if ($config->getOption('fileLogging')) {
            $this->addLogger('file', $this->getLoglevel($config->getOption('fileLogLevel')), sys_log_File::getInstance());
        }
        if ($config->getOption('syslogLogging')) {
            $this->addLogger('syslog', $this->getLoglevel($config->getOption('syslogLogLevel')), sys_log_Syslog::getInstance());
        }
        if ($config->getOption('dbLogging')) {
            $this->addLogger('db', $this->getLoglevel($config->getOption('dbLogLevel')), sys_log_Db::getInstance());
        }
        if (defined('CLI') && CLI && $config->getOption('cliConsoleLogging')) {
            $this->addLogger('cliConsole', $this->getLoglevel($config->getOption('cliConsoleLogLevel')), sys_log_CliConsole::getInstance());
        }
        $this->addLogger('emailAlert', self::LEVEL_ERROR, sys_log_EmailAlert::getInstance());
    }

    protected function getLogLevel($logLevelString) {
        switch(strtolower(trim($logLevelString))) {
            case 'error':
                return self::LEVEL_ERROR;
                break;
            case 'warning':
                return self::LEVEL_WARNING;
                break;
            case 'info':
                return self::LEVEL_INFO;
                break;
            case 'debug':
                return self::LEVEL_DEBUG;
                break;
        }
        return self::LEVEL_NONE;
    }

    /**
     * Creates and returns the singleton instance
     *
     * @return sys_Log
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_Log();
        }
        return self::$INSTANCE;
    }

    /**
     * Logs an event
     *
     * @param integer $level
     * @param string $source
     * @param string $type
     * @param string $description
     * @param mixed $data
     * @param integer $userId
     */
    protected function doLog($level, $source, $type = null, $description = '', $data = null, $userId = null) {
        foreach($this->loggers as $name=>$logger) {
            if ($level <= $logger['level']) {
                $logger['obj']->log($level, $source, $type, $description, $data, $userId);
            }
        }
    }

    /**
     * Logs an event
     *
     * @param integer $level Log level
     * @param string $source Event source. Defaults to 'System'
     * @param string $type Event type. Optional
     * @param string $description Event description. Optional
     * @param mixed $data Additional data. Optional. Not logged by all loggers
     * @param integer $userId User ID. Optional. Not logged by all loggers
     */
    public static function log($level, $source = 'System', $type = null, $description = '', $data = null, $userId = null) {
        self::getInstance()->doLog($level, $source, $type, $description, $data, $userId);
    }

    /**
     * Removes an active debugger
     *
     * This function should only be used by a logger if they encountered an error,
     * so they can remove themselves from the logging process.
     *
     * @param string $name
     */
    public function removeLogger($name) {
        if (isset($this->loggers[(string)$name])) {
            unset($this->loggers[(string)$name]);
        }
    }

    public function addLogger($name, $level, sys_ILog $logger) {
        if ($level == self::LEVEL_NONE) {
            return true;
        }
        if($logger->checkStatus()) {
            $this->loggers[$name] = array(
                'level' => $level,
                'obj'   => $logger,
            );
            return true;
        }
        return false;
    }

}