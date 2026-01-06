<?php

class Minder_Log extends Zend_Log {
    public function startDetailedLog($message = null, $priority = Zend_Log::INFO) {
        $newDetailedLog = new Minder_Log_Detailed($this, $priority);

        if (!is_null($message)) {
            $this->log($message, $priority);
        }

        return $newDetailedLog;
    }
}