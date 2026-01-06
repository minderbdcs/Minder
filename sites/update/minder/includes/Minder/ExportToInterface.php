<?php

interface Minder_ExportToInterface
{
    public function proceedData(array $headers, array $data);

    public function passThru();

    public function sendHttpHeaders();

    public function proceedDataRow(array $headers, array $dataRow);

    public function proceedHeaders(array $headers);
}