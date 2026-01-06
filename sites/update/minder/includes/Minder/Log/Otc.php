<?php

/**
 * @method info()
 */
class Minder_Log_Otc extends Zend_Log {
    protected function _setupDefaultWriter() {
        $otcLogDir = realpath(ROOT_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'otc');
        $otcLogFile = $otcLogDir . DIRECTORY_SEPARATOR . session_id() . '.log';
        $this->addWriter(new Zend_Log_Writer_Stream($otcLogFile));
    }

    public function log($message, $priority)
    {
        if (empty($this->_writers))
            $this->_setupDefaultWriter();

        parent::log($message, $priority);
    }

}