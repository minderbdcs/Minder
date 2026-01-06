<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class VariableCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Variable::CLASS_NAME;
    }

}