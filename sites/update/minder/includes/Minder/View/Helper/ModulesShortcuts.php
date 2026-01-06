<?php

class Minder_View_Helper_ModulesShortcuts extends Zend_View_Helper_Abstract {

    protected function _buildTopMenu($topMenu) {
        if (empty($topMenu))
            return '';

        $xhtml = '';

        foreach ($topMenu as $name => $url) {
            $xhtml .= '<li><a href="' . $this->view->escape($url) . '">' . $this->view->escape($name) . '</a></li>' . PHP_EOL;
        }

        return $xhtml;
    }

    public function modulesShortcuts($topMenu = array()) {
        $topMenu = (is_array($topMenu)) ? $topMenu : array();

        return '<ul id="modules">' . PHP_EOL . $this->_buildTopMenu($topMenu) . PHP_EOL . '</ul>';
    }
}