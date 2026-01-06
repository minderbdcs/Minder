<?php

class Minder2_Mapper_SsnType extends Minder2_Model_Mapper_Abstract {
    public function fetchAll($fetchMode = self::FETCH_MODE_MODEL) {
        $sql = "SELECT * FROM SSN_TYPE ORDER BY DESCRIPTION";

        $result = array();

        $sqlResult = Minder::getInstance()->fetchAllAssoc($sql);

        if (false === $sqlResult)
            return $result;

        foreach ($sqlResult as $resultRow) {
            switch ($fetchMode) {
                case (self::FETCH_MODE_ARRAY):
                    $result[] = $resultRow;
                    break;
                case self::FETCH_MODE_MODEL:
                default:
                    $result[] = new Minder2_Model($resultRow);
            }
        }

        return $result;
    }

}