<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Screen;

class ScreenHasNoResultForm extends Exception {
    public function __construct(Screen $screen, $code = 0, Exception $previous = null)
    {
        parent::__construct('Screen has no result form #' . $screen->getId(), $code, $previous);
    }

}