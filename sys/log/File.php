<?php

class sys_log_File implements sys_ILog {

    const ROTATED_FILES = 4;

    /**
     *
     * @var sys_log_File
     */
    protected static $INSTANCE;

    /**
     *
     * @var array
     */
    protected $handles = array();

    /**
     *
     * @var array
     */
    protected $disabledHandles = array();

    /**
     *
     * @var string
     */
    protected $logDir;

    /**
     *
     * @var integer
     */
    protected $sizeLimit;

    /**
     *
     */
    protected function __construct() {
        $this->handles = array();
        $this->disabledHandles = array();
        $config = sys_ApplicationConfiguration::getInstance();
        $this->logDir = $config->getPath('logDir');
        $this->sizeLimit = $config->getOption('fileLogSizeLimit');
        if(!file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }
    }

    /**
     * Closes open handles
     */
    public function __destruct() {
        foreach($this->handles as $fh) {
            fclose($fh);
        }
    }

    /**
     *
     * @return boolean
     */
    public function checkStatus() {
        if (!file_exists($this->logDir) || !is_dir($this->logDir) || !is_writable($this->logDir)) {
            return false;
        }
        return $this->addHandle('system');
    }

    /**
     *
     * @return sys_log_File
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_log_File();
        }
        return self::$INSTANCE;
    }

    /**
     * Opens a new file handle
     *
     * @param string $source
     * @return boolean
     */
    protected function addHandle($source) {
        if (isset($this->disabledHandles[$source])) {
            return false;
        }
        $fileName = str_replace(array('.', '/', '?', '*'), '_', $source);
        if (file_exists($this->logDir.$fileName) && $this->sizeLimit > 0) {
            if (filesize($this->logDir.$fileName) > $this->sizeLimit) {
                $this->rotateLogs($fileName);
            }
        }
        $fh = @fopen($this->logDir.$fileName, 'ab');
        if (!$fh || !is_resource($fh)) {
            $this->disabledHandles[$source] = 1;
            sys_Log::log(sys_Log::LEVEL_ERROR, 'log', 'file', 'Error opening file: '.$fileName);
            return false;
        }
        $this->handles[$source] = $fh;
        return true;
    }

    protected function rotateLogs($logFile) {
        $baseFile = $this->logDir.$logFile;
        $counter = self::ROTATED_FILES;
        if (file_exists($baseFile.'.'.$counter)) {
            unlink($baseFile.'.'.$counter);
        }
        while($counter > 1) {
            $counter--;
            if (file_exists($baseFile.'.'.$counter)) {
                rename($baseFile.'.'.$counter, $baseFile.'.'.($counter+1));
            }
        }
        if (file_exists($baseFile)) {
            rename($baseFile, $baseFile.'.1');
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
    public function log($level, $source, $type = null, $description = '', $data = null, $userId = null) {
        if (!isset($this->handles[$source])) {
            if (!$this->addHandle($source)) {
                return;
            }
        }
        $line = date('Y-m-d H:i:s')."\t".str_pad($source, 20)."\t".str_pad($type, 60)."\t".str_replace("\n", '\n', $description)."\n";
        fwrite($this->handles[$source], $line);
    }
}