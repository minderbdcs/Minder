<?php


class Minder_Routine_LocationGenerate {

    const UPDATE            = 'UPDATE';
    const UPDATE_AND_INSERT = 'UPDATE_AND_INSERT';
    const ADD_CHECK_DIGIT   = 'ADD_CHECK_DIGIT';
    const REFRESH           = 'REFRESH';

    /**
     * @param $method
     * @return Minder_Routine_LocationGenerate_UpdateStrategy
     * @throws Exception
     */
    protected function _getUpdateStrategy($method) {
        switch ($method) {
            case self::UPDATE:
                return new Minder_Routine_LocationGenerate_UpdateStrategy_Update();
            case self::UPDATE_AND_INSERT:
                return new Minder_Routine_LocationGenerate_UpdateStrategy_UpdateAndInsert();
            case self::ADD_CHECK_DIGIT:
                return new Minder_Routine_LocationGenerate_UpdateStrategy_AddCheckDigit();
            case self::REFRESH:
                return new Minder_Routine_LocationGenerate_UpdateStrategy_Refresh();
            default:
                throw new Exception('Bad Location Update method: ' . $method);
        }
    }

    /**
     * @param array $locationList
     * @param string $method
     * @return Minder_Routine_LocationGenerate_UpdateResult
     */
    public function updateLocation($locationList, $method = self::UPDATE_AND_INSERT) {
        return $this->_getUpdateStrategy($method)->update($locationList);
    }
}