<?php
  
class Minder_SysBackup {
    
    protected $_checkMessages  = array();
    protected $_checkErrors    = array();
    protected $_wasCheckErrors = false;
    
    protected $_restoreMessages = array();
    protected $_restoreErrors   = array();

    /**
     * @var Zend_Loader_PluginLoader
     */
    protected $_loader = null;
    
    protected $_backupTableNames = array(
        'SYS_MENU',
        'SYS_SCREEN',
        'SYS_SCREEN_ACTION',
        'SYS_SCREEN_BUTTON',
        'SYS_SCREEN_COLOUR',
        'SYS_SCREEN_ORDER',
        'SYS_SCREEN_PROCEDURE',
        'SYS_SCREEN_TAB',
        'SYS_SCREEN_TABLE',
        'SYS_SCREEN_VAR',
        'OPTIONS',
        'PARAM',
        'SYS_LABEL',
        'SYS_LABEL_VAR'
    );

    /**
     * @return Zend_Loader_PluginLoader
     */
    protected function _getLoader() {
        if (is_null($this->_loader))
            $this->_loader = new Zend_Loader_PluginLoader(array('Minder_SysBackup_Table_' => 'Minder/SysBackup/Table'));

        return $this->_loader;
    }

    protected function _formatClassName($tableName) {
        $nameArray = explode('_', strtolower($tableName));

        foreach ($nameArray as &$namePart)
            $namePart = ucfirst($namePart);

        return implode('', $nameArray);
    }

    protected function _loadTableClass($tableName) {
        $loader = $this->_getLoader();
        $className = $loader->load($this->_formatClassName($tableName), false);

        if (false === $className)
            return new Minder_SysBackup_Table($tableName);

        return new $className();
    }
    
    /**
    * @param table $tableName
    * @return Minder_SysBackup_Table_Interface
    */
    protected function getSysBackupTableObject($tableName) {
        return $this->_loadTableClass($tableName);
    }
    
    /**
    * @param Minder_SysBackup_Writer_Interface $backupWriter
    */
    public function doBackup($backupWriter) {
        foreach ($this->_backupTableNames as $tableName) {
            $backupTable = $this->getSysBackupTableObject($tableName);
            $backupTable->doBackup();
            $backupWriter->writeSection($backupTable);
        }
    }
    
    /**
    * @param array $backupData
    * 
    * @returns boolean
    */
    public function doRestore($backupData) {
        $this->_restoreErrors   = array();
        $this->_restoreMessages = array();
        $minder = Minder::getInstance();
        
        try {
            $minder->beginTransaction();
            
            /**
            * @var Minder_SysBackup_BackupSection
            */
            $backupSection = null;
            foreach ($backupData as $backupSection) {
                
                if (array_search($backupSection->getTableName(), $this->_backupTableNames) === false) {
                    throw new Minder_SysBackup_Exception('Unknown Backup Table "' . $backupSection->getTableName() . '"');
                }
                
                $backupTable  = $this->getSysBackupTableObject($backupSection->getTableName());
                $backupTable->doRestore($backupSection);
                $this->_restoreMessages[] = $backupTable->getTableName() . ' data restored.';
            }
            
            $minder->commitTransaction();
        } catch (Exception $e) {
            $this->_restoreErrors[]   = $e->getMessage();
            $this->_restoreMessages[] = array();
            $minder->rollbackTransaction();
            
            return false;
        }
        
        return true;
    }
    
    public function getRestoreMessages() {
        return $this->_restoreMessages;
    }
    
    public function getRestoreErrors() {
        return $this->_restoreErrors;
    }
    
    
    /**
    * @param Minder_SysBackup_Reader_Interface $backupReader
    * 
    * @returns array
    */
    public function checkBackup($backupReader) {
        $backupData            = array();
        $this->_wasCheckErrors = false;
        $this->_checkMessages  = array();
        $this->_checkErrors    = array();
        
        $tablesToRestore = $this->_backupTableNames;
        
        while ($backupSection = $backupReader->nextSection()) {
            $backupData[] = $backupSection;

            if (($valueIndex = array_search($backupSection->getTableName(), $tablesToRestore)) === false) {
                $this->_checkErrors[] = 'Unknown Backup Table "' . $backupSection->getTableName() . '"';
                $this->_wasCheckErrors = true;
            } else {
                unset($tablesToRestore[$valueIndex]);
                $backupTable  = $this->getSysBackupTableObject($backupSection->getTableName());
                $backupTable->checkRestorationData($backupSection);
                
                $this->_checkMessages[] = $backupTable->getTableName() . ': ';
                $this->_checkMessages   = array_merge($this->_checkMessages, $backupTable->getCheckMessages());
                
                if ($backupTable->wasErrors()) {
                    $this->_checkErrors[]  = $backupTable->getTableName() . ': ';
                    $this->_checkErrors    = array_merge($this->_checkErrors, $backupTable->getCheckErrors());
                    $this->_wasCheckErrors = true;
                }
            }
        }
        
        if (count($tablesToRestore) > 0) {
            $this->_checkErrors[]  = 'Some Tables was not found in backup: "' . implode('", "', $tablesToRestore) . '"';
            $this->_wasCheckErrors = true;
        }
        
        return $backupData;
    }
    
    /**
    * @returns boolean
    */
    public function wasCheckErrors() {
        return $this->_wasCheckErrors;
    }
    
    /**
    * @returns array(string)
    */
    public function getCheckMessages() {
        return $this->_checkMessages;
    }
    
    /**
    * @returns array(string)
    */
    public function getCheckErrors() {
        return $this->_checkErrors;
    }
}