<?php

class Minder_ConnoteProccess_LabelGenerator {
    protected $carrierId   = '';
    protected $serviceType = '';
    
    protected $connoteLabelFormat = array();
    protected $packIdLabelFormat  = array();
    
    protected $fields = array();
    
    protected $serialNo = '';
    
    public function __construct($carrierId = '', $serviceType = '') {
        $this->carrierId   = $carrierId;
        $this->serviceType = $serviceType;
    }
    
    protected function buildConnoteLabelDescription() {
        $this->connoteLabelFormat = array('CARRIER_SERVICE.SERVICE_LOCATION_ID', '%SERIAL_NO'); //default CONNOTE label format used by APCD

        if (!isset($this->fields['CARRIER_SERVICE.CS_LABEL_TYPE']) || empty($this->fields['CARRIER_SERVICE.CS_LABEL_TYPE']))
            return;

        $minder = Minder::getInstance();
        $optionDescription = $minder->getOptionsRecordByCode('CONNOTE', $this->fields['CARRIER_SERVICE.CS_LABEL_TYPE']);

        if (count($optionDescription) > 0) {
            $optionDescription = current($optionDescription);
            $this->connoteLabelFormat = explode('|', $optionDescription['DESCRIPTION2']);
        }
    }
    
    protected function buildPackIdLabelDescription() {
        if (!isset($this->fields['CARRIER_SERVICE.CS_LABEL_TYPE']) || empty($this->fields['CARRIER_SERVICE.CS_LABEL_TYPE']))
            return;
        
        $minder = Minder::getInstance();
        $optionDescription = $minder->getOptionsRecordByCode('PACK_ID', $this->fields['CARRIER_SERVICE.CS_LABEL_TYPE']);
        
        if (count($optionDescription) > 0) {
            $optionDescription = current($optionDescription);
            $this->packIdLabelFormat = explode('|', $optionDescription['DESCRIPTION2']);
        }
    }
    
    public function init($carrierId = '', $serviceType = '') {
        $this->carrierId   = (empty($carrierId)) ? $this->carrierId : $carrierId;
        $this->serviceType = (empty($serviceType)) ? $this->serviceType : $serviceType;
        
        $minder = Minder::getInstance();
        
        $sql = "
            SELECT 
                CARRIER.CONNOTE_GENERATOR,
                CARRIER.CONNOTE_CHECK_DIGIT_METHOD,
                CARRIER.DEFAULT_CONNOTE_ISSO,
                CARRIER.CONNOTE_START_NO,
                CARRIER.CONNOTE_END_NO,
                CARRIER.PREFIX,
                CARRIER.ACCOUNT,
                CARRIER.DEFAULT_CONNOTE_TYPE,
                CARRIER.CUSTOMER_CODE,

                CARRIER.PACK_LABEL_START_NO,
                CARRIER.PACK_LABEL_END_NO,
                CARRIER.PACK_LABEL_GENERATOR,
                CARRIER.PACK_LABEL_PARAM_ID,
                CARRIER.PACK_LABEL_PREFIX,
                CARRIER.PACK_LABEL_SUFFIX,


                CARRIER_SERVICE.CARRIER_ID,
                CARRIER_SERVICE.SERVICE_TYPE,
                CARRIER_SERVICE.DESCRIPTION,
                CARRIER_SERVICE.SERVICE_SERVICE_CODE,
                CARRIER_SERVICE.CS_LABEL_TYPE,
                CARRIER_SERVICE.SERVICE_SIGNATURE_REQD,
                CARRIER_SERVICE.SERVICE_MULTIPART_CONSIGNMENT,
                CARRIER_SERVICE.SERVICE_PARTIAL_DELIVERY,
                CARRIER_SERVICE.SERVICE_CASH_TO_COLLECT,
                CARRIER_SERVICE.SERVICE_PROFILE_ID,
                CARRIER_SERVICE.SERVICE_CHARGE_CODES,
                CARRIER_SERVICE.SERVICE_LOCATION_ID
            FROM 
                CARRIER
                LEFT JOIN CARRIER_SERVICE ON CARRIER.CARRIER_ID = CARRIER_SERVICE.CARRIER_ID
            WHERE
                CARRIER_SERVICE.CARRIER_ID = ? 
                AND CARRIER_SERVICE.SERVICE_TYPE = ?
        ";
        
        if (false === ($this->fields = $minder->fetchAllAssocExt($sql, $this->carrierId, $this->serviceType))) 
            throw new Minder_ConnoteProccess_LabelGenerator_Exception('Cannot get label description: ' . $minder->lastError);
        
        if (empty($this->fields))
            throw new Minder_ConnoteProccess_LabelGenerator_Exception('Cannot get label description: No description found for CARRIER_ID = "' . $this->carrierId . '" and SERVICE_TYPE = "' . $this->serviceType . '" combination.');
        
        $this->fields = current($this->fields);
        
        $this->buildConnoteLabelDescription();
        $this->buildPackIdLabelDescription();
    }
    
    public function addFields($fields = array()) {
        $this->fields = array_merge($this->fields, $fields);
    }
    
    protected function getParamValue($paramName) {
        $args = func_get_args();
        
        switch (true) {
            case $paramName == '%SERIAL_NO':
                return $this->serialNo;
            case array_key_exists($paramName, $this->fields):
                return $this->fields[$paramName];
            case $paramName == '%CHECK_DIGIT':
                if (empty($this->fields['CARRIER.CONNOTE_CHECK_DIGIT_METHOD']))
                    throw new Minder_ConnoteProccess_LabelGenerator_Exception('CONNOTE_CHECK_DIGIT_METHOD is not defined.');
            
            
                $noToCheck = $this->fields['CARRIER_SERVICE.SERVICE_LOCATION_ID']
                             . $this->fields['PACK_ID.PACK_SERIAL_NO']
                             . $this->fields['PACK_ID.PACK_SEQUENCE_NO']
                             . $this->fields['CARRIER_SERVICE.SERVICE_SERVICE_CODE']
                             . $this->fields['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR'];
                             
                $sql       = "SELECT out_data FROM CALC_CHECK_DIGIT_BDCS('" . $noToCheck . "', 'T')";
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $sql));
                $minder = Minder::getInstance();
                $noWithCheckDigit = $minder->findValue($sql);
                $checkDigit = substr($noWithCheckDigit, 15, 1);
                
                return $checkDigit;
            case $paramName == '%CONNOTE_TYPE':
                if (isset($this->fields['PICK_DESPATCH.PICKD_CONNOTE_TYPE'])) {
                    return trim($this->fields['PICK_DESPATCH.PICKD_CONNOTE_TYPE']);
                } else {
                    return isset($this->fields['CARRIER.DEFAULT_CONNOTE_TYPE']) ?  trim($this->fields['CARRIER.DEFAULT_CONNOTE_TYPE']) : '';
                }
            case $paramName == '%CARRIER_PREFIX':
                // use the carrier.prefix field
                // translate 0-9 to be '00' - '09'
                // translate A-Z to be '10' - '35'
                $wkResult = "";
                for ($wkIdx = 0; $wkIdx < strlen($this->fields['CARRIER.PREFIX']) ; $wkIdx++)
                {
                    $wkChar = $this->fields['CARRIER.PREFIX'][$wkIdx];
                    if ($wkChar >= '0' and $wkChar <= '9') {
                        $wkResult = $wkResult . '0' . $wkChar;
                    } else {
                        $wkNum = ord($wkChar) - 55; 
                        if ($wkNum > 9) {
                            $wkResult = $wkResult . $wkNum;
                        }
                    }
                }

                return $wkResult;
            case $paramName == '%PACK_LABEL_NUMBER':
            case $paramName == '%LABEL_TYPE':
                return isset($this->fields['PACK_ID.PACK_LABEL_TYPE']) ?  trim($this->fields['PACK_ID.PACK_LABEL_TYPE']) : '';
            case $paramName == '%PACK_LABEL_NUMBER_7':
            case $paramName == '%PACK_SERIAL_NO_7':
                return str_pad($this->fields['PACK_ID.PACK_SERIAL_NO'], 7, '0', STR_PAD_LEFT);
            case $paramName == '%PACK_NO_3':
                return str_pad($this->fields['PACK_ID.PACK_NO'], 3, '0', STR_PAD_LEFT);
            default:
                return $paramName;
        }
    }
    
    public function getNextConnote($pickOrder) {
        if ($this->fields['CARRIER.DEFAULT_CONNOTE_ISSO'] == 'T')
            return $pickOrder;
            
        if (empty($this->fields['CARRIER.CONNOTE_GENERATOR']))
            return '';
            
        $minder = Minder::getInstance();
            
        $sql            = 'SELECT GEN_ID(' . $this->fields['CARRIER.CONNOTE_GENERATOR'] . ', 1 ) FROM RDB$DATABASE;';
        $this->serialNo = $minder->findValue($sql);
	if (empty($this->fields['CARRIER.CONNOTE_START_NO']))
            $this->fields['CARRIER.CONNOTE_START_NO'] = 0;
	if (empty($this->fields['CARRIER.CONNOTE_END_NO']))
            $this->fields['CARRIER.CONNOTE_END_NO'] = 9999999;
        $wkDiff = 0;
        if ($this->fields['CARRIER.CONNOTE_START_NO'] > $this->serialNo)
            $wkDiff += $this->fields['CARRIER.CONNOTE_START_NO'] ;
        if ($this->fields['CARRIER.CONNOTE_END_NO'] < $this->serialNo)
            $wkDiff -= $this->fields['CARRIER.CONNOTE_END_NO'] ;
        if ($wkDiff != 0)
        {
            $sql           = 'SELECT GEN_ID(' . $this->fields['CARRIER.CONNOTE_GENERATOR'] . ', ' . $wkDiff . ' ) FROM RDB$DATABASE;';
            $this->serialNo = $minder->findValue($sql);
        }

	// how many digits
	$wkLen = strlen((string)$this->fields['CARRIER.CONNOTE_END_NO']);
	$wkConnoteLen = max($wkLen, 7);
        //$this->serialNo = str_pad($this->serialNo, 7, '0', STR_PAD_LEFT);
        $this->serialNo = str_pad($this->serialNo, $wkConnoteLen, '0', STR_PAD_LEFT);
        
        $connoteNo = '';
        foreach ($this->connoteLabelFormat as $paramName)
            $connoteNo .= $this->getParamValue($paramName);
            
        return $connoteNo;
    }
    
    public function getSerialNo() {
        return $this->serialNo;
    }
    
    public function getPackIdLabel() {
        $label = '';
        foreach ($this->packIdLabelFormat as $paramName)
            $label .= $this->getParamValue($paramName);
            
        return $label;
    }

    public function getNextPackLabelNumber()
    {
        if (empty($this->fields['CARRIER.PACK_LABEL_GENERATOR'])) {
            //for backward compatibility with AUSTPOST
            return $this->serialNo;
        }

        $minder = Minder::getInstance();

        $sql            = 'SELECT GEN_ID(' . $this->fields['CARRIER.PACK_LABEL_GENERATOR'] . ', 1 ) FROM RDB$DATABASE;';
        $result = $minder->findValue($sql);
        if (empty($this->fields['CARRIER.PACK_LABEL_START_NO']))
            $this->fields['CARRIER.PACK_LABEL_START_NO'] = 0;
        if (empty($this->fields['CARRIER.PACK_LABEL_END_NO']))
            $this->fields['CARRIER.PACK_LABEL_END_NO'] = 9999999;
        $wkDiff = 0;
        if ($this->fields['CARRIER.PACK_LABEL_START_NO'] > $result)
            $wkDiff += $this->fields['CARRIER.PACK_LABEL_START_NO'] ;
        if ($this->fields['CARRIER.PACK_LABEL_END_NO'] < $result)
            $wkDiff -= $this->fields['CARRIER.PACK_LABEL_END_NO'] ;
        if ($wkDiff != 0)
        {
            $sql            = 'SELECT GEN_ID(' . $this->fields['CARRIER.PACK_LABEL_GENERATOR'] . ', ' . $wkDiff . ' ) FROM RDB$DATABASE;';
            $result = $minder->findValue($sql);
        }

        return $result;
    }
}

class Minder_ConnoteProccess_LabelGenerator_Exception extends Exception {}
