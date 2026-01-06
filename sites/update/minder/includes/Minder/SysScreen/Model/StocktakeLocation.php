<?php

class Minder_SysScreen_Model_StocktakeLocation extends Minder_SysScreen_Model {

    protected function getLocnIdandWhId($rowOffset, $itemsCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemsCountPerPage, 'DISTINCT WH_ID, LOCN_ID');
    }

    /**
     * @param Minder_JSResponse $result
     * @return Minder_JSResponse
     */
    public function releaseLocations($result) {
        $locations = $this->getLocnIdandWhId(0, count($this));

        if (empty($locations))
            return $result;

        foreach ($locations as $location) {
            $releaseResult = $this->_getMinder()->releaseLocation($location['WH_ID'], $location['LOCN_ID']);

            if (false === $releaseResult) {
                $result->errors[] = $this->_getMinder()->lastError;
            } else {
                $result->messages[] = $location['WH_ID'] . $location['LOCN_ID'] . '. ' . $releaseResult[0];
            }
        }

        return $result;
    }
}