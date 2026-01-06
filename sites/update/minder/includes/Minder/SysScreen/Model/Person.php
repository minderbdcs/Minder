<?php
class Minder_SysScreen_Model_Person extends Minder_SysScreen_Model_Editable
{
    public function __construct() {
        parent::__construct();
    }
    
    public function selectPersonId($rowOffset, $itemCountPerPage) {
        $personId = array();
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PERSON.PERSON_ID');

        if (is_array($result) && count($result) > 0)
            $personId = array_map(create_function('$item', 'return $item["PERSON_ID"];'), $result);
        
        return $personId;
    }

    public function selectPersonIdAndCompanyId($rowOffset, $itemCountPerPage) {
        $selectResult = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PERSON.PERSON_ID, PERSON.COMPANY_ID');

        if (is_array($selectResult) && count($selectResult) > 0)
            return $selectResult;;

        return array();
    }
    
    public function updateRecords($dataset) {
        parent::validateData($dataset, 'update');
        $updatedPersons = array();
        
        foreach ($dataset as $rowId => $row) {
            $this->setConditions($this->makeConditionsFromId(array($rowId)));

            $selectResult = $this->selectPersonIdAndCompanyId(0, 1);

            if (empty($selectResult))
                throw new Minder_SysScreen_Model_Person_Exception('PERSON #' . $rowId . ' not found.');

            $selectResult = $selectResult[0];
            
            $this->updateRow($row, 'PERSON', array('PERSON_ID = ?' => array($selectResult['PERSON_ID']), 'COMPANY_ID = ?' => array($selectResult['COMPANY_ID'])));
            $updatedPersons[] = '(' . $selectResult['PERSON_ID'] . ', ' . $selectResult['COMPANY_ID'] . ')';
        }
        
        return $updatedPersons;
    }
    
    public function createRecords($dataset) {
        parent::validateData($dataset, 'create');
        
        foreach ($dataset as $row) {
            $this->createRow($row, 'PERSON');
        }
        
        return count($dataset);
    }

    protected function validateField($dataField, $validateFor = 'update')
    {
        $fieldDesc = $this->getField($dataField['column_id']);

        if ($fieldDesc['SSV_NAME'] == 'PERSON_ID' && empty($dataField['value']))
            throw new Minder_SysScreen_Model_Person_Exception('PERSON_ID cannot be empty.');

        if ($fieldDesc['SSV_NAME'] == 'COMPANY_ID' && empty($dataField['value']))
            throw new Minder_SysScreen_Model_Person_Exception('COMPANY_ID cannot be empty.');

        parent::validateField($dataField, $validateFor);
    }


}

class Minder_SysScreen_Model_Person_Exception extends Minder_SysScreen_Model_Exception {}