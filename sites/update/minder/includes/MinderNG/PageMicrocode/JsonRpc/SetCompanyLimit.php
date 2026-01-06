<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Microcode;

class SetCompanyLimit {
    public function execute(Microcode $microcode, $companyLimit) {
        $company = $microcode->getPageComponents()->companyLimitList->findCompany($companyLimit);
        $microcode->getPageComponents()->companyLimit->set($company->getAttributes());

        return $microcode->getPageComponents()->getArrayCopy();
    }
}