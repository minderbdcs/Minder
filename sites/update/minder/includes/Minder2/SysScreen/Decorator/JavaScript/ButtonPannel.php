<?php

class Minder2_SysScreen_Decorator_JavaScript_ButtonPannel extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {
    protected function _getDefaultVariablePrefix()
    {
        return 'buttonPannel_';
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/container.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/button.js');
    }

    /**
     * @return string - template selector
     */
    protected function _loadDefaultTemplate()
    {
        return $this->_loadTemplateFile('jquery/buttons-pannel.jqtmpl', $this->_getTemplateClass());
    }

    private function _getButtonTemplate($buttonId)
    {
        return $this->_loadTemplateFile('jquery/button.jqtmpl', 'button_' . $buttonId);
    }

    public function renderButtons() {
        $view = $this->_getView();
        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();

        foreach ($this->_getButtons() as $button) {
            $varName = 'button_' . $button['RECORD_ID'];
            $onClickMethodName = 'onButton' . $button['RECORD_ID'] . 'Click';
            $autoloadHelper->appendScript('function ' . $onClickMethodName . '(evt){' . $button['SSB_ACTION'] . '};');
            $autoloadHelper->appendScript('var ' . $varName . ' = new Minder_View_Button("button' . $button['RECORD_ID'] . '", ' . $this->_getModelVariableName() . ',' . $this->_getButtonTemplate($button['RECORD_ID']) . ', ' . json_encode($button) . ');');
            $autoloadHelper->appendScript($varName . '.click(' . $onClickMethodName . ')');
            $autoloadHelper->appendScript($this->_getVariableName() . '.addSubView(' . $varName . ');');
        }
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
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new Minder_View_Container("' . $this->_getName() . '", ' . $this->_getModelVariableName() . ',' . $this->_getTemplate() . ', ' . json_encode($this->getOption('settings')) . ');');

        $this->renderButtons();

        return $content;

    }

    private function _getButtons()
    {
        $buttons = $this->getOption('buttons');
        return !is_array($buttons) ? array() : $buttons;
    }
}
