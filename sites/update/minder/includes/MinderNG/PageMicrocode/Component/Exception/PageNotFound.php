<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;

class PageNotFound extends Exception {
    public function __construct($pageId, Exception $previous = null)
    {
        parent::__construct("Page #" . $pageId . ' not found.', 0, $previous);
    }
}