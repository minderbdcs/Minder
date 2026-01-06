<?php

namespace MinderNG\PageMicrocode\Controller\Exception;

use Exception;

/**
 * Class FormNotFound
 * @package MinderNG\PageMicrocode\Controller\Exception
 *
 * @deprecated
 */
class FormNotFound extends Exception {
    public function __construct($formId, Exception $previous = null)
    {
        parent::__construct('Form ' . $formId . ' not found.', 0, $previous);
    }
}