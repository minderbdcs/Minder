<?php
  
/**
* Used to generate standard Search results template
* 
* It will add such templates:
*        SRDefaultHeader
*        SRDefaultFooter
*        SRTabContainer
*        SRPaginator
*        SRTabHeaders
*        SRTabs
*        SRTabContent
*        SRColumnHeaders
*        SRRows
*        SRCell
*        SRFields
* 
* to prevent some template to be inserted use $excludeTemplates array
*/
class Minder_View_Helper_JsTemplates_StandardSRTemplate extends Zend_View_Helper_FormElement
{
    public function StandardSRTemplate($excludeTemplates = array(), $sysScreenDefinedButtons = false) {
        if ($sysScreenDefinedButtons) {
            $excludeTemplates[] = 'SRDefaultButtons';
        } else {
            $excludeTemplates[] = 'SRButtons';
        }

        return $this->render($excludeTemplates);
    }
    
    public function render($excludeTemplates = array()) {
        $result = '';
        
        $defaultTemplates = array(
            'SRDefaultHeader',
            'SRDefaultFooter',
            'SRTabContainer',
            'SRPaginator',
            'SRTabHeaders',
            'SRTabs',
            'SRTabContent',
            'SRColumnHeaders',
            'SRRows',
            'SRCell',
            'SRFields',
            'SRDefaultButtons',
            'SRButtons'
        );
        
        $templatesToInclude = array_diff($defaultTemplates, $excludeTemplates);
        
        foreach ($templatesToInclude as $templateClass)
            $result .= $this->view->$templateClass($excludeTemplates);
        
        return $result;
    }
}