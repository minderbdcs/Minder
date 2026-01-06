<?php

class Minder_SysBackup_Writer_Csv implements Minder_SysBackup_Writer_Interface {
    const EOL                     = PHP_EOL;
    const DELIMETER               = ',';
    const SECTION_START_DELIMETER = "!--section start--";
    const SECTION_END_DELIMETER   = "!--section end--";
    const ESCAPE_CHARACTER        = '"';
    const QUOTE_CHARACTER         = '"';
    
    protected function escape($value) {
        return str_replace(self::QUOTE_CHARACTER, self::ESCAPE_CHARACTER . self::QUOTE_CHARACTER, $value);
    }
    
    protected function quote($value) {
        return self::QUOTE_CHARACTER . $value . self::QUOTE_CHARACTER;
    }

    protected function writeSectionDelimiter($sectionDelimiter) {
        echo $this->quote($this->escape($sectionDelimiter)) . self::DELIMETER . self::EOL;
    }
    
    /**
    * @param Minder_SysBackup_Table_Interface $sysBackupTable
    */
    protected function writeTable($sysBackupTable) {
        echo $this->quote($this->escape($sysBackupTable->getTableName())) . self::DELIMETER . self::EOL;
    }
    
    /**
    * @param Minder_SysBackup_Table_Interface $sysBackupTable
    */
    protected function writeFields($sysBackupTable) {
        $fields = $sysBackupTable->getFields();
        $pKeys  = $sysBackupTable->getPKeys();
        
        foreach ($fields as &$field) {
            if (in_array($field, $pKeys))
                $field .= ' *';
        }
        
        echo implode(self::DELIMETER, array_map(array($this, 'quote'), array_map(array($this, 'escape'), $fields))) . self::DELIMETER . self::EOL;
    }
    
    /**
    * @param Minder_SysBackup_Table_Interface $sysBackupTable
    * @param array $row
    */
    protected function writeBackupRow($sysBackupTable, $row) {
        foreach ($sysBackupTable->getFields() as $fieldName) {
            echo $this->quote($this->escape($row[$fieldName])) . self::DELIMETER;
        }
        
        echo self::EOL;
    }
    
    /**
    * @param Minder_SysBackup_Table_Interface $sysBackupTable
    */
    function writeSection($sysBackupTable) {
        $this->writeSectionDelimiter(self::SECTION_START_DELIMETER);
        $this->writeTable($sysBackupTable);
        $this->writeFields($sysBackupTable);
        foreach ($sysBackupTable->getBackupedRows() as $row) {
            $this->writeBackupRow($sysBackupTable, $row);
        }
        $this->writeSectionDelimiter(self::SECTION_END_DELIMETER);
    }
}
