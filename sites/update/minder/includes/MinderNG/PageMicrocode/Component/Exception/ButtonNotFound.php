<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Button;

class ButtonNotFound extends Exception {
    public function __construct(Button $button, $code = 0, Exception $previous = null)
    {
        parent::__construct('Button #' . $button->getId() . ' not found.', $code, $previous);
    }

}