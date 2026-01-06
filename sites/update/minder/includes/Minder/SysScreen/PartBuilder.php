<?php

abstract class Minder_SysScreen_PartBuilder {
    protected $_ssRealName = '';

    protected $_partName = '';
    protected $_tableName = '';
    protected $_orderByFieldName = '';

    protected $_staticLimitsMask = 15;
    protected $_staticLimitsUserExpression    = '';
    protected $_staticLimitsDeviceExpression  = '';
    protected $_staticLimitsWhExpression      = 'WH_ID';
    protected $_staticLimitsCompanyExpression = 'COMPANY_ID';

    protected $minder = null;
    protected $_userCategory = null;
    /**
     * @var Minder_SysScreen_VarianceManager
     */
    protected $_varianceManager;

    const WH_LIMIT_MASK        = 8;
    const COMPANY_LIMIT_MASK   = 4;
    const DEVICE_LIMIT_MASK    = 2;
    const USER_TYPE_LIMIT_MASK = 1;

    const SCREEN_DESCRIPTIONS_CONTAINER_NAME = 'SCREEN_DESCRIPTIONS';
    const SCREEN_DESCRIPTIONS_SESSION_INSTANCE = 'SCREEN_DESCRIPTIONS_SESSION';
    const INHERITANCE_SETTINGS = 'INHERITANCE_SETTINGS';

    public function __construct($ssName) {
        $this->minder      = Minder::getInstance();
        $this->_setSsName($ssName);
    }

    protected function _setSsName($ssName) {
        $this->_ssRealName = $this->_getSsRealName($ssName);
    }

    protected function _getSsRealName($ssName) {
        $tmpDescriptions  = $this->_getSavedScreensDescriptions();

        if (isset($tmpDescriptions[$ssName])) {
            $ssRealName                                          = $tmpDescriptions[$ssName]['REAL_NAME'];
        } else {
            $tmpDescriptions[$ssName] = array();
            $tmpDescriptions[$ssName]['REAL_NAME'] = $ssRealName = $this->minder->getScreenRealName($ssName);
        }

//        Zend_Registry::set(self::SCREEN_DESCRIPTIONS_CONTAINER_NAME, $tmpDescriptions);
        $session = self::_getSessionInstance();
        $session->sysScreenDescriptions = $tmpDescriptions;
        return $ssRealName;
    }

    static protected function _getSessionInstance() {
        if (Zend_Registry::isRegistered(self::SCREEN_DESCRIPTIONS_SESSION_INSTANCE))
            return Zend_Registry::get(self::SCREEN_DESCRIPTIONS_SESSION_INSTANCE);

        $session = new Zend_Session_Namespace(self::SCREEN_DESCRIPTIONS_SESSION_INSTANCE, true);
        Zend_Registry::set(self::SCREEN_DESCRIPTIONS_SESSION_INSTANCE, $session);
        return $session;
    }

    static public function dropScreenDescriptionsCache() {
        $session = self::_getSessionInstance();
        if (isset($session->sysScreenDescriptions))
            unset($session->sysScreenDescriptions);
    }

    /**
     * @return array
     */
    protected function _getSavedScreensDescriptions() {
        $emptyDescriptions  = array(
            '_timeout' => 300, //5 minutes
            '_started' => time()
        );
        $session = self::_getSessionInstance();
        if (isset($session->sysScreenDescriptions))
            $savedDescription = $session->sysScreenDescriptions;
        else
            return $emptyDescriptions;

        if (!isset($savedDescription['_timeout']) || !isset($savedDescription['_started']))
            return $emptyDescriptions;

        if ((time() - $savedDescription['_started']) > $savedDescription['_timeout'])
            return $emptyDescriptions;

//        if (Zend_Registry::isRegistered(self::SCREEN_DESCRIPTIONS_CONTAINER_NAME)) {
//            $tmpDescriptions = Zend_Registry::get(self::SCREEN_DESCRIPTIONS_CONTAINER_NAME);
//        }

        return $savedDescription;
    }

    /**
     * @return array
     */
    protected function _getSavedSysScreenDescription() {
        $tmpDescriptions  = $this->_getSavedScreensDescriptions();
        $screenDesription = array();

        if (isset($tmpDescriptions[$this->_ssRealName]))
            $screenDesription = $tmpDescriptions[$this->_ssRealName];

        return $screenDesription;
    }

    /**
     * @param array $sysScreenDescription
     * @return void
     */
    protected function _saveSysScreenDescription($sysScreenDescription) {
        $tmpDescriptions  = $this->_getSavedScreensDescriptions();
        $tmpDescriptions[$this->_ssRealName] = $sysScreenDescription;
//        Zend_Registry::set(self::SCREEN_DESCRIPTIONS_CONTAINER_NAME, $tmpDescriptions);
        $session = self::_getSessionInstance();
        $session->sysScreenDescriptions = $tmpDescriptions;
    }

    /**
     * @return array
     */
    public function build() {
        $sysScreenDescription = $this->_getSavedSysScreenDescription();
        $partDescription = array();

        if (isset($sysScreenDescription[$this->_partName])) {
            $partDescription = $sysScreenDescription[$this->_partName];
        } else {
            if ($this->_hasBaseScreen()) {
                $partDescription = $this->_getBaseResults();
            }

            $partDescription = array_merge($partDescription, $this->_doBuild());
        }

        $sysScreenDescription[$this->_partName] = $partDescription;
        $this->_saveSysScreenDescription($sysScreenDescription);

        return $partDescription;
    }

    /**
     * @return string
     */
    protected function _getUserCompanyId() {
        return Minder2_Environment::getCurrentUser()->COMPANY_ID;
    }

    /**
     * Always returns one COMPANY_ID
     * See: http://binary-studio.office-on-the.net/issues/6388#note-7
     *
     * @return string
     */
    protected function _getCompanyLimit() {
        if (($this->minder->limitCompany != 'all') && (!empty($this->minder->limitCompany)))
            return $this->minder->limitCompany;

        $userCompanyId = $this->_getUserCompanyId();

        if (!empty($userCompanyId))
            return $userCompanyId;

        return $this->minder->defaultControlValues['COMPANY_ID'];
    }

    /**
    * Builds werehouse and company limits for builder queries.
    * Cann't use Minder::getWarehouseAndCompanyLimit() as it adds strict limits,
    * while all SYS_SCREEN table allow any user to see records with WH_ID = NULL and COMPANY_ID = NULL
    *
    * @param integer $mask      - limit bit mask:   1 bit - USER_TYPE limit
    *                                               2 bit - DEVICE_TYPE limit
    *                                               3 bit - COMPANY_ID limit
    *                                               4 bit - WH_ID limit
    *                             Example: if one want to get only USER_TYPE and WH_ID limits, he should set
    *                                      1-st and 4-th bits, mask will looks like 1001 or in decimail 9.
    * @param array   $prefixes  - expressiond for each limit fields. For example, for WH_ID field it can be SYS_SCREEN_VAR.WH_ID.
    *
    * @return array - conditions array(CONDITION => array(CONDITION_PARAMS))
    */
    protected function _getStaticLimits() {
        $filter = array();

        //there is a question about company limits for SYS_SCREEN tables
        //so for now always disable COMPANY limit
//        $mask       = $this->_staticLimitsMask & 11;
        $mask       = $this->_staticLimitsMask;

        if (($mask & self::DEVICE_LIMIT_MASK) == self::DEVICE_LIMIT_MASK) {
            $myDeviceType = $this->_getCurrentDeviceType();

            $filter['(' . $this->_staticLimitsDeviceExpression . " = ? OR " . $this->_staticLimitsDeviceExpression . " = ? OR " . $this->_staticLimitsDeviceExpression . ' IS NULL)'] = array($myDeviceType, '');
        }

        if (($mask & self::COMPANY_LIMIT_MASK) == self::COMPANY_LIMIT_MASK) {
            $filter['(' . $this->_staticLimitsCompanyExpression . " = ? OR " . $this->_staticLimitsCompanyExpression . " = '' OR " . $this->_staticLimitsCompanyExpression . ' IS NULL)'] = array($this->_getCompanyLimit());
        }

        if (($mask & self::WH_LIMIT_MASK) == self::WH_LIMIT_MASK) {
            $filter['(' . $this->_staticLimitsWhExpression . " = ? OR " . $this->_staticLimitsWhExpression . " = ? OR " . $this->_staticLimitsWhExpression . ' IS NULL)'] = array($this->minder->whId, '');
        }

        return $filter;
    }

    protected function _selectDbRows() {
        $staticLimits         = array_merge($this->_getPartFilters(), $this->_getStaticLimits());
        $filters              = array_keys($staticLimits);
        $conditionString      = '';
        if (count($filters) > 0) {
            $conditionString  = ' WHERE ' . implode(' AND ', $filters);
        }

        $staticArgs           = array_reduce($staticLimits, array($this, '_auxMergeFunction'), null);
        $staticArgs           = empty($staticArgs) ? array() : $staticArgs;

        $sql = '
            SELECT
                *
            FROM
            ' . $this->_tableName . '
            ' . $conditionString . '
        ';
        $args         = array_merge(array($sql), $staticArgs);
        return call_user_func_array(array($this->minder, 'fetchAllAssoc'), $args);
    }

    /**
     * @return string
     */
    protected function _getUserCategory() {
        return Minder2_Environment::getCurrentUser()->USER_CATEGORY;
    }

    protected function _getCurrentDeviceType() {
        return (string)Minder2_Environment::getCurrentDevice()->DEVICE_TYPE;
    }

    /**
     * @param array $row
     * @return bool
     */
    protected function _UserCategoryIsValid($row) {
        $userCategory = $this->_getUserCategory();
        if (!empty($row[$this->_staticLimitsUserExpression])) {
            //restricted field so check rights
            $validUserCategories       = explode('|', $row[$this->_staticLimitsUserExpression]);
            if ($this->minder->userId != 'Admin' && (!in_array($userCategory, $validUserCategories) || empty($userCategory))) {
                return false;
            }
        }

        return true;
    }

    protected function _getBaseScreen() {
        return $this->_getVarianceManager()->getAll()->getScreenVariance($this->_ssRealName)->primaryScreen;
    }

    protected function _hasBaseScreen() {
        return $this->_isExpandable() && ($this->_ssRealName !== $this->_getBaseScreen());
    }

    protected function _getBaseResults() {
        /**
         * @var Minder_SysScreen_PartBuilder $builder
         */
        $builder = new static($this->_getBaseScreen());
        return $builder->build();
    }

    /**
     * @return array
     */
    protected function _doBuild()
    {
        $result = array();

        foreach ($this->_selectDbRows() as $row) {

            if (!$this->_UserCategoryIsValid($row)) {
                continue;
            }

            $result[$row['RECORD_ID']]  = $this->_prepareDbRow($row);
        }

        return $result;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function _prepareDbRow($row) {
        $row['ORDER_BY_FIELD_NAME'] = $this->_orderByFieldName;
        return $row;
    }

    abstract protected function _getPartFilters();

    protected function _auxMergeFunction($data, $item) {
        $data = is_array($data) ? $data : array();
        return array_merge($data, array_values($item));
    }

    protected function prepareSql($sql) {
        $parameters = array();

        $regExp = '/\{(\w+|)\.(\w+)\}/';
        $matches = array();

        $matchResult = preg_match_all($regExp, $sql, $matches, PREG_SET_ORDER);

        if (false === $matchResult)
            throw new Minder_SysScreen_Builder_Exception('Error parsing query "' . $sql . '".');

        if ($matchResult > 0) {
            foreach($matches as $val) {
                $parameters[] = array(
                    'PARAM' => $val[0],
                    'TABLE' => $val[1],
                    'NAME'  => $val[2],
                    'ALIAS' => strtoupper(uniqid('PARAM_'))
                );
            }
        }

        return $parameters;
    }

    protected function _isExpandable() {
        return false;
    }

    /**
     * @return Minder_SysScreen_VarianceManager
     */
    protected function _getVarianceManager()
    {
        if (empty($this->_varianceManager)) {
            $this->_varianceManager = new Minder_SysScreen_VarianceManager();
        }
        return $this->_varianceManager;
    }

    /**
     * @return Minder_SysScreen_InheritanceSettings
     */
    protected function _getInheritanceSettings() {
        $storedDescription = $this->_getSavedSysScreenDescription();

        if (isset($storedDescription[static::INHERITANCE_SETTINGS])) {
            $settings = $storedDescription[static::INHERITANCE_SETTINGS];
        } else {
            $settings = $storedDescription[static::INHERITANCE_SETTINGS] = $this->_getVarianceManager()->getInheritanceSettings($this->_ssRealName);
            $this->_saveSysScreenDescription($storedDescription);
        }

        return $settings;
    }
}