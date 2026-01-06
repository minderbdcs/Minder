<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

class Device extends Model {
    public static function getIdAttribute()
    {
        return 'DEVICE_ID';
    }

    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        $attributes['DEVICE_ID'] = isset($attributes['DEVICE_ID']) ? $attributes['DEVICE_ID'] : '';
        return $attributes;
    }


}