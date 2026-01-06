<?php

class Minder_Controller_Action_Helper_WaitPicking extends Zend_Controller_Action_Helper_Abstract {

    public function printPickBlock($amount, Minder_Printer_Abstract $printer, Minder_JSResponse $response = null) {
        $response = is_null($response) ? new Minder_JSResponse() : $response;
        $amount = intval($amount);

        $labelLimit = $this->_getLabelLimit();
        if ($amount < 1) {
            $response->addErrors('Please, specify label amount.');
        } elseif ($amount > $labelLimit) {
            $response->addErrors('Label amount cannot be greater then ' . $labelLimit);
        } else {
            $this->_doPrint($amount, $printer, $response);
        }

        return $response;
    }

    protected function _doPrint($amount, Minder_Printer_Abstract $printer, Minder_JSResponse $response) {

        $printedAmount = 0;

        try {
            $labels = $this->_getLabelData($amount, Minder2_Environment::getWarehouseLimit()->WH_ID);

            $locationsFound = count($labels);

            if ($locationsFound > 0) {
                if ($locationsFound < $amount) {
                    $response->addWarnings('Only ' . $locationsFound . ' free PICK BLOCKS found.');
                }

                foreach ($labels as $labelData) {
                    $result = $printer->printPickBlock($labelData);

                    if($result['RES'] < 0) {
                        throw new Exception('Error while print label(s): ' . $result['ERROR_TEXT']);
                    }

                    $printedAmount++;

                    $this->_updateLocationLabelDate($labelData['LOCATION.WH_ID'], $labelData['LOCATION.LOCN_ID']);
                }
            } else {
                $response->addWarnings('No free PICK BLOCKS found.');
            }

        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        if ($printedAmount > 0) {
            $response->addMessages($printedAmount . ' label(s) printed.');
        }

        return $response;
    }

    protected function _updateLocationLabelDate($whId, $locnId) {
        $sql = "
            UPDATE LOCATION SET
                LABEL_DATE = 'NOW',
                LAST_UPDATE_DATE = 'NOW',
                LAST_UPDATE_BY = '" . Minder2_Environment::getCurrentUser()->USER_ID . "'
            WHERE
                WH_ID = ?
                AND LOCN_ID = ?
        ";

        if (false === $this->_getMinder()->execSQL($sql, array($whId, $locnId))) {
            throw new Exception($this->_getMinder()->lastError);
        }
    }

    protected function _getLabelData($amount, $whId) {
        $sql = "
            SELECT FIRST " . $amount . "
                LOCATION.*
            FROM
                LOCATION
            WHERE
                LOCN_STAT = 'OK'
                AND MOVEABLE_LOCN = 'T'
                AND LOCN_REPRINT = 'T'
                AND NOT EXISTS (
                    SELECT
                        RECORD_ID
                    FROM
                        PICK_LOCATION AS PL2
                    WHERE
                        LOCATION.WH_ID = PL2.WH_ID
                        AND LOCATION.LOCN_ID = PL2.LOCN_ID
                        AND PL2.PICK_LOCATION_STATUS IN ('OP', 'DS')
                )
        ";

        if (!empty($whId)) {
            $sql .= "
                AND CURRENT_WH_ID = '" . $whId . "'
            ";
        }

        $sql .= "
            ORDER BY LOCATION.LABEL_DATE ASC
        ";

        return $this->_getMinder()->fetchAllAssocExt($sql);
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getLabelLimit() {
        return 99;
    }
}