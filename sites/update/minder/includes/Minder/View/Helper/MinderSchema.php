<?php

class Minder_View_Helper_MinderSchema extends Zend_View_Helper_Abstract {
    const DEFAULT_SCHEMA_NAME = 'default';

    public function minderSchema() {

        $this->_headLink()->appendStylesheet($this->_baseUrl() . 'scripts/ui/themes/flora/flora.all.css');
        $this->_headLink()->appendStylesheet($this->_baseUrl() . 'style/themes/' . static::DEFAULT_SCHEMA_NAME . '.css');

        $schemaName = $this->_getSchemaName();

        if ($schemaName !== static::DEFAULT_SCHEMA_NAME) {
            $this->_headLink()->appendStylesheet($this->_baseUrl() . 'style/themes/' . $schemaName . '.css');
        }
    }

    protected function _getSchemaName() {
        $controlValues = Minder::getInstance()->getValuesFromControl();
        return empty($controlValues['VISUAL_SCHEMA_NAME']) ? static::DEFAULT_SCHEMA_NAME : strtolower($controlValues['VISUAL_SCHEMA_NAME']);
    }

    /**
     * @return Zend_View_Helper_HeadLink
     */
    protected function _headLink() {
        return $this->view->headLink();
    }

    protected function _baseUrl() {
        return $this->view->baseUrl;
    }
}