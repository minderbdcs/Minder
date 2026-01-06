<?php

class Minder_LabelPrinter_Logon extends Minder_LabelPrinter_Abstract {

    protected $_sysUser = array();

    protected function _fetchSysUser($userId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM SYS_USER WHERE SSN_ID = ?', $labelId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        if (count($queryResult) < 1) {
            throw new Exception('SYS_USER #' . $userId . ' not found ');
        }

        return array_shift($queryResult);
    }

    protected function _getSysUser($userId) {
        if (empty($this->_sysUser[$userId])) {
            $this->_sysUser[$userId] = $this->_fetchSysUser($userId, $userId);
        }

        return $this->_sysUser[$userId];
    }

    protected function _fetchLabelDataFromTable($tableName, $labelId)
    {
        switch ($tableName) {
            case 'SYS_USER':
                return $this->_getSysUser($labelId);
            default:
                return array();
        }
    }

    protected function _printLabel($labeldata)
    {
        return $this->_getPrinter()->printLogonLabel($labeldata);
    }

    function __construct()
    {
        parent::__construct('LOGON');
    }
}