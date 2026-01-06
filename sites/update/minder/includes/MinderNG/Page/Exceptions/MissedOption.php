<?php

namespace MinderNG\Page\Exceptions;

use Exception;

class MissedOption extends Exception {
    public function __construct($option, Exception $previous = null)
    {
        parent::__construct('Page Option missed "' . $option . '"', 0, $previous);
    }

}