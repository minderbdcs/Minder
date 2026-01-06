<?php

class Transaction_Response_DSGS implements Transaction_Response_Interface {
    const DESPATCH_ID = 'PICK_DESPATCH.DESPATCH_ID';
    const CONSIGNMENT_NO = 'PACK_SSCC.AWB_CONSIGNMENT_NO';
    const RECORD_ID = 'PACK_SSCC.RECORD_ID';
    const SSCC = 'PACK_SSCC.PS_SSCC';

    protected $_success = false;
    protected $_despatchId = '';
    protected $_consignmentNo = '';
    protected $_psRecordId = '';
    protected $_sscc = '';

    function __construct($message, $sscc)
    {
        $this->_sscc = $sscc;
        $this->_parseMessage($message);
    }


    protected function _parseMessage($message)
    {
        $this->_success = (false !== stripos($message, 'success'));

        foreach (explode('|', $message) as $part) {
            $keyValue = explode('=', $part);

            switch (strtoupper(trim($keyValue[0]))) {
                case static::DESPATCH_ID:
                    $this->_setDespatchId(isset($keyValue[1]) ? $keyValue[1] : '');
                    break;
                case static::CONSIGNMENT_NO:
                    $this->_setConsignmentNo(isset($keyValue[1]) ? $keyValue[1] : '');
                    break;
                case static::RECORD_ID:
                    $this->_setPsRecordId(isset($keyValue[1]) ? $keyValue[1] : '');
                    break;
                case static::SSCC:
                    $this->_setSscc(isset($keyValue[1]) ? $keyValue[1] : '');
                    break;
            }
        }
    }

    public function isSuccess()
    {
        return $this->_success;
    }

    /**
     * @return string
     */
    public function getDespatchId()
    {
        return $this->_despatchId;
    }

    /**
     * @param string $despatchId
     * @return $this
     */
    protected function _setDespatchId($despatchId)
    {
        $this->_despatchId = $despatchId;
        return $this;
    }

    /**
     * @return string
     */
    public function getConsignmentNo()
    {
        return $this->_consignmentNo;
    }

    /**
     * @param string $consignmentNo
     * @return $this
     */
    protected function _setConsignmentNo($consignmentNo)
    {
        $this->_consignmentNo = $consignmentNo;
        return $this;
    }

    /**
     * @return string
     */
    public function getPsRecordId()
    {
        return $this->_psRecordId;
    }

    /**
     * @param string $psRecordId
     * @return $this
     */
    protected function _setPsRecordId($psRecordId)
    {
        $this->_psRecordId = $psRecordId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSscc()
    {
        return $this->_sscc;
    }

    /**
     * @param string $sscc
     * @return $this
     */
    protected function _setSscc($sscc)
    {
        $this->_sscc = $sscc;
        return $this;
    }
}