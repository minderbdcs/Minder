<?php

class Minder_SysScreen_PartBuilder_InputMethodParser {
    public $inputMethod = '';
    public $transformations = array();

    protected $_rawInputMethod = null;

    protected function _setRawInputMethod($val) {
        $this->_rawInputMethod = strval($val);
        $this->inputMethod = '';
        $this->transformations = array();
        return $this;
    }

    protected function _getRawInputMethod() {
        if (is_null($this->_rawInputMethod))
            $this->_setRawInputMethod('');

        return $this->_rawInputMethod;
    }

    protected function _parse() {
        $this->transformations = array();
        $pattern = '/^(UPPERCASE)\((.*)\)$/i';
        $matches = array();
        $reminder = $this->_getRawInputMethod();

        while (preg_match($pattern, $reminder, $matches)) {
            $this->transformations[] = strtoupper($matches[1]);
            $reminder = $matches[2];
        }

        $this->inputMethod = $reminder;

        return $this;
    }

    public function parse($inputMethod) {
        return $this->_setRawInputMethod($inputMethod)->_parse();
    }

    public function parseSysScreenVarInputMethods($sysScreenVar) {
        $this->parse($sysScreenVar['SSV_INPUT_METHOD']);
        $sysScreenVar['SSV_INPUT_METHOD'] = $this->inputMethod;
        $sysScreenVar['TRANSFORMATIONS']  = $this->transformations;

        $this->parse($sysScreenVar['SSV_INPUT_METHOD_NEW']);
        $sysScreenVar['SSV_INPUT_METHOD_NEW'] = $this->inputMethod;
        $sysScreenVar['TRANSFORMATIONS_NEW']  = $this->transformations;

        return $sysScreenVar;
    }
}