<?php

namespace MinderNG\Events;

abstract class AbstractEvent implements EventInterface {
    protected $_args = '';
    protected $_name = array();

    private $_defaultPrevented = false;

    /**
     * @return void
     */
    public function preventDefault()
    {
        $this->_defaultPrevented = true;
    }

    /**
     * @return boolean
     */
    public function defaultPrevented()
    {
        return $this->_defaultPrevented;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->_args;
    }
}

