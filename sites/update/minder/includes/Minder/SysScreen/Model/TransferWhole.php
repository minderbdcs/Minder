<?php

class Minder_SysScreen_Model_TransferWhole extends Minder_SysScreen_Model {
    public function filterLocation($location) {
        $whId   = substr($location, 0, 2);
        $locnId = substr($location, 2);

        $this->setConditions(array('ISSN.WH_ID = ?' => array($whId), 'ISSN.LOCN_ID = ?' => array($locnId)));
    }

    public function selectLocation($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'ISSN.WH_ID || ISSN.LOCN_ID AS LOCATION');
    }

    public function getFromLocation() {
        $locationList = $this->selectLocation(0, 1);

        if (count($locationList) < 1)
            return null;

        $firstLocation = current($locationList);

        return $firstLocation['LOCATION'];
    }
}