<?php

class Minder_Routine_LocationGenerate_UpdateStrategy_UpdateAndInsert extends Minder_Routine_LocationGenerate_UpdateStrategy {
    /**
     * @param Location $location - LOCATION fields
     * @throws Exception
     * @return int - amount of inserted locations
     */
    function _insertLocation($location)
    {
        if (count($location->items) < 1)
            return 0;

        $sql = 'INSERT INTO LOCATION (' . implode(', ', array_keys($location->items)) . ') VALUES (' . substr(str_repeat('?, ', count($location->items)), 0, -2) . ')';

        if (false === ($result = $this->_getMinder()->execSQL($sql, array_values($location->items))))
            throw new Exception('Error inserting rows into LOCATION: ' . $this->_getMinder()->lastError);

        return $result;
    }
}