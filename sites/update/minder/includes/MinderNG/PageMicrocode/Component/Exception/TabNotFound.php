<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Tab;

class TabNotFound extends Exception {
    public function __construct(Tab $tab, $code = 0, Exception $previous = null)
    {
        parent::__construct('Tab #' . $tab->getId() . ' not found.', $code, $previous);
    }

}