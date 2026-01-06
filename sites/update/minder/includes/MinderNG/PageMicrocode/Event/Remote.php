<?php

namespace MinderNG\PageMicrocode\Event;

class Remote extends AbstractEvent {
    const EVENT_NAME = 'remote';
    private $_exactName = '';
    private $_eventData;

    function __construct($exactName, $data)
    {
        $this->_name = static::EVENT_NAME;
        $this->_exactName = $exactName;
        $this->_eventData = $data;
        $this->_args = func_get_args();
    }

    /**
     * @return string
     */
    public function getExactName()
    {
        return $this->_exactName;
    }

    /**
     * @return mixed
     */
    public function getEventData()
    {
        return $this->_eventData;
    }


}