<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class TabCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Tab::CLASS_NAME;
    }

}