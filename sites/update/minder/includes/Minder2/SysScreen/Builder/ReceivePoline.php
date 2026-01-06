<?php

class Minder2_SysScreen_Builder_ReceivePoline extends  Minder2_SysScreen_Builder_Default {
    /**
     * @param $ssName
     * @return Minder2_Model_SysScreen
     */
    function build($ssName)
    {
        $sysScreen = parent::build($ssName);
        $sysScreen->_TITLE = 'Lines List';
        return $sysScreen;
    }

}