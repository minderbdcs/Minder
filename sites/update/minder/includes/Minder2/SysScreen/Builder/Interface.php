<?php

interface Minder2_SysScreen_Builder_Interface {
    /**
     * @abstract
     * @param $ssName
     * @return Minder2_Model_SysScreen
     */
    function build($ssName);
}