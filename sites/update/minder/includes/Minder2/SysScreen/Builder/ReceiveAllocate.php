<?php

class Minder2_SysScreen_Builder_ReceiveAllocate extends Minder2_SysScreen_Builder_Default {
    protected function _getSummaryElements($tabPageId, $searchResultModelVariableName)
    {
        $summaryElements = parent::_getSummaryElements($tabPageId, $searchResultModelVariableName);
        $tmpScreen = $this->_getSysScreen();

        $totalSelectedUnitsVariable = 'totalSelectedUnits' . $tabPageId;
        $tmpScreen->addDecorator($totalSelectedUnitsVariable, array('decorator' => 'ViewElement', 'variableName' => $totalSelectedUnitsVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => '_TOTAL_SELECTED_UNITS', 'javaScriptClass' => 'MinderView_RowSelectionInformer', 'templateFile' => 'jquery/field-with-caption.jqtmpl', 'settings' => array('_CAPTION' => 'Total Selected Units:')));
        $summaryElements[] = array('name' => '_TOTAL_SELECTED_UNITS', 'variableName' => $totalSelectedUnitsVariable);

        $totalOrderUnitsVariable = 'totalOrderUnits' . $tabPageId;
        $tmpScreen->addDecorator($totalOrderUnitsVariable, array('decorator' => 'ViewElement', 'variableName' => $totalOrderUnitsVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => '_TOTAL_ORDER_UNITS', 'javaScriptClass' => 'Minder_View_DataField', 'templateFile' => 'jquery/field-with-caption.jqtmpl', 'settings' => array('_CAPTION' => 'Total Order Units:')));
        $summaryElements[] = array('name' => '_TOTAL_ORDER_UNITS', 'variableName' => $totalOrderUnitsVariable);

        return $summaryElements;
    }

    function build($ssName)
    {
        $sysScreen = parent::build($ssName);
        $sysScreen->_TITLE = 'Pick Items';
        return $sysScreen;
    }

}