<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection;

/**
 * Class Page
 * @package MinderNG\PageMicrocode\Component
 *
 * @property string SM_MENU_ID
 * @property string SM_SUBMENU_ID
 */
class Page extends Collection\Model {
    public static function calculateId(array $attributes = array())
    {
        return implode('-', array(
            isset($attributes['SM_MENU_ID']) ? $attributes['SM_MENU_ID'] : '',
            isset($attributes['SM_SUBMENU_ID']) ? $attributes['SM_SUBMENU_ID'] : ''
        ));
    }

    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        return static::_parse($attributes);
    }


    static function _parse($attributes) {
        $attributes['topLevel'] = empty($attributes['SM_MENU_ID']);

        return $attributes;
    }
}