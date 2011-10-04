<?php

class sys_log_CliConsole implements sys_ILog {

    const ROTATED_FILES = 4;

    /**
     *
     * @var sys_log_File
     */
    protected static $INSTANCE;

    /**
     *
     */
    protected function __construct() {

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
     * @return sys_log_File
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_log_CliConsole();
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
        echo $this->getLogLevelName($level) . "\t" . str_pad($source, 20)."\t".str_pad($type, 60)."\t".str_replace("\n", '\n', $description)."\n";
    }

    protected function getLogLevelName($level) {
        switch($level) {
            case sys_Log::LEVEL_ERROR:
                $levelName = 'ERROR';
                break;
            case sys_Log::LEVEL_WARNING:
                $levelName = 'WARNING';
                break;
            case sys_Log::LEVEL_INFO:
                $levelName = 'INFO';
                break;
            case sys_Log::LEVEL_DEBUG:
                $levelName = 'DEBUG';
                break;
            default:
                $levelName = 'NONE';
                break;
        }
        return str_pad($levelName, 8);
    }
}