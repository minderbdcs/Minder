<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Screen
 * @package MinderNG\PageMicrocode\Component
 * @property string SS_NAME
 */
class Screen extends Model {
    public static function calculateId(array $attributes = array())
    {
        return implode('/', array(
            isset($attributes['SS_MENU_ID']) ? $attributes['SS_MENU_ID'] : '',
            isset($attributes['SS_NAME']) ? $attributes['SS_NAME'] : ''
        ));
    }
}