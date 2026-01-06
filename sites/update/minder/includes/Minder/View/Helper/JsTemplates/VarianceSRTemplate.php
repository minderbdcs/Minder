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
class Minder_View_Helper_JsTemplates_VarianceSRTemplate extends Zend_View_Helper_FormElement
{
    public function VarianceSRTemplate($excludeTemplates = array()) {
        return $this->render($excludeTemplates);
    }

    public function render($excludeTemplates = array()) {
        $result = '';

        $defaultTemplates = array(
            'SRDefaultHeader',
            'SRDefaultFooter',
            'VarianceSRTabContainer',
            'SRPaginator',
            'VarianceSRTabs',
            'SRTabContent',
            'VarianceSRTabHeader',
            'SRColumnHeaders',
            'SRRows',
            'SRCell',
            'SRFields',
            'SRButtons'
        );

        $templatesToInclude = array_diff($defaultTemplates, $excludeTemplates);

        foreach ($templatesToInclude as $templateClass)
            $result .= $this->view->$templateClass($excludeTemplates);

        return $result;
    }
}