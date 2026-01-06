<?php

class Minder_ArrayUtils {

    public static function filterFieldValueInList($source, $field, $list) {
        $list = array_flip($list);
        return array_filter($source, function($item)use($list, $field){
            return isset($list[$item[$field]]);
        });
    }

    public static function filterFieldValueNotInList($source, $field, $list) {
        $list = array_flip($list);
        return array_filter($source, function($item)use($list, $field){
            return !isset($list[$item[$field]]);
        });
    }

    public static function mapField(array $source, $fieldName) {
        return array_map(function($current)use($fieldName){return $current[$fieldName];}, $source);
    }

    public static function mapKey(array $source, $fieldName) {
        $result = array();
        foreach ($source as $rowData) {
            $result[$rowData[$fieldName]] = $rowData;
        }

        return $result;
    }

    public static function mapKeyValue(array $source, $keyName, $valueName) {
        $result = array();
        foreach ($source as $rowData) {
            $result[$rowData[$keyName]] = $rowData[$valueName];
        }

        return $result;
    }

    public static function reMap(array $values, array $keyMap, $injectNewKey = null) {
        $result = array();
        foreach ($keyMap as $newKey => $oldKey) {
            if (isset($values[$oldKey])) {
                $result[$newKey] = $values[$oldKey];

                if (!empty($injectNewKey)) {
                    $result[$newKey][$injectNewKey] = $newKey;
                }
            }
        }

        return $result;
    }

    public static function toList(array $source, $keyName, $valueName) {
        $result = array();

        foreach ($source as $row) {
            $result[$row[$keyName]] = $result[$row[$valueName]];
        }

        return $result;
    }

    public static function populate(array $source, $valueToPopulate) {
        $result = array();
        foreach ($source as $key => $oldValue) {
            $result[$key] = $valueToPopulate;
        }

        return $result;
    }

    public static function recursiveGroup(array $source, array $groupBy) {
        $mapPartial = self::_getMapPartial($groupBy);
        return array_reduce($source, function($previousResult, $currentValue)use($mapPartial) {
            $path = $mapPartial($currentValue);
            Minder_ArrayUtils::recursivePush($previousResult, $path, $currentValue);
            return $previousResult;
        }, array());
    }

    public static function recursivePush(array &$array, $path, $value) {
        $recursivePush = self::_getRecursivePush($path);
        $recursivePush($array, $value);
    }

    protected static function _getRecursivePush(array $path) {
        if (empty($path)) {
            return function(array &$array, $value){
                $array[] = $value;
            };
        } else {
            $index = array_shift($path);
            $recursivePush = self::_getRecursivePush($path);
            return function(array &$array, $value)use($index, $recursivePush){
                $array[$index] = is_array($array[$index]) ? $array[$index] : array();
                $recursivePush($array[$index], $value);
            };
        }
    }

    protected static function _getMapPartial($fieldsList) {
        return function($source)use($fieldsList){
            return array_map(function($fieldName)use($source){return $source[$fieldName];}, $fieldsList);
        };
    }
}