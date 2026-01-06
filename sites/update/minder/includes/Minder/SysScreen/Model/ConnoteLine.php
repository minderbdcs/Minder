<?php
class Minder_SysScreen_Model_ConnoteLine extends Minder_SysScreen_Model implements Minder_SysScreen_Model_ConnoteLine_Interface
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * @deprecated
     * @param $rowOffset
     * @param $itemCountPerPage
     * @return array
     * @throws Minder_SysScreen_Model_ConnoteLine_Exception
     */
    public function selectPickLabelNo($rowOffset, $itemCountPerPage) {
        if ($this->_tableExists('PICK_ITEM_DETAIL'))
            return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ITEM_DETAIL.PICK_LABEL_NO');
            
        if ($this->_tableExists('PICK_ITEM'))
            return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ITEM.PICK_LABEL_NO');
            
        throw new Minder_SysScreen_Model_ConnoteLine_Exception('PICK_ITEM_DETAIL and PICK_ITEM tables were not found in models table list, cannot get PICK_LABEL_NO.');
    }
}

class Minder_SysScreen_Model_ConnoteLine_Exception extends Minder_SysScreen_Model_Exception {}