<?php

class Minder_SysBackup_Reader_Csv extends Minder_Reader_Csv implements Minder_SysBackup_Reader_Interface {
    
    const SECTION_START_DELIMETER = "!--section start--";
    const SECTION_END_DELIMETER   = "!--section end--";
    const ESCAPE_CHARACTER        = '"';

    protected function readSectionStart() {
        $nextLine = $this->nextLine();
        
        if ($nextLine === false)
            return false;
            
        if (current($nextLine) !== self::SECTION_START_DELIMETER)
            throw new Minder_SysBackup_Reader_Exception('Bad backup format. Section Start Delimeter expected.');
            
        return true;
    }
    
    protected function readTableName() {
        if (false === ($nextLine = $this->nextLine()))
            throw new Minder_SysBackup_Reader_Exception('Bad backup format. Table Name expected.');
            
        return current($nextLine);
    }
    
    protected function isFieldPKey($fieldDescription) {
        return (strpos($fieldDescription, '*') === false) ? false : true;
    }
    
    protected function getFieldName($fieldDescription) {
        return trim(str_replace('*', '', $fieldDescription));
    }
    
    protected function readFieldsInformation() {
        $pKeys = array();
        $fields = array();
        
        if (false === ($nextLine = $this->nextLine()))
            throw new Minder_SysBackup_Reader_Exception('Bad backup format. Fields Information expected.');
        
        foreach ($nextLine as $tmpFieldDescription) {
            $tmpFieldName = $this->getFieldName($tmpFieldDescription);
            if (empty($tmpFieldName)) continue;
            if ($this->isFieldPKey($tmpFieldDescription))
                $pKeys[] = $tmpFieldName;
                
            $fields[] = $tmpFieldName;
        }
        
        return array($pKeys, $fields);
    }
    
    protected function isSectionEndDelimeter($row) {
        return (current($row) === self::SECTION_END_DELIMETER);
    }
    
    protected function parseRow($fields, $row) {
        $result = array();
        
        foreach ($fields as $index => $fieldName) {
            if (empty($fieldName)) {
                throw new Minder_SysBackup_Reader_Exception('Bad backup format. Empty Field Name.');
            }
            
            $result[$fieldName] = isset($row[$index]) ? $row[$index] : '';
        }
        
        return $result;
    }
    
    protected function removeEmptyField($fields) {
        $result = array();
        
        foreach ($fields as $fieldName)
            if (!empty($fieldName)) $result[] = $fieldName;
        
        return $result;
    }
    
    /**
    * @returns Minder_SysBackup_BackupSection | false
    */
    public function nextSection() {
        $backupSection = new Minder_SysBackup_BackupSection();
        
        if ($this->readSectionStart() === false)
            return false; //end of file
            
        $backupSection->setTableName($this->readTableName());
        
        list($tmpPKeys, $tmpFields) = $this->readFieldsInformation();
        
        $tmpRows = array();
        $nextRow = $this->nextLine();
        while (($nextRow !== false) && !$this->isSectionEndDelimeter($nextRow)) {
            $tmpRows[] = $this->parseRow($tmpFields, $nextRow);
            
            $nextRow = $this->nextLine();
        }
        
        $tmpFields = $this->removeEmptyField($tmpFields);
        
        $backupSection->setPKeys($tmpPKeys)->setFields($tmpFields)->setRows($tmpRows);
        return $backupSection;
    }
}