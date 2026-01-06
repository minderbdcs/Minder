<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class GroupCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Group::CLASS_NAME;
    }

}