<?php

interface sys_ILog {

    /**
     * Logs an event
     *
     * @param integer $level Log level
     * @param string $source Event source. Defaults to 'System'
     * @param string $type Event type. Optional
     * @param string $description Event description. Optional
     * @param mixed $data Additional data. Optional
     * @param integer $userId User ID. Optional
     */
    public function log($level, $source, $type = null, $description = '', $data = null, $userId = null);

    /**
     * Returns the singleton instance
     *
     * @return sys_ILog
     */
    public static function getInstance();

    /**
     * Checks if the logger is operational
     *
     * @return boolean
     */
    public function checkStatus();
}