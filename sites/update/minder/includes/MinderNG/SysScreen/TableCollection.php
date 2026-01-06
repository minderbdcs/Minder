<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class TableCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Table::CLASS_NAME;
    }

}