<?php

namespace MinderNG\Collection;

class AddOptions implements SetOptionsInterface {
    private $_silent = false;
    private $_parse = false;
    private $_merge = false;

    function __construct($silent = false, $parse = false, $merge = false)
    {
        if (is_array($silent)) {
            $this->_fromArray($silent);
        } else {
            $this->_silent = (bool)$silent;
            $this->_parse = (bool)$parse;
            $this->_merge = (bool)$merge;
        }
    }

    private function _fromArray($options) {
        $this->_silent = isset($options[self::SILENT]) ? (bool)$options[self::SILENT] : false;
        $this->_parse = isset($options[self::PARSE]) ? (bool)$options[self::PARSE] : false;
        $this->_merge = isset($options[self::MERGE]) ? (bool)$options[self::MERGE] : true;
    }

    /**
     * @return bool
     */
    public function silent()
    {
        return $this->_silent;
    }

    /**
     * @return bool
     */
    public function parse()
    {
        return $this->_parse;
    }

    /**
     * @return bool
     */
    public function add()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function merge()
    {
        return $this->_merge;
    }

    /**
     * @return bool
     */
    public function remove()
    {
        return false;
    }
}