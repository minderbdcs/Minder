<?php

abstract class Minder_Routine_LocationGenerate_UpdateStrategy {

    protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @param Location $location
     * @return null | Location
     */
    protected function _findLocation($location) {
        $whId   = (isset($location['WH_ID']))   ? $location['WH_ID']   : '';
        $locnId = (isset($location['LOCN_ID'])) ? $location['LOCN_ID'] : '';
        $result = $this->_getMinder()->fetchAssoc('SELECT * FROM LOCATION WHERE WH_ID = ? AND LOCN_ID = ?', $whId, $locnId);

        if (false === $result)
            return null;
        else
            return new Location($result);
    }

    /**
     * @param Location $newLocation - LOCATION fields
     * @param Location $originalLocation
     * @throws Exception
     * @return int - amount of updated locations
     */
    function _updateLocation($newLocation, $originalLocation) {
        $fields = array();
        $values = array();
        foreach ($newLocation->items as $field => $value) {
            if ($field == 'WH_ID' || $field == 'LOCN_ID') continue;

            $fields[] = $field . ' = ?';
            $values[] = $value;
        }

        if (count($fields) < 1)
            return 0;

        $sql = 'UPDATE LOCATION SET ' . implode(', ', $fields) . ' WHERE WH_ID = ? AND LOCN_ID = ?';

        $whID   = isset($newLocation['WH_ID'])   ? $newLocation['WH_ID']   : '';
        $locnID = isset($newLocation['LOCN_ID']) ? $newLocation['LOCN_ID'] : '';

        array_push($values, $whID, $locnID);

        if (false === ($result = $this->_getMinder()->execSQL($sql, $values)))
            throw new Exception('Error Updating LOCATION: ' . $this->_getMinder()->lastError);

        return $result;
    }

    /**
     * @abstract
     * @param Location $location - LOCATION fields
     * @return int - amount of inserted locations
     */
    abstract function _insertLocation($location);

    /**
     * @param array $locationList
     * @return Minder_Routine_LocationGenerate_UpdateResult
     */
    public function update($locationList) {
        $result = new Minder_Routine_LocationGenerate_UpdateResult();

        foreach ($locationList as $location) {
            $foundLocation = $this->_findLocation($location);

            if (is_null($foundLocation))
                $result->inserted += $this->_insertLocation($location);
            else
                $result->updated  += $this->_updateLocation($location, $foundLocation);
        }

        return $result;
    }
}