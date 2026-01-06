<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class ConstraintCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Constraint::CLASS_NAME;
    }

}