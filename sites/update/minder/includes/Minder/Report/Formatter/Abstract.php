<?php

/**
 * @property string outputFormat
 */
abstract class Minder_Report_Formatter_Abstract implements Minder_Report_Formatter_Interface {
    protected $_outputFormat = '';

    public function __get($name) {
        $tmpName = '_' . $name;
        return $this->$tmpName;
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'outputFormat':
                $this->_outputFormat = strval($value);
                break;
            default:
                throw new Minder_Report_Formatter_Exception('Unknown property "' . $name . '"');
        }
    }

    public function __construct($outputFormat = '') {
        $this->__set('outputFormat', $outputFormat);
    }
}