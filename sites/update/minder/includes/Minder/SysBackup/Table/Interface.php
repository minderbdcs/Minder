<?php
  
interface Minder_SysBackup_Table_Interface {
    
    //------------------ BACKUP INTERFACE ---------------
    public function doBackup();
    public function getTableName();
    public function getFields();
    public function getPKeys();
    public function getBackupedRows();
    //------------------ BACKUP INTERFACE ---------------
    
    //------------------ RESTORE INTERFACE --------------
    /**
    * @param Minder_SysBackup_BackupSection $backupSection
    */
    public function doRestore($backupSection);
    
    /**
    * @param Minder_SysBackup_BackupSection $backupSection
    */
    public function checkRestorationData($backupSection);
    
    /**
    * @returns array(string)
    */
    public function getCheckMessages();
    //------------------ RESTORE INTERFACE --------------
}