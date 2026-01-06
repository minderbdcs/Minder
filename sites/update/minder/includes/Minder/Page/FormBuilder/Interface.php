<?php


interface Minder_Page_FormBuilder_Interface {
    /**
     * @param string $ssName
     * @return Zend_Config
     */
    public function build($ssName);

}