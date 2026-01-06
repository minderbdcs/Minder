<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class ButtonCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Button::CLASS_NAME;
    }

}