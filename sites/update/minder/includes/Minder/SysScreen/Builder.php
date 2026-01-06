<?php
class Minder_SysScreen_Builder
{
    const DEFAULT_SELECTION_MODE = 'all';

    protected $minder;
    protected $_colorTestBuilder;

    public function __construct() {
        $this->minder = Minder::getInstance();
    }

    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }

    static public function dropScreenDescriptionsCache() {
        Minder_SysScreen_PartBuilder::dropScreenDescriptionsCache();
    }

    /**
     * @static
     * @return array
     */
    static public function getSysScreenTableList() {
        return array(
            'SYS_SCREEN',
            'SYS_SCREEN_ACTION',
            'SYS_SCREEN_BUTTON',
            'SYS_SCREEN_COLOUR',
            'SYS_SCREEN_ORDER',
            'SYS_SCREEN_TAB',
            'SYS_SCREEN_TABLE',
            'SYS_SCREEN_VAR',
            'SYS_SCREEN_PROCEDURE',
            'SYS_SCREEN_TRANSACTION',
            'SYS_MENU',
            'COMPANY',
            'ACCESS_COMPANY'
        );
    }

    public function getScreensOrder($screenNames) {
        function sortScreens($a, $b) {
            return $a['order'] - $b['order'];
        }

        $screenOrder = array();

        foreach ($screenNames as $screenName) {
            if (null !== ($tmpOrder = $this->getSysScreenOrder($screenName)))
                $screenOrder[] = array('name' => $screenName, 'order' => $tmpOrder);
        }

        usort($screenOrder, 'sortScreens');

        return $screenOrder;
    }

    /**
    * Create or fill existed SYS SCREEN model.
    * 
    * @param string                 $ssName   - screen name
    * @param Minder_SysScreen_Model $newModel - exested screen model
    * 
    * @return Minder_SysScreen_Model
    * 
    * @throws Minder_SysScreen_Builder_Exception
    */
    public function buildSysScreenModel($ssName, &$newModel = null) {
        $ssRealName = $this->_getSSRealName($ssName);
        $sysScreen = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($ssRealName));
        $fields = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_ModelFields($ssRealName));
        $PKeys  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SRPrimaryKeys($ssRealName));

        $tables = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Table($ssRealName));
        $tableParams = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_TableParam($ssRealName));
        $order  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Order($ssRealName));
        $colors = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Color($ssRealName));
        $group  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Group($ssRealName));
        $staticConditionsFields = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_StaticCond($ssRealName));
        
        //make some checks for data integrity
        if (count($PKeys) < 1) 
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no primary keys.");
            
        if (count($fields) < 1)
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no fields.");
        
        if (count($tables) < 1)
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no tables.");

        $tableMap = array();
        foreach ($tables as $recordId => $tableDescription) {
            if (!isset($tableMap[$tableDescription['SST_TABLE']]))
                $tableMap[$tableDescription['SST_TABLE']] = array();
            $tableMap[$tableDescription['SST_TABLE']][] = $recordId;
        }

        usort($tableParams, array($this, '_sortCallback'));

        foreach ($tableParams as $tableParamDesc) {
            if (isset($tableMap[$tableParamDesc['SST_TABLE']]))
                foreach ($tableMap[$tableParamDesc['SST_TABLE']] as $tableRecordId)
                    $tables[$tableRecordId]['TABLE_PARAMS'][] = $tableParamDesc['SSP_NAME'];
        }

        //add fields for parametric queryes
        $paramFields = array();
        foreach ($fields as $fieldDesc) {
            if (isset($fieldDesc['SQL_PARAMS']))
                $paramFields = array_merge($paramFields, $this->getParamFields($fieldDesc['SQL_PARAMS']));

            if (isset($fieldDesc['DEFAULT_SQL_PARAMS']))
                $paramFields = array_merge($paramFields, $this->getParamFields($fieldDesc['DEFAULT_SQL_PARAMS']));
        }
        $fields = array_merge($fields, $paramFields);
            
        $requiredTables = array();
        foreach ($fields as $fieldDescription) {
            if (!empty($fieldDescription['SSV_TABLE']))
                $requiredTables[strtoupper($fieldDescription['SSV_TABLE'])] = $fieldDescription['SSV_TABLE'];
        }
        
        foreach ($tables as $tableDescription) {
            if (isset($requiredTables[strtoupper($tableDescription['SST_ALIAS'])]))
                unset($requiredTables[strtoupper($tableDescription['SST_ALIAS'])]);
        }
        
        if (count($requiredTables)) {
            throw new Minder_SysScreen_Builder_Exception("Error building screen $ssName. Some tables are required for SYS_SCREEN_VAR: ('" . implode($requiredTables) . "'). But was not found in SYS_SCREEN_TABLE.");
        }
        
        //create static conditions array
        $tmpStaticConditions = array();
        foreach ($staticConditionsFields as $fieldDesc) {
            if (!empty($fieldDesc['SSV_EXPRESSION']))
                $tmpStaticConditions[$fieldDesc['SSV_EXPRESSION']] = array();
        }
        
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
            
        if (is_null($newModel))
            $newModel = new Minder_SysScreen_Model();
            
        $newModel->fields      = $fields;
        $newModel->tables      = $tables;
        $newModel->pkeys       = $PKeys;
        $newModel->order       = $order;
        $newModel->group       = $group;
        $newModel->colorFields = $colorFields;

        if (!empty($sysScreen)) {
            $sysScreen = current($sysScreen);
            $newModel->mainTable   = $sysScreen['SS_TYPE'];
        }

        if (count($tmpStaticConditions) > 0)
            $newModel->addStaticConditions($tmpStaticConditions); //add static conditions

        $newModel->init();

        return $newModel;
    }

    public function getMasterSysScreens($ssName) {
        $result = array();
        $relations = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_MasterRelations($this->_getSSRealName($ssName)));

        foreach ($relations as $relationDescription) {
            $result[$relationDescription['MASTER_SS_NAME']] = (isset($result[$relationDescription['MASTER_SS_NAME']])) ? $result[$relationDescription['MASTER_SS_NAME']] : array();
            $result[$relationDescription['MASTER_SS_NAME']][] = $relationDescription;
        }

        return $result;
    }

    public function getSlaveSysScreens($ssName) {
        $result = array();
        $relations = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SlaveRelations($this->_getSSRealName($ssName)));

        foreach ($relations as $relationDescription) {
            $result[$relationDescription['SLAVE_SS_NAME']] = (isset($result[$relationDescription['SLAVE_SS_NAME']])) ? $result[$relationDescription['SLAVE_SS_NAME']] : array();
            $result[$relationDescription['SLAVE_SS_NAME']][] = $relationDescription;
        }

        return $result;
    }

    public function getSysScreenTitle($ssName) {
        $options = new Minder2_Options();
        return $options->getScreenTitle($ssName);
    }

    public function getSysScreenDescription($ssName) {
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($this->_getSSRealName($ssName)));
        return current($sysScreenDesc);
    }

    public function isSysScreenDefined($ssName) {
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($this->_getSSRealName($ssName)));
        return !empty($sysScreenDesc);
    }

    /**
     * @param string $ssName
     * @return integer | null - if screen not defined
     */
    public function getSysScreenDefaultSelectionMode($ssName) {
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($this->_getSSRealName($ssName)));
        if (empty($sysScreenDesc)) return static::DEFAULT_SELECTION_MODE;
        $sysScreenDesc = current($sysScreenDesc);

        return (strtoupper($sysScreenDesc['SS_MULTI_ROWS']) == 'F') ? 'one' : 'all';
    }

    /**
     * @param string $ssName
     * @return integer | null - if screen not defined
     */
    public function getSysScreenOrder($ssName) {
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($this->_getSSRealName($ssName)));
        if (empty($sysScreenDesc)) return null;
        $sysScreenDesc = current($sysScreenDesc);

        return $sysScreenDesc['SS_SEQUENCE'];
    }

    /**
     * @param  $ssName
     * @return screen
     * @deprecated
     */
    protected function _getSSRealName($ssName) {
//        $tmpDescriptions  = array();
//
//        if (Zend_Registry::isRegistered('SCREEN_DESCRIPTIONS')) {
//            $tmpDescriptions = Zend_Registry::get('SCREEN_DESCRIPTIONS');
//        }
//
//        if (isset($tmpDescriptions[$ssName])) {
//            $ssRealName                                          = $tmpDescriptions[$ssName]['REAL_NAME'];
//        } else {
//            $tmpDescriptions[$ssName]['REAL_NAME'] = $ssRealName = $this->minder->getScreenRealName($ssName);
//        }
//
//        Zend_Registry::set('SCREEN_DESCRIPTIONS', $tmpDescriptions);
        return $ssName;
    }

    /**
     * @param Minder_SysScreen_PartBuilder $partBuilder
     * @return array
     */
    public  function getScreenPartDesc($partBuilder) {
        return $partBuilder->build();
    }
    
    public function buildSysScreenSearchResult($ssName, $required = false) {
        $ssRealName = $this->_getSSRealName($ssName);
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($ssRealName));
        $fields  = array();
        $tabs    = array();
        $colors  = array();
        $actions = array();

        if (empty($sysScreenDesc)) {
            if ($required)
                throw new Minder_SysScreen_Builder_Exception('SYS_SCREEN "' . $ssName . '" is required but was not defined in SYS_SCREEN table.');
            else 
                return array(
                    $fields, 
                    $tabs, 
                    $colors, 
                    $actions, 
                    $sysScreenDesc,
                    'fields' => $fields, 
                    'tabs' => $tabs, 
                    'colors' => $colors, 
                    'actions' => $actions,
                    'sys_screen_desc' => $sysScreenDesc
                );
        }
        
        
        $fields  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SRFields($ssRealName));
        $tabs    = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SRTab($ssRealName));
        $colors  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Color($ssRealName));
        $actions = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Action($ssRealName));
        
        if (count($fields) < 1)
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no fields.");
            
        if (count($tabs) < 1) {
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no tabs.");
        }
            
            
        return array($fields, $tabs, $colors, $actions, 'fields' => $fields, 'tabs' => $tabs, 'colors' => $colors, 'actions' => $actions);
    }

    public function buildScreenButtons($ssName) {
        $buttons = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Button($this->_getSSRealName($ssName)));
        return array($buttons, 'buttons' => $buttons);
    }
    
    public function buildSysScreenSearchFields($ssName, $required = false) {
        $ssRealName    = $this->_getSSRealName($ssName);
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($ssRealName));
        $searchFields  = array();
        $actions       = array();
        $tabs          = array();
        $giFields      = array();
        
        if (empty($sysScreenDesc)) {
            if ($required)
                throw new Minder_SysScreen_Builder_Exception('SYS_SCREEN "' . $ssName . '" is required but was not defined in SYS_SCREEN table.');
            else 
                return array(
                    $searchFields, 
                    $actions, 
                    $tabs, 
                    $giFields,
                    $sysScreenDesc, 
                    'search_fields'   => $searchFields, 
                    'actions'         => $actions, 
                    'tabs'            => $tabs, 
                    'gi_fields'       => $giFields,
                    'sys_screen_desc' => $sysScreenDesc
                ); //no screen description found, return empty descriptions
        }
        
        $searchFields  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SEFields($ssRealName));
        $actions       = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Action($ssRealName));
        $tabs          = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SETab($ssRealName));
        foreach ($searchFields as $key => $fieldDesc) {
            if ($fieldDesc['SSV_INPUT_METHOD'] == 'GI') {
                
                if (empty($fieldDesc['SSV_NAME']))
                    throw new Minder_SysScreen_Builder_Exception("You should define SSV_NAME for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
                
                if (empty($fieldDesc['SSV_EXPRESSION']))
                    throw new Minder_SysScreen_Builder_Exception("You should define SSV_EXPRESSION for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
                
                unset($searchFields[$key]);
                
                $giFields[$key] = $fieldDesc;
            }
        }
        
        return array(
            $searchFields, 
            $actions, 
            $tabs, 
            $giFields,
            $sysScreenDesc, 
            'search_fields'   => $searchFields, 
            'actions'         => $actions, 
            'tabs'            => $tabs, 
            'gi_fields'       => $giFields,
            'sys_screen_desc' => $sysScreenDesc
        );
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
    
    protected function getParamFields($sqlParams = array()) {
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

    public function buildSysScreenModelFileSystem($ssName, &$newModel = null) {
        $ssRealName = $this->_getSSRealName($ssName);
        $sysScreen = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($ssRealName));
        $fields = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_ModelFields($ssRealName));
        $PKeys  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SRPrimaryKeys($ssRealName));

        if (empty($PKeys))
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no primary keys.");

        if (empty($sysScreen))
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName defined uncorrect.");

        if (count($fields) < 1)
            throw new Minder_SysScreen_Builder_Exception("System screen $ssName has no fields.");

        if (is_null($newModel))
            $newModel = new Minder_SysScreen_Model_FileSystem();

        $newModel->fields = $fields;
        $newModel->pkeys  = $PKeys;

        return $newModel;
    }
}
