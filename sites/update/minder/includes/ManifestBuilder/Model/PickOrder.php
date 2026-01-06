<?php

/**
 * Class ManifestBuilder_Model_PickOrder
 * @property string D_TITLE
 * @property string D_FIRST_NAME
 * @property string D_LAST_NAME
 * @property string P_TITLE
 * @property string P_FIRST_NAME
 * @property string P_LAST_NAME
 * @property string P_ADDRESS_LINE1
 * @property string P_ADDRESS_LINE2
 * @property string P_ADDRESS_LINE3
 * @property string P_ADDRESS_LINE4
 * @property string P_ADDRESS_LINE5
 * @property string D_ADDRESS_LINE1
 * @property string D_ADDRESS_LINE2
 * @property string D_ADDRESS_LINE3
 * @property string D_ADDRESS_LINE4
 * @property string D_ADDRESS_LINE5
 * @property string P_POST_CODE
 * @property string P_CITY
 * @property string P_STATE
 * @property string P_COUNTRY
 * @property string D_POST_CODE
 * @property string D_CITY
 * @property string D_STATE
 * @property string D_COUNTRY
 * @property string OTHER1
 * @property string OTHER5
 * @property string OTHER6
 * @property string OTHER7
 * @property string OTHER9
 * @property string PICK_ORDER
 * @property string COMPANY_ID
 */
class ManifestBuilder_Model_PickOrder extends Zend_Db_Table_Row {
    protected $_tableClass = 'ManifestBuilder_Table_PickOrder';

    public function getReceiverName() {
        $title      = empty($this->D_FIRST_NAME) ? $this->P_TITLE : $this->D_TITLE;
        $firstName  = empty($this->D_FIRST_NAME) ? $this->P_FIRST_NAME : $this->D_FIRST_NAME;
        $lastName   = empty($this->D_FIRST_NAME) ? $this->P_LAST_NAME : $this->D_LAST_NAME;

        return empty($title) ? $firstName : $title . ' ' . $firstName . ' ' . $lastName;
    }

    public function getReceiverAddress() {
        return empty($this->D_FIRST_NAME) ?
            trim($this->P_ADDRESS_LINE1) . ' ' . trim($this->P_ADDRESS_LINE2) . ' ' . trim($this->P_ADDRESS_LINE3) . ' ' . trim($this->P_ADDRESS_LINE4) . ' ' . trim($this->P_ADDRESS_LINE5) :
            trim($this->D_ADDRESS_LINE1) . ' ' . trim($this->D_ADDRESS_LINE2) . ' ' . trim($this->D_ADDRESS_LINE3) . ' ' . trim($this->D_ADDRESS_LINE4) . ' ' . trim($this->D_ADDRESS_LINE5);
    }

    public function getReceiverPostCode() {
        return empty($this->D_FIRST_NAME) ? $this->P_POST_CODE : $this->D_POST_CODE;
    }

    public function getReceiverState() {
        return empty($this->D_FIRST_NAME) ? $this->P_STATE : $this->D_STATE;
    }

    public function getReceiverSuburb() {
        //return empty($this->D_FIRST_NAME) ? $this->P_CITY : $this->D_CITY;
	if (empty($this->D_FIRST_NAME)) {
		return empty($this->P_SUBURB) ? $this->P_CITY : $this->P_SUBURB;
	} else {
		return empty($this->D_SUBURB) ? $this->D_CITY : $this->D_SUBURB;
	} 
    }

    public function getReceiverCountry() {
        return empty($this->D_FIRST_NAME) ? $this->P_COUNTRY : $this->D_COUNTRY;
    }

}
