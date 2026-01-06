<?php

interface Minder_SysBackup_Reader_Interface {
    
    /**
    * @returns Minder_SysBackup_BackupSection | false
    */
    public function nextSection();
}