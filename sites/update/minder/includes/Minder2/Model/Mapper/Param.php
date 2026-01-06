<?php

class Minder2_Model_Mapper_Param extends Minder2_Model_Mapper_Abstract {
    public function find($dataId) {
        $sql = "SELECT * FROM PARAM WHERE DATA_ID = ?";

        $sqlResult = $this->_getMinder()->fetchAssoc($sql, $dataId);
        if (false == $sqlResult) {
            $paramObject = new Minder2_Model_Param();
        } else {
            $paramObject = new Minder2_Model_Param($sqlResult);
            $paramObject->existed = true;
        }

        return $paramObject;
    }
}