<?php

class Minder2_SysScreen_Decorator_JavaScript_Tab extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {
    const TAB_VAR_PREFIX = 'tab_';
    protected function _getDefaultVariablePrefix()
    {
        return self::TAB_VAR_PREFIX;
    }

    protected function _loadDefaultTemplate()
    {
        return $this->_loadTemplateFile('jquery/tab.jqtmpl', $this->_getTemplateClass());
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/ui/ui.base.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/ui/ui.tabs.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/tab.js');

    }


    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        $this->_includeRequiredLibraries();

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new Minder_View_Tab("' . $this->_getName() . '", ' . $this->_getModelVariableName() . ',' . $this->_getTemplate() . ');');

        $pages = $this->getOption('pages');

        if (empty($pages))
            return $content;

        foreach ($pages as $pageVariableName) {
            $autoloadHelper->appendScript($this->_getVariableName() . '.addPage(' . $pageVariableName . ');');
        }

        return $content;
    }


}