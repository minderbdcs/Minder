<?php

class Minder2_Options {
    const DEFAULT_COST_CENTER_CAPTION = 'Cost Center';

    protected $_minder = null;

    protected function _getMinder() {
        if (is_null($this->_minder))
            $this->_minder = Minder::getInstance();

        return $this->_minder;
    }

    protected function _getOptionsMapper() {
        return new Minder2_Model_Mapper_Options();
    }

    /**
     * @return Minder2_Model_Options|null
     */
    public function getProdIdGenerator() {
        $generators = $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'PROD_ID_GN'
            )
        );

        if (count($generators) < 1)
            return null;

        return current($generators);
    }

    /**
     * @return array
     */
    public function getPostCheckLinesSubTypes() {
        $options = $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'POST_CHKLN'
            )
        );

        $result = array();

        if (count($options) < 1)
            return $result;

        foreach($options as $option) {
            if (!empty($option->CODE)) {
                $result = array_merge($result, explode('|', $option->CODE));
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getSerialNumberSubTypes() {
        $options = $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'PO_SUBTYPE'
            )
        );

        $result = array();

        if (count($options) < 1)
            return $result;

        foreach($options as $option) {
            if (!empty($option->CODE)) {
                list($orderSubType, $flag) = explode('|', $option->CODE);

                if (strtoupper(trim($flag)) == 'SN') {
                    $result[] = $orderSubType;
                }
            }
        }

        return $result;
    }

    /**
     * @return Minder2_Model_Options[]
     */
    public function getEnquireTypes() {
        return $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'ENQ_TYPES'
        ));
    }

    /**
     * @return Minder2_Model_Options[]
     */
    public function getScreenVariances() {
        return $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'SS_VAR'
        ));
    }

    /**
     * @param $type
     * @return string
     */
    public function getScnFormat($type) {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'SCN_FORMAT',
            Minder2_Model_Mapper_Options::CODE => $type
        ));

        if (empty($options)) {
            return '';
        }

        /**
         * @var Minder2_Model_Options $option
         */
        $option = current($options);

        return $option->DESCRIPTION;
    }

    public function getQueryLimits() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'QueryLimit',
        ));

        $result = array();
        foreach ($options as $option) {
            $result[$option->CODE] = $option->DESCRIPTION;
        }

        return $result;
    }

    public function getScnRadioButton() {
        return $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'SCN_RADIOB',
            Minder2_Model_Mapper_Options::CODE => 'T'
        ));
    }

    public function getDefPrice() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'DEF_PRICE',
        ));

        if (empty($options)) {
            return '';
        }

        /**
         * @var Minder2_Model_Options $option
         */
        $option = current($options);

        return $option->CODE;
    }

    public function getReportButtons() {
        return $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'REPORT_BTN',
            ),
            Minder2_Model_Mapper_Options::FETCH_MODE_ARRAY
        );
    }

    public function getScreenTitle($screenName) {
        $options = $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'SCN_TITLE',
                Minder2_Model_Mapper_Options::CODE => strtoupper($screenName),
            )
        );

        if (count($options) > 0) {
            $option = current($options);
            return $option->DESCRIPTION;
        }

        return $screenName;
    }

    /**
     * @param $screenName
     * @return Minder2_Model_Options|null
     */
    public function getInheritanceOption($screenName) {
        $options = $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'SS_VAR_INH',
                Minder2_Model_Mapper_Options::CODE => strtoupper($screenName),
            )
        );

        if (count($options) > 0) {
            return current($options);
        }

        return null;
    }

    /**
     * @return array[]|Minder2_Model_Options[]
     */
    public function getStateOptions() {
        return $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'STATE_CODE'
            )
        );
    }

    /**
     * @return array[]|Minder2_Model_Options[]
     */
    public function getTransferOrderStatuses() {
        return $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'TR_STATUS'
            )
        );
    }

    public function getPrompts($type) {
        return $this->_getOptionsMapper()->find(
            array(
                Minder2_Model_Mapper_Options::GROUP_CODE => 'PROMPTS',
                Minder2_Model_Mapper_Options::CODE => strtoupper($type) . '|%',
            )
        );
    }

    public function getPoSubTypes() {
        return $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'PO_SUBTYPE'
        ));
    }

    public function getCostCenterCaption() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'COSTC_NAME'
        ));

        $option = current($options);

        return empty($option) ? static::DEFAULT_COST_CENTER_CAPTION : $option->CODE;
    }

    public function shouldReportOverdueTools() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'OVERDUE_RP',
            Minder2_Model_Mapper_Options::CODE => 'T',
        ));

        return count($options) > 0;
    }

    public function shouldRefuseIfSafeTestExpired() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'EXPIRED_TS',
            Minder2_Model_Mapper_Options::CODE => 'T',
        ));

        return count($options) > 0;
    }

    public function shouldRefuseIfCalibrationExpired() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'EXPIRED_TC',
            Minder2_Model_Mapper_Options::CODE => 'T',
        ));

        return count($options) > 0;
    }

    public function shouldRefuseIfInspectionExpired() {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'EXPIRED_TI',
            Minder2_Model_Mapper_Options::CODE => 'T',
        ));

        return count($options) > 0;
    }

    public function getExpirationSettings() {
        $result = new Minder2_Options_Expiration();
        $result->shouldRefuseIfCalibrationExpired = $this->shouldRefuseIfCalibrationExpired();
        $result->shouldRefuseIfInspectionExpired = $this->shouldRefuseIfInspectionExpired();
        $result->shouldRefuseIfSafeTestExpired = $this->shouldRefuseIfSafeTestExpired();

        return $result;
    }

    public function getLocationFormatMap() {
        $result = array();

        $options = $this->_getOptionsMapper()->find(array(Minder2_Model_Mapper_Options::GROUP_CODE => 'DISP_LOCN'));

        foreach ($options as $option) {
            $result[$option->CODE] = $option->DESCRIPTION;
        }

        return $result;
    }

    public function getSsnEditFormStyle($default = 'default') {
        $options = $this->_getOptionsMapper()->find(array(
            Minder2_Model_Mapper_Options::GROUP_CODE => 'FRM_STYLE',
            Minder2_Model_Mapper_Options::CODE => 'SSN',
        ));

        foreach($options as $option) {
            return strtolower($option->DESCRIPTION);
        }

        return $default;
    }
}