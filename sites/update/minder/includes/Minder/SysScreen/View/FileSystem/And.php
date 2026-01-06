<?php

class Minder_SysScreen_View_FileSystem_And implements Minder_SysScreen_View_FileSystem_ConstraintInterface {
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
            if (!$subConstraint->isValid($rowData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Minder_SysScreen_View_FileSystem_ConstraintInterface[]
     */
    protected function _getSubConstraints()
    {
        return $this->_subConstraints;
    }

    /**
     * @param Minder_SysScreen_View_FileSystem_ConstraintInterface[] $parts
     * @return $this
     */
    protected function _setSubConstraints(array $parts)
    {
        $this->_subConstraints = $parts;
        return $this;
    }
}