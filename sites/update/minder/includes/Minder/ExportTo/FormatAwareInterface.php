<?php

interface Minder_ExportTo_FormatAwareInterface {

    const OOXML_XLS = 'OOXML';
    const BIFF8 = 'BIFF8';
    const CSV = 'CSV';
    const XLSX = 'XLSX';
    const XLS = 'XLS';

    public function setFormat($format);
}