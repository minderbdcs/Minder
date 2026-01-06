<?php

/**
 * @property string PICKD_CARRIER_ID
 * @property string PICKD_SERVICE_TYPE
 * @property string DESPATCH_ID
 * @property string AWB_CONSIGNMENT_NO
 * @property string CREATE_DATE
 * @property string SENDER_ACCOUNT
 * @property string RECEIVER_ACCOUNT
 * @property string PICKD_CHARGE_TO
 * @property string PICKD_PALLET_QTY
 * @property string PICKD_CARTON_QTY
 * @property string PICKD_SATCHEL_QTY
 * @property string PICKD_WT_CALC
 * @property string PICKD_WT_ACTUAL
 * @property string PICKD_VOL_ACTUAL
 * @property string PICKD_SERVICE_RECORD_ID
 * @property string SENT_FROM_FIRST_NAME
 * @property string SENT_FROM_LAST_NAME
 * @property string SENT_FROM_ADDRESS_1
 * @property string SENT_FROM_ADDRESS_2
 * @property string SENT_FROM_CITY
 * @property string SENT_FROM_SUBURB
 * @property string SENT_FROM_STATE
 * @property string SENT_FROM_POST_CODE
 * @property string SENT_FROM_COUNTRY
 * @property string SENT_FROM_DEPOT_ID
 * @property string SENT_FROM_SERVICE_ACCOUNT
 * @property string POST_CODE_DEPOT_RECORD_ID
 * @property string SENT_FROM_DEPOT_RECORD_ID
 * @property string SENT_FROM_OTHER1
 * @property string SENT_FROM_OTHER2
 * @property string SENT_FROM_OTHER3
 * @property string SENT_FROM_OTHER4
 * @property string SENT_FROM_OTHER5
 * @property string SENT_FROM_OTHER6
 * @property string SENT_FROM_OTHER7
 * @property string SENT_FROM_OTHER8
 * @property string SENT_FROM_OTHER9
 * @property string SENT_FROM_OTHER10
 * */
class ManifestBuilder_Model_PickDespatch extends Zend_Db_Table_Row {
    protected $_tableClass = 'ManifestBuilder_Table_PickDespatch';

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