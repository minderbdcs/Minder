<?php

class Minder_Routine_LocationGenerate_UpdateStrategy_Refresh extends Minder_Routine_LocationGenerate_UpdateStrategy {

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
        if (!empty($newLocation->items['LOCN_ID2'])
            && !empty($originalLocation->items['LOCN_ID2'])
            && $originalLocation->items['LOCN_ID2'] !== $newLocation->items['LOCN_ID2']) {
            return 0; //do not update not empty LOCN_ID2 value
        }

        return parent::_updateLocation($newLocation, $originalLocation);
    }
}