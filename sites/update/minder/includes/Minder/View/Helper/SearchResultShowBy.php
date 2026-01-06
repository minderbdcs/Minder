<?php

class Minder_View_Helper_SearchResultShowBy extends Zend_View_Helper_Abstract {
    const DEFAULT_VIEW_BY_MAX = 1000;

    public function searchResultShowBy($name, $value, $viewByMax = null, $htmlAttributes = array()) {
        $viewByMax = empty($viewByMax) ? static::DEFAULT_VIEW_BY_MAX : $viewByMax;
        $viewByMax = max($value, $viewByMax);

        return $this->view->formSelect($name, $value, $htmlAttributes, $this->_buildValuesRange($viewByMax));
    }

    protected function _buildValuesRange($viewByMax) {
        $initialRange = array(5, 10, 15, 20, 30, 40);
        $currentValue = current($initialRange);
        $result = array();

        while ($currentValue <= $viewByMax) {
            $result[$currentValue] = $currentValue;
            $initialRange[] = $currentValue * 10;
            $currentValue = next($initialRange);
        }

        return empty($result) ? array($currentValue => $currentValue) : $result;
    }
}