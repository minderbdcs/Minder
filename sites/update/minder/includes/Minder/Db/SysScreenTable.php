<?php

class Minder_Db_SysScreenTable extends Zend_Db_Table {
    const REAL_TABLES      = 'realTables';
    const DEFAULT_ORDER_BY = 'dafaultOrderBy';
    const STATIC_WHERE     = 'staticWhere';

    const SELECT_RAW                    = 0;
    const SELECT_WITH_FROM_PART         = 1;
    const SELECT_WITH_WHERE_PART        = 2;
    const SELECT_WITH_ORDER_BY_PART     = 4;
    const SELECT_WITHOUT_ORDER_BY_PART  = 3;
    const SELECT_FULL                   = 7;

    protected $_realTables       = array();
    protected $_defaultOrderBy   = array();
    protected $_staticConditions = array();

    protected static $_defaultSysScreenDb = null;

    public static function setDefaultAdapter($db = null)
    {
        self::$_defaultSysScreenDb = self::_setupAdapter($db);
    }

    public static function getDefaultAdapter()
    {
        return self::$_defaultSysScreenDb;
    }

    /**
     * Initialize database adapter.
     *
     * @return void
     */
    protected function _setupDatabaseAdapter()
    {
        if (! $this->_db) {
            $this->_db = self::getDefaultAdapter();
            if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception('No adapter found for ' . get_class($this));
            }
        }
    }

    protected function _setupMetadata()
    {
        if ($this->metadataCacheInClass() && (count($this->_metadata) > 0)) {
            return true;
        }

        // Assume that metadata will be loaded from cache
        $isMetadataFromCache = true;
        $metadataObject      = new Minder_Db_SysScreenTable_MetadataObject();


        // If $this has no metadata cache but the class has a default metadata cache
        if (null === $this->_metadataCache && null !== self::$_defaultMetadataCache) {
            // Make $this use the default metadata cache of the class
            $this->_setMetadataCache(self::$_defaultMetadataCache);
        }

        // If $this has a metadata cache
        if (null !== $this->_metadataCache) {
            // Define the cache identifier where the metadata are saved

            //get db configuration
            $dbConfig = $this->_db->getConfig();

            // Define the cache identifier where the metadata are saved
            $cacheId = md5( // port:host/dbname:schema.table (based on availabilty)
                (isset($dbConfig['options']['port']) ? ':'.$dbConfig['options']['port'] : null)
                . (isset($dbConfig['options']['host']) ? ':'.$dbConfig['options']['host'] : null)
                . '/'.$dbConfig['dbname'].':'.$this->_schema.'.'.$this->_name
                );
        }

        // If $this has no metadata cache or metadata cache misses
        if (null === $this->_metadataCache || !($metadataObject = $this->_metadataCache->load($cacheId))) {
            // Metadata are not loaded from cache
            $isMetadataFromCache = false;

            // Fetch metadata from the adapter's describeTable() method
            $metadataObject->metadata   = $this->getAdapter()->describeTable($this->_name, $this->_schema);
            $metadataObject->orderBy    = $this->getAdapter()->describeOrderBy($this->_name);
            $metadataObject->realTables = $this->getAdapter()->describeRealTables($this->_name);
            $metadataObject->staticConditions = $this->getAdapter()->describeStaticConditions($this->_name);
            // If $this has a metadata cache, then cache the metadata
            if (null !== $this->_metadataCache && !$this->_metadataCache->save($metadataObject, $cacheId)) {
                /**
                 * @see Zend_Db_Table_Exception
                 */
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception('Failed saving metadata to metadataCache');
            }
        }

        // Assign the metadata to $this
        $this->_metadata         = $metadataObject->metadata;
        $this->_defaultOrderBy   = $metadataObject->orderBy;
        $this->_realTables       = $metadataObject->realTables;
        $this->_staticConditions = $metadataObject->staticConditions;

        // Return whether the metadata were loaded from cache
        return $isMetadataFromCache;
    }

    protected function _setupPrimaryKey()
    {
        if (!$this->_primary) {
            $this->_setupMetadata();
            $this->_primary = array();
            foreach ($this->_metadata as $col) {
                if (isset($col['SSV_PRIMARY_ID']) && $col['PRIMARY']) {
                    $this->_primary[ $col['PRIMARY_POSITION'] ] = $col['COLUMN_NAME'];
                    if ($col['IDENTITY']) {
                        $this->_identity = $col['PRIMARY_POSITION'];
                    }
                }
            }
            // if no primary key was specified and none was found in the metadata
            // then throw an exception.
            if (empty($this->_primary)) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception('A table must have a primary key, but none was found');
            }
        } else if (!is_array($this->_primary)) {
            $this->_primary = array(1 => $this->_primary);
        } else if (isset($this->_primary[0])) {
            array_unshift($this->_primary, null);
            unset($this->_primary[0]);
        }

        $cols = $this->_getCols();
        if (! array_intersect((array) $this->_primary, $cols) == (array) $this->_primary) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Primary key column(s) ("
                . implode(',', (array) $this->_primary)
                . ") are not columns in this table ("
                . implode(',', $cols)
                . ")");
        }

        $primary    = (array) $this->_primary;
        $pkIdentity = $primary[(int) $this->_identity];

        /**
         * Special case for PostgreSQL: a SERIAL key implicitly uses a sequence
         * object whose name is "<table>_<column>_seq".
         */
        if ($this->_sequence === true && $this->_db instanceof Zend_Db_Adapter_Pdo_Pgsql) {
            $this->_sequence = $this->_db->quoteIdentifier("{$this->_name}_{$pkIdentity}_seq");
            if ($this->_schema) {
                $this->_sequence = $this->_db->quoteIdentifier($this->_schema) . '.' . $this->_sequence;
            }
        }
    }

    /**
     * @return Minder_Db_Adapter_Sysscreen
     */
    public function getAdapter()
    {
        return parent::getAdapter();
    }

    /**
     * @return array
     */
    protected function _getStaticWhere() {
        //todo:
        return array();
    }

    public function info($key = null)
    {
        $this->_setupPrimaryKey();

        $info = array(
            self::SCHEMA           => $this->_schema,
            self::NAME             => $this->_name,
            self::COLS             => $this->_getCols(),
            self::PRIMARY          => (array) $this->_primary,
            self::METADATA         => $this->_metadata,
            self::ROW_CLASS        => $this->getRowClass(),
            self::ROWSET_CLASS     => $this->getRowsetClass(),
            self::REFERENCE_MAP    => $this->_referenceMap,
            self::DEPENDENT_TABLES => $this->_dependentTables,
            self::SEQUENCE         => $this->_sequence,
            self::REAL_TABLES      => $this->_realTables,
            self::DEFAULT_ORDER_BY => $this->_defaultOrderBy,
            self::STATIC_WHERE     => $this->_staticConditions
        );

        if ($key === null) {
            return $info;
        }

        if (!array_key_exists($key, $info)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('There is no table information for the key "' . $key . '"');
        }

        return $info[$key];
    }


    public function select($selectMode = self::SELECT_FULL)
    {
        $select = new Minder_Db_SysScreenTable_Select($this);

        if ($selectMode & self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }

        if ($selectMode & self::SELECT_WITH_WHERE_PART) {
            foreach ($this->info(self::STATIC_WHERE) as $where)
                $select->where($where);
        }

        if ($selectMode & self::SELECT_WITH_ORDER_BY_PART) {
            $select->order($this->info(self::DEFAULT_ORDER_BY));
        }

        return $select;
    }

    protected function _getPrimaryKeyExpression($fieldName) {
        $columnMetadata = $this->_metadata[$fieldName];

        if ($columnMetadata['VIRTUAL'])
            return $columnMetadata['SSV_EXPRESSION'];

        return $this->_db->quoteTableAs($columnMetadata['TABLE_NAME'], null, true) . '.' . $this->_db->quoteIdentifier($columnMetadata['COLUMN_NAME'], true);
    }

    public function find()
    {
        $this->_setupPrimaryKey();
        $args = func_get_args();
        $keyNames = array_values((array) $this->_primary);

        if (count($args) < count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too few columns for the primary key");
        }

        if (count($args) > count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Too many columns for the primary key");
        }

        $whereList = array();
        $numberTerms = 0;
        foreach ($args as $keyPosition => $keyValues) {
            $keyValuesCount = count($keyValues);
            // Coerce the values to an array.
            // Don't simply typecast to array, because the values
            // might be Zend_Db_Expr objects.
            if (!is_array($keyValues)) {
                $keyValues = array($keyValues);
            }
            if ($numberTerms == 0) {
                $numberTerms = $keyValuesCount;
            } else if ($keyValuesCount != $numberTerms) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new Zend_Db_Table_Exception("Missing value(s) for the primary key");
            }
            $keyValues = array_values($keyValues);
            for ($i = 0; $i < $keyValuesCount; ++$i) {
                if (!isset($whereList[$i])) {
                    $whereList[$i] = array();
                }
                $whereList[$i][$keyPosition] = $keyValues[$i];
            }
        }

        $whereClause = null;
        if (count($whereList)) {
            $whereOrTerms = array();

            foreach ($whereList as $keyValueSets) {
                $whereAndTerms = array();
                foreach ($keyValueSets as $keyPosition => $keyValue) {
                    $type = $this->_metadata[$keyNames[$keyPosition]]['DATA_TYPE'];
                    $expression = $this->_getPrimaryKeyExpression($keyNames[$keyPosition]);
                    $whereAndTerms[] = $this->_db->quoteInto($expression . ' = ?', $keyValue, $type);
                }
                $whereOrTerms[] = '(' . implode(' AND ', $whereAndTerms) . ')';
            }
            $whereClause = '(' . implode(' OR ', $whereOrTerms) . ')';
        }

        // issue ZF-5775 (empty where clause should return empty rowset)
        if ($whereClause == null) {
            $rowsetClass = $this->getRowsetClass();
            if (!class_exists($rowsetClass)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($rowsetClass);
            }
            return new $rowsetClass(array('table' => $this, 'rowClass' => $this->getRowClass(), 'stored' => true));
        }

        return $this->fetchAll($whereClause);
    }

    /**
     * @param string $fieldIndex
     * @return array
     * @throws Exception
     */
    public function describeField($fieldIndex) {
        $metadata = $this->info(Zend_Db_Table::METADATA);

        if (!isset($metadata[$fieldIndex]))
            throw new Exception($fieldIndex . ' is not in ' . $this->info(Zend_Db_Table::NAME));

        return $metadata[$fieldIndex];
    }

    /**
     * @param string $fieldIndex
     * @return string
     */
    public function getRealFieldColumnName($fieldIndex) {
        $fieldMetadata = $this->describeField($fieldIndex);
        return isset($fieldMetadata['SSV_NAME']) ? $fieldMetadata['SSV_NAME'] : $fieldMetadata['COLUMN_NAME'];
    }

    /**
     * @param string $fieldIndex
     * @return string
     */
    public function getRealFieldTableName($fieldIndex) {
        $fieldMetadata = $this->describeField($fieldIndex);
        return isset($fieldMetadata['SSV_TABLE']) ? $fieldMetadata['SSV_TABLE'] : $fieldMetadata['TABLE_NAME'];
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function describeRealTable($tableName) {
        $dbTable = new Zend_Db_Table(array(Zend_Db_Table::NAME => $tableName));
        return $dbTable->info();
    }

    /**
     * @param $realFieldTableName
     * @param $realFieldColumnName
     * @return string
     */
    public function formatRealFieldIndex($realFieldTableName, $realFieldColumnName) {
        return $this->getAdapter()->formatRealFieldAlias($realFieldTableName, $realFieldColumnName);
    }
}