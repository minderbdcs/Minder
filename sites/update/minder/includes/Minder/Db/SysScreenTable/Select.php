<?php

class Minder_Db_SysScreenTable_Select extends Zend_Db_Table_Select {
    protected $_integrityCheck = false; //disable until fix problems with assemble() method

    protected function _tableSorter($tableA, $tableB) {
        return intval($tableA['SST_SEQUENCE']) - intval($tableB['SST_SEQUENCE']);
    }

    protected function _renderVirtualFromPart($table, $joinIsApplicable) {
        $tmpTable = new Minder_Db_SysScreenTable($table['tableName']);
        $realTables = $tmpTable->info(Minder_Db_SysScreenTable::REAL_TABLES);

        usort($realTables, array($this, '_tableSorter'));

        $result = array();

        foreach ($realTables as $tableDescription) {
            $result[] = $this->_renderRealFromPart($tableDescription['SST_ALIAS'],
                                                   array(
                                                        'tableName' => $tableDescription['SST_TABLE'],
                                                        'joinType'  => $tableDescription['SST_JOIN'],
                                                        'schema'    => null,
                                                        'joinCondition' => $tableDescription['SST_VIA']
                                                   ),
                                                   $joinIsApplicable || (!empty($result))
            );
        }

        return $result;
    }

    protected function _renderRealFromPart($correlationName, $table, $joinIsApplicable) {
        $tmp = '';
        $joinType = ($table['joinType'] == self::FROM) ? self::INNER_JOIN : $table['joinType'];

        // Add join clause (if applicable)
        if ($joinIsApplicable) {
            $tmp .= ' ' . strtoupper($joinType) . ' ';
        }

        $tmp .= $this->_getQuotedSchema($table['schema']);
        $tmp .= $this->_getQuotedTable($table['tableName'], $correlationName);
        // Add join conditions (if applicable)
        if ($joinIsApplicable && !empty($table['joinCondition'])) {
            $tmp .= ' ' . self::SQL_ON . ' ' . $table['joinCondition'];
        }
        return $tmp;
    }

    protected function _renderFrom($sql)
    {
        /*
         * If no table specified, use RDBMS-dependent solution
         * for table-less query.  e.g. DUAL in Oracle.
         */
        if (empty($this->_parts[self::FROM])) {
            $this->_parts[self::FROM] = $this->_getDummyTable();
        }

        $from = array();

        foreach ($this->_parts[self::FROM] as $correlationName => $table) {
            if (Minder_Db_Adapter_Sysscreen::schemaIsSysScreenSchema($table['schema'])) {
                $tmp = (array)$this->_renderVirtualFromPart($table, !empty($from));
            } else {
                $tmp = (array)$this->_renderRealFromPart($correlationName, $table, !empty($from));
            }

            // Add the table name and condition add to the list
            $from += $tmp;
        }

        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . self::SQL_FROM . ' ' . implode("\n", $from);
        }

        return $sql;
    }

    protected function _doWildcardConvert($metadata) {
        $result = array();

        foreach ($metadata as $columnMetadata) {
            if ($columnMetadata['VIRTUAL'])
                $result += $this->_doConvertFromVirtualColumn('', $columnMetadata);
        }

        return $result;
    }

    protected function _doConvertFromVirtualColumn($alias, $columnMetadata) {
        $alias = (empty($alias) ? $columnMetadata['COLUMN_NAME'] : $alias);
        $expression = $columnMetadata['SSV_EXPRESSION'] . ' AS ' . $alias;
        return array($alias => $expression);
    }

    protected function _doConvertFromRealColumn($alias, $columnMetadata) {
        /**
         * @var Minder_Db_Adapter_Sysscreen $adapter
         */
        $adapter = $this->getAdapter();
        $alias = (empty($alias) ? $adapter->formatRealFieldAlias($columnMetadata['CORRELATION'], $columnMetadata['COLUMN_NAME']) : $alias);
        $columnName = $columnMetadata['CORRELATION'] . '.' . $columnMetadata['COLUMN_NAME'];
        return array($alias => $columnName . ' AS ' . $alias);
    }

    protected function _convertVirtualCol($correlationName, $column, $alias) {
        $table = $this->_parts[self::FROM][$correlationName];
        $dbTable = new Minder_Db_SysScreenTable(array('name' => $table['tableName'], 'schema' => $table['schema']));
        $metadata = $dbTable->info('metadata');

        if ($column == self::SQL_WILDCARD)
            return $this->_doWildcardConvert($metadata);


        if (!isset($metadata[$column]))
            throw new Minder_Db_SysScreenTable_Select_Exception('Column "' . $column . '" does not belong to "' . $correlationName . '" correlation.');

        if ($metadata[$column]['VIRTUAL'])
            return $this->_doConvertFromVirtualColumn($alias, $metadata[$column]);

        return $this->_doConvertFromRealColumn($alias, $metadata[$column]);
    }

    protected function _renderColumns($sql)
    {
        if (!count($this->_parts[self::COLUMNS])) {
            return null;
        }

        $columns = array();
        foreach ($this->_parts[self::COLUMNS] as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($column instanceof Zend_Db_Expr) {
                $columns[] = $this->_adapter->quoteColumnAs($column, $alias, true);
            } else {
                $columns += (array)$this->_convertVirtualCol($correlationName, $column, $alias);
            }
        }

        return $sql . ' ' . implode(', ', $columns);
    }


    /**
     * Adds to the internal table-to-column mapping array.
     *
     * @param string $correlationName
     * @param  array|string $cols The list of columns; preferably as
     * an array, but possibly as a string containing one column.
     * @param bool|string $afterCorrelationName
     * @return void
     */
    protected function _tableCols($correlationName, $cols, $afterCorrelationName = null)
    {
        if (!is_array($cols)) {
            $cols = array($cols);
        }

        if ($correlationName == null) {
            $correlationName = '';
        }

        $columnValues = array();

        foreach (array_filter($cols) as $alias => $col) {
            $currentCorrelationName = $correlationName;
            if (is_string($col)) {
                // Check for a column matching "<column> AS <alias>" and extract the alias name
                if (preg_match('/^(.+)\s+' . self::SQL_AS . '\s+(.+)$/i', $col, $m)) {
                    $col = $m[1];
                    $alias = $m[2];
                }
                // Check for columns that look like functions and convert to Zend_Db_Expr
                if (preg_match('/\(.*\)/', $col)) {
                    $col = new Zend_Db_Expr($col);
                }
            }
            $columnValues[] = array($currentCorrelationName, $col, is_string($alias) ? $alias : null);
        }

        if ($columnValues) {

            // should we attempt to prepend or insert these values?
            if ($afterCorrelationName === true || is_string($afterCorrelationName)) {
                $tmpColumns = $this->_parts[self::COLUMNS];
                $this->_parts[self::COLUMNS] = array();
            } else {
                $tmpColumns = array();
            }

            // find the correlation name to insert after
            if (is_string($afterCorrelationName)) {
                while ($tmpColumns) {
                    $this->_parts[self::COLUMNS][] = $currentColumn = array_shift($tmpColumns);
                    if ($currentColumn[0] == $afterCorrelationName) {
                        break;
                    }
                }
            }

            // apply current values to current stack
            foreach ($columnValues as $columnValue) {
                array_push($this->_parts[self::COLUMNS], $columnValue);
            }

            // finish ensuring that all previous values are applied (if they exist)
            while ($tmpColumns) {
                array_push($this->_parts[self::COLUMNS], array_shift($tmpColumns));
            }
        }
    }

}