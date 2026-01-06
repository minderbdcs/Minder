<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class ControlValues
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string COMPANY_ID
 */
class ControlValues extends Model {
    public static function getIdAttribute()
    {
        return 'COMPANY_ID';
    }

}