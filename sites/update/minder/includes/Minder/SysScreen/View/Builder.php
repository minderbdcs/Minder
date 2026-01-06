<?php

class Minder_SysScreen_View_Builder {
    protected $_colorTestBuilder;

    protected function _getViewTables($screenName) {
        $tableBuilder = new Minder_SysScreen_PartBuilder_Table($screenName);
        return $tableBuilder->build();
    }

    protected function _getSubViewScreenNames($tables) {
        return array_unique(
            array_filter(
                array_map(
                    function($table){
                        $parts = explode(':', $table['SST_TABLE']);

                        return count($parts) > 1 ? (string)$parts[1] : '';
                    },
                    $tables
                ),
                function($screenName){
                    return strlen($screenName) > 0;
                }
            )
        );
    }

    protected function _buildUnionSource($screenName, $tables, $subScreens) {
        if (count($subScreens) != count($tables)) {
            throw new Minder_SysScreen_View_BuilderException("Cannot mix sub screens and tables in Union Screen: $screenName.");
        }

        $result = new Minder_SysScreen_View_UnionSource($screenName);

        foreach ($subScreens as $subScreenName) {
            $result->addSubView($this->_buildViewSource($subScreenName));
        }

        return $result;
    }

    protected function _getPrimaryKeys($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_SRPrimaryKeys($screenName);
        return $builder->build();
    }

    protected function _getParameterFields($fields) {
        $viewBuilder = $this;

        return array_reduce(
            array_map(
                function($fieldDesc)use($viewBuilder){
                    $result = array();

                    if (isset($fieldDesc['SQL_PARAMS']))
                        $result = array_merge($result, $viewBuilder->getParamFields($fieldDesc['SQL_PARAMS']));

                    if (isset($fieldDesc['DEFAULT_SQL_PARAMS']))
                        $result = array_merge($result, $viewBuilder->getParamFields($fieldDesc['DEFAULT_SQL_PARAMS']));

                    return $result;
                },
                $fields
            ),
            function(&$result, $nextFields) {
                return array_merge($result, $nextFields);
            },
            array()
        );
    }

    protected function _getFields($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_ModelFields($screenName);
        $fields = $builder->build();

        $fields = array_merge($fields, $this->_getParameterFields($fields));

        return $fields;
    }

    protected function _fillTableParams($screenName, $tables) {
        $paramBuilder = new Minder_SysScreen_PartBuilder_TableParam($screenName);
        $tableParams = $paramBuilder->build();
        usort($tableParams, array($this, '_sortCallback'));

        foreach ($tableParams as $tableParamDesc) {
            foreach ($tables as &$table) {
                if ($table['SST_TABLE'] == $tableParamDesc['SST_TABLE']) {
                    $table['TABLE_PARAMS'][] = $tableParamDesc['SSP_NAME'];
                }
            }
        }

        return $tables;
    }

    protected function _getStaticConditions($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_StaticCond($screenName);
        $staticConditionsFields = $builder->build();

        return array_reduce(
            $staticConditionsFields,
            function(&$result, $fieldDescription){
                $result[$fieldDescription['SSV_EXPRESSION']] = array();
                return $result;
            },
            array()
        );
    }

    protected function _getColorFields($screenName, $fields) {
        $builder = new Minder_SysScreen_PartBuilder_Color($screenName);
        $colors = $builder->build();
        //now build color fields
        $testExprPattern = '{TEST_FIELD}';
        usort($colors, create_function('$a, $b', 'return $a[$a["ORDER_BY_FIELD_NAME"]] - $b[$b["ORDER_BY_FIELD_NAME"]];'));
        $colorTests = array();

        foreach ($colors as $colorDesc) {
            if (!isset($colorTests[$colorDesc['SSC_COLOUR_TEST_NAME']]))
                $colorTests[$colorDesc['SSC_COLOUR_TEST_NAME']] = array();
            $colorTests[$colorDesc['SSC_COLOUR_TEST_NAME']] = array_merge($colorTests[$colorDesc['SSC_COLOUR_TEST_NAME']], $this->_buildColorTest($colorDesc, $testExprPattern));
        }

        foreach ($colorTests as &$testDesc) {
            $tmpCondition = 'CASE ' . implode(' ', array_keys($testDesc)) . ' END';
            $tmpArgs      = array_reduce($testDesc, create_function('$res, $item', '$res = (is_array($res)) ? $res : array(); return array_merge($res, $item);'), null);
            $testDesc = array('TEST' => $tmpCondition, 'ARGS' => $tmpArgs);
        }

        $colorFields = array();
        foreach ($fields as $fieldDesc) {
            if (!isset($colorTests[$fieldDesc['SSV_COLOUR_TEST_NAME']]))
                continue;

            if (empty($fieldDesc['SSV_COLOUR_TEST_FIELD']))
                continue;

            $colorFields[] = array(
                'SSV_ALIAS'      => $fieldDesc['COLOR_FIELD_ALIAS'],
                'SSV_EXPRESSION' => str_ireplace($testExprPattern, $fieldDesc['SSV_COLOUR_TEST_FIELD'], $colorTests[$fieldDesc['SSV_COLOUR_TEST_NAME']]['TEST']),
                'ARGS'       => $colorTests[$fieldDesc['SSV_COLOUR_TEST_NAME']]['ARGS'],
                'EXPRESSION_PARAMS' => array()
            );
        }

        return $colorFields;
    }

    protected function _getMainTable($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_SysScreen($screenName);
        $screens = $builder->build();

        if (!empty($screens)) {
            $screen = current($screens);
            return $screen['SS_TYPE'];
        }

        return null;
    }

    protected function _getSearchFields($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_SEFields($screenName);
        return $builder->build();
    }

    protected function _buildScreenSource($screenName, $tables) {
        $tables             = $this->_fillTableParams($screenName, $tables);
        $primaryKeys        = $this->_getPrimaryKeys($screenName);
        $fields             = $this->_getFields($screenName);
        $staticConditions   = $this->_getStaticConditions($screenName);
        $colorFields        = $this->_getColorFields($screenName, $fields);
        $mainTable          = $this->_getMainTable($screenName);
        $searchFields       = $this->_getSearchFields($screenName);
        $order              = $this->_getOrder($screenName);

        if (empty($primaryKeys)) {
            throw new Minder_SysScreen_View_BuilderException("System screen $screenName has no primary keys.");
        }

        if (empty($fields)) {
            throw new Minder_SysScreen_View_BuilderException("System screen $screenName has no fields.");
        }

        $result = new Minder_SysScreen_View_ScreenSource($screenName);

        $result->setTables($tables);
        $result->setPrimaryKeys($primaryKeys);
        $result->setFields($fields);
        $result->setStaticConditions($staticConditions);
        $result->setColorFields($colorFields);
        $result->setSearchFields($searchFields);
        $result->setOrder($order);

        if (!empty($mainTable)) {
            $result->setMainTable($mainTable);
        }

        return $result;
    }

    protected function _buildViewSource($screenName) {
        $tables = $this->_getViewTables($screenName);

        if (empty($tables)) {
            throw new Minder_SysScreen_View_BuilderException("System screen $screenName has no tables.");
        }

        $subViewScreenNames = $this->_getSubViewScreenNames($tables);

        if (count($subViewScreenNames) > 0) {
            $result = $this->_buildUnionSource($screenName, $tables, $subViewScreenNames);
        } else {
            $result = $this->_buildScreenSource($screenName, $tables);
        }

        return $result;
    }

    public function buildSysScreenModel($screenName, $modelPrototype = null) {
        $result = is_null($modelPrototype) ? new Minder_SysScreen_View() : clone $modelPrototype;
        $source = $this->_buildViewSource($screenName);
        $source->init();

        $result->setViewSource($source);

        return $result;
    }

    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }

    public function getParamFields($sqlParams = array()) {
        $paramFields = array();
        foreach ($sqlParams as $paramDesc) {
            $paramFields[$paramDesc['ALIAS']] = array(
                'RECORD_ID'            => $paramDesc['ALIAS'],
                'SSV_NAME'             => $paramDesc['NAME'],
                'SSV_TABLE'            => $paramDesc['TABLE'],
                'SSV_INPUT_METHOD'     => 'NONE',
                'SSV_ALIAS'            => $paramDesc['ALIAS'],
                'SSV_COLOUR_TEST_NAME' => ''
            );
        }

        return $paramFields;
    }

    protected function _getColorTestBuilder() {
        if (empty($this->_colorTestBuilder)) {
            $this->_colorTestBuilder = new Minder_SysScreen_ColorTestBuilder();
        }

        return $this->_colorTestBuilder;
    }

    protected function _buildColorTest($testDesc, $testExprPattern) {
        return $this->_getColorTestBuilder()->buildColorTest($testDesc, $testExprPattern);
    }

    protected function _getOrder($screenName) {
        $builder = new Minder_SysScreen_PartBuilder_Order($screenName);
        return $builder->build();
    }
}