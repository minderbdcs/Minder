<?php

interface Minder_Page_FormController_EditForm_Interface {
    /**
     * @abstract
     * @param string $recordId
     * @return Minder_Page_FormController_EditForm_FormData
     */
    function load($recordId);
}