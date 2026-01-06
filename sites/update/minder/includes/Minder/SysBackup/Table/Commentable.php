<?php
  
abstract class Minder_SysBackup_Table_Commentable extends Minder_SysBackup_Table {
    
    abstract protected function getCommentExpression();

    protected function removeRows($removingRowsPKeys, $rowsWithPKeys, $pKeysFields) {
        $sql = 'UPDATE ' . $this->getTableName() . ' SET ' . $this->getCommentExpression() . ' WHERE ' . implode(' AND ', array_map(array($this, 'formatFieldToUseInQuery') , $pKeysFields));
        
        foreach ($removingRowsPKeys as $pKeyValue) {
            $rowData = $rowsWithPKeys[$pKeyValue];
            $args = array();
            foreach ($pKeysFields as $fieldName)
                $args[] = $rowData[$fieldName];

            $this->_execSql($sql, $args);
        }
    }
    
    protected function checkRows($pKeysFields, $rows) {
        list(
            $updatingRowsPKeys,
            $insertingRowsPKeys,
            $removingRowsPKeys) = $this->splitRowsPKeysByType($pKeysFields, $this->calculatePKeyExpression($pKeysFields, $rows));
        
        $this->_checkMessages[] = count($removingRowsPKeys)   . ' record(s) will be commented.';
        $this->_checkMessages[] = count($updatingRowsPKeys)   . ' record(s) will be updated.';
        $this->_checkMessages[] = count($insertingRowsPKeys)  . ' record(s) will be inserted.';
    }
}
