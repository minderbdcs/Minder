<?php

class Minder_SysScreen_View_FileSystem_Not implements Minder_SysScreen_View_FileSystem_ConstraintInterface {
    /**
     * @var Minder_SysScreen_View_FileSystem_ConstraintInterface
     */
    protected $_subConstraint;

    function __construct(Minder_SysScreen_View_FileSystem_ConstraintInterface $subConstraint)
    {
        $this->_setSubConstraint($subConstraint);
    }


    public function isValid($rowData)
    {
        return !$this->_getSubConstraint()->isValid($rowData);
    }

    /**
     * @return Minder_SysScreen_View_FileSystem_ConstraintInterface
     */
    protected function _getSubConstraint()
    {
        return $this->_subConstraint;
    }

    /**
     * @param Minder_SysScreen_View_FileSystem_ConstraintInterface $subConstraint
     * @return $this
     */
    protected function _setSubConstraint(Minder_SysScreen_View_FileSystem_ConstraintInterface $subConstraint)
    {
        $this->_subConstraint = $subConstraint;
        return $this;
    }
}