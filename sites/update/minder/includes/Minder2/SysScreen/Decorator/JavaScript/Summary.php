<?php

class Minder2_SysScreen_Decorator_JavaScript_Summary extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {
    protected function _getDefaultVariablePrefix()
    {
        return 'summaryPanel';
    }

    protected function _getElements() {
        return $this->getOption('elements');
    }

    /**
     * @return string - template selector
     */
    protected function _loadDefaultTemplate()
    {
        /**
         * @var Minder2_View_Helper_JQueryTemplate $jQueryTemplate
         */
        $jQueryTemplate = $this->_getView()->jQueryTemplate();

        $templateClass = $this->_getTemplateClass();

        $template = '<table class="with-border" style="width: 100%"><tr>';

        foreach ($this->_getElements() as $element) {
            $template .= '<td><span class="minder-element ' . $element['name'] . '">' . $element['name'] . ':Placement</span></td>';
        }
        $template .= '</tr></table>';

        $jQueryTemplate->append($jQueryTemplate->createData('text/x-jquery-tmpl', array('class' => $templateClass), $template));

        return '$(".' . $templateClass . '")';
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

        $addElementDecorator = new Minder2_SysScreen_Decorator_JavaScript_AddSubView();
        $addElementDecorator->setElement($this->getElement());

        foreach ($this->_getElements() as $element) {
            $addElementDecorator->setOptions(array(
                                                 'variableName' => $this->_getVariableName(),
                                                 'subViewVariable' => $element['variableName']
                                             ));
            $addElementDecorator->render($content);
        }

        return $content;
    }
}