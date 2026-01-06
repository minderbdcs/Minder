<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Company
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string COMPANY_ID
 */
class Company extends Model {
    public static function getIdAttribute()
    {
        return 'COMPANY_ID';
    }

}