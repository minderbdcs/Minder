<?php

class Minder2_SysScreen_Decorator_JavaScript_SearchTabPage extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {
    protected function _getDefaultVariablePrefix()
    {
        return 'searchTabPage';
    }

    protected function _formatSearchFieldName($fieldDescription) {
        return "SEARCH_FIELD_" . $fieldDescription['RECORD_ID'];
    }

    protected function _formatSearchFieldVariable($fieldDescription) {
        return 'searchField' . $fieldDescription['RECORD_ID'];
    }

    protected function _getSerachFieldWidth($fieldDescription) {
        switch ($fieldDescription['SSV_INPUT_METHOD']) {
            default:
                return 1;
        }
    }

    protected function _getSearchFieldInputMethod($inputMethod) {
        $inputMethodParces = new Minder_Page_FormBuilder_InputMethodParcer();
        return $inputMethodParces->parse($inputMethod);
    }

    protected function _getButtons() {
        return $this->getOption('buttons');
    }

    protected function _hasButtons() {
        $buttons = $this->_getButtons();
        return !empty($buttons);
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

        $template = "
            <div id='tab_page_" . $this->_getName() . "'>
                <table>
                    <body>
                        <tr>
        ";

        $addElementDecorator = new Minder2_SysScreen_Decorator_JavaScript_AddSubView();
        $addElementDecorator->setElement($this->getElement());
        $usedCells = 0;
        foreach ($this->_getSearchFields() as $fieldDescription) {

            $tmpInputMethod = $this->_getSearchFieldInputMethod($fieldDescription['SSV_INPUT_METHOD']);
            if ($tmpInputMethod->inputMethod == Minder_Page_FormBuilder_InputMethod::NONE || $tmpInputMethod->inputMethod == Minder_Page_FormBuilder_InputMethod::READ_ONLY) {
                continue;
            }

            $fieldWidth = $this->_getSerachFieldWidth($fieldDescription);

            if ($usedCells + $fieldWidth > 2) {
                for (;$usedCells < 3;$usedCells++)
                    $template .= "<th>&nbsp;</th><td>&nbsp;</td>";
                $template .= "</tr><tr>";
                $usedCells = 0;
            }

            $fieldName    = $this->_formatSearchFieldName($fieldDescription);
            $template .= "<th>" . $fieldDescription['SSV_TITLE'] . "</th>";
            $template .= "<td><span class='minder-element " . $fieldName . "'>" . $fieldName . ":Placement</span></td>";

            $usedCells += $fieldWidth;

            if ($usedCells > 2) {
                $usedCells = 0;
                $template .= "</tr><tr>";
            }
        }

        for (;$usedCells < 3;$usedCells++)
            $template .= "<th>&nbsp;</th><td>&nbsp;</td>";

        $template .= "
                        </tr>
                        <tr><td colspan='6'>&nbsp;</td></tr>
        ";

        if ($this->_hasButtons()) {
            $template .= "
                            <tr><td colspan='6'>
                                <span class='minder-element BUTTON_PANEL-" . $this->_getName() . "'>BUTTON_PANEL-" . $this->_getName() . ":Placement</span>
                            </td></tr>
            ";
        }

        $template .= "
                    <tbody>
                </table>
            </div>
        ";

        $jQueryTemplate->append($jQueryTemplate->createData('text/x-jquery-tmpl', array('class' => $templateClass), $template));

        return '$(".' . $templateClass . '")';
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/container.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/tabPage.js');
    }

    protected function _getElements() {
        return $this->getOption('elements');
    }

    protected function _getSearchFields() {
        $searchFields = $this->getOption('searchFields');
        $searchFields = is_array($searchFields) ? $searchFields : array();

        return $searchFields;
    }

    protected function _renderFieldElement($fieldDescription, $content) {
        switch ($fieldDescription['SSV_INPUT_METHOD']) {
            case 'IN':
                $javascriptClass = 'Minder_View_In';
                break;
            default:
                $javascriptClass = 'Minder_View_Caption';
        }

        $fieldElement = new Minder2_SysScreen_Decorator_JavaScript_ViewElement();
        $fieldElement->setElement($this->getElement());
        $fieldElement->setOptions(array(
                                       'variableName' => $this->_formatSearchFieldVariable($fieldDescription),
                                       'name' => $this->_formatSearchFieldName($fieldDescription),
                                       'javaScriptClass' => $javascriptClass,
                                       'modelVariable' => $this->_getModelVariableName()
                                  ));
        return $fieldElement->render($content);
    }

    protected function _renderButtonPanel($content) {
        if (!$this->_hasButtons())
            return $content;

        $buttonPanelDecorator = new Minder2_SysScreen_Decorator_JavaScript_ButtonPannel();
        $buttonPanelDecorator->setElement($this->getElement());
        $buttonPanelVariableName = 'searchButtonPanel' . $this->_getName();
        $buttonPanelDecorator->setOptions(array('buttons' => $this->_getButtons(), 'variableName' => $buttonPanelVariableName, 'name' => 'BUTTON_PANEL-' . $this->_getName(), 'modelVariable' => $this->_getModelVariableName()));

        $content = $buttonPanelDecorator->render($content);

        $addElementDecorator = new Minder2_SysScreen_Decorator_JavaScript_AddSubView();
        $addElementDecorator->setElement($this->getElement());

        $addElementDecorator->setOptions(array(
                                             'variableName' => $this->_getVariableName(),
                                             'subViewVariable' => $buttonPanelVariableName
                                         ));
        return $addElementDecorator->render($content);
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

        foreach ($this->_getSearchFields() as $fieldDescription) {
            $content = $this->_renderFieldElement($fieldDescription, $content);

            $addElementDecorator->setOptions(array(
                                                 'variableName' => $this->_getVariableName(),
                                                 'subViewVariable' => $this->_formatSearchFieldVariable($fieldDescription)
                                             ));
            $content = $addElementDecorator->render($content);
        }

        return $this->_renderButtonPanel($content);
    }
}