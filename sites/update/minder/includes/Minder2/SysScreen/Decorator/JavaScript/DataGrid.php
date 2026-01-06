<?php

class Minder2_SysScreen_Decorator_JavaScript_DataGrid extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {

    const DATA_GRID_VAR_PREFIX        = 'dataSet_';
    const DATA_GRID_HEADER_VAR_PREFIX = 'header_';
    const DATA_GRID_FIELD_VAR_PREFIX  = 'field_';

    protected $_inputMethods = array();

    protected function _getDefaultVariablePrefix()
    {
        return self::DATA_GRID_VAR_PREFIX;
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/jquery.blockUI.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/container.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/staticText.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataGridElement.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataGridSelectRow.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/caption.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataGrid.js');
    }

    /**
     * @return string
     */
    protected function _loadDefaultTemplate() {
        return $this->_loadTemplateFile('jquery/data-grid.jqtmpl', $this->_getTemplateClass());
    }

    protected function _getFieldVariableName(&$fieldDescription) {
        if (!isset($fieldDescription['fieldVariable'])) {
            $fieldDescription['fieldVariable'] = self::DATA_GRID_FIELD_VAR_PREFIX . ((isset($fieldDescription['RECORD_ID'])) ? $fieldDescription['RECORD_ID'] : '');
        }

        return $fieldDescription['fieldVariable'];
    }

    protected function _getHeaderVariableName(&$fieldDescription) {
        if (!isset($fieldDescription['headerVariable'])) {
            $fieldDescription['headerVariable'] = self::DATA_GRID_HEADER_VAR_PREFIX . ((isset($fieldDescription['RECORD_ID'])) ? $fieldDescription['RECORD_ID'] : '');
        }

        return $fieldDescription['headerVariable'];
    }

    /**
     * @param $inputMethod
     * @return Minder_Page_FormBuilder_InputMethod
     */
    protected function _parseInputMethod($inputMethod) {
        $inputMethodParser = new Minder_Page_FormBuilder_InputMethodParcer();
        return $inputMethodParser->parse($inputMethod);
    }

    /**
     * @param $fieldDescription
     * @return Minder_Page_FormBuilder_InputMethod
     */
    protected function _getInputMethod($fieldDescription) {
        if (!isset($this->_inputMethods[$fieldDescription['RECORD_ID']])) {
            $this->_inputMethods[$fieldDescription['RECORD_ID']] = $this->_parseInputMethod($fieldDescription['SSV_INPUT_METHOD']);
        }

        return $this->_inputMethods[$fieldDescription['RECORD_ID']];
    }

    protected function _getFieldElement($fieldDescription) {
        $inputMethod = $this->_getInputMethod($fieldDescription);

        switch ($inputMethod->inputMethod) {
            case Minder_Page_FormBuilder_InputMethod::INPUT:
                return 'Minder_View_DataGridIn';
            default:
                return 'Minder_View_DataGridElement';
        }
    }

    protected function _getFieldName($fieldDescription) {
        return $fieldDescription['SSV_ALIAS'];
    }

    protected function _getFieldTemplate($fieldDescription) {
        $templateClass = self::TEMPLATE_PREFIX . $this->_getFieldVariableName($fieldDescription);
        $view = $this->_getView();

        $inputMethod = $this->_getInputMethod($fieldDescription);

        switch ($inputMethod->inputMethod) {
            case Minder_Page_FormBuilder_InputMethod::INPUT:
                $view->jQueryTemplate('jquery/in.jqtmpl', array('class' => $templateClass));
                break;
            default:
                $view->jQueryTemplate('jquery/static-text.jqtmpl', array('class' => $templateClass));
        }


        return '$(".' . $templateClass . '")';
    }

    protected function _getHeaderElement($fieldDescription) {
        return 'Minder_View_StaticText';
    }

    protected function _getHeaderTemplate($fieldDescription) {
        $view = $this->_getView();
        $view->jQueryTemplate('jquery/static-text.jqtmpl', array('class' => 'static-header-template'));
        return '$(".static-header-template")';
    }

    protected function _getTitle($fieldDescription) {
        return $fieldDescription['SSV_TITLE'];
    }

    protected function _getColorFieldName($fieldDescription) {
        return $fieldDescription['COLOR_FIELD_ALIAS'];
    }

    /**
     * @param $fieldDescription
     * @param Minder2_View_Helper_AutoloadScript $autoloadHelper
     * @return void
     */
    protected function _renderField(&$fieldDescription, $autoloadHelper) {
        $autoloadHelper->appendScript('var ' . $this->_getHeaderVariableName($fieldDescription) . ' = new ' . $this->_getHeaderElement($fieldDescription) . '(null, null, ' . $this->_getHeaderTemplate($fieldDescription) . ');');
        $autoloadHelper->appendScript($this->_getHeaderVariableName($fieldDescription) . '.setText("' . $this->_getTitle($fieldDescription) . '");');

        $fieldElement = $this->_getFieldElement($fieldDescription);
        $this->_getView()->includeRequiredFiles($fieldElement);
        $autoloadHelper->appendScript('var ' . $this->_getFieldVariableName($fieldDescription) . ' = new ' . $fieldElement . '("' . $this->_getFieldName($fieldDescription) . '", ' . $this->_getModelVariableName() . ', ' . $this->_getFieldTemplate($fieldDescription) . ', '. json_encode($fieldDescription) . ');');
        $autoloadHelper->appendScript($this->_getFieldVariableName($fieldDescription) . '.setColorFieldName("' . $this->_getColorFieldName($fieldDescription) . '");');
    }

    protected function _getDisableRowSelector() {
        return (boolean)$this->getOption('disableRowSelector');
    }

    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        $fields = $this->getOption('fields');
        if (empty($fields))
            return $content;

        $this->_includeRequiredLibraries();

        $dataGridVar = $this->_getVariableName();

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();

        $autoloadHelper->appendScript('var ' . $dataGridVar . ' = new Minder_View_DataGrid("' . $this->_getName() . '", ' . $this->_getModelVariableName() . ',' . $this->_getTemplate() . ');');

        if (!$this->_getDisableRowSelector()) {
            $rowSelectorVariable = $dataGridVar . '_rowSelector';


            $viewElementDecorator = new Minder2_SysScreen_Decorator_JavaScript_ViewElement();
            $viewElementDecorator->setElement($this->getElement());
            $viewElementDecorator->setOptions(array(
                                                  'variableName' => $rowSelectorVariable,
                                                  'javaScriptClass' => 'Minder_View_DataGridSelectRow',
                                                  'modelVariable' => $this->_getModelVariableName(),
                                                  'name' => 'selectRow',
                                                  'settings' => array(
                                                      'SSV_FIELD_WIDTH' => 20
                                                  )
                                              ));
            $viewElementDecorator->render($content);

            $selectAllVariable = $dataGridVar . '_selectAll';
            $viewElementDecorator->setOptions(array(
                                                  'variableName' => $selectAllVariable,
                                                  'javaScriptClass' => 'Minder_View_DataGridSelectRow',
                                                  'modelVariable' => $this->_getModelVariableName(),
                                                  'name' => 'selectRow'
                                              ));
            $viewElementDecorator->render($content);
            $autoloadHelper->appendScript($selectAllVariable . '.setRowId("all");');

            $selectionModeVariable = $dataGridVar . '_selectionMode';
            $viewElementDecorator->setOptions(array(
                'variableName' => $selectionModeVariable,
                'javaScriptClass' => 'Minder_View_SelectionModeSwitcher',
                'modelVariable' => $this->_getModelVariableName(),
                'name' => 'selectionMode'
            ));
            $viewElementDecorator->render($content);

            $headerContainerVariable = $dataGridVar . '_selectHeader';
            $viewElementDecorator->setOptions(array(
                'variableName' => $headerContainerVariable,
                'javaScriptClass' => 'Minder_View_Container',
                'modelVariable' => $this->_getModelVariableName(),
                'name' => $headerContainerVariable,
                'settings' => array(
                    'style' => 'white-space: nowrap;'
                )
            ));
            $viewElementDecorator->render($content);
            $autoloadHelper->appendScript($headerContainerVariable . '.addSubView(' . $selectAllVariable . ');');
            $autoloadHelper->appendScript($headerContainerVariable . '.addSubView(' . $selectionModeVariable . ');');

            $autoloadHelper->appendScript($dataGridVar . '.addField(' . $rowSelectorVariable . ', ' . $headerContainerVariable . ');');
        }

        foreach ($fields as &$fieldDescription) {
            $this->_renderField($fieldDescription, $autoloadHelper);
            $autoloadHelper->appendScript($dataGridVar . '.addField(' . $this->_getFieldVariableName($fieldDescription) . ', ' . $this->_getHeaderVariableName($fieldDescription) . ');');
        }

        return $content;
    }

}