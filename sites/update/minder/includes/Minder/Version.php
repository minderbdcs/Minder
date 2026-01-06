<?php

class Minder_Version {

    const VERSION = '5.4';
    const RELEASE = '218';
    public static function getFull() {
        return static::VERSION . '.' . static::RELEASE;
    }
}
