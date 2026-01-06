<?php

class PostcodeDepot_Mapper {

    /**
     * @param  $recordId
     * @return PostcodeDepot
     */
    public function find($recordId) {
        $minder = Minder::getInstance();

        if (false !== ($result = $minder->fetchAssoc("SELECT * FROM POSTCODE_DEPOT WHERE RECORD_ID = ?", $recordId)))
            return new PostcodeDepot($result);

        return new PostcodeDepot_NullRecord();
    }

    /**
     * @throws Minder_Exception
     * @param string $postcode
     * @param string $newDepotId
     * @param string $depotIdField
     * @return void
     */
    public function massDepotIdUpdate($postcode, $newDepotId, $depotIdField) {
        $minder = Minder::getInstance();
        if (false === $minder->execSQL("UPDATE POSTCODE_DEPOT SET " . $depotIdField . " = ? WHERE POST_CODE = ?", array($newDepotId, $postcode)))
            throw new Minder_Exception($minder->lastError);
    }

    /**
     * @throws Minder_Exception
     * @param PostcodeDepot $postcodeDepotRecord
     * @return void
     */
    protected function _createRecord($postcodeDepotRecord) {
        $sql = "
            INSERT INTO POSTCODE_DEPOT
                (POST_CODE,
                LOCALITY,
                STATE,
                COMMENTS,
                DELIVERY_OFFICE,
                PRE_SORT_INDICATOR,
                PARCEL_ZONE,
                BSP_NUMBER,
                BSP_NAME,
                CATEGORY,
                COUNTRY,
                DESCRIPTION,
                DEPOT_01,
                DEPOT_02,
                DEPOT_03,
                DEPOT_04,
                DEPOT_05,
                DEPOT_06,
                DEPOT_07,
                DEPOT_08,
                DEPOT_09,
                DEPOT_10)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $minder = Minder::getInstance(); 

        if (false === $minder->execSQL(
                $sql,
                array(
                    $postcodeDepotRecord->postCode,
                    $postcodeDepotRecord->locality,
                    $postcodeDepotRecord->state,
                    $postcodeDepotRecord->comments,
                    $postcodeDepotRecord->deliveryOffice,
                    $postcodeDepotRecord->preSortIndicator,
                    $postcodeDepotRecord->parcelZone,
                    $postcodeDepotRecord->bspNumber,
                    $postcodeDepotRecord->bspName,
                    $postcodeDepotRecord->category,
                    $postcodeDepotRecord->country,
                    $postcodeDepotRecord->description,
                    $postcodeDepotRecord->depot01,
                    $postcodeDepotRecord->depot02,
                    $postcodeDepotRecord->depot03,
                    $postcodeDepotRecord->depot04,
                    $postcodeDepotRecord->depot05,
                    $postcodeDepotRecord->depot06,
                    $postcodeDepotRecord->depot07,
                    $postcodeDepotRecord->depot08,
                    $postcodeDepotRecord->depot09,
                    $postcodeDepotRecord->depot10
                )
            )
        ) {
            throw new Minder_Exception($minder->lastError);
        }
    }

    /**
     * @throws Minder_Exception
     * @param PostcodeDepot $postcodeDepotRecord
     * @return void
     */
    protected function _updateRecord($postcodeDepotRecord) {
        $sql = "
            UPDATE POSTCODE_DEPOT SET
                POST_CODE           = ?,
                LOCALITY            = ?,
                STATE               = ?,
                COMMENTS            = ?,
                DELIVERY_OFFICE     = ?,
                PRE_SORT_INDICATOR  = ?,
                PARCEL_ZONE         = ?,
                BSP_NUMBER          = ?,
                BSP_NAME            = ?,
                CATEGORY            = ?,
                COUNTRY             = ?,
                DESCRIPTION         = ?,
                DEPOT_01            = ?,
                DEPOT_02            = ?,
                DEPOT_03            = ?,
                DEPOT_04            = ?,
                DEPOT_05            = ?,
                DEPOT_06            = ?,
                DEPOT_07            = ?,
                DEPOT_08            = ?,
                DEPOT_09            = ?,
                DEPOT_10            = ?
            WHERE
                RECORD_ID = ?
        ";

        $minder = Minder::getInstance();

        if (false === $minder->execSQL(
                $sql,
                array(
                    $postcodeDepotRecord->postCode,
                    $postcodeDepotRecord->locality,
                    $postcodeDepotRecord->state,
                    $postcodeDepotRecord->comments,
                    $postcodeDepotRecord->deliveryOffice,
                    $postcodeDepotRecord->preSortIndicator,
                    $postcodeDepotRecord->parcelZone,
                    $postcodeDepotRecord->bspNumber,
                    $postcodeDepotRecord->bspName,
                    $postcodeDepotRecord->category,
                    $postcodeDepotRecord->country,
                    $postcodeDepotRecord->description,
                    $postcodeDepotRecord->depot01,
                    $postcodeDepotRecord->depot02,
                    $postcodeDepotRecord->depot03,
                    $postcodeDepotRecord->depot04,
                    $postcodeDepotRecord->depot05,
                    $postcodeDepotRecord->depot06,
                    $postcodeDepotRecord->depot07,
                    $postcodeDepotRecord->depot08,
                    $postcodeDepotRecord->depot09,
                    $postcodeDepotRecord->depot10,
                    $postcodeDepotRecord->recordId
                )
            )
        ) {
            throw new Minder_Exception($minder->lastError);
        }
    }

    /**
     * @throws Minder_Exception
     * @param PostcodeDepot $postcodeDepotRecord
     * @return void
     */
    public function save($postcodeDepotRecord) {
        if ($postcodeDepotRecord->existedRecord())
            $this->_updateRecord($postcodeDepotRecord);
        else
            $this->_createRecord($postcodeDepotRecord);
    }

    /**
     * @param PostcodeDepot_Collection $postcodeDepotCollection
     * @return void
     */
    public function saveCollection($postcodeDepotCollection) {
        foreach ($postcodeDepotCollection as $postcodeDepotRecord) {
            $this->save($postcodeDepotRecord);
        }
    }

    protected function _buildEqualFilter($field, $value) {
        if (is_null($value))
            return array($field . ' IS NULL' => array());
        return array($field . ' = ?' => array($value));
    }

    /**
     * @param string $postcode
     * @param string $locality
     * @param string $state
     * @param string $country
     * @return PostcodeDepot_Collection
     */
    protected function _findSiblingRecords($postcode, $locality, $state, $country) {
        $result = new PostcodeDepot_Collection();
        $filters = array();
        $filters += $this->_buildEqualFilter('POST_CODE', $postcode);
        $filters += $this->_buildEqualFilter('LOCALITY', $locality);
        $filters += $this->_buildEqualFilter('STATE', $state);
        $filters += $this->_buildEqualFilter('COUNTRY', $country);
        $sql = "
            SELECT
                *
            FROM
                POSTCODE_DEPOT
            WHERE
                " . implode(' AND ', array_keys($filters)) . "
        ";

        $args = array_reduce(array_values($filters), create_function('$res, $item', '$res = (is_array($res)) ? $res : array(); return array_merge($res, $item);'), array());
        array_unshift($args, $sql);

        if (false !== ($queryResult = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args))) {
            $result->loadFromTableRows($queryResult);
        }

        return $result;
    }

    /**
     * @param string $postcode
     * @param string $locality
     * @param string $state
     * @param string $country
     * @param int $siblingOrder
     * @return string|null
     */
    public function findSiblingRecordId($postcode, $locality, $state, $country, $siblingOrder = 0) {
        $siblingsCollection = $this->_findSiblingRecords($postcode, $locality, $state, $country);
        return $siblingsCollection->getElement($siblingOrder)->recordId;
    }

    /**
     * @throws Minder_Exception
     * @return void
     */
    public function deleteAll() {
        $minder = Minder::getInstance();
        if (false === $minder->execSQL('DELETE FROM POSTCODE_DEPOT'))
            throw new Minder_Exception($minder->lastError);
    }
}
