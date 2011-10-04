<?php

class sys_log_Db implements sys_ILog {

    /**
     *
     * @var sys_log_Db
     */
    protected static $INSTANCE;

    /**
     *
     * @var module_db_interface_Log
     */
    protected $db;

    protected function __construct() {
        $this->db = getPersistClass('Log');
        if(rand(0, 100) == 1) {
            $config = sys_ApplicationConfiguration::getInstance();
            $this->db->cleanupLogs(date('Y-m-d H:i:s', time()-((int)$config->getOption('dbLogLifetime'))));
        }
    }

    /**
     *
     * @return boolean
     */
    public function checkStatus() {
        return true;
    }

    /**
     *
     * @return sys_log_Db
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_log_Db();
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
        $this->db->log($level, $source, $type, $description, $data, $userId);
    }
}