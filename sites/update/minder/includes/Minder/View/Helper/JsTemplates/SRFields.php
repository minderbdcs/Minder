<?php
  
/**
* Used to add all supported Search results fields templates to page at once
*/
class Minder_View_Helper_JsTemplates_SRFields extends Zend_View_Helper_FormElement
{
    public function SRFields($excludeTemplates = array()) {
        return $this->render($excludeTemplates);
    }
    
    public function render($excludeTemplates = array()) {
        $result = '';
        
        $defaultTemplates = array(
            'SRROField',
            'SRINField',
            'SRDDField',
            'SRDPField'
        );
        
        $templatesToInclude = array_diff($defaultTemplates, $excludeTemplates);
        
        foreach ($templatesToInclude as $templateClass)
            $result .= $this->view->$templateClass($excludeTemplates);
        
        return $result;
    }
}