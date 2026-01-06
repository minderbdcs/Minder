<?php

class Minder_Prompt_Manager {
    const ORDER_CHECK = 'ORDERCHECK';
    const REPACK = 'REPACKSSCC';

    /**
     * @var Minder2_Options
     */
    protected $_optionsManager;

    function __construct(Minder2_Options $optionsManager)
    {
        $this->_setOptionsManager($optionsManager);
    }

    public function getOrderCheckPrompts() {
        return $this->_getPrompts(static::ORDER_CHECK);
    }

    public function getRePackSsccPrompts() {
        return $this->_getPrompts(static::REPACK);
    }

    protected function _getPrompts($type) {
        $result = array();
        foreach ($this->_getOptionsManager()->getPrompts($type) as $option) {
            $result[] = $this->_fromOption($option);
        }

        return $result;
    }

    protected function _fromOption(Minder2_Model_Options $option) {
        list($type, $code) = explode('|',$option->CODE);

        return new Minder_Prompt_Model($code, $option->DESCRIPTION, $type);
    }

    /**
     * @return Minder2_Options
     */
    protected function _getOptionsManager()
    {
        return $this->_optionsManager;
    }

    /**
     * @param Minder2_Options $optionsManager
     * @return $this
     */
    protected function _setOptionsManager($optionsManager)
    {
        $this->_optionsManager = $optionsManager;
        return $this;
    }
}