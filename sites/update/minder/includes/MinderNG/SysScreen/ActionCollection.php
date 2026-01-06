<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class ActionCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Action::CLASS_NAME;
    }

}