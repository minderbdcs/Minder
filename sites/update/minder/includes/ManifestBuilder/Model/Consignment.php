<?php

/**
 * @property string AWB_CONSIGNMENT_NO
 * @property string CREATE_DATE
 * @property string SERVICE_CHARGE_CODES
 * @property string SENDER_ACCOUNT
 * @property string RECEIVER_ACCOUNT
 * @property string PICKD_CHARGE_TO
 * @property string PICKD_PALLET_QTY
 * @property string PICKD_CARTON_QTY
 * @property string PICKD_SATCHEL_QTY
 * @property string PICKD_WT_CALC
 * @property string PICKD_WT_ACTUAL
 * @property string PICKD_VOL_ACTUAL
 * @property string ACCOUNT
 * @property string PICK_LABEL_NO
 * @property string PICK_ORDER
 * @property string PO_OTHER1
 *
 */
class ManifestBuilder_Model_Consignment extends ArrayObject {
    public function __construct($input = null, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, static::ARRAY_AS_PROPS, $iterator_class);
    }

    public function getCreateDate() {
        return new Zend_Date($this->CREATE_DATE);
    }

    public function getAccountNumber() {
        return $this->PICKD_CHARGE_TO == 'R' ? $this->RECEIVER_ACCOUNT : $this->ACCOUNT;
    }

    public function getTotalLogisticUnits() {
        return (int)$this->PICKD_CARTON_QTY + (int)$this->PICKD_PALLET_QTY + (int)$this->PICKD_SATCHEL_QTY;
    }
}