<?php

class Minder_View_Helper_SysScreenVarianceTabs extends Zend_View_Helper_Abstract {
    public function sysScreenVarianceTabs($variance){
        $xhtml = '';

        foreach ($this->_getTabs($variance) as $tab) {
            $xhtml .= $this->renderTab($tab);
        }

        return '<ul class="ui-tabs-nav">' . $xhtml . '</ul>';
    }

    protected function _getTabs($variance) {
        $result = array();

        foreach($variance as $screenResult) {
            $result = array_merge($result, $screenResult['searchResults']['tabs']);
        }

        usort($result, function($tabA, $tabB){
            return $tabA[$tabA['ORDER_BY_FIELD_NAME']] - $tabB[$tabB['ORDER_BY_FIELD_NAME']];
        });

        return $result;
    }

    protected function renderTab($tab) {
        $tabId = implode('-', array('TAB', $tab['SS_NAME'], $tab['SST_TAB_NAME']));
        return '<li><a href="#' . $tabId . '"><span>' . $tab['SST_TITLE'] . '</span></a></li>';
    }
}