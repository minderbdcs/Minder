<?php

class Minder_View_Helper_TransferOrderStatusSelect extends Zend_View_Helper_Abstract {
    /**
     * @var Minder2_Options
     */
    protected $_minderOptions;

    public function transferOrderStatusSelect($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n") {
        $options = is_null($options) ? $this->_buildOptions() : $options;

        return $this->view->formSelect($name, $value, $attribs, $options, $listsep);
    }

    protected function _buildOptions() {
        $result = array();
        foreach ($this->_getMinderOptions()->getTransferOrderStatuses() as $option) {
            $result[$option->CODE] = $option->DESCRIPTION;
        }

        return $result;
    }

    protected function _getMinderOptions() {
        if (empty($this->_minderOptions)) {
            $this->_minderOptions = new Minder2_Options();
        }

        return $this->_minderOptions;
    }
}