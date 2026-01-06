<?php

namespace MinderNG\Page;

use MinderNG\Collection\Collection;

class PageCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Page::CLASS_NAME;
    }

}