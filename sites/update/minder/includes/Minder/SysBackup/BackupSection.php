<?php
  
class Minder_SysBackup_BackupSection { 
    
    protected $tableName = '';
    protected $fields    = array();
    protected $pKeys     = array();
    protected $rows      = array();
    
    public function __construct($tableName = '', $fields = array(), $pKeys = array(), $rows = array()) {
        $this->setTableName($tableName)
                ->setFields($fields)
                ->setPKeys($pKeys)
                ->setRows($rows);
    }
    
    /**
    * @param string $tableName
    * 
    * @returns Minder_SysBackup_BackupSection
    */
    public function setTableName($tableName) {
        $this->tableName = $tableName;
        
        return $this;
    }
    
    /**
    * @param array $fields
    * 
    * @returns Minder_SysBackup_BackupSection
    */
    public function setFields($fields) {
        $this->fields = $fields;
        
        return $this;
    }
    
    /**
    * @param array $pKeys
    * 
    * @returns Minder_SysBackup_BackupSection
    */
    public function setPKeys($pKeys) {
        $this->pKeys = $pKeys;
        
        return $this;
    }
    
    /**
    * @param array $rows
    * 
    * @returns Minder_SysBackup_BackupSection
    */
    public function setRows($rows) {
        $this->rows = $rows;
        
        return $this;
    }
    
    /**
    * @returns string
    */
    public function getTableName() {
        return $this->tableName;
    }
    
    /**
    * @returns array
    */
    public function getFields() {
        return $this->fields;
    }
    
    /**
    * @returns array
    */
    public function getPKeys() {
        return $this->pKeys;
    }
    
    /**
    * @returns array
    */
    public function getRows() {
        return $this->rows;
    }
}
