<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class DataSetRowMetadata
 * @package MinderNG\PageMicrocode\Component
 *
 * @property integer index
 */
class DataSetRowMetadata extends Model {
    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        $attributes['index'] = isset($attributes['index']) ? intval($attributes['index']) : 0;

        return $attributes;
    }

}