<?php
/**
* Minder_SysScreen_Model_ConnoteLines_Interface provides set of methods which is used at connote 
* screen to retrive needed data for some transactions (PINV, PILN, etc.) from selected lines.
* 
* Each lines model wich will be used at connote screen should provide this interface
*/

interface Minder_SysScreen_Model_ConnoteLine_Interface extends Minder_SysScreen_Model_Interface
{
    /**
     * @deprecated
     * @param $rowOffset
     * @param $itemCountPerPage
     * @return mixed
     */
    public function selectPickLabelNo($rowOffset, $itemCountPerPage);
}
