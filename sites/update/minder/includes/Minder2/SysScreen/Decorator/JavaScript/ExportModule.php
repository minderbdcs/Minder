<?php

class Minder2_SysScreen_Decorator_JavaScript_ExportModule extends Minder2_SysScreen_Decorator_Abstract {
    public function render($content)
    {
        /**
         * @var Minder2_Model_Page $page
         */
        $page = $this->getElement();

        if (!$page instanceof Minder2_Model_Page)
            return $content;


        /**
         * @var Zend_View_Helper_HeadScript $script
         */
        $script = $this->getElement()->getView()->headScript(Zend_View_Helper_HeadScript::SCRIPT);

        $script->appendScript("
            function makeReport(format, SysScreen) {
                $('#' + SysScreen + '-SEARCH_RESULTS > div').data('minderElement').getModel().exportRows(format);
            };
        ");

        return $content;
    }

}