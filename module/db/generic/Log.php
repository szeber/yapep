<?php

class module_db_generic_Log extends module_db_DbModule implements module_db_interface_Log {
    public function cleanupLogs($date) {
        $this->conn->delete('cms_log_data', 'event_date<'.$this->conn->quote($date));
    }

    public function log($level, $source, $type = null, $description = '', $data = null, $userId = null) {
        $insert = array(
            'event_level'           => (int)$level,
            'event_source'          => $source,
            'event_type'            => $type,
            'event_description'     => $description,
            'user_id'               => $userId,
        );
        if (!is_null($data)) {
            $insert['event_data'] = var_export($data, true);
        }
        $this->conn->quoteData('cms_log_data', $insert);
        $insert['event_date'] = 'NOW()';
        $this->conn->insert('cms_log_data', $insert, false);
    }
}