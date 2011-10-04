<?php
/**
 * @TODO IMPLEMENT
 */
class sys_log_Syslog implements sys_ILog {

    /**
     *
     * @var sys_log_Syslog
     */
    protected static $INSTANCE;

    /**
     *
     */
    protected function __construct() {

    }

    /**
     *
     */
    public function __destruct() {

    }

    /**
     *
     * @return sys_log_Syslog
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_log_Syslog();
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

    }

    /**
     * return boolean
     */
    public function checkStatus() {
        
    }
}