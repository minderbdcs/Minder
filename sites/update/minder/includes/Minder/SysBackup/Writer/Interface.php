<?php

interface Minder_SysBackup_Writer_Interface {
    /**
    * @param Minder_SysBackup_Table_Interface $sysBackupTable
    */
    function writeSection($sysBackupTable);
}