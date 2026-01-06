<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class DataSourceFieldCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\DataSourceField';
    }
}