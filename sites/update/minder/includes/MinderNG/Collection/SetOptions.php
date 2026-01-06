<?php

namespace MinderNG\Collection;

class SetOptions implements SetOptionsInterface {
    private $_silent = false;
    private $_parse = false;
    private $_add = true;
    private $_merge = true;
    private $_remove = true;

    function __construct($silent = false, $parse = false, $add = true, $merge = true, $remove = true)
    {
        if (is_array($silent)) {
            $this->_fromArray($silent);
        } else {
            $this->_silent = (bool)$silent;
            $this->_parse = (bool)$parse;
            $this->_add = (bool)$add;
            $this->_merge = (bool)$merge;
            $this->_remove = (bool)$remove;
        }
    }

    private function _fromArray($options) {
        $this->_silent = isset($options[self::SILENT]) ? (bool)$options[self::SILENT] : false;
        $this->_parse = isset($options[self::PARSE]) ? (bool)$options[self::PARSE] : false;
        $this->_add = isset($options[self::ADD]) ? (bool)$options[self::ADD] : true;
        $this->_merge = isset($options[self::MERGE]) ? (bool)$options[self::MERGE] : true;
        $this->_remove = isset($options[self::REMOVE]) ? (bool)$options[self::REMOVE] : true;
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
        return $this->_add;
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
        return $this->_remove;
    }
}