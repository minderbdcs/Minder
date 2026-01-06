<?php

class Minder_Issn_Mapper {

    /**
     * @param $borrowerId
     * @return Minder_Issn_Collection
     * @throws Minder_Exception
     */
    public function getBorrowerIssns($borrowerId) {
        $result = new Minder_Issn_Collection();

        $sql = "SELECT * FROM ISSN WHERE WH_ID = 'XB' AND LOCN_ID = ?";

        return $result->fromArray($this->_getMinder()->fetchAllAssoc($sql, $borrowerId));
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}