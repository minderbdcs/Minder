<?php
  
class Minder_SysBackup_Table implements Minder_SysBackup_Table_Interface {
    /**
     * @var string
     */
    protected $_table        = '';

    /**
     * @var array
     */
    protected $_pKeys        = null;
    protected $_backupedRows = array();
    
    protected $_checkMessages = array();
    protected $_checkErrors   = array();

    /**
     * @var array
     */
    protected $_metadata      = null;
    
    const PKEY_DELIMETER = '{-}';

    /**
     * @param array|null $metadata
     * @return Minder_SysBackup_Table
     */
    protected function _setMetadata(array $metadata = null) {
        $this->_metadata = $metadata;
        return $this;
    }

    /**
     * @return array
     */
    protected function _fetchTableMetadata() {
        $metadata = array();

        foreach (Minder::getInstance()->getTableFieldType($this->getTableName()) as $fieldMetadata) {
            $metadata[trim($fieldMetadata['FIELD_NAME'])] = $fieldMetadata;
        }

        return $metadata;
    }

    /**
     * @return array
     */
    protected function _getMetadata() {
        if (is_null($this->_metadata))
            $this->_setMetadata($this->_fetchTableMetadata());

        return $this->_metadata;
    }
    
    public function __construct($table) {
        $this->setTableName($table);
    }
    
    protected function backupRows() {
        $minder = Minder::getInstance();
        $sql = 'SELECT * FROM ' . $this->getTableName();
        
        $this->_backupedRows = $minder->fetchAllAssoc($sql);
    }
    
    public function doBackup() {
        $this->backupRows();
    }

    public function setTableName($value) {
        $this->_table = strval($value);
        $this->_setMetadata(null)->_setPKeys(null);

        return $this;
    }

    public function getTableName() {
        if (empty($this->_table))
            throw new Minder_SysBackup_Table_Exception('TableName is empty.');

        return $this->_table;
    }

    /**
     * @return array
     */
    public function getFields() {
        $metadata = $this->_getMetadata();
        return array_keys($metadata);
    }

    /**
     * @param array|null $value
     * @return Minder_SysBackup_Table
     */
    protected function _setPKeys(array $value = null) {
        $this->_pKeys = $value;
        return $this;
    }

    /**
     * @return array
     */
    protected function _fetchPKeys() {
        $result = Minder::getInstance()->getUniqueConstraint($this->getTableName());

        if ($result === false)
            return array();

        return $result;
    }

    /**
     * @return array
     */
    public function getPKeys() {
        if (empty($this->_pKeys))
            $this->_setPKeys($this->_fetchPKeys());
        
        return $this->_pKeys;
    }
    
    public function getBackupedRows() {
        return $this->_backupedRows;
    }
    
    protected function formatFieldToUseInQuery($fieldName) {
        return $fieldName . ' = ?';
    }

    protected function _execSql($sql, $args) {
        $minder = Minder::getInstance();
        $minder->lastError = '';

        $minder->execSQL($sql, $args);

        if (strlen($minder->lastError) > 0)
            throw new Minder_SysBackup_Table_Exception($minder->lastError);
    }

    protected function _getCurrentUserId() {
        return Minder::getInstance()->userId;
    }
    
    protected function removeRows($removingRowsPKeys, $rowsWithPKeys, $pKeysFields) {
        $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE ' . implode(' AND ', array_map(array($this, 'formatFieldToUseInQuery') , $pKeysFields));
        
        foreach ($removingRowsPKeys as $pKeyValue) {
            $rowData = $rowsWithPKeys[$pKeyValue];
            $args = array();
            foreach ($pKeysFields as $fieldName)
                $args[] = $rowData[$fieldName];

            $this->_execSql($sql, $args);
        }
    }

    /**
     * @throws Minder_SysBackup_Table_Exception
     * @param string $fieldName
     * @return array
     */
    protected function _getFieldMetadata($fieldName) {
        $metadata = $this->_getMetadata();

        if (!isset($metadata[$fieldName]))
            throw new Minder_SysBackup_Table_Exception('Field "' . $fieldName . '" does not belong to "' . $this->getTableName() . '" table.');

        return $metadata[$fieldName];
    }

    protected function _formatFieldValue($fieldName, $fieldValue) {
        if (is_null($fieldValue))
            return $fieldValue;

        switch (strtoupper($fieldName)) {
            case 'LAST_UPDATE_DATE':
                return null; //as LAST_UPDATE_DATE will be overwritten by update or insert trigger
            case 'LAST_UPDATE_BY':
                return $this->_getCurrentUserId();
        }

        $fieldMetadata = $this->_getFieldMetadata($fieldName);

        if ($fieldValue === '') {
            if ($fieldMetadata['FIELD_NOT_NULL_CONSTRAINT'])
                return is_null($fieldMetadata['FIELD_DEFAULT_VALUE']) ? '' : $fieldMetadata['FIELD_DEFAULT_VALUE'];

            return null;
        }

        return $fieldValue;
    }
    
    protected function updateRows($updatingRowsPKeys, $rowsWithPKeys, $fields, $pKeysFields) {
        foreach ($updatingRowsPKeys as $pKeyValue) {
            $rowData    = $rowsWithPKeys[$pKeyValue];
            $fieldsExpr = array();
            $whereExpr  = array();
            $args       = array();
            
            foreach ($fields as $fieldName) {
                $fieldValue = $this->_formatFieldValue($fieldName, $rowData[$fieldName]);

                if (is_null($fieldValue)) {
                    $fieldsExpr[] = $fieldName . ' = NULL';
                } else {
                    $fieldsExpr[] = $fieldName . ' = ?';
                    $args[]       = $fieldValue;
                }
            }

            foreach ($pKeysFields as $fieldName) {
                if ($rowData[$fieldName] == '') {
                    $whereExpr[] = '(' . $fieldName . ' = ? OR ' . $fieldName . ' IS NULL)';
                } else {
                    $whereExpr[] = '(' . $fieldName . ' = ?)';
                }
                $args[]   = $rowData[$fieldName];
            }
                
            $sql = 'UPDATE ' . $this->getTableName() . ' SET ' . implode(', ', $fieldsExpr) . ' WHERE ' . implode(' AND ', $whereExpr);
            $this->_execSql($sql, $args);
        }
    }
    
    protected function insertRows($insertingRowsPKeys, $rowsWithPKeys, $fields) {
        $fieldsListStr = implode(', ', $fields);
        
        foreach ($insertingRowsPKeys as $pKeyValue) {
            $rowData    = $rowsWithPKeys[$pKeyValue];
            $args       = array();
            $fieldsExpr = array();
            
            foreach ($fields as $fieldName) {
                $fieldValue = $this->_formatFieldValue($fieldName, $rowData[$fieldName]);

                if (is_null($fieldValue)) {
                    $fieldsExpr[] = 'NULL';
                } else {
                    $fieldsExpr[] = '?';
                    $args[]       = $fieldValue;
                }
            }

            $sql = 'INSERT INTO ' . $this->getTableName() . '(' . $fieldsListStr . ') VALUES (' . implode(', ', $fieldsExpr)  . ')';
            $this->_execSql($sql, $args);
        }
    }

    /**
     * @return array
     */
    protected function _getGenerators() {
        return array();
    }

    protected function _getGeneratorStartValue($generator) {
        return 5000;
    }

    protected function _adjustGenerators() {
        foreach ($this->_getGenerators() as $generatorField => $generator) {
            $sql = "SELECT MAX(" . $generatorField . ") FROM " . $this->getTableName();

            $maxValue = Minder::getInstance()->findValue($sql);
            $maxValue = is_null($maxValue) ? $this->_getGeneratorStartValue($generator) : max(intval($maxValue), $this->_getGeneratorStartValue($generator));

            $sql = "ALTER SEQUENCE " . $generator . " RESTART WITH " . $maxValue;
            $this->_execSql($sql, array());
        }

        return $this;
    }

    /**
    * @param Minder_SysBackup_BackupSection $backupSection
    */
    public function doRestore($backupSection) {
        $rowsWithPKeys = $this->calculatePKeyExpression($backupSection->getPKeys(), $backupSection->getRows());
        list(
            $updatingRowsPKeys,
            $insertingRowsPKeys,
            $removingRowsPKeys,
            $existingRows) = $this->splitRowsPKeysByType($backupSection->getPKeys(), $rowsWithPKeys);
        
        $this->removeRows($removingRowsPKeys, $existingRows, $backupSection->getPKeys());
        $this->updateRows($updatingRowsPKeys, $rowsWithPKeys, $backupSection->getFields(), $backupSection->getPKeys());
        $this->insertRows($insertingRowsPKeys, $rowsWithPKeys, $backupSection->getFields());
        $this->_adjustGenerators();
    }
    
    protected function checkTableStructure($fields, $pKeysFields) {
        $tmpPKeys = array_intersect($this->getPKeys(), $pKeysFields);
        
        if (count($tmpPKeys) != count($this->getPKeys()) || count($tmpPKeys) != count($pKeysFields))
            $this->_checkErrors[]   = 'Primary Keys structure is differ.';
            
        $tmpFields = array_intersect($fields, $this->getFields());
        
        if (count($tmpFields) < count($fields))
            $this->_checkMessages[] = 'Some fields was removed since backup: (' . implode(', ', array_diff($fields, $tmpFields)) . ')';
            
        if (count($tmpFields) < count($this->getFields()))
            $this->_checkMessages[] = 'Some fields was added since backup: (' . implode(', ', array_diff($this->getFields(), $tmpFields)) . ')';
    }
    
    protected function getPKeyExpression($pKeysFields) {
        return implode(" || '" . self::PKEY_DELIMETER . "' || ", array_map(create_function('$item', 'return "COALESCE(" . $item . ", " . $item . ", \'\')";'), $pKeysFields));
    }
    
    
    protected function calculatePKeyExpression($pKeysFields, $rows) {
        $result = array();
        
        foreach ($rows as $singleRow) {
            $tmpPKeyValues = array();
            foreach ($pKeysFields as $fieldName) {
                $tmpPKeyValues[] = $singleRow[$fieldName];
            }
            
            $result[implode(self::PKEY_DELIMETER, $tmpPKeyValues)] = $singleRow;
        }
        
        return $result;
    }
    
    protected function splitRowsPKeysByType($pKeysFields, $rowsWithPKeys) {
        $minder= Minder::getInstance();
        
        $sql = 'SELECT ' . $this->getPKeyExpression($pKeysFields) . ' AS PKEY, ' . implode(', ', $pKeysFields) . ' FROM ' . $this->getTableName();
        
        $existingRows        = array();
        
        foreach ($minder->fetchAllAssoc($sql) as $resultRow) {
            $existingRows[$resultRow['PKEY']] = $resultRow;
        }
        
        $existingRowsPKeys   = array_keys($existingRows);
        $importingPKeys      = array_keys($rowsWithPKeys);
        
        $updatingRowsPKeys   = array_intersect($existingRowsPKeys, $importingPKeys);
        $insertingRowsPKeys  = array_diff($importingPKeys, $updatingRowsPKeys);
        $removingRowsPKeys   = array_diff($existingRowsPKeys, $updatingRowsPKeys);
        
        return array($updatingRowsPKeys, $insertingRowsPKeys, $removingRowsPKeys, $existingRows);
    }
    
    protected function checkRows($pKeysFields, $rows) {
        list(
            $updatingRowsPKeys,
            $insertingRowsPKeys,
            $removingRowsPKeys) = $this->splitRowsPKeysByType($pKeysFields, $this->calculatePKeyExpression($pKeysFields, $rows));
        
        $this->_checkMessages[] = count($removingRowsPKeys)   . ' record(s) will be removed.';
        $this->_checkMessages[] = count($updatingRowsPKeys)   . ' record(s) will be updated.';
        $this->_checkMessages[] = count($insertingRowsPKeys)  . ' record(s) will be inserted.';
    }

    /**
    * @param Minder_SysBackup_BackupSection $backupSection
    */
    public function checkRestorationData($backupSection) {
        $this->_checkMessages = array();
        $this->_checkErrors   = array();
        
        $this->checkTableStructure($backupSection->getFields(), $backupSection->getPKeys());
        $this->checkRows($backupSection->getPKeys(), $backupSection->getRows());
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
    
    /**
    * @return boolean
    */
    public function wasErrors() {
        return (count($this->_checkErrors) > 0);
    }
}