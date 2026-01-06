<?php

class Minder_SysScreen_View_FileSystem_Or implements Minder_SysScreen_View_FileSystem_ConstraintInterface {

    /**
     * @var Minder_SysScreen_View_FileSystem_ConstraintInterface[]
     */
    protected $_subConstraints = array();

    /**
     * @param Minder_SysScreen_View_FileSystem_ConstraintInterface[] $subConstraints
     */
    function __construct(array $subConstraints)
    {
        $this->_setSubConstraints($subConstraints);
    }

    public function isValid($rowData)
    {
        foreach ($this->_getSubConstraints() as $subConstraint) {
            if ($subConstraint->isValid($rowData)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Minder_SysScreen_View_FileSystem_ConstraintInterface[]
     */
    protected function _getSubConstraints()
    {
        return $this->_subConstraints;
    }

    /**
     * @param Minder_SysScreen_View_FileSystem_ConstraintInterface[] $subConstraints
     * @return $this
     */
    protected function _setSubConstraints($subConstraints)
    {
        $this->_subConstraints = $subConstraints;
        return $this;
    }
}