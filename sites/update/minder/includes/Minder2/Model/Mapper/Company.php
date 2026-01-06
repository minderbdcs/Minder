<?php

class Minder2_Model_Mapper_Company {

    /**
     * @param string $companyId
     * @return Minder2_Model_Company
     */
    public function find($companyId) {
        if (empty($companyId))
            return new Minder2_Model_Company();

        $sql = "SELECT * FROM COMPANY WHERE COMPANY_ID = ?";

        $result = Minder::getInstance()->fetchAssoc($sql, $companyId);

        if (false === $result) {
            $company = new Minder2_Model_Company();
            $company->existed = false;
        } else {
            $company = new Minder2_Model_Company($result);
            $company->existed = true;
        }

        return $company;
    }

    /**
     * @param Minder2_Model_SysUser $user
     * @return Minder2_Model_Company[]
     */
    public function selectUsersAccessCompanyList($user) {
        if (!$user->existed)
            return array();

        if ($user->isSuperAdmin()) {
            $sql = '
                SELECT
                    *
                FROM
                    COMPANY
            ';

            $result = Minder::getInstance()->fetchAllAssoc($sql);
        } else {
            $sql = "
                SELECT
                    COMPANY.*
                FROM
                    ACCESS_COMPANY
                    LEFT JOIN COMPANY ON ACCESS_COMPANY.COMPANY_ID = COMPANY.COMPANY_ID
                WHERE
                    ACCESS_COMPANY.USER_ID = ?
            ";

            $result = Minder::getInstance()->fetchAllAssoc($sql, $user->USER_ID);
        }

        if (false === $result)
            return array();

        $companyList = array();
        foreach ($result as $companyDescription) {
            $tmpCompany = new Minder2_Model_Company($companyDescription);
            $tmpCompany->existed = true;
            $companyList[$tmpCompany->COMPANY_ID] = $tmpCompany;
        }

        return $companyList;
    }
}