<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Warehouse
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string WH_ID
 */
class Warehouse extends Model {
    public static function getIdAttribute()
    {
        return 'WH_ID';
    }

}