<?php

class sys_log_EmailAlert implements sys_ILog {
    
    /**
     *
     * @var sys_log_EmailAlert
     */
    protected static $INSTANCE;

    /**
     *
     * @var string
     */
    protected $address;

    /**
     *
     */
    protected function __construct() {
        $this->address = sys_ApplicationConfiguration::getInstance()->getOption('emailAlertAddress');
    }

    /**
     *
     * @return sys_log_EmailAlert
     */
    public static function getInstance() {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new sys_log_EmailAlert();
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
        if ($level > sys_Log::LEVEL_ERROR) {
            return;
        }
        switch($source) {
            case 'Database':
                $message = "Database error\n\nDATE\n".date('Y-m-d H:i:s')."\n\nQUERY:\n".$data['query']
                    ."\n\nERROR:\n".$data['error']."\n\nPHP_SELF: ".$_SERVER['PHP_SELF']."\n\nTRACE:\n"
                    .print_r($data['trace'], true)."\n\nGET\n".  print_r($data['GET'], true)
                    ."\n\nPOST:\n".print_r($data['POST'], true)."\nSESSION\n\n".print_r($_SESSION, true);
                $subject = 'DB error - '.$_SERVER['HTTP_HOST'];
                break;
            case 'Page':
                $message = "Page generation error\n\nDATE\n".date('Y-m-d H:i:s')."\n\nERROR:\n".$description
                    ."\n\nPHP_SELF: ".$_SERVER['PHP_SELF']."\n\nTRACE:\n"
                    .print_r($data['trace'], true)."\n\nGET\n".  print_r($data['GET'], true)
                    ."\n\nPOST:\n".print_r($data['POST'], true)."\nSESSION\n\n".print_r($_SESSION, true);;
                $subject = 'Site error - '.$_SERVER['HTTP_HOST'];
                break;
            default:
                return;
                break;
        }
        $this->sendMail($subject, $message);
    }

    /**
     * Sends the email alert
     *
     * @param string $subject
     * @param string $message
     */
    protected function sendMail($subject, $message) {
        $mailer = sys_LibFactory::getMailer();
        $mailer->From = $this->address;
        $mailer->Sender = $this->address;
        $mailer->Subject = $subject;
        $mailer->Body = $message;
        $mailer->AddAddress($this->address);
        $mailer->Send();
        
    }

    /**
     *
     * @return boolean
     */
    public function checkStatus() {
        return strlen(trim($this->address)) > 0;
    }
}