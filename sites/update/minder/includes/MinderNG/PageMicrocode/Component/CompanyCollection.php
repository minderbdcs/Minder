<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class CompanyCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Company';
    }

    /**
     * @param string|array|Company $idOrAggregate
     * @return Company
     * @throws Exception\CompanyNotFound
     */
    public function findCompany($idOrAggregate) {
        $company = ($idOrAggregate instanceof Company) ? $idOrAggregate : $this->newCompany($idOrAggregate);
        $foundCompany = $this->get($company);

        if (empty($foundCompany)) {
            throw new Exception\CompanyNotFound($company);
        }

        return $foundCompany;
    }

    /**
     * @param $idOrAggregate
     * @return Company
     */
    public function newCompany($idOrAggregate) {
        return $this->newModelInstance(is_string($idOrAggregate) ? array('COMPANY_ID' => $idOrAggregate) : $idOrAggregate);
    }
}