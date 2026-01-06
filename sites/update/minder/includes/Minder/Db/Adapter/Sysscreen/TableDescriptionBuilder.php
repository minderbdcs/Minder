<?php

class Minder_Db_Adapter_Sysscreen_TableDescriptionBuilder {
    /**
     * @var string
     */
    protected $_sysScreenTableName = null;

    /**
     * @var string
     */
    protected $_schema = null;

    /**
     * @var array
     */
    protected $_realFields    = null;

    /**
     * @var array
     */
    protected $_virtualFields = null;

    /**
     * @var array
     */
    protected $_colorFields   = null;

    /**
     * @var array
     */
    protected $_sysScreenTables = null;

    /**
     * @var array
     */
    protected $_sysScreenVars   = null;

    /**
     * @var Minder_Db_Adapter_Sysscreen_Mapper_SysScreenTable
     */
    protected $_sysScreenTableMapper = null;

    /**
     * @var Minder_Db_Table_Mapper_SysScreenVar
     */
    protected $_sysScreenVarMapper   = null;

    /**
     * @var Minder_Db_Adapter_Sysscreen_Mapper_SysScreenOrder
     */
    protected $_sysScreenOrderMapper = null;

    /**
     * @var int
     */
    protected $_primaryIdPosition    = 1;

    protected $_realTables           = null;

    protected $_orderBy              = null;

    protected $_staticConditions     = null;

    function __construct($sysScreenTable = null, $schema = null)
    {
        $this->_setSysScreenTableName($sysScreenTable)->_setSchema($schema);
    }

    protected function _resetState() {
        $this->_realFields         = null;
        $this->_virtualFields      = null;
        $this->_colorFields        = null;
        $this->_sysScreenTables    = null;
        $this->_sysScreenVars      = null;
        $this->_realTables         = null;
        $this->_primaryIdPosition  = 1;

    }

    protected function _setSchema($val) {
        $val = trim(strval($val));

        if ($this->_schema !== $val)
            $this->_resetState();

        $this->_schema = $val;

        return $this;
    }

    protected function _getSchema() {
        if (empty($this->_schema))
            throw new Minder_Db_Adapter_Sysscreen_Exception('_schema is empty.');

        return $this->_schema;
    }

    /**
     * @param string $val
     * @return Minder_Db_Adapter_Sysscreen_TableDescriptionBuilder
     */
    protected function _setSysScreenTableName($val) {
        $val = trim(strval($val));

        if ($this->_sysScreenTableName !== $val)
            $this->_resetState();

        $this->_sysScreenTableName = $val;

        return $this;
    }

    /**
     * @throws Minder_Db_Adapter_Sysscreen_Exception
     * @return string
     */
    protected function _getSysScreenTableName() {
        if (empty($this->_sysScreenTableName))
            throw new Minder_Db_Adapter_Sysscreen_Exception('_sysScreenTableName is empty.');

        return $this->_sysScreenTableName;
    }

    /**
     * @return Minder_Db_Adapter_Sysscreen_Mapper_SysScreenTable
     */
    protected function _getSysScreenTableMapper() {
        if (is_null($this->_sysScreenTableMapper))
            $this->_sysScreenTableMapper = new Minder_Db_Adapter_Sysscreen_Mapper_SysScreenTable();

        return $this->_sysScreenTableMapper;
    }

    /**
     * @return array
     */
    protected function _buildSysScreenTables() {
        return $this->_getSysScreenTableMapper()->fetchBySsName($this->_getSysScreenTableName());
    }

    /**
     * @return array
     */
    protected function _getSysScreenTables() {
        if (empty($this->_sysScreenTables))
            $this->_sysScreenTables = $this->_buildSysScreenTables();

        return $this->_sysScreenTables;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getMainAdapter() {
        return Zend_Db_Table::getDefaultAdapter();
    }

    public function formatRealFieldAlias($tableName, $columnName) {
        return $tableName . '__' . $columnName;
    }

    /**
     * @return array
     */
    protected function _describeRealFields() {
        $result = array();

        $realTables = $this->_getRealTables();

        /**
         * @var Minder_Db_Table_SysScreenTableRow $sysScreenTable
         */
        foreach ($this->_getSysScreenTables() as $sysScreenTable) {
            foreach ($this->_getMainAdapter()->describeTable($sysScreenTable->SST_TABLE) as $fieldDescription) {
                $fieldDescription['CORRELATION'] = $realTables[$fieldDescription['TABLE_NAME']]['SST_ALIAS'];
                $fieldDescription['VIRTUAL']     = false;
                $fieldIndex = $this->formatRealFieldAlias($fieldDescription['TABLE_NAME'], $fieldDescription['COLUMN_NAME']);
                $result[$fieldIndex] = $fieldDescription;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function _getRealFields() {
        if (is_null($this->_realFields))
            $this->_realFields = $this->_describeRealFields();

        return $this->_realFields;
    }

    /**
     * @return Minder_Db_Table_Mapper_SysScreenVar
     */
    protected function _getSysScreenVarMapper() {
        if (is_null($this->_sysScreenVarMapper))
            $this->_sysScreenVarMapper = new Minder_Db_Table_Mapper_SysScreenVar();

        return $this->_sysScreenVarMapper;
    }

    /**
     * @return array
     */
    protected function _buildSysScreenVars() {
        return $this->_getSysScreenVarMapper()->fetchCurrent($this->_getSysScreenTableName(), $this->_getSchema());
    }

    /**
     * @return array
     */
    protected function _getSysScreenVars() {
        if (is_null($this->_sysScreenVars))
            $this->_sysScreenVars = $this->_buildSysScreenVars();

        return $this->_sysScreenVars;
    }

    /**
     * @throws Minder_Db_Adapter_Sysscreen_Exception
     * @param Minder_Db_Table_SysScreenVarRow $sysScreenVar
     * @return void
     */
    protected function _validateSysScreenVar($sysScreenVar) {
        if (empty($sysScreenVar->SSV_NAME) && empty($sysScreenVar->SSV_EXPRESSION))
            throw new Minder_Db_Adapter_Sysscreen_Exception('SYS_SCREEN_VAR #' . $sysScreenVar->RECORD_ID . ' has no SSV_NAME and no SSV_EXPRESSION.');

        if (empty($sysScreenVar->SSV_NAME) && empty($sysScreenVar->SSV_ALIAS))
            throw new Minder_Db_Adapter_Sysscreen_Exception('SYS_SCREEN_VAR #' . $sysScreenVar->RECORD_ID . ' has no SSV_NAME and no SSV_ALIAS.');

        if (!empty($sysScreenVar->SSV_TABLE)) {
            $realTables = $this->_getRealTables();
            if (!isset($realTables[$sysScreenVar->SSV_TABLE]))
                throw new Minder_Db_Adapter_Sysscreen_Exception('SYS_SCREEN_VAR #' . $sysScreenVar->RECORD_ID . ' requires ' . $sysScreenVar->SSV_TABLE . ' but was not defined in SYS_SCREEN_TABLE.');
        }
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $sysScreenVar
     * @return array
     */
    protected function _makePureVirtualFieldDescription($sysScreenVar) {
        $fieldDescription                     = $sysScreenVar->toArray();
        $fieldDescription['SCHEMA_NAME']      = $this->_getSchema();
        $fieldDescription['TABLE_NAME']       = $sysScreenVar->SS_NAME;
        $fieldDescription['COLUMN_NAME']      = 'FIELD_' . $sysScreenVar->RECORD_ID;
        $fieldDescription['COLUMN_POSITION']  = 0;
        $fieldDescription['DATA_TYPE']        = 'CHAR';
        $fieldDescription['DEFAULT']          = '';
        $fieldDescription['NULLABLE']         = true;
        $fieldDescription['LENGTH']           = 0;
        $fieldDescription['SCALE']            = null;
        $fieldDescription['PRECISION']        = null;
        $fieldDescription['UNSIGNED']         = false;
        $fieldDescription['PRIMARY']          = ($fieldDescription['SSV_PRIMARY_ID'] == 'T');
        $fieldDescription['PRIMARY_POSITION'] = ($fieldDescription['PRIMARY'] ? $this->_primaryIdPosition++ : null);
        $fieldDescription['VIRTUAL']          = true;

        return $fieldDescription;
    }

    /**
     * @param string $realTableName
     * @param string $realFieldName
     * @return array
     */
    protected function _getRealFieldDescription($realTableName, $realFieldName) {
        $realFields = $this->_getRealFields();
        $realFieldAlias = $this->formatRealFieldAlias($realTableName, $realFieldName);
        if (isset($realFields[$realFieldAlias]))
            return $realFields[$realFieldAlias];

        return null;
    }

    /**
     * @throws Minder_Db_Adapter_Sysscreen_Exception - if base field not found
     * @param Minder_Db_Table_SysScreenVarRow $sysScreenVar
     * @return array
     */
    protected function _makeVirtualFieldDescription($sysScreenVar) {
        $fieldDescription = $this->_getRealFieldDescription($sysScreenVar->SSV_TABLE, $sysScreenVar->SSV_NAME);

        if (is_null($fieldDescription))
            throw new Minder_Db_Adapter_Sysscreen_Exception('SYS_SCREEN_VAR #' . $sysScreenVar->RECORD_ID . ' base field "' . $sysScreenVar->SSV_TABLE . '.' . $sysScreenVar->SS_NAME . '" not found.');

        $fieldDescription                     = array_merge($fieldDescription, $sysScreenVar->toArray());
        $fieldDescription['SCHEMA_NAME']      = $this->_getSchema();
        $fieldDescription['TABLE_NAME']       = $sysScreenVar->SS_NAME;
        $fieldDescription['COLUMN_NAME']      = 'FIELD_' . $sysScreenVar->RECORD_ID;
        $fieldDescription['PRIMARY']          = ($fieldDescription['SSV_PRIMARY_ID'] == 'T');
        $fieldDescription['PRIMARY_POSITION'] = ($fieldDescription['PRIMARY'] ? $this->_primaryIdPosition++ : null);
        $fieldDescription['VIRTUAL']          = true;
        $fieldDescription['SSV_EXPRESSION']   = (empty($fieldDescription['SSV_EXPRESSION']) ? (empty($fieldDescription['SSV_TABLE']) ? $fieldDescription['SSV_NAME'] : $fieldDescription['SSV_TABLE']. '.' . $fieldDescription['SSV_NAME']) : $fieldDescription['SSV_EXPRESSION']);

        return $fieldDescription;
    }

    /**
     * @return array
     */
    protected function _describeVirtualFields() {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenVarRow $sysScreenVar
         */
        foreach ($this->_getSysScreenVars() as $sysScreenVar) {
            $this->_validateSysScreenVar($sysScreenVar);

            if (empty($sysScreenVar->SSV_NAME))
                $fieldDescription = $this->_makePureVirtualFieldDescription($sysScreenVar);
            else
                $fieldDescription = $this->_makeVirtualFieldDescription($sysScreenVar);

            $result[$fieldDescription['COLUMN_NAME']] = $fieldDescription;
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function _getVirtualFields() {
        if (is_null($this->_virtualFields))
            $this->_virtualFields = $this->_describeVirtualFields();

        return $this->_virtualFields;
    }

    /**
     * @return array
     */
    protected function _getColorFields() {
        if (is_null($this->_colorFields))
            $this->_colorFields = array(); //todo: add COLOR FIELDS description builder method

        return $this->_colorFields;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME => string; name of database or schema
     * TABLE_NAME  => string;
     * COLUMN_NAME => string; column name
     * COLUMN_POSITION => number; ordinal position of column in table
     * DATA_TYPE   => string; SQL datatype name of column
     * DEFAULT     => string; default expression of column, null if none
     * NULLABLE    => boolean; true if column can have nulls
     * LENGTH      => number; length of CHAR/VARCHAR
     * SCALE       => number; scale of NUMERIC/DECIMAL
     * PRECISION   => number; precision of NUMERIC/DECIMAL
     * UNSIGNED    => boolean; unsigned property of an integer type
     * PRIMARY     => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     *
     * @param string|null $sysScreenTable
     * @param string|null $schema
     * @return array
     */
    public function doBuild($sysScreenTable = null, $schema = null) {
        if (!is_null($sysScreenTable))
            $this->_setSysScreenTableName($sysScreenTable);

        if (!is_null($schema))
            $this->_setSchema($schema);

        return array_merge($this->_getRealFields(), $this->_getVirtualFields(), $this->_getColorFields());
    }

    protected function _describeRealTables() {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenTableRow $sysScreenTable
         */
        foreach ($this->_getSysScreenTables() as $sysScreenTable) {
            $sysScreenTable->SST_ALIAS          = (empty($sysScreenTable->SST_ALIAS) ? $sysScreenTable->SST_TABLE : $sysScreenTable->SST_ALIAS);
            $sysScreenTable->SST_VIA            = trim($sysScreenTable->SST_VIA);
            $sysScreenTable->SST_VIA            = (stripos($sysScreenTable->SST_VIA, 'ON ') === 0 ? substr($sysScreenTable->SST_VIA, 3) : $sysScreenTable->SST_VIA);
            $result[$sysScreenTable->SST_ALIAS] = $sysScreenTable->toArray();
        }

        return $result;
    }

    protected function _getRealTables() {
        if (is_null($this->_realTables))
            $this->_realTables = $this->_describeRealTables();

        return $this->_realTables;
    }

    public function buildRealTableDescriptions($sysScreenTable = null) {
        $this->_setSysScreenTableName($sysScreenTable);
        return $this->_getRealTables();
    }

    /**
     * @return Minder_Db_Adapter_Sysscreen_Mapper_SysScreenOrder
     */
    protected function _getSysScreenOrderMapper() {
        if (is_null($this->_sysScreenOrderMapper))
            $this->_sysScreenOrderMapper = new Minder_Db_Adapter_Sysscreen_Mapper_SysScreenOrder();

        return $this->_sysScreenOrderMapper;
    }

    protected function _fetchOrderBy() {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenOrderRow $sysScreenOrder
         */
        foreach ($this->_getSysScreenOrderMapper()->fetchBySsName($this->_getSysScreenTableName()) as $sysScreenOrder) {
            $result[] = str_ireplace('ORDER BY', '', $sysScreenOrder->SSO_ORDER);
        }

        return $result;
    }

    protected function _getOrderBy() {
        if (is_null($this->_orderBy)) {
            $this->_orderBy = $this->_fetchOrderBy();
        }

        return $this->_orderBy;
    }

    public function buildOrderBy($sysScreenTable = null) {
        $this->_setSysScreenTableName($sysScreenTable);
        return $this->_getOrderBy();
    }

    protected function _fetchStaticConditions() {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenVarRow $sysScreenVar
         */
        foreach ($this->_getSysScreenVarMapper()->fetchCurrent($this->_getSysScreenTableName(), 'SE') as $sysScreenVar) {
            if ($sysScreenVar->SSV_INPUT_METHOD == 'RO' || $sysScreenVar->SSV_INPUT_METHOD == 'NONE')
                $result[] = $sysScreenVar->SSV_EXPRESSION;
        }

        return $result;
    }

    protected function _getStaticConditions() {
        if (is_null($this->_staticConditions))
            $this->_staticConditions = $this->_fetchStaticConditions();

        return $this->_staticConditions;
    }

    public function buildStaticConditions($sysScreenTable = null) {
        $this->_setSysScreenTableName($sysScreenTable);
        return $this->_getStaticConditions();
    }
}