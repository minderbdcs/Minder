<?php

/**
* View Helper to create search forms for screen
*/
class Minder_View_Helper_SysScreenSearchForm2 extends Zend_View_Helper_FormElement
{
    /**
    * Returns rendered search form
    * 
    * @param string $ssName - screen name to build form for
    * @param array $extraParams
    * @return string
    */
    public function sysScreenSearchForm2($ssName, $extraParams = array()) {
        return $this->render($ssName, $extraParams);
    }
    
    public function render($ssName, $extraParams = array()) {
        $xhtml = '';
        
        $screenBuilder = new Minder_SysScreen_Builder();
        list(
            $searchFields,
            $actions,
            $tabs,
            $giFields
        ) = $screenBuilder->buildSysScreenSearchFields($ssName);
        $extraParams['SYS_SCREEN_NAME'] = $ssName;
        
        switch (true) {
            case (count($giFields) > 0):
                //we have GI fields defined for this screen, so use global input as search field
                $extraParams['GI_FIELDS'] = $giFields;
                $xhtml = $this->view->sysScreenSearchFormGI($extraParams);
                break;
            case (count($tabs) > 0):
                if (isset($extraParams['search_fields'])) {
                    $searchFields = $extraParams['search_fields'];
                    unset($extraParams['search_fields']);
                }
        
                //we have tabs defined for screen, so will use tabbed search form
                $xhtml = $this->view->sysScreenTabbedSearchForm($extraParams, $tabs, $searchFields, $actions);
                break;
            default:
                //will use simple search form
                $xhtml = $this->view->sysScreenSearchForm($extraParams, $searchFields, $actions);
        }
        
        return $xhtml;
    }
}