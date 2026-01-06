<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class DataSetRow
 * @package MinderNG\PageMicrocode\Component
 * @property string _CID_
 * @property string PRIMARY_ID
 */
class DataSetRow extends Model {
    const ROW_METADATA  = 'metadata';
    const CID_PREFIX    = 'SCID';
    const FIELD_CID     = '_CID_';

    /**
     * @var DataSetRowMetadata
     */
    private $_metadata;

    public static function getIdAttribute()
    {
        return "PRIMARY_ID";
    }

    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        if (isset($attributes[static::ROW_METADATA])) {
            $this->_getMetadata()->set($attributes[static::ROW_METADATA]);
            unset($attributes[static::ROW_METADATA]);
        }

        $attributes[static::FIELD_CID] = empty($attributes[static::FIELD_CID]) ? uniqid(static::CID_PREFIX, true) : $attributes[static::FIELD_CID];

        return $attributes;
    }


    function __clone()
    {
        $result = new DataSetRow();
        $result->init($this->_previousAttributes);
        $result->set($this->getArrayCopy(true), false, false, true);

        return $result;
    }

    public function setIndex($index) {
        $this->_getMetadata()->index = $index;
    }

    public function getArrayCopy($full = false)
    {
        $result = $this->_attributes;

        if ($full) {
            $result[static::ROW_METADATA] = $this->_getMetadata()->getArrayCopy($full);
        }

        return $result;
    }


    /**
     * @return DataSetRowMetadata
     */
    private function _getMetadata()
    {
        if (empty($this->_metadata)) {
            $this->_metadata = new DataSetRowMetadata();
            $this->_metadata->init();
        }

        return $this->_metadata;
    }
}