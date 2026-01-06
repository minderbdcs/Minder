<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class TableCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Table';
    }

    protected function _getComparator()
    {
        return Table::FIELD_SEQUENCE;
    }


}