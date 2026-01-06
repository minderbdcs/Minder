<?php

namespace MinderNG\Events;

abstract class AbstractCommand implements CommandInterface {
    protected $_name ='';
    protected $_args = array();
    private $_executed = false;
    private $_response;

    /**
     * @return array
     */
    final public function getArgs()
    {
        return $this->_args;
    }

    /**
     * @return string
     */
    final public function getName()
    {
        return $this->_name;
    }


    final public function isExecuted()
    {
        return $this->_executed;
    }

    final public function setExecuted($executed = true)
    {
        $this->_executed = !!$executed;
    }

    final public function getResponse()
    {
        return $this->_response;
    }

    final public function setResponse($response)
    {
        $this->_response = $response;
        $this->_executed = !is_null($response);
    }
}