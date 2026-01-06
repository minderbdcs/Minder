<?php

class Minder2_SysScreen_Builder_OrderStatistics implements Minder2_SysScreen_Builder_Interface {
    function build($ssName)
    {
        $tmpScreen = new Minder2_Model_ChartScreen(
            array(
                'SS_NAME' => $ssName,
                'SS_SEQUENCE' => 3,
                'SS_REFRESH'  => 30,
                'SS_MENU_ID'  => 'DASHBOARD'
            )
        );
        $tmpScreen->chartUrl = '/minder/dashboard/get-chart/page-id/DASHBOARD/chart-id/' . $ssName;
        $tmpScreen->addPrefixPath('Minder2_SysScreen_Decorator_JavaScript_', 'Minder2/SysScreen/Decorator/JavaScript/', Minder2_Model::DECORATOR);

        $tmpScreen->addDecorator('screenModel', array('decorator' => 'Model', 'javaScriptModel' => 'Minder_Model_ChartScreen'));
        $chartVariable = 'chart_' . $ssName;
        $tmpScreen->addDecorator($chartVariable, array('decorator' => 'ViewElement', 'variableName' => $chartVariable, 'name' => $ssName, 'javaScriptClass' => 'Minder_View_Chart'));

        $containerId = $ssName . '-CHART';
        $tmpScreen->addDecorator('defaultContainer', array('decorator' => 'defaultContainer', 'containerId' => $containerId));
        $tmpScreen->addDecorator('render', array('decorator' => 'render', 'variableName' => $chartVariable, 'placement' => '$("#' . $containerId . ' span")'));

        return $tmpScreen;
    }

}