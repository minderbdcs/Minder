<?php

class Minder2_SysScreen_Decorator_pageTitle extends Minder2_SysScreen_Decorator_Abstract {
    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        /**
         * @var Minder2_Model_Page $page
         */
        $page = $this->_getModel();

        $view->headTitle($page->SM_TITLE);

        return $content;
    }

}