<?php

class Minder_Routine_LocationGenerate_UpdateStrategy_Update extends Minder_Routine_LocationGenerate_UpdateStrategy {
    /**
     * @param array $location - LOCATION fields
     * @return int - amount of inserted locations
     */
    function _insertLocation($location)
    {
        return 0; //do not insert new rows
    }

}