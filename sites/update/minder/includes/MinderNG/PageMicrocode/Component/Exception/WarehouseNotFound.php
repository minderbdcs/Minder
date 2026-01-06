<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Warehouse;

class WarehouseNotFound extends Exception {
    public function __construct(Warehouse $warehouse, $code = 0, Exception $previous = null)
    {
        parent::__construct('Warehouse #' . $warehouse->getId() . ' not found.', $code, $previous);
    }


}