<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class ColourCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Colour::CLASS_NAME;
    }

}