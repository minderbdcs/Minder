<?php

class Minder2_SysScreen_Decorator_JavaScript_PageMessages extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return '';
    }

    protected function _includeRequiredLibraries()
    {
        /**
         * @var Zend_View_Helper_HeadScript $headScript
         */
        $headScript = $this->_getView()->headScript();

        $headScript->appendFile($this->_getView()->baseUrl() . '/scripts/warehouse.js');
    }


    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        $this->_includeRequiredLibraries();

        $this->_includeRequiredLibraries();

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();

        /**
         * @var Minder2_Model_Page $page
         */
        $page = $this->_getModel();

        if ($page->hasMessages())
            $autoloadHelper->appendScript('showMessages(' . json_encode($page->getMessages()) . ');');

        if ($page->hasWarnings())
            $autoloadHelper->appendScript('showWarnings(' . json_encode($page->getWarnings()) . ');');

        if ($page->hasErrors())
            $autoloadHelper->appendScript('showErrors(' . json_encode($page->getErrors()) . ');');

        $page->clearAllMessages();

        return $content;
    }


}