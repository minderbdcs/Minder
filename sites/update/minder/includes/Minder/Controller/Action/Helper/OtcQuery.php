<?php

class Minder_Controller_Action_Helper_OtcQuery extends Zend_Controller_Action_Helper_Abstract {

    const ISSN_BASED_FROM = "
                    ISSN
                    LEFT JOIN LOCATION ON ISSN.WH_ID = LOCATION.WH_ID AND ISSN.LOCN_ID = LOCATION.LOCN_ID
                    LEFT JOIN SSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID
    ";

    const SSN_BASED_FROM = "
                    SSN
                    LEFT JOIN ISSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID
                    LEFT JOIN LOCATION ON ISSN.WH_ID = LOCATION.WH_ID AND ISSN.LOCN_ID = LOCATION.LOCN_ID
    ";

    const TOOL_FIELDS = "
                    ISSN.SSN_ID AS RECORD_ID,
                    ISSN.SSN_ID,
                    ISSN.ISSN_STATUS,
                    SSN.COST_CENTER,
                    COALESCE(ISSN.ISSN_DESCRIPTION, SSN.SSN_DESCRIPTION) AS DESCRIPTION,
                    ISSN.WH_ID || ISSN.LOCN_ID AS LOCATION,
                    LOCATION.LOCN_NAME AS BORROWER_NAME,
                    CASE WHEN (ISSN.WH_ID = 'XB') THEN ISSN.LOCN_ID ELSE '' END AS BORROWER
    ";

    public function getSearchRequest() {
        $request = $this->getRequest();
        $queryParameter =  $request->getParam('param', array(
            'param_type' => '',
            'param_name' => '',
            'param_filtered_value' => ''
        ));

        $paramType = isset($queryParameter['param_type']) ? $queryParameter['param_type'] : '';

        return new Minder_OtcSearchRequest(
            (strtolower($paramType) == 'null') ? '' : $paramType,
            isset($queryParameter['param_name']) ? $queryParameter['param_name'] : '',
            isset($queryParameter['param_filtered_value']) ? $queryParameter['param_filtered_value'] : '',
            $request->getParam('warehouse', ''),
            $request->getParam('isLoaned', 'false') == 'true',
            $request->getParam('matchCase', 'false') == 'true'
        );
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function getResult($searchRequest, $offset, $limit) {
        switch (true) {
            case $searchRequest->isToolRequest():
                return $this->_queryTool($searchRequest, $offset, $limit);
            case $searchRequest->isLocationRequest():
                return $this->_queryLocation($searchRequest, $offset, $limit);
            case $searchRequest->isBorrowerRequest():
                return $this->_queryBorrower($searchRequest, $offset, $limit);
            case $searchRequest->isProductRequest():
                if ($searchRequest->isLoaned()) {
                    return $this->_queryLoanedProduct($searchRequest, $offset, $limit);
                } else {
                    return $this->_queryProduct($searchRequest, $offset, $limit);
                }
            case $searchRequest->isPartialBorrowerRequest():
                return $this->_findBorrower($searchRequest, $offset, $limit);
            case $searchRequest->isCostCenterRequest():
                return $this->_queryCostCenter($searchRequest, $offset, $limit);
            case $searchRequest->isLegacyToolCodeRequest():
                return $this->_queryLegacyToolCode($searchRequest, $offset, $limit);
            case $searchRequest->isToolSerialNumberRequest():
                return $this->_queryToolSerialNumber($searchRequest, $offset, $limit);
            default:
                return $this->_queryDescription($searchRequest, $offset, $limit);
        }
    }

    protected function _queryToolSerialNumber($searchRequest, $offset, $limit) {
        $issnSearchField = $searchRequest->ignoreCase() ? 'UPPER(ISSN.SERIAL_NUMBER)' : 'ISSN.SERIAL_NUMBER';
        $ssnSearchField = $searchRequest->ignoreCase() ? 'UPPER(SSN.SERIAL_NUMBER)' : 'SSN.SERIAL_NUMBER';
        $searchValue = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                " . static::ISSN_BASED_FROM ."
            WHERE
                (" . $issnSearchField . " = ? OR " . $ssnSearchField . " = ?)
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $searchValue, $searchValue);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    " . static::TOOL_FIELDS . "
                FROM
                    " . static::ISSN_BASED_FROM ."
                WHERE
                    (" . $issnSearchField . " = ? OR " . $ssnSearchField . " = ?)
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $searchValue, $searchValue);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     * @throws Minder_Exception
     */
    protected function _queryTool($searchRequest, $offset, $limit) {
        $searchField = $searchRequest->ignoreCase() ? 'UPPER(ISSN.SSN_ID)' : 'ISSN.SSN_ID';
        $searchValue = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                " . static::ISSN_BASED_FROM ."
            WHERE
                " . $searchField . " = ?
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $searchValue);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    " . static::TOOL_FIELDS . "
                FROM
                    " . static::ISSN_BASED_FROM ."
                WHERE
                    " . $searchField . " = ?
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $searchValue);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function _queryLocation($searchRequest, $offset, $limit) {
        $whField = $searchRequest->ignoreCase() ? 'UPPER(ISSN.WH_ID)' : 'ISSN.WH_ID';
        $locnField = $searchRequest->ignoreCase() ? 'UPPER(ISSN.LOCN_ID)' : 'ISSN.LOCN_ID';
        $value = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();

        $whId = substr($value, 0, 2);
        $locnId = substr($value, 2);

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                " . static::ISSN_BASED_FROM ."
            WHERE
                " . $whField . " = ?
                AND " . $locnField . " = ?
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $whId, $locnId);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    " . static::TOOL_FIELDS . "
                FROM
                    " . static::ISSN_BASED_FROM ."
                WHERE
                    " . $whField . " = ?
                    AND " . $locnField . " = ?
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $whId, $locnId);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function _queryBorrower($searchRequest, $offset, $limit) {

        $originalValue = $searchRequest->getParameterValue();
        $searchRequest->setParameterValue('XB' . $originalValue);
        $result = $this->_queryLocation($searchRequest, $offset, $limit);
        $searchRequest->setParameterValue($originalValue); //restore original borrower id
        return $result;
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function _queryProduct($searchRequest, $offset, $limit) {
        $idField = $searchRequest->ignoreCase() ? 'UPPER(PROD_PROFILE.PROD_ID)' : 'PROD_PROFILE.PROD_ID';
        $altField = $searchRequest->ignoreCase() ? 'UPPER(PROD_PROFILE.ALTERNATE_ID)' : 'PROD_PROFILE.ALTERNATE_ID';
        $value = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();
        $validStatus = $this->_getMinder()->defaultControlValues['PICK_IMPORT_SSN_STATUS'];

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                PROD_PROFILE
                LEFT JOIN ISSN ON PROD_PROFILE.PROD_ID = ISSN.PROD_ID
                LEFT JOIN LOCATION ON ISSN.WH_ID = LOCATION.WH_ID AND ISSN.LOCN_ID = LOCATION.LOCN_ID
            WHERE
                (" . $idField . " = ? OR " . $altField . " = ?)
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
                AND ISSN.CURRENT_QTY > 0
                AND ISSN.WH_ID <> 'XX'
                AND ISSN.WH_ID <> 'XB'
                AND POSITION(ISSN.ISSN_STATUS IN '" . $validStatus . "') > 0
                AND (
                    PROD_PROFILE.COMPANY_ID = ISSN.COMPANY_ID
                    OR (
                        (PROD_PROFILE.COMPANY_ID = 'ALL' OR PROD_PROFILE.COMPANY_ID = '' OR PROD_PROFILE.COMPANY_ID IS NULL)
                        AND NOT EXISTS (
                            SELECT
                                COMPANY_ID
                            FROM
                                PROD_PROFILE AS SUB_PP
                            WHERE
                                ISSN.PROD_ID = SUB_PP.PROD_ID
                                AND ISSN.COMPANY_ID = SUB_PP.COMPANY_ID
                        )
                    )
                )
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $value, $value, $validStatus);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    ISSN.SSN_ID AS RECORD_ID,
                    ISSN.SSN_ID,
                    CASE
                        WHEN (ISSN.ISSN_DESCRIPTION <> '' AND NOT ISSN.ISSN_DESCRIPTION IS NULL) THEN ISSN.ISSN_DESCRIPTION
                        ELSE PROD_PROFILE.SHORT_DESC
                    END AS ISSN_DESCRIPTION,
                    ISSN.WH_ID || ISSN.LOCN_ID AS LOCATION,
                    LOCATION.LOCN_NAME AS BORROWER_NAME,
                    CASE WHEN (ISSN.WH_ID = 'XB') THEN ISSN.LOCN_ID ELSE '' END AS BORROWER,
                    PROD_PROFILE.PROD_ID,
                    PROD_PROFILE.ALTERNATE_ID,
                    ISSN.CURRENT_QTY
                FROM
                    PROD_PROFILE
                    LEFT JOIN ISSN ON PROD_PROFILE.PROD_ID = ISSN.PROD_ID
                    LEFT JOIN LOCATION ON ISSN.WH_ID = LOCATION.WH_ID AND ISSN.LOCN_ID = LOCATION.LOCN_ID
                WHERE
                    (" . $idField . " = ? OR " . $altField . " = ?)
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
                    AND ISSN.CURRENT_QTY > 0
                    AND ISSN.WH_ID <> 'XX'
                    AND ISSN.WH_ID <> 'XB'
                    AND POSITION(ISSN.ISSN_STATUS IN '" . $validStatus . "') > 0
                    AND (
                        PROD_PROFILE.COMPANY_ID = ISSN.COMPANY_ID
                        OR (
                            (PROD_PROFILE.COMPANY_ID = 'ALL' OR PROD_PROFILE.COMPANY_ID = '' OR PROD_PROFILE.COMPANY_ID IS NULL)
                            AND NOT EXISTS (
                                SELECT
                                    COMPANY_ID
                                FROM
                                    PROD_PROFILE AS SUB_PP
                                WHERE
                                    ISSN.PROD_ID = SUB_PP.PROD_ID
                                    AND ISSN.COMPANY_ID = SUB_PP.COMPANY_ID
                            )
                        )
                    )
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $value, $value, $validStatus);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    protected function _queryLoanedProduct($searchRequest, $offset, $limit) {
        $idField = $searchRequest->ignoreCase() ? 'UPPER(TRANSACTIONS_ARCHIVE.OBJECT)' : 'TRANSACTIONS_ARCHIVE.OBJECT';
        $altField = $searchRequest->ignoreCase() ? 'UPPER(PROD_PROFILE.ALTERNATE_ID)' : 'PROD_PROFILE.ALTERNATE_ID';
        $value = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();

        $sql = "
            SELECT FIRST 1
                TRANSACTIONS_ARCHIVE.RECORD_ID,
                PROD_PROFILE.SHORT_DESC,
                TRANSACTIONS_ARCHIVE.WH_ID || TRANSACTIONS_ARCHIVE.LOCN_ID AS LOCATION,
                LOCATION.LOCN_NAME AS BORROWER_NAME,
                PROD_PROFILE.PROD_ID,
                PROD_PROFILE.ALTERNATE_ID,
                TRANSACTIONS_ARCHIVE.QTY
            FROM
                TRANSACTIONS_ARCHIVE
                LEFT JOIN PROD_PROFILE ON TRANSACTIONS_ARCHIVE.OBJECT = PROD_PROFILE.PROD_ID
                LEFT JOIN LOCATION ON TRANSACTIONS_ARCHIVE.WH_ID = LOCATION.WH_ID AND TRANSACTIONS_ARCHIVE.LOCN_ID = LOCATION.LOCN_ID
            WHERE
                TRANSACTIONS_ARCHIVE.TRN_TYPE = 'ISIZ'
                AND TRANSACTIONS_ARCHIVE.TRN_CODE = 'P'
                AND TRANSACTIONS_ARCHIVE.WH_ID = 'XB'
                AND (" . $idField . " = ? OR " . $altField . " = ?)

            ORDER BY TRN_DATE DESC
        ";

        $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $value, $value);

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => count($queryResults));
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function _findBorrower($searchRequest, $offset, $limit) {
        $idField = $searchRequest->ignoreCase() ? 'UPPER(LOCATION.LOCN_ID)' : 'LOCATION.LOCN_ID';
        $nameField = $searchRequest->ignoreCase() ? 'UPPER(LOCATION.LOCN_NAME)' : 'LOCATION.LOCN_NAME';

        $borrower = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();
        $borrower = '%' . trim($borrower, '%') . '%';

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                LOCATION
            WHERE
                LOCATION.WH_ID = 'XB'
                AND (" . $idField . " LIKE ? OR " . $nameField . " LIKE ?)
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $borrower, $borrower);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    LOCATION.LOCN_NAME AS BORROWER_NAME,
                    LOCATION.LOCN_ID AS RECORD_ID
                FROM
                    LOCATION
                WHERE
                    LOCATION.WH_ID = 'XB'
                    AND (" . $idField . " LIKE ? OR " . $nameField . " LIKE ?)
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $borrower, $borrower);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function _queryDescription($searchRequest, $offset, $limit) {
        $issnField = $searchRequest->ignoreCase() ? 'UPPER(ISSN.ISSN_DESCRIPTION)' : 'ISSN.ISSN_DESCRIPTION';
        $ssnField = $searchRequest->ignoreCase() ? 'UPPER(SSN.SSN_DESCRIPTION)' : 'SSN.SSN_DESCRIPTION';

        $description = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();
        $description = trim($description, '%');
        $description = strlen($description) ? '%' . $description . '%' : '%';

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                " . static::ISSN_BASED_FROM ."
            WHERE
                (" . $issnField . " LIKE ? OR " . $ssnField . " LIKE ?)
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
        ";

        if ($searchRequest->isLoaned()) {
            $countSql .= " AND ISSN.WH_ID = 'XB'";
        } else {
            if ($searchRequest->exactWarehouse()) {
                $countSql .= " AND ISSN.WH_ID = '" . $searchRequest->getWarehouse() . "'";
            } else {
                $countSql .= " AND ISSN.WH_ID <> 'XB'";
            }
        }

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $description, $description);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    " . static::TOOL_FIELDS . "
                FROM
                    " . static::ISSN_BASED_FROM ."
                WHERE
                    (" . $issnField . " LIKE ? OR " . $ssnField . " LIKE ?)
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
            ";

            if ($searchRequest->isLoaned()) {
                $sql .= " AND ISSN.WH_ID = 'XB'";
            } else {
                if ($searchRequest->exactWarehouse()) {
                    $sql .= " AND ISSN.WH_ID = '" . $searchRequest->getWarehouse() . "'";
                } else {
                    $sql .= " AND ISSN.WH_ID <> 'XB'";
                }
            }

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $description, $description);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    /**
     * @param Minder_OtcSearchRequest $request
     * @return array
     */
    public function getResultHeaders($request) {
        switch (true) {
            case $request->isProductRequest():
                if ($request->isLoaned()) {
                    return array(
                        'PROD_ID' => 'Product#',
                        'ALTERNATE_ID' => 'Alternate ID',
                        'LOCATION' => 'Location',
                        'BORROWER_NAME' => 'Borrower',
                        'SHORT_DESC' => 'Description',
                        'QTY' => 'Qty',
                    );
                } else {
                    return array(
                        'RECORD_ID' => 'ISSN#',
                        'PROD_ID' => 'Product#',
                        'ALTERNATE_ID' => 'Alternate ID',
                        'LOCATION' => 'Location',
                        'BORROWER_NAME' => 'Borrower',
                        'ISSN_DESCRIPTION' => 'Description',
                        'CURRENT_QTY' => 'Qty',
                    );
                }

            case $request->isPartialBorrowerRequest():
                return array(
                    'RECORD_ID' => 'BORROWER ID',
                    'BORROWER_NAME' => 'Borrower',
                );
            default:
                return array(
                    'RECORD_ID' => 'ISSN',
                    'COST_CENTER' => $this->_minderOptions()->getCostCenterCaption(),
                    'LOCATION' => 'LOCATION',
                    'ISSN_STATUS' => 'STATUS',
                    'BORROWER_NAME' => 'LOCATION NAME',
                    'DESCRIPTION' => 'DESCRIPTION',
                );
        }

    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @return Minder_Controller_Action_Helper_MinderOptions
     */
    protected function _minderOptions() {
        return $this->getActionController()->getHelper('MinderOptions');
    }

    protected function _queryCostCenter(Minder_OtcSearchRequest $searchRequest, $offset, $limit)
    {
        $searchField = $searchRequest->ignoreCase() ? 'UPPER(SSN.COST_CENTER)' : 'SSN.COST_CENTER';
        $searchValue = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                " . static::SSN_BASED_FROM . "
            WHERE
                " . $searchField . " = ?
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $searchValue);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    " . static::TOOL_FIELDS . "
                FROM
                    " . static::SSN_BASED_FROM . "
                WHERE
                    " . $searchField . " = ?
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $searchValue);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }

    /**
     * @param Minder_OtcSearchRequest $searchRequest
     * @param $offset
     * @param $limit
     * @return array
     * @throws Minder_Exception
     */
    protected function _queryLegacyToolCode($searchRequest, $offset, $limit) {
        $searchField = $searchRequest->ignoreCase() ? 'UPPER(SSN.LEGACY_ID)' : 'SSN.LEGACY_ID';
        $searchValue = $searchRequest->ignoreCase() ? strtoupper($searchRequest->getParameterValue()) : $searchRequest->getParameterValue();

        $countSql = "
            SELECT
                COUNT(*)
            FROM
                " . static::SSN_BASED_FROM . "
            WHERE
                " . $searchField . " = ?
                AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
        ";

        $queryResults   = array();
        $totalRows      = (int)$this->_getMinder()->fetchOne($countSql, $searchValue);

        if ($totalRows > 0) {
            $sql = "
                SELECT FIRST " . $limit . " SKIP " . $offset . "
                    " . static::TOOL_FIELDS . "
                FROM
                    " . static::SSN_BASED_FROM . "
                WHERE
                    " . $searchField . " = ?
                    AND " . $this->_getMinder()->formatCompanyLimit('ISSN') . "
                    AND " . $this->_getMinder()->formatWarehouseLimit('ISSN') . "
            ";

            $queryResults = $this->_getMinder()->fetchAllAssoc($sql, $searchValue);
        }

        return array(Minder_Controller_Action::DATA => $queryResults, Minder_Controller_Action::TOTAL => $totalRows);
    }
}