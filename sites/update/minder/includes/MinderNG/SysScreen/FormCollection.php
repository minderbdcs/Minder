<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class FormCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Form::CLASS_NAME;
    }

}