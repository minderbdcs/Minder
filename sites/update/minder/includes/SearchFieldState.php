<?php

/**
 * @property mixed $state
 * @property boolean $wasSaved
 */
class SearchFieldState {
    public $name = '';
    protected $_state = null;
    protected $_wasSaved   = false;

    function __construct($name, $state = null)
    {
        $this->name = $name;

        if (!is_null($state))
            $this->state = $state;
    }


    function __get($name) {
        switch ($name) {
            case 'state':
                if ($this->_wasSaved)
                    return $this->_state;
                return null;
            case 'wasSaved':
                return $this->_wasSaved;
        }
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'state':
                $this->_wasSaved   = true;
                $this->_state = $value;
                break;
        }
    }


}