<?php

abstract class Minder_SysScreen_View_FileSystem_AbstractFieldConstraint implements Minder_SysScreen_View_FileSystem_ConstraintInterface {
    /**
     * @var string
     */
    protected $_fieldName;

    /**
     * @var mixed
     */
    protected $_term;

    /**
     * @param string $fieldName
     * @param mixed $term
     */
    function __construct($fieldName, $term)
    {
        $this->_setFieldName($fieldName);
        $this->_setTerm($term);
    }

    /**
     * @return string
     */
    protected function _getFieldName()
    {
        return $this->_fieldName;
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    protected function _setFieldName($fieldName)
    {
        $this->_fieldName = $fieldName;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _getTerm()
    {
        return $this->_term;
    }

    /**
     * @param mixed $term
     * @return $this
     */
    protected function _setTerm($term)
    {
        $this->_term = $term;
        return $this;
    }

}