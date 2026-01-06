<?php

class Minder_Process_ImportMappedOrders {
    protected $_type = null;
    protected $_filepath = null;

    protected $_filename = null;

    protected $_fieldMap = null;
    protected $_importTable = null;

    protected $_ignorePath = false;

    protected $_path = null;
    protected $_extension = null;

    protected $_paramManager = null;

    /**
     * @var Minder_Process_ImportMappedOrders_Status
     */
    protected $_importStatus = null;

    function __construct($type, $filepath, $ignorePath = false)
    {
        $this->_type = $type;
        $this->_filepath = $filepath;

        $this->_setIgnorePath($ignorePath);
    }

    protected function _getFilename() {
        if (is_null($this->_filename)) {
            $this->_filename = basename($this->_filepath);
        }

        return $this->_filename;
    }

    protected function _getPath() {
        if (is_null($this->_path)) {
            $this->_path = dirname($this->_filepath);
        }

        return $this->_path;
    }

    protected function _getImportTable() {
        if (is_null($this->_importTable)) {
            $sql = "
                SELECT
                    DESCRIPTION
                FROM
                    OPTIONS
                WHERE
                    GROUP_CODE = ?
                    AND CODE CONTAINING ?
            ";

            $this->_importTable = Minder::getInstance()->findValue($sql, 'IMPORT_MAP', '|' . strtoupper($this->_type));

            if (empty($this->_importTable))
                throw new Minder_Exception('Cannot find IMPORT TABLE for IMPORT MAP TYPE: "' . $this->_type . '". Check system setup.');
        }

        return $this->_importTable;
    }

    /**
     * @return Zend_Db_Table_Rowset
     * @throws Minder_Exception
     */
    protected function _fetchImportMap() {
        $sql = "
            SELECT
                *
            FROM
                IMPORT_MAP
            WHERE
                IMPORT_MAP.MAP_IMS_TABLE = ?
                AND ? LIKE IMPORT_MAP.MAP_IMPORT_FILENAME
        ";

        if (false === ($result = Minder::getInstance()->fetchAllAssoc($sql, $this->_getImportTable(), $this->_getFilename()))) {
            throw new Minder_Exception('Cannot find IMPORT_MAP for TABLE: "' . $this->_getImportTable() . '" and FILENAME: "' . $this->_getFilename() . '" combination. ' . Minder::getInstance()->lastError);
        }

        return new Zend_Db_Table_Rowset(array('data' => $result, 'rowClass' => 'Minder_Process_ImportMappedOrders_MapField'));
    }

    /**
     * @param $importRule
     * @return Zend_Db_Table_Rowset
     */
    protected function _getFieldMap($importRule) {
        $sql = "SELECT * FROM IMPORT_MAP WHERE IMPORT_RULES_NAME = ? ORDER BY MAP_IMS_SEQUENCE ASC";
        $result = Minder::getInstance()->fetchAllAssoc($sql, $importRule['IMPORT_RULES_NAME']);

        return new Zend_Db_Table_Rowset(array('data' => $result, 'rowClass' => 'Minder_Process_ImportMappedOrders_MapField'));
    }

    protected function _prepareInsertData($lineData, $fieldMap) {
        $tables = array();

        foreach ($fieldMap as $description) {
            /**
             * @var Minder_Process_ImportMappedOrders_MapField $description
             */
            $tableName = $description->getTable();

            $table = isset($tables[$tableName]) ? $tables[$tableName] : array(
                'fields' => array(),
                'values' => array(),
                'args' => array(),
            );

            $realColumn = $description->getColumnIndex();
            $table['fields'][] = $description->getFieldName();
            $tmpValue   = $description->formatValue(isset($lineData[$realColumn]) ? $lineData[$realColumn] : null);

            $paramNames = $description->getParamNames();

            if (!empty($paramNames)) {
                if (!$this->_isValidDataIdentifier($tmpValue, $paramNames)) {
                    return null; //skipping insert
                }
            }

            if (empty($tmpValue)) {
                $table['values'][] = 'NULL';
            } else {
                $table['values'][] = '?';
                $table['args'][] = $tmpValue;
            }

            $tables[$tableName] = $table;
        }

        return $tables;
    }

    protected function _importLine($lineData, $fieldMap) {
        $tables = $this->_prepareInsertData($lineData, $fieldMap);

        if (is_null($tables)) {
            $this->_getImportStatus()->skip();
            return;
        }

        $inserted = 0;
        foreach ($tables as $tableName => $table) {
            $sql = "
                INSERT INTO " . $tableName . " (" . implode(', ', $table['fields']) . ")
                VALUES (" . implode(', ', $table['values']) . ")
            ";

            $minder = Minder::getInstance();
            $result = $minder->execSQL($sql, $table['args']);
            if (false === $result) {
                throw new Minder_Exception('Error importing row: ' . $minder->lastError);
            } else {
                $inserted += $result;
            }
        }
        $this->_getImportStatus()->success($inserted);
    }

    /**
     * @return Minder_Process_ImportMappedOrders_Status
     */
    public function doImport() {
        try {
            $this->_setImportStatus(new Minder_Process_ImportMappedOrders_Status());
            $importRule = $this->_getImportRule();

            if (is_null($importRule)) {
                $this->_getImportStatus()->error('No import rule found.');
                return $this->_getImportStatus();
            }

            $fieldMap = $this->_getFieldMap($importRule);

            if ($fieldMap->count() < 1) {
                $this->_getImportStatus()->error('No import map found.');
                return $this->_getImportStatus();
            }

            $this->_onPreImport($importRule);
        } catch (Exception $e) {
            $this->_getImportStatus()->error($e->getMessage());
            return $this->_getImportStatus();
        }

        $reader = new Minder_Reader_Csv($this->_filepath);

        try {
            $lineNo = 0;

            foreach ($reader as $importRow) {
                if (strtoupper($importRule['IMPORT_RULES_USE_ROW_1']) === 'F' && $lineNo < 1) {
                    $lineNo++;
                    continue;
                }

                $this->_importLine($importRow, $fieldMap);
                $lineNo++;
            }

            $reader->close();
            $this->_onAfterImport($importRule);

        } catch (Exception $e) {
            $reader->close();
            $this->_getImportStatus()->error($e->getMessage());
        }

        $this->_moveSourceFile($importRule);

        return $this->_getImportStatus();
    }

    /**
     * @return boolean
     */
    protected function _isIgnorePath()
    {
        return $this->_ignorePath;
    }

    /**
     * @param boolean $ignorePath
     * @return $this
     */
    protected function _setIgnorePath($ignorePath)
    {
        $this->_ignorePath = $ignorePath;
        return $this;
    }

    protected function _getImportRule() {
        $query = 'SELECT * FROM IMPORT_RULES WHERE IMPORT_RULES_TYPE	= ?';

        foreach (Minder::getInstance()->fetchAllAssoc($query, $this->_type) as $rule) {
            if ($this->_isValidRule($rule)) {
                return $rule;
            }
        }

        return null;
    }

    protected function _isValidRule($importRule) {
        if (!$this->_isIgnorePath()) {
            if (strtolower($importRule['IMPORT_RULES_PATH']) !== strtolower($this->_getPath())) {
                return false;
            }
        }

        if (!empty($importRule['IMPORT_RULES_FILENAME_PREFIX'])) {
            if (stripos($this->_getFilename(), $importRule['IMPORT_RULES_FILENAME_PREFIX']) !== 0) {
                return false;
            }
        }

        if (!empty($importRule['IMPORT_RULES_FILENAME_EXTENSN'])) {
            if (strtolower(ltrim($importRule['IMPORT_RULES_FILENAME_EXTENSN'], '.')) !== strtolower($this->_getExtension())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return null
     */
    protected function _getExtension()
    {
        if (is_null($this->_extension)) {
            $pathInfo = pathinfo($this->_filepath);
            $this->_extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
        }
        return $this->_extension;
    }

    protected function _isValidDataIdentifier($tmpValue, $paramNames)
    {
        foreach ($this->_getParamManager()->getMany($paramNames) as $dataId => $paramDescription) {
            if (is_null($paramDescription)) {
                user_error('Data Identifier "' . $dataId . '" not found.');
            } else {
                if ($paramDescription->isValid($tmpValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return Minder_Param_Manager
     */
    protected function _getParamManager()
    {
        if (is_null($this->_paramManager)) {
            $this->_setParamManager(new Minder_Param_Manager());
        }

        return $this->_paramManager;
    }

    /**
     * @param Minder_Param_Manager $paramManager
     * @return $this
     */
    protected function _setParamManager(Minder_Param_Manager $paramManager)
    {
        $this->_paramManager = $paramManager;
        return $this;
    }

    /**
     * @return Minder_Process_ImportMappedOrders_Status
     */
    protected function _getImportStatus()
    {
        if (is_null($this->_importStatus)) {
            $this->_setImportStatus(new Minder_Process_ImportMappedOrders_Status());
        }
        return $this->_importStatus;
    }

    /**
     * @param Minder_Process_ImportMappedOrders_Status $importStatus
     * @return $this
     */
    protected function _setImportStatus($importStatus)
    {
        $this->_importStatus = $importStatus;
        return $this;
    }

    protected function _onPreImport($importRule)
    {
        $minder = Minder::getInstance();
        if (!empty($importRule['IMPORT_RULES_PRE_PROCEDURE'])) {
            if (false === $minder->execSQL('SELECT * FROM ' . $importRule['IMPORT_RULES_PRE_PROCEDURE'])) {
                throw new Minder_Exception('Error executing pre-import procedure: ' . $minder->lastError);
            }
        }
    }

    protected function _onAfterImport($importRule)
    {
        $minder = Minder::getInstance();
        if (!empty($importRule['IMPORT_RULES_POST_PROCEDURE'])) {
            if (false === $minder->execSQL('SELECT * FROM ' . $importRule['IMPORT_RULES_POST_PROCEDURE'])) {
                throw new Minder_Exception('Error executing pre-import procedure: ' . $minder->lastError);
            }
        }

    }

    protected function _formatNewFilePath($importRule) {
        $prefix =  $importRule['IMPORT_RULES_FILENAME_PREFIX'];
        $extension = $importRule['IMPORT_RULES_FILENAME_EXTENSN'];

        $rawName = substr(substr($this->_getFilename(), strlen($prefix)), 0, -strlen($extension));

        if ($this->_getImportStatus()->isError()) {
            $newPrefix = $importRule['IMPORT_RULES_ERROR_PREFIX'];
            $newExtension = $importRule['IMPORT_RULES_ERROR_EXTENSN'];
        } else {
            $newPrefix = $importRule['IMPORT_RULES_SUCCESS_PREFIX'];
            $newExtension = $importRule['IMPORT_RULES_SUCCESS_EXTENSN'];
        }

        return $this->_getPath() . '/' . $newPrefix . $rawName . $newExtension;
    }

    protected function _moveSourceFile($importRule)
    {
        $newFilePath = $this->_formatNewFilePath($importRule);

        if (file_exists($newFilePath) && !is_writable($newFilePath)) {
            user_error('Cannot move ' . $this->_filepath . ' to ' . $newFilePath . ': file exists and not writable.');
            $this->_getImportStatus()->error('Cannot move file to new destination.');
        } else {
            if (false === rename($this->_filepath, $newFilePath)) {
                $this->_getImportStatus()->error('Cannot move file to new destination.');
            }
        }

    }


}