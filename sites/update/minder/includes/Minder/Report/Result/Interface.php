<?php

interface Minder_Report_Result_Interface {
    public function getOutputFormat();

    public function getResult();

    public function setResult($reportResult);
}