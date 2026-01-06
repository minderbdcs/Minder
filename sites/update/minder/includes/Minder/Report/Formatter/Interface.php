<?php

interface Minder_Report_Formatter_Interface {

    /**
     * @abstract
     * @param Minder_Report_Abstract $reportInstance
     * @return Minder_Report_Result
     */
    public function makeReport($reportInstance);
}