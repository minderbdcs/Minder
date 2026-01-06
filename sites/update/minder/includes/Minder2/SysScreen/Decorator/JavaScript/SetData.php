<?php

class Minder2_SysScreen_Decorator_JavaScript_SetData extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();

        /**
         * @var Minder2_Model_SysScreen $sysScreen
         */
        $sysScreen = $this->getElement();

        $showBy = (isset($sysScreen->_SHOW_BY)) ? $sysScreen->_SHOW_BY : 5;
        $pageNo = (isset($sysScreen->_PAGE_NO)) ? $sysScreen->_PAGE_NO : 1;

        $totalRows = count($sysScreen);

        $totalPages = ceil($totalRows / $showBy);
        $normalizedPage = min($pageNo, $totalPages);

        $autoloadHelper->appendScript($this->_getModelVariableName() . '.setData(' . json_encode($sysScreen->getItems($showBy * ($normalizedPage - 1), $showBy)) . ');');

        return $content;
    }

    protected function _getDefaultVariablePrefix()
    {
        return '';
    }


}