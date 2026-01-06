<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class ScreenCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Screen::CLASS_NAME;
    }
}