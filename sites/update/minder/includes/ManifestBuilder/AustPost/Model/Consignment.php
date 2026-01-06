<?php

/**
 * @property string AWB_CONSIGNMENT_NO
 * @property string SERVICE_CHARGE_CODES

 * @property string PICK_ORDER
 * @property string P_TITLE
 * @property string P_FIRST_NAME
 * @property string P_LAST_NAME
 * @property string P_ADDRESS_LINE1
 * @property string P_ADDRESS_LINE2
 * @property string P_ADDRESS_LINE3
 * @property string P_ADDRESS_LINE4
 * @property string P_ADDRESS_LINE5
 * @property string P_CITY
 * @property string P_STATE
 * @property string P_POST_CODE
 * @property string P_COUNTRY

 * @property string D_TITLE
 * @property string D_FIRST_NAME
 * @property string D_LAST_NAME
 * @property string D_ADDRESS_LINE1
 * @property string D_ADDRESS_LINE2
 * @property string D_ADDRESS_LINE3
 * @property string D_ADDRESS_LINE4
 * @property string D_ADDRESS_LINE5
 * @property string D_CITY
 * @property string D_STATE
 * @property string D_POST_CODE
 * @property string D_COUNTRY

 * @property string OTHER5
 * @property string OTHER6
 * @property string OTHER9
 *
 * @property string ADDRESS_LINE1
 * @property string ADDRESS_LINE2
 * @property string ADDRESS_LINE3
 * @property string ADDRESS_LINE4
 * @property string ADDRESS_LINE5
 * @property string CITY
 * @property string COUNTRY
 * @property string STATE
 * @property string POST_CODE
 * @property string FIRST_NAME
 *
 * @property string ACCOUNT
 * @property string SERVICE_SIGNATURE_REQD
 * @property string SERVICE_PARTIAL_DELIVERY
 *
 * @property string EMAIL_TRACKING
 */
class ManifestBuilder_AustPost_Model_Consignment extends ArrayObject {

    public $createDateTime;
    public $containsDangerousGoods = 'false';

    const ADDRESS_LINE1_LEN = 40;
    const ADDRESS_LINE2_LEN = 60;
    const ADDRESS_LINE3_LEN = 40;
    const ADDRESS_LINE4_LEN = 40;

    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct(empty($input) ? array() : $input, static::ARRAY_AS_PROPS, $iterator_class);
    }

    public function getConnoteNo() {
        return trim($this->AWB_CONSIGNMENT_NO);
    }

    public function getServiceChargeCode() {
        return trim($this->SERVICE_CHARGE_CODES);
    }

    public  function getDeliveryName() {
        $title     = (empty($this->D_FIRST_NAME)) ? $this->P_TITLE      : $this->D_TITLE;
        $firstName = (empty($this->D_FIRST_NAME)) ? $this->P_FIRST_NAME : $this->D_FIRST_NAME;
        $lastName  = (empty($this->D_FIRST_NAME)) ? $this->P_LAST_NAME  : $this->D_LAST_NAME;

        return empty($title) ? $firstName : $title . ' ' . $firstName . ' ' . $lastName;
    }

    protected function getDeliveryAddress() {
        $addressLine = (empty($this->D_FIRST_NAME)) ?
            $this->P_ADDRESS_LINE1 . $this->P_ADDRESS_LINE2 . $this->P_ADDRESS_LINE3 . $this->P_ADDRESS_LINE4 . $this->P_ADDRESS_LINE5
            : $this->D_ADDRESS_LINE1 . $this->D_ADDRESS_LINE2 . $this->D_ADDRESS_LINE3 . $this->D_ADDRESS_LINE4 . $this->D_ADDRESS_LINE5;

        return $addressLine;
    }

    public function getDeliveryAddressLine1() {
        return substr($this->getDeliveryAddress(), 0, static::ADDRESS_LINE1_LEN);
    }

    public function hasDeliveryAddressLine2() {
        return strlen($this->getDeliveryAddress()) >= static::ADDRESS_LINE1_LEN;
    }

    public function getDeliveryAddressLine2() {
        return substr($this->getDeliveryAddress(), static::ADDRESS_LINE1_LEN, static::ADDRESS_LINE2_LEN);
    }

    public function hasDeliveryAddressLine3() {
        return strlen($this->getDeliveryAddress()) >= static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN;
    }

    public function getDeliveryAddressLine3() {
        return substr($this->getDeliveryAddress(), static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN, static::ADDRESS_LINE3_LEN);
    }

    public function hasDeliveryAddressLine4() {
        return strlen($this->getDeliveryAddress()) >= static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN + static::ADDRESS_LINE3_LEN;
    }

    public function getDeliveryAddressLine4() {
        return substr($this->getDeliveryAddress(), static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN + static::ADDRESS_LINE3_LEN, static::ADDRESS_LINE4_LEN);
    }

    public function getDeliverySuburb() {
        return (empty($this->D_FIRST_NAME)) ? $this->P_CITY      : $this->D_CITY;
    }

    public function getDeliveryStateCode() {
        return (empty($this->D_FIRST_NAME)) ? $this->P_STATE      : $this->D_STATE;
    }

    public function getDeliveryPostCode() {
        return (empty($this->D_FIRST_NAME)) ? $this->P_POST_CODE      : $this->D_POST_CODE;
    }

    public function getDeliveryCountryCode() {
        $tmpDeliveryCountry = strtoupper(empty($this->D_COUNTRY) ? $this->P_COUNTRY : $this->D_COUNTRY);
        return (in_array($tmpDeliveryCountry, array('AU', 'AUSTRALIA', ''))) ? 'AU' : $tmpDeliveryCountry;
    }

    public function getDeliveryEmailAddress() {
        return $this->OTHER9;
    }

    public function hasDeliveryEmailAddress() {
        //return strlen($this->getDeliveryEmailAddress()) > 0 ;
        return strlen($this->getDeliveryEmailAddress()) > 0  and $this->EMAIL_TRACKING == "T";
    }

    public function getDeliveryPhoneNumber() {
        return $this->OTHER5;
    }

    public function hasDeliveryPhoneNumber() {
        //return strlen($this->getDeliveryPhoneNumber()) > 0 ;
        return strlen($this->getDeliveryPhoneNumber()) > 0 and $this->EMAIL_TRACKING == "T" ;
    }


    public function getIsInternationalDelivery() {
        if ($this->getDeliveryCountryCode() == 'AU')
            return 'false';

        return 'true';
    }

    public function getReturnName() {
        return $this->FIRST_NAME;
    }

    public function hasReturnName() {
        return strlen($this->getReturnName()) > 0 and $this->EMAIL_TRACKING == "T";
    }

    public function getReturnAddress() {
        return $this->ADDRESS_LINE1 . $this->ADDRESS_LINE2 . $this->ADDRESS_LINE3 . $this->ADDRESS_LINE4 . $this->ADDRESS_LINE5;
    }

    public function getReturnAddressLine1() {
        return substr($this->getReturnAddress(), 0, static::ADDRESS_LINE1_LEN);
    }

    public function hasReturnAddressLine2() {
        return strlen($this->getReturnAddress()) >= static::ADDRESS_LINE1_LEN;
    }

    public function getReturnAddressLine2() {
        return substr($this->getReturnAddress(), static::ADDRESS_LINE1_LEN, static::ADDRESS_LINE2_LEN);
    }

    public function hasReturnAddressLine3() {
        return strlen($this->getReturnAddress()) >= static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN;
    }

    public function getReturnAddressLine3() {
        return substr($this->getReturnAddress(), static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN, static::ADDRESS_LINE3_LEN);
    }

    public function hasReturnAddressLine4() {
        return strlen($this->getReturnAddress()) >= static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN + static::ADDRESS_LINE3_LEN;
    }

    public function getReturnAddressLine4() {
        return substr($this->getReturnAddress(), static::ADDRESS_LINE1_LEN + static::ADDRESS_LINE2_LEN + static::ADDRESS_LINE3_LEN, static::ADDRESS_LINE4_LEN);
    }

    public function getIsSignatureRequired() {
        return ($this->SERVICE_SIGNATURE_REQD == 'T') ? 'Y' : 'N';
    }

    public function getDeliverPartConsignment() {
        return ($this->SERVICE_PARTIAL_DELIVERY == 'T') ? 'Y' : 'N';
    }

    public function getReference1() {
        return $this->PICK_ORDER;
    }

    public function getReference2() {
        return $this->OTHER6;
    }
}
