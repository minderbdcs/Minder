<?php

class Minder_View_Helper_Options extends Zend_View_Helper_Abstract {
    /**
     * @var Minder2_Options
     */
    protected $_options;

    public function options() {
        return $this;
    }

    public function getCostCenterCaption() {
        $result = Minder2_Options::DEFAULT_COST_CENTER_CAPTION;

        try {
            $result = $this->_getOptions()->getCostCenterCaption();
        } catch (Exception $e) {
            trigger_error(__METHOD__ . ': ' . $e->getMessage(), E_USER_ERROR);
        }

        return $result;
    }

    public function getLocationFormatMap() {
        $result = array();

        try {
            $result = $this->_getOptions()->getLocationFormatMap();
        } catch (Exception $e) {
            trigger_error(__METHOD__ . ': ' . $e->getMessage(), E_USER_ERROR);
        }

        return $result;
    }

    protected function _getOptions() {
        if (empty($this->_options)) {
            $this->_options = new Minder2_Options();
        }

        return $this->_options;
    }
}