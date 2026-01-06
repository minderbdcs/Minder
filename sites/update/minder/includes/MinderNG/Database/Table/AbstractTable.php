<?php

namespace MinderNG\Database\Table;

abstract class AbstractTable extends \Zend_Db_Table_Abstract {

    protected $_defaultCompanyId;

    public function setDefaultCompanyId($companyId)
    {
        $this->_defaultCompanyId = $companyId;
    }

    protected function _formatInListLimit(array $list, $field, $table = null) {
        $fullFieldName = empty($table) ? $field : $table . '.' . $field;

        $dbAdapter = $this->getAdapter();
        return $fullFieldName . " IN (" . implode(", ", array_map(function ($value) use ($dbAdapter) {
                return $dbAdapter->quote($value);
            }, $list)) . ")";
    }

    protected function _formatAccessLimit(array $accessList = array(), $field, $table = null)
    {
        $fullFieldName = empty($table) ? $field : $table . '.' . $field;

        $result = $fullFieldName . ' IS NULL OR ' . $fullFieldName . " = ''";

        if (!empty($accessList)) {
            $result .= ' OR ' . $this->_formatInListLimit($accessList, $field, $table);
        }

        return "(" . $result . ")";
    }

    protected function _formatEmptyOrEqualLimit($value, $field, $table)
    {
        $field = empty($table) ? $field : $table . '.' . $field;
        $result = $field . ' IS NULL OR ' . $field . " = ''";

        if (!empty($value)) {
            $result .= ' OR ' . $field . " = " . $this->getAdapter()->quote($value);
        }

        return "(" . $result . ")";
    }

    /**
     * Always returns one COMPANY_ID
     * See: http://binary-studio.office-on-the.net/issues/6388#note-7
     *
     * @param \Minder2_Model_SysUser $user
     * @param $companyLimit
     * @return string
     */
    protected function _getCompanyLimit(\Minder2_Model_SysUser $user, \Minder2_Model_Company $companyLimit)
    {
        if (($companyLimit->COMPANY_ID != 'all') && (!empty($companyLimit->COMPANY_ID)))
            return $companyLimit->COMPANY_ID;

        if (!empty($user->COMPANY_ID))
            return $user->COMPANY_ID;

        return $this->_defaultCompanyId;
    }
}