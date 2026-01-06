<?php
  
class Minder_Mail_Transport_FileSystem extends Zend_Mail_Transport_Abstract
{
    protected $baseDir = '';
    
    public function __construct($baseDir = '../mail/') {
        $this->setBaseDir($baseDir);
    }
    
    public function setBaseDir($baseDir = '../mail/') {
        if (false === ($this->baseDir = realpath($baseDir)))
            throw new Minder_Mail_Transport_FileSystem_Exception('Dir "' . $baseDir . '" does not exists.');
    }
    
    public function getBaseDir() {
        return $this->baseDir;
    }
    
    protected function _sendMail() {
        $fileName = rtrim($this->baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date('YmdHis') . '.eml';
        $fp = fopen($fileName, 'w');
        fputs($fp, $this->header);
        fputs($fp, $this->body);
        fclose($fp);
    }
}

class Minder_Mail_Transport_FileSystem_Exception extends Minder_Exception {}