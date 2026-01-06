<?php

class Minder2_SysScreen_Decorator_JavaScript_ViewElement extends Minder2_SysScreen_Decorator_JavaScript_TemplatedElement {
    const ELEMENT_VAR_PREFIX = 'element_';

    protected function _getDefaultVariablePrefix()
    {
        return self::ELEMENT_VAR_PREFIX;
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();

        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/container.js');
        switch ($this->_getElementJavaScriptClass()) {
            case 'Minder_View_StaticText':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/staticText.js');
                break;
            case 'Minder_View_Caption':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/caption.js');
                break;
            case 'Minder_View_DataGridSelectRow':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataGridSelectRow.js');
                break;
            case 'Minder_View_Multy':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/multy.js');
                break;
            case 'Minder_View_Chart':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/chart.js');
                break;
            case 'Minder_View_In':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/in.js');
                break;
            case 'Minder_View_CheckBox':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/checkBox.js');
                break;
            case 'Minder_View_PageSelector':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/multy.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/pageSelector.js');
                break;
            case 'Minder_View_DataField':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataField.js');
                break;
            case 'Minder_View_PaginatorInformer':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataField.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/paginatorInformer.js');
                break;
            case 'MinderView_RowSelectionInformer':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataField.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/rowSelectionInformer.js');
                break;
            case 'Minder_View_SelectionModeSwitcher':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/checkBox.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/selectionModeSwitcher.js');
                break;
            case 'Minder_View_SelectAll':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/checkBox.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/selectAll.js');
                break;
        }
    }


    /**
     * @return string - template selector
     */
    protected function _loadDefaultTemplate()
    {
        switch ($this->_getElementJavaScriptClass()) {
            case 'Minder_View_PaginatorInformer':
                $templateFile = 'jquery/paginator-informer.jqtmpl';
                break;
            case 'MinderView_RowSelectionInformer':
            case 'Minder_View_DataField':
                $templateFile = 'jquery/field-with-caption.jqtmpl';
                break;
            case 'Minder_View_Caption':
            case 'Minder_View_StaticText':
                $templateFile = 'jquery/static-text.jqtmpl';
                break;
            case 'Minder_View_DataGridSelectRow':
                $templateFile = 'jquery/row-selector.jqtmpl';
                break;
            case 'Minder_View_Multy':
            case 'Minder_View_PageSelector':
                $templateFile = 'jquery/drop-down.jqtmpl';
                break;
            case 'Minder_View_Chart':
                $templateFile = 'jquery/chart.jqtmpl';
                break;
            case 'Minder_View_In':
                $templateFile = 'jquery/in.jqtmpl';
                break;
            case 'Minder_View_SelectAll':
            case 'Minder_View_CheckBox':
                $templateFile = 'jquery/check-box.jqtmpl';
                break;
            case 'Minder_View_Container':
                $templateFile = 'jquery/container.jqtmpl';
                break;
            case 'Minder_View_SelectionModeSwitcher':
                $templateFile = 'jquery/select-mode-switcher.jqtmpl';
                break;
            default:
                return '';
        }

        return $this->_loadTemplateFile($templateFile, $this->_getTemplateClass());
    }

    protected function _getElementJavaScriptClass() {
        return $this->getOption('javaScriptClass');
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
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new ' . $this->_getElementJavaScriptClass() . '("' . $this->_getName() . '", ' . $this->_getModelVariableName() . ',' . $this->_getTemplate() . ', ' . json_encode($this->getOption('settings')) . ');');

        return $content;
    }


}