<?php
class Minder_SysScreen_Model_PersonAddress extends Minder_SysScreen_Model_Editable implements Minder_SysScreen_Model_AddressLabelProvider_Interface
{
    public function __construct() {
        parent::__construct();
    }
    
    public function selectRecordId($rowOffset, $itemCountPerPage) {
        $recordId = array();
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PERSON_ADDRESS.RECORD_ID');

        if (is_array($result) && count($result) > 0)
            $recordId = array_map(create_function('$item', 'return $item["RECORD_ID"];'), $result);
        
        return $recordId;
    }

    public function updateRecords($dataset) {
        parent::validateData($dataset, 'update');
        $updatedAddresses = array();
        
        foreach ($dataset as $rowId => $row) {
            $this->setConditions($this->makeConditionsFromId(array($rowId)));
            $addressId = $this->selectRecordId(0, 1);
            
            $this->updateRow($row, 'PERSON_ADDRESS', array('RECORD_ID = ?' => $addressId));
            $updatedAddresses[] = $addressId[0];
        }
        
        return $updatedAddresses;
    }

    protected function _getPersonIdFromRow($row) {
        foreach ($row as $field) {
            $tmpField = $this->getField($field['column_id']);
            if ($tmpField['SSV_NAME'] == 'PERSON_ID')
                return $field['value'];
        }

        return '';
    }

    protected function _fillPersonId($row, $personId) {
        if (empty($personId))
            $personId = $this->_getPersonIdFromRow($row);

        if (empty($personId))
            throw new Minder_SysScreen_Model_PersonAddress_Exception('PERSON_ID cannot be empty.');

        if (false === ($tmpField = $this->_fieldExists('PERSON_ID')))
            throw new Minder_SysScreen_Model_PersonAddress_Exception('PERSON_ID field not found in dataset.');

        $row[] = array(
            'column_id' => $tmpField['RECORD_ID'],
            'value' => $personId
        );

        return $row;
    }

    public function createRecords($dataset, $personId = null) {
        parent::validateData($dataset, 'create');

        foreach ($dataset as $row) {
            $this->createRow($this->_fillPersonId($row, $personId), 'PERSON_ADDRESS');
        }
        
        return count($dataset);
    }
    
    /**
    * Returns Address Label data to pass into print request
    * 
    * @param int $rowOffset
    * @param int $itemCountPerPage
    * 
    * @return array - each row should contain data for one Address Label. 
    *                 Important: each row should contain _ADDR_TYPE_ field to find out label format!
    */
    public function selectAddressLabelData($rowOffset, $itemCountPerPage) {
        $addressLabels = array();
        
        $recordIds = $this->selectRecordId($rowOffset, $itemCountPerPage);
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $recordIds));
        if (count($recordIds) < 1)
            return $addressLabels;

        $limitExpr = $this->_getLimitExpression($rowOffset, $itemCountPerPage);
        $sql = "
            SELECT " . $limitExpr . " DISTINCT 
                * 
            FROM
                PERSON_ADDRESS
                LEFT OUTER JOIN PERSON ON PERSON_ADDRESS.PERSON_ID = PERSON.PERSON_ID
            WHERE
                PERSON_ADDRESS.RECORD_ID IN (" . substr(str_repeat('?, ', count($recordIds)), 0, -2) . ")
        ";
        
        $args = $recordIds;
        array_unshift($args, $sql);
        
        $minder = Minder::getInstance();
        $addressLabels = call_user_func_array(array($minder, 'fetchAllAssocExt'), $args);
        
        foreach ($addressLabels as &$label) {
            $label['_ADDR_TYPE_'] = isset($label['PERSON_ADDRESS.ADDR_TYPE']) ? $label['PERSON_ADDRESS.ADDR_TYPE'] : '';
        }
        
        return $addressLabels;
    }
}

class Minder_SysScreen_Model_PersonAddress_Exception extends Minder_SysScreen_Model_Exception {}
