<?php

class Minder_Form_Decorator_FormTabs extends Zend_Form_Decorator_Abstract {

    protected function _renderTabs() {
        $xhtml = '';

        /**
         * @var Minder_Form_DisplayGroup $pages
         */
        foreach ($this->getElement()->getElements() as $pages) {
            $xhtml .= '<li><a href="#' . $pages->getName() . '"><span><b>' . $pages->getLegend() . '</b></span></a></li>';
        }

        return '<ul class="ui-tabs-nav">' . $xhtml . '</ul>';
    }

    public function render($content)
    {
        return $this->_renderTabs() . $content;
    }

}