<?php

    /**
     * @param ManifestBuilder_Model_CarrierService $carrier
     * @return ManifestBuilder_TnT_Model_Person
     */
class ManifestBuilder_TnT_Table_Person extends Zend_Db_Table {
    protected $_name = 'PERSON';
    protected $_rowClass = 'ManifestBuilder_TnT_Model_Person';

    public function _getSentFromPerson(ManifestBuilder_Model_CarrierService $carrier) {

//var_dump($carrier);
//var_dump($carrier->CARRIER_ID);exit;

        $select = $this->select();

        $select->from('PERSON', ARRAY('FIRST_NAME', 'PERSON_ID') )

            ->where('PERSON.PERSON_ID = ?', array($carrier->CARRIER_ID))
            ->limit(1);
        $result = $this->fetchRow($select);

        return is_null($result) ? $this->createRow(array()) : $result;
    }
}
