<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class ProcedureCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Procedure::CLASS_NAME;
    }

}