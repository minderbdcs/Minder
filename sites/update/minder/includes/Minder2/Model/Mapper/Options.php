<?php

class Minder2_Model_Mapper_Options extends Minder2_Model_Mapper_Abstract {
    const GROUP_CODE = 'GROUP_CODE';
    const CODE       = 'CODE';

    /**
     * @param array $keys
     * @param string $fetchMode
     * @return array[] | Minder2_Model_Options[]
     */
    public function find(array $keys, $fetchMode = self::FETCH_MODE_MODEL) {
        $result = array();
        $filters = array();
        $keys = array_change_key_case($keys, CASE_UPPER);
        if (isset($keys[self::GROUP_CODE]))
            $filters[self::GROUP_CODE . ' = ?'] = $keys[self::GROUP_CODE];

        if (isset($keys[self::CODE])) {
            if (false === strpos($keys[self::CODE], '%')) {
                $filters[self::CODE . ' = ?'] = $keys[self::CODE];
            } else {
                $filters[self::CODE . ' LIKE ?'] = $keys[self::CODE];
            }
        }

        if (empty($filters))
            return $result;

        $sql = "SELECT * FROM OPTIONS WHERE " . implode(' AND ', array_keys($filters));
        $args = array_values($filters);

        array_unshift($args, $sql);

        if (false === ($sqlResult = call_user_func_array(array( $this->_getMinder(), 'fetchAllAssoc'), $args)))
            return $result;

        foreach ($sqlResult as $resultRow) {
            switch ($fetchMode) {
                case self::FETCH_MODE_ARRAY:
                    $result[] = $resultRow;
                    break;
                case self::FETCH_MODE_MODEL:
                default:
                    $optionsObj = new Minder2_Model_Options($resultRow);
                    $optionsObj->existed = true;
                    $result[] = $optionsObj;
            }
        }

        return $result;
    }
}