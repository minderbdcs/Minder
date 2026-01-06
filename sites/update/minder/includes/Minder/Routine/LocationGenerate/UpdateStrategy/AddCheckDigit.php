<?php

class Minder_Routine_LocationGenerate_UpdateStrategy_AddCheckDigit extends Minder_Routine_LocationGenerate_UpdateStrategy {

    /**
     * @param Location $location - LOCATION fields
     * @return int - amount of inserted locations
     */
    function _insertLocation($location)
    {
        return 0; //do not insert rows
    }

    /**
     * @param Location $newLocation
     * @param Location $originalLocation
     * @return int
     */
    function _updateLocation($newLocation, $originalLocation)
    {
        if ($newLocation->hadCheckDigit) {
            return 0; //do not update existed check digit
        }

        return parent::_updateLocation($newLocation, $originalLocation);
    }
}