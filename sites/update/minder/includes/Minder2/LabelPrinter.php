<?php

class Minder2_LabelPrinter {

    protected $_loaders = array();

    protected function _formatClassName($name) {
        return Minder_Page::formatSysScreenClassName($name);
    }

    /**
     * @param $labelName
     * @return Zend_Loader_PluginLoader
     */
    protected function _getLoader($labelName) {
        $labelClassName = $this->_formatClassName($labelName);

        if (!isset($this->_loaders[$labelClassName])) {
            $this->_loaders[$labelClassName] = new Zend_Loader_PluginLoader(array('Minder2_LabelPrinter_' . $labelClassName => 'Minder2/LabelPrinter/' . $labelClassName));
        }

        return $this->_loaders[$labelClassName];
    }

    /**
     * @param $sysScreen
     * @param $labelName
     * @return Minder2_LabelPrinter_Interface
     */
    protected function _getConcretePrinter($sysScreen, $labelName) {
        $loader = $this->_getLoader($labelName);

        $class = $loader->load($this->_formatClassName($sysScreen), false);

        if (false === $class) {
            $class = $loader->load('Default');
        }

        return new $class($sysScreen, $labelName);
    }

    public function printLabels($sysScreen, $labelName, $paramsMap, $data, $printer) {
        return $this->_getConcretePrinter($sysScreen, $labelName)->printLabel($paramsMap, $data, $printer);
    }
}