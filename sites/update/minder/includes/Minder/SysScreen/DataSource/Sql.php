<?php

/**
 * @property string $sql
 */
class Minder_SysScreen_DataSource_Sql implements Minder_SysScreen_DataSource_Interface {

    protected $_sql = null;
    protected $_params = array();

    function __set($name, $value)
    {
        if ($name == 'sql') $this->setSql($value);
    }

    function __get($name)
    {
        if ($name == 'sql') return $this->_sql;

        return null;
    }

    protected function _prepareSql() {
        $this->_params  = array();
        $tmpFoundParams = array();
        if (preg_match_all('/%\w+%/', $this->_sql, $tmpFoundParams))
            $this->_params = $tmpFoundParams[0];
    }

    public function setSql($sql) {
        $this->_sql = $sql;
        $this->_prepareSql();
    }

    /**
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return string
     */
    protected function _formatSql($parametersProvider) {
        $formatedSql = $this->_sql;
        foreach ($this->_params as $paramName) {
            $formatedSql = str_ireplace($paramName, $parametersProvider->getValue($paramName), $formatedSql);
        }

        return $formatedSql;
    }

    /**
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return array
     */
    public function fetchAllAssoc($parametersProvider)
    {
        return Minder::getInstance()->fetchAllAssoc($this->_formatSql($parametersProvider));
    }

    /**
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return string
     */
    function fetchOne($parametersProvider)
    {
        return Minder::getInstance()->fetchOne($this->_formatSql($parametersProvider));
    }

    /**
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parametersProvider
     * @return array
     */
    function fetchAssoc($parametersProvider)
    {
        return Minder::getInstance()->fetchAssoc($this->_formatSql($parametersProvider));
    }


}