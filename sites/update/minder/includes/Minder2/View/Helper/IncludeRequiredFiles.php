<?php

class Minder2_View_Helper_IncludeRequiredFiles extends Zend_View_Helper_Abstract {

    protected function _getView() {
        return $this->view;
    }

    public function includeRequiredFiles($className) {
        switch ($className) {
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
            case 'Minder_View_DataGridIn':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/dataGridElement.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/DataGridIn.js');
                break;
        }
    }
}