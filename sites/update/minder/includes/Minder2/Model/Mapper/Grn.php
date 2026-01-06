<?php

class Minder2_Model_Mapper_Grn extends  Minder2_Model_Mapper_Abstract {
    /**
     * @param string $grn
     * @return Minder2_Model_Grn
     */
    public function find($grn) {
        if (empty($grn))
            return new Minder2_Model_Grn();

        $sql = "SELECT * FROM GRN WHERE GRN = ?";

        $selectResult = $this->_getMinder()->fetchAssoc($sql, $grn);

        if (false === $selectResult) {
            return new Minder2_Model_Grn();
        }

        $grnObject = new Minder2_Model_Grn($selectResult);
        $grnObject->existed = true;

        return $grnObject;
    }
}