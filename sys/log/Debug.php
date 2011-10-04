<?php

class sys_log_Debug implements sys_ILog {

    /**
     *
     * @var sys_log_Debug
     */
    protected static $INSTANCE;

    /**
     *
     * @var sys_Debugger
     */
    protected $debugger;

    /**
     *
     */
    protected function __construct() {
        $this->debugger = sys_Debugger::getInstance();
    }

    /**
     *
     * @return sys_log_Debug
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_log_Debug();
        }
        return self::$INSTANCE;
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
    public function log($level, $source, $type = null, $description = '', $data = null, $userId = null) {
        $this->debugger->addLog($level, $source, $type, $description, $data, $userId);
    }

    /**
     *
     * @return boolean
     */
    public function checkStatus() {
        return true;
    }
}