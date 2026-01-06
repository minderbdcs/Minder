<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class PageSettings
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string pageId
 * @property string companyId
 */
class PageSettings extends Model {
    public static function getIdAttribute()
    {
        return 'pageId';
    }

}