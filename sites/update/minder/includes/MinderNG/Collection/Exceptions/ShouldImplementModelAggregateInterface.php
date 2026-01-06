<?php

namespace MinderNG\Collection\Exceptions;

use Exception;
use MinderNG\Collection\ModelAggregateInterface;

class ShouldImplementModelAggregateInterface extends Exception {
    public function __construct($className, $code = 0, Exception $previous = null)
    {
        parent::__construct($className . ' should implement \\MinderNG\\Events\\ModelAggregateInterface', $code, $previous);
    }

}