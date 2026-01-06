<?php

class Minder_SysScreen_FileContentBuilder_HeaderInfo {
    protected $path = null;
    protected $_hasHeaders = false;

    function __construct($path, $hasHeaders)
    {
        $this->path = trim((string)$path, '.');
        $this->_hasHeaders = (strtoupper((string)$hasHeaders)=='T');
    }

    public function getPath() {
        return $this->path;
    }

    public function hasHeaders() {
        return $this->_hasHeaders;
    }
}