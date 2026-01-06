<?php

class Minder_Controller_Action_Helper_PackDimensions extends Zend_Controller_Action_Helper_Abstract {
    public function getScreenDescription(
        $screenModelName,
        Minder_SysScreen_Builder $screenBuilder,
        Minder_SysScreen_DataSource_Sql $dataSource,
        Minder_SysScreen_DataSource_Parameter_Interface $parameterProvider)
    {
        list(
            $fields,
            $tabs,
            $colors,
            $actions
            )                           = $screenBuilder->buildSysScreenSearchResult($screenModelName, true);

        foreach ($fields as &$fieldDesc) {
            if (!empty($fieldDesc['SSV_DROPDOWN_DEFAULT'])) {
                $dataSource->sql = $fieldDesc['SSV_DROPDOWN_DEFAULT'];
                $fieldDesc['DEFAULT_VALUE'] = $dataSource->fetchOne($parameterProvider);
            }
        }

        return new Minder_SysScreen_Definition($fields, $tabs, $colors, $actions);
    }
}