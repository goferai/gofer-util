<?php

namespace GoferUtil;

use Logger;
Logger::configure($_SERVER['DOCUMENT_ROOT'] . '/../logging.xml');

class Log {

    const TYPE_MAIN = 'main';

    private $log;

    public function __construct($logName, $type = 'main') {
        $this->log = Logger::getLogger($logName);
    }
    
    public function debug($text, $removeLineBreaks = false) {
        if ($removeLineBreaks) {
            $text = str_replace(PHP_EOL, '', $text);
        }
        $this->log->debug($text);
    }
    
    public function error($text, $exception = null) {
        if ($exception != null) {
            $this->log->error($text, $exception);
        } else {
            $this->log->error($text);
        }
    }
    
}