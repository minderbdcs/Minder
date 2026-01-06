<?php

interface Minder_SysScreen_DataSource_Interface {

    /**
     * @abstract
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return array
     */
    function fetchAllAssoc($parametersProvider);

    /**
     * @abstract
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return array
     */
    function fetchAssoc($parametersProvider);

    /**
     * @abstract
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return string
     */
    function fetchOne($parametersProvider);
}