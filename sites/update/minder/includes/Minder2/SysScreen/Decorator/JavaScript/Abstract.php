<?php

abstract class Minder2_SysScreen_Decorator_JavaScript_Abstract extends Minder2_SysScreen_Decorator_Abstract {
    const MODEL_VAR_PREFIX            = 'model_';

    public function _getJavaScriptModel() {
        return $this->getOption('javaScriptModel');
    }

    protected function _getModelVariableName() {
        $modelVarName = $this->getOption('modelVariable');
        if (empty($modelVarName)) {
            $modelVarName = $this->_getModel()->getName();
            $this->setOption('modelVariable', $modelVarName);
        }

        return $modelVarName;
    }

    protected function _getName() {
        return $this->getOption('name');
    }

    protected function _getVariableName() {
        $variableName = $this->getOption('variableName');

        if (empty($variableName)) {
            $variableName = $this->_getDefaultVariablePrefix() . $this->_getName();
            $this->setOption('variableName', $variableName);
        }

        return $variableName;
    }

    protected function _includeRequiredLibraries() {
        /**
         * @var Zend_View_Helper_HeadScript $headScript
         */
        $headScript = $this->_getView()->headScript();

        $headScript->appendFile($this->_getView()->baseUrl() . '/scripts/ui/jquery-1.3.2.min.js')
                ->appendFile($this->_getView()->baseUrl() . '/scripts/jquery.inherit-1.3.2.M.js');
}

    abstract protected function _getDefaultVariablePrefix();
}