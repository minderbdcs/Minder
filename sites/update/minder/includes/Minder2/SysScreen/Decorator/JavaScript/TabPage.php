<?php

class Minder2_SysScreen_Decorator_JavaScript_TabPage extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {
    const TAB_PAGE_VAR_PREFIX = 'tabPage_';

    protected function _loadDefaultTemplate()
    {
        /**
         * @var Minder2_View_Helper_JQueryTemplate $jQueryTemplate
         */
        $jQueryTemplate = $this->_getView()->jQueryTemplate();

        $templateClass = $this->_getTemplateClass();

        $template = '<div>';

        foreach ($this->_getElements() as $element) {
            $template .= '<span class="minder-element ' . $element['name'] . '">' . $element['name'] . ':Placement</span>';
        }
        $template .= '</div>';

        $jQueryTemplate->append($jQueryTemplate->createData('text/x-jquery-tmpl', array('class' => $templateClass), $template));

        return '$(".' . $templateClass . '")';
    }


    protected function _getDefaultVariablePrefix()
    {
        return self::TAB_PAGE_VAR_PREFIX;
    }

    protected function _getElements() {
        return $this->getOption('elements');
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/container.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/tabPage.js');
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
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new Minder_View_TabPage("' . $this->_getName() . '", ' . $this->_getModelVariableName() . ',' . $this->_getTemplate() . ', ' . json_encode($this->getOption('settings')) . ');');

        $addElementDecorator = new Minder2_SysScreen_Decorator_JavaScript_AddSubView();
        $addElementDecorator->setElement($this->getElement());

        foreach ($this->_getElements() as $pageElement) {
            $addElementDecorator->setOptions(array(
                                                 'variableName' => $this->_getVariableName(),
                                                 'subViewVariable' => $pageElement['variableName']
                                             ));
            $addElementDecorator->render($content);
        }

        return $content;
    }
}