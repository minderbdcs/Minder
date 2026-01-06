<?php

class Minder_Page {
    const FORM_TYPE_EDIT_FORM     = 'FORM_TYPE_EDIT_FORM';
    const FORM_TYPE_SEARCH_RESULT = 'FORM_TYPE_SEARCH_RESULT';
    const FORM_TYPE_SEARCH_FORM   = 'FORM_TYPE_SEARCH_FORM';
    const FORM_TYPE_NEW_FORM      = 'FORM_TYPE_NEW_FORM';

    public static function formatSysScreenClassName($sysScreen) {
        $parts = explode('_', strtolower($sysScreen));
        foreach ($parts as &$part)
            $part = ucfirst($part);

        return implode('', $parts);
    }
}