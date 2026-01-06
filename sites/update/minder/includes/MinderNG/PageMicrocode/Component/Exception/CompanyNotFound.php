<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Company;

class CompanyNotFound extends Exception {
    public function __construct(Company $company, $code = 0, Exception $previous = null)
    {
        parent::__construct('Company #' . $company->getId() . ' not found.', $code, $previous);
    }

}