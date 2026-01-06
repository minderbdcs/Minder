<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use MinderNG\PageMicrocode\Component\Screen;

class ScreenNotFound extends Exception {
    public function __construct(Screen $screen, $code = 0, Exception $previous = null)
    {
        parent::__construct('Screen #' . $screen->getId() . ' not found.', $code, $previous);
    }

}