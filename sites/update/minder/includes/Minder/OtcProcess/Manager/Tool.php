<?php

class Minder_OtcProcess_Manager_Tool {
    protected $_options;

    public function saveToolImages($id, $newImages) {
        $tool = $this->getTool($id, 'S');

        if (!$tool->existed) {
            throw new Exception('ISSN #' . $id . ' not exists.');
        }

        $basePath = $this->_getBasePath($tool->companyId);

        if (!is_writable($basePath)) {
            if (file_exists($basePath)) {
                throw new Exception('Cannot write to ' . $basePath . '. Check file permissions.');
            }

            if (!mkdir($basePath, 0777, true)) {
                throw new Exception('Cannot create image directory ' . $basePath . '. Check file permissions.');
            }
        }

        foreach ($newImages as $index => $image) {
            $imagePath      = $basePath . $this->_formatFileName($tool->id, $index);
            list(,$data)    = explode(',', $image['image']);

            if (false === file_put_contents($imagePath, base64_decode($data))) {
                throw new Exception('Cannot write image file ' . $imagePath . '. Check file permissions.');
            }
        }

        return $tool;
    }

    public function reloadTool(Minder_OtcProcess_State_AbstractItem $tool) {
        if ($tool->legacyId) {
            return $this->getToolByLegacyId($tool->id, $tool->via);
        }

        return $this->getTool($tool->id, $tool->via);
    }

    public function getTool($id, $via) {
        return $this->_getToolObject($id, $via, $this->_getIssn($id));
    }

    public function getToolByLegacyId($id, $via) {
        $result = $this->_getToolObject($id, $via, $this->_getIssnByLegacyId($id));
        $result->legacyId = true;

        return $result;
    }

    protected function _getToolObject($id, $via, $issn) {
        $ssn = null;
        $result = new Minder_OtcProcess_State_Tool($id, $via);

        if (!empty($issn)) {
            $ssn = $this->_getMinder()->getSsn($issn['ORIGINAL_SSN']);

            if (!empty($ssn)) {
                $defaultCompanyId = $this->_getMinder()->defaultControlValues['COMPANY_ID'];

                $result->existed = true;
                $result->currentQty = $issn['CURRENT_QTY'];
                $result->companyId = empty($issn['COMPANY_ID']) ? $defaultCompanyId : $issn['COMPANY_ID'];
                $result->description = empty($issn['ISSN_DESCRIPTION']) ? (empty($ssn['SSN_DESCRIPTION']) ? ''
                    : $ssn['SSN_DESCRIPTION']) : $issn['ISSN_DESCRIPTION'];

                $result->whId = $issn['WH_ID'];
                $result->locnId = $issn['LOCN_ID'];

                $result->images = $this->_getToolImages($issn, $defaultCompanyId);
                $result->homeLocation = $ssn['HOME_LOCN_ID'];

		$result->homeWhId = $ssn['HOME_WH_ID'];

	 ///
                if(!empty($result->homeWhId)&&!empty($result->homeLocation))
                {
                        $location=$this->_getMinder()->getLocnName($ssn['HOME_LOCN_ID'], $ssn['HOME_WH_ID']);
                        if(!empty($location))
                        {
                                $result->locnName = $location['LOCN_NAME'];
                        }
                }

                ///


                if ($this->_isToolOnLoan($issn)) {
                    $result->onLoan = true;
                    $result->onLoanAt = $ssn['COST_CENTER'];
                    $result->loanedTo = $this->_toolIsLoanedTo($issn);
                }

                $this->_fillCheckPeriods($result, $ssn, $this->_getOptions()->getExpirationSettings());

            }
        }

        return $result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getOptions() {
        if (is_null($this->_options)) {
            $this->_options = new Minder2_Options();
        }

        return $this->_options;
    }

    private function _toolIsLoanedTo($issn)
    {
        $borrower = $this->_getMinder()->getBorrower($issn['LOCN_ID']);

        return empty($borrower) ? 'Unknown Borrower' : $borrower['LOCN_NAME'];
    }

    protected function _getBasePath($companyId) {
        return ROOT_DIR . '/www/images/LowRes/' . $companyId . '/ISSN/';
    }

    protected function _getBaseURL($companyId) {
        return '/minder/images/LowRes/' . $companyId . '/ISSN/';
    }

    protected function _formatFileName($ssnId, $imageNo) {
        return $ssnId . '_' . ($imageNo ? $imageNo : '0') . '.png';
    }

    protected function _formatFileURL($ssnId, $imageNo, $baseUrl, $basePath) {
        $fileName   = $this->_formatFileName($ssnId, $imageNo);
        $mtime      = filemtime($basePath . $fileName);

        return $baseUrl . $fileName . ($mtime ? '?mtime =' . $mtime : '' );
    }

    private function _getToolImages($issn, $defaultCompanyId)
    {
        $companyId = empty($issn['COMPANY_ID']) ? $defaultCompanyId : $issn['COMPANY_ID'];
        $basePath = $this->_getBasePath($companyId);
        $baseUrl = $this->_getBaseURL($companyId);

        return new Minder_OtcProcess_ItemImages(
            $this->_formatFileURL($issn['SSN_ID'], 0, $baseUrl, $basePath),
            $this->_formatFileURL($issn['SSN_ID'], 1, $baseUrl, $basePath),
            $this->_formatFileURL($issn['SSN_ID'], 2, $baseUrl, $basePath)
        );
    }

    private function _isToolOnLoan($issn)
    {
        return strtoupper($issn['WH_ID']) == 'XB';
    }

    private function _fillCheckPeriods($result, $ssn, $expirationOptions)
    {
        $result->safetyTestPeriod = new Minder_OtcProcess_CheckPeriod(
            $ssn['LOAN_SAFETY_CHECK'] == 'T',
            $ssn['LOAN_LAST_SAFETY_CHECK_DATE'],
            $ssn['LOAN_SAFETY_PERIOD_NO'],
            $ssn['LOAN_SAFETY_PERIOD'],
            $expirationOptions->shouldRefuseIfSafeTestExpired
        );

        $result->calibratePeriod = new Minder_OtcProcess_CheckPeriod(
            $ssn['LOAN_CALIBRATE_CHECK'] == 'T',
            $ssn['LOAN_LAST_CALIBRATE_CHECK_DATE'],
            $ssn['LOAN_CALIBRATE_PERIOD_NO'],
            $ssn['LOAN_CALIBRATE_PERIOD'],
            $expirationOptions->shouldRefuseIfCalibrationExpired
        );

        $result->inspectionPeriod = new Minder_OtcProcess_CheckPeriod(
            $ssn['LOAN_INSPECT_CHECK'] == 'T',
            $ssn['LOAN_LAST_INSPECT_CHECK_DATE'],
            $ssn['LOAN_INSPECT_PERIOD_NO'],
            $ssn['LOAN_INSPECT_PERIOD'],
            $expirationOptions->shouldRefuseIfInspectionExpired
        );

        $result->expirationConfirmed = !(
            $result->safetyTestPeriod->expired
            || $result->calibratePeriod->expired
            || $result->inspectionPeriod->expired
        );
    }

    /**
     * @param $id
     * @return array|false
     * @throws Exception
     */
    protected function _getIssn($id)
    {
        return $this->_getMinder()->getIssn($id);
    }

    /**
     * @param $id
     * @return array|false
     * @throws Minder_Exception
     */
    protected function _getIssnByLegacyId($id)
    {
        $sql = "SELECT FIRST 1 ISSN.* FROM SSN LEFT JOIN ISSN ON SSN.SSN_ID = ISSN.SSN_ID WHERE SSN.LEGACY_ID = ?";

        $issn = $this->_getMinder()->fetchAssoc($sql, $id);
        return $issn;
    }
}
