<?php

class Minder2_SysScreen_Builder_OrderTotals implements Minder2_SysScreen_Builder_Interface {

    protected function _getFields() {
        return array(
            array(
                'RECORD_ID' => 'ORDER_TOTALS_TOTAL_ORDERS',
                'SSV_ALIAS' => 'TOTAL_ORDERS',
                'SSV_TITLE' => 'Total Orders:',
                'COLOR_FIELD_ALIAS' => ''
            ),
            array(
                'RECORD_ID' => 'ORDER_TOTALS_TOTAL_LINES',
                'SSV_ALIAS' => 'TOTAL_LINES',
                'SSV_TITLE' => 'Total Lines:',
                'COLOR_FIELD_ALIAS' => ''
            ),
            array(
                'RECORD_ID' => 'ORDER_TOTALS_APPROVED_DESPATCH',
                'SSV_ALIAS' => 'APPROVED_DESPATCH',
                'SSV_TITLE' => 'Approved Despatch:',
                'COLOR_FIELD_ALIAS' => ''
            ),
            array(
                'RECORD_ID' => 'ORDER_TOTALS_APPROVED_PICKING',
                'SSV_ALIAS' => 'APPROVED_PICKING',
                'SSV_TITLE' => 'Approved Picking:',
                'COLOR_FIELD_ALIAS' => ''
            ),
            array(
                'RECORD_ID' => 'ORDER_TOTALS_AWAITING_DESPATCH',
                'SSV_ALIAS' => 'AWAITING_DESPATCH',
                'SSV_TITLE' => 'Awaiting Despatch:',
                'COLOR_FIELD_ALIAS' => ''
            ),
            array(
                'RECORD_ID' => 'ORDER_TOTALS_AWAITING_APPROVAL',
                'SSV_ALIAS' => 'AWAITING_APPROVAL',
                'SSV_TITLE' => 'Awaiting Approval:',
                'COLOR_FIELD_ALIAS' => ''
            ),
            array(
                'RECORD_ID' => 'ORDER_TOTALS_UNCONFIRMED',
                'SSV_ALIAS' => 'UNCONFIRMED',
                'SSV_TITLE' => 'Unconfirmed:',
                'COLOR_FIELD_ALIAS' => ''
            ),
        );
    }

    function build($ssName)
    {
        $screenBuilder = new Minder_SysScreen_Builder();

        if (!$screenBuilder->isSysScreenDefined($ssName))
            return null;

        $tmpScreen = new Minder2_Model_SysScreen($screenBuilder->getSysScreenDescription($ssName));
        $tmpScreen->restoreState();
        $tmpScreen->serviceUrl = '/minder/dashboard/screen/';

        $tmpScreen->addPrefixPath('Minder2_SysScreen_Decorator_JavaScript_', 'Minder2/SysScreen/Decorator/JavaScript/', Minder2_Model_SysScreen::DECORATOR);
        $tmpScreen->addDecorator('screenModel', array('decorator' => 'Model', 'javaScriptModel' => 'Minder_Model_SysScreen'));

        $dataGridFields = $this->_getFields();

        $dataGridVariable = 'dataGrid_' . $ssName;
        $tmpScreen->addDecorator($dataGridVariable, array('decorator' => 'DataGrid', 'templateFile' => 'jquery/order-totals-data-grid.jqtmpl', 'disableRowSelector' => true, 'fields' => $dataGridFields, 'variableName' => $dataGridVariable, 'name' => 'DATA_GRID-' . $ssName));

        $containerId = $ssName . '-SEARCH_RESULTS';
        $tmpScreen->addDecorator('defaultContainer', array('decorator' => 'defaultContainer', 'containerId' => $containerId));
        $tmpScreen->addDecorator('render', array('decorator' => 'render', 'variableName' => $dataGridVariable, 'placement' => '$("#' . $containerId . ' span")'));
        $tmpScreen->addDecorator('setData', array('decorator' => 'setData'));

        $tmpScreen->setDataSet(new Minder2_DataSet_SysScreenModel(new Minder_SysScreen_Model_OrderTotals()));

        return $tmpScreen;
    }

}