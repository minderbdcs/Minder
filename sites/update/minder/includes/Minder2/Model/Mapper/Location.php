<?php

class Minder2_Model_Mapper_Location extends Minder2_Model_Mapper_Abstract {
    public function fetchReceiveLocations() {
        $sql = "SELECT * FROM LOCATION WHERE MOVE_STAT=? AND LOCN_STAT = ?";

        $selectResult = $this->_getMinder()->fetchAllAssoc($sql, 'RC', 'OK');

        $result = array();
        foreach ($selectResult as $resultRow) {
            $tmpLocation = new Minder2_Model_Location($resultRow);
            $tmpLocation->existed = true;
            $result[] = $tmpLocation;
        }

        return $result;
    }
}