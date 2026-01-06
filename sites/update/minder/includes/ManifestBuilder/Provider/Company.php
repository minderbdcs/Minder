<?php

class ManifestBuilder_Provider_Company {

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    public function getCurrentCompany() {
        $select = new Zend_Db_Select($this->getDbAdapter());

        $select->from('CONTROL', array('DEFAULT_COMPANY_ID' => 'COMPANY_ID'))
            ->join('PERSON', 'CONTROL.COMPANY_ID = PERSON.COMPANY_ID' , array('PERSON_ID', 'ADDRESS_LINE1', 'ADDRESS_LINE2', 'ADDRESS_LINE3', 'ADDRESS_LINE4', 'ADDRESS_LINE5', 'CITY', 'COUNTRY', 'STATE', 'POST_CODE'))
            ->limit(1, 0);

        return new ManifestBuilder_Model_Company($select->query()->fetch());
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
        return $this;
    }
}