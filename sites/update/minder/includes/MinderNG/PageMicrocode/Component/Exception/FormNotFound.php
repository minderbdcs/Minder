<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Form;

class FormNotFound extends Exception {
    public function __construct(Form $form, $code = 0, Exception $previous = null)
    {
        parent::__construct('Form #' . $form->getId() . ' not found.', $code, $previous);
    }
}