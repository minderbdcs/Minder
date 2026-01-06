<?php

interface Minder2_Model_Interface {
    /**
     * @abstract
     * @return string
     */
    function getName();

    /**
     * @abstract
     * @return array
     */
    function getFields();

    /**
     * @abstract
     * @return mixed
     */
    function getState();

    /**
     * @abstract
     * @param mixed $state
     * @return Minder2_Model_Interface 
     */
    function setState($state);

    /**
     * @abstract
     * @return string
     */
    function getStateId();

    /**
     * @abstract
     * @return int
     */
    function getOrder();
}