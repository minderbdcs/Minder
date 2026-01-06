<?php

class Minder_View_Helper_MinderVariant extends Zend_View_Helper_Abstract {
    public function variant($defaultValue) {
        $controls = $this->_getMinder()->defaultControlValues;
        return empty($controls['MINDER_VARIANT']) ? $defaultValue : $controls['MINDER_VARIANT'];
    }

    public function edition($defaultValue) {
        $controls = $this->_getMinder()->defaultControlValues;
        return empty($controls['WHM_VARIANT']) ? $defaultValue : $controls['WHM_VARIANT'];
    }

    function minderVariant($defaultValue = null)
    {
        return is_null($defaultValue) ? $this : $this->variant($defaultValue);
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}