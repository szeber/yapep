<?php

interface module_db_interface_Log {
    public function log($level, $source, $type = null, $description = '', $data = null, $userId = null);
    public function cleanupLogs($date);
}