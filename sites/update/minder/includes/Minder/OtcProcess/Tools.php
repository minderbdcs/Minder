<?php

class Minder_OtcProcess_Tools {

    const LOAN_SAFETY_CHECK     = 'LOAN_SAFETY_CHECK';
    const LOAN_CALIBRATE_CHECK  = 'LOAN_CALIBRATE_CHECK';
    const LOAN_INSPECT_CHECK	= 'LOAN_INSPECT_CHECK';

    public function getLoanDefaults() {
        return $this->_addDefaultDefaults($this->_groupByType($this->_buildTypeDefaults($this->_getOptions())));
    }

    protected function _buildTypeDefaults(array $options) {
        $result = array();

        foreach ($options as $option) {
            $loanDefault = new Minder_OtcProcess_LoanDefaults();
            list($loanDefault->TOOL_TYPE) = explode('|', strtoupper($option['CODE']));

            list(
                $loanDefault->TYPE,
                $loanDefault->CHECKED,
                $loanDefault->CHECK_DATE,
                $loanDefault->PERIOD_NO,
                $loanDefault->PERIOD
                ) = explode('|', $option['DESCRIPTION']);

            $loanDefault->CHECKED = ($loanDefault->CHECKED == 'T');

            $result[] = $loanDefault;
        }

        return $result;
    }

    /**
     * @param Minder_OtcProcess_LoanDefaults[] $loanDefaults
     * @return Minder_OtcProcess_LoanDefaults[]
     */
    protected function _groupByType(array $loanDefaults) {
        $result = array();

        foreach ($loanDefaults as $default) {
            $result[$default->TYPE] = isset($result[$default->TYPE]) ? $result[$default->TYPE] : array();
            $result[$default->TYPE][$default->TOOL_TYPE] = $default;
        }

        return $result;
    }

    protected function _addDefaultDefaults(array $loanDefaults) {
        $loanDefaults[static::LOAN_CALIBRATE_CHECK] = isset($loanDefaults[static::LOAN_CALIBRATE_CHECK]) ? $loanDefaults[static::LOAN_CALIBRATE_CHECK] : array();
        $loanDefaults[static::LOAN_CALIBRATE_CHECK][''] = new Minder_OtcProcess_LoanDefaults();

        $loanDefaults[static::LOAN_SAFETY_CHECK] = isset($loanDefaults[static::LOAN_SAFETY_CHECK]) ? $loanDefaults[static::LOAN_SAFETY_CHECK] : array();
        $loanDefaults[static::LOAN_SAFETY_CHECK][''] = new Minder_OtcProcess_LoanDefaults();

        $loanDefaults[static::LOAN_INSPECT_CHECK] = isset($loanDefaults[static::LOAN_INSPECT_CHECK]) ? $loanDefaults[static::LOAN_INSPECT_CHECK] : array();
        $loanDefaults[static::LOAN_INSPECT_CHECK][''] = new Minder_OtcProcess_LoanDefaults();

        return $loanDefaults;
    }

    protected function _getOptions() {
        return $this->_getMinder()->fetchAllAssoc('SELECT CODE, DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = ?', 'SSN_LOAN');
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}