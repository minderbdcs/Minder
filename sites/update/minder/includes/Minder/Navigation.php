<?php

class Minder_Navigation extends Zend_Navigation {

    protected static $_defaultInstabce = null;

    public static function getNavigationInstance($section) {
        return new Minder_Navigation(new Zend_Config_Ini(APPLICATION_CONFIG_DIR . '/nav/navigation.ini', $section));
    }

    /**
     * @throws Minder_Exception
     * @param string $sectionName
     * @return array
     */
    public function buildMinderMenuArray() {
        if (!$this->hasPages()) return array();

        return $this->_buildSectionMenu($this);
    }

    /**
     * @param Zend_Navigation_Container $section
     * @return array
     */
    protected function _buildSectionMenu($section) {
        $result = array();

        /**
         * @var Zend_Navigation_Page $page
         */
        foreach ($section as $page) {
            if (!$page->isVisible(true)) continue;
            
            $label = ($page->isActive(true)) ? ('<' . $page->getLabel() . '>') : $page->getLabel();

            if ($page->hasPages())
                $result[$label] = $this->_buildSectionMenu($page);
            else
                $result[$label] = $page->getHref();

        }

        return $result;
    }

    /**
     * @throws Minder_Exception
     * @param string $sectionName
     * @return array
     */
    public function buildMinderTooltipArray($sectionName, $view) {
        $section = $this->findOneBy('section', $sectionName);

        if (is_null($section)) return array();

        if (!$section->hasPages()) return array();

        return $this->_buildSectionTooltips($section, $view);
    }

    /**
     * @param  $section
     * @param Zend_View $view
     * @return array
     */
    protected function _buildSectionTooltips($section, $view) {
        $result = array();

        /**
         * @var Zend_Navigation_Page $page
         */
        foreach ($section as $page) {
            if (!$page->isVisible(true)) continue;

            $label = ($page->isActive(true)) ? ('<' . $page->getLabel() . '>') : $page->getLabel();

            $properties = $page->getCustomProperties();

            $result[$view->escape($label)] = '';
            if (isset($properties['tooltip']))
                $result[$view->escape($label)] = $properties['tooltip'];

            if ($page->hasPages())
                $result += $this->_buildSectionTooltips($page, $view);
        }

        return $result;
    }
}