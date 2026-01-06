<?php

/**
 * @property string _PROD_ID
 */
class Minder2_Model_SysScreen_ReceiveAllocate extends Minder2_Model_SysScreen {
    protected $_customMasterSlaveHandler = true;

    public function searchProdId($prodId, $rowOffset = null, $itemCountPerPage = null) {
        $this->selectComplete(false);
        $this->storeFieldValue('_PROD_ID', $prodId);

        $items = $this->getItems($rowOffset, $itemCountPerPage);
        $fields = $this->getStatistics();

        return array('items' => $items, 'fields' => $fields);
    }

    protected function _fillRequiredQty($items) {

        foreach ($items as &$item) {
            $rowId = $this->_mapRowId($item);
            $findCondition = $this->makeFindConditions(array($rowId));
            $fetchResult = $this->fetchFields(array('PICK_ITEM.PICK_ORDER_QTY - COALESCE(PICK_ITEM.PICKED_QTY, 0) AS REQUIRED_TO_COMPLETE_QTY'), $findCondition, 0, 1);

            if (count($fetchResult) > 0)
                $item = array_merge($item, current($fetchResult));
        }

        return $items;
    }

    public function getItems($rowOffset = null, $itemCountPerPage = null, $conditions = null)
    {
        $items = parent::getItems($rowOffset, $itemCountPerPage, $conditions);

        if (count($items) < 1) return $items;
        reset($items);
        $firstRow = current($items);

        if (!isset($firstRow['REQUIRED_TO_COMPLETE_QTY']))
            return $this->_fillRequiredQty($items); //todo

        return $items;
    }

    protected function _compileConditionObject($conditions = null)
    {
        $conditionObject = parent::_compileConditionObject($conditions);
        $conditionObject->addConditions(array($this->_getProdIdSearchConditions()), 'PROD_ID_SEARCH');

        return $conditionObject;
    }


    protected function _fetchTotalOrderUnits($extraConditions = null) {
        $conditionsObject = new Minder_SysScreen_ModelCondition();
        $conditionsObject->addConditions(array($this->_getSearchCondition()), Minder_SysScreen_ModelCondition::SEARCH_NAMESPACE);
        $conditionsObject->addConditions(array($this->_getDependentConditions()), Minder_SysScreen_ModelCondition::DEPENDENT_NAMESPACE);
        $conditionsObject->addConditions(array($this->_getProdIdSearchConditions()));

        if (!is_null($extraConditions))
            $conditionsObject->addConditions(array($extraConditions));

        $result = $this->fetchAggregatedValue('SUM(PICK_ITEM.PICK_ORDER_QTY - COALESCE(PICK_ITEM.PICKED_QTY, 0))', $conditionsObject);

        return empty($result) ? 0 : $result;
    }

    public function getStatistics()
    {
        return array(
            '_TOTAL_ROWS' => count($this),
            '_SELECTED_ROWS' => $this->getSelectedRowsAmount(),
            '_TOTAL_ORDER_UNITS' => $this->_fetchTotalOrderUnits(),
            '_TOTAL_SELECTED_UNITS' => $this->_fetchTotalOrderUnits($this->_getSelectedRowsCondition())
        );
    }

    protected function _getProdIdSearchConditions()
    {
        if (empty($this->_PROD_ID))
            return new Minder_SysScreen_ModelCondition(array('1 = 2 ' => array()));

        return new Minder_SysScreen_ModelCondition(array('PICK_ITEM.PROD_ID = ?' => array($this->_PROD_ID)));
    }

    protected function _fetchPickLabelNo($extraConditions = null) {
        $totalRows = $this->countRows($extraConditions);

        $result = array();
        if ($totalRows < 1)
            return $result;

        $conditionObject = $this->_compileConditionObject($extraConditions);
        $fetchResult = $this->fetchFields(array('PICK_ITEM.PICK_LABEL_NO'), $conditionObject, 0, $totalRows, true);

        if (empty($fetchResult))
            return $result;

        foreach ($fetchResult as $fetchResultRow) {
            $result[] = array_shift($fetchResultRow);
        }

        return $result;
    }

    public function receive($allocateDetails, $rows = array()) {
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $rows, $allocateDetails));

        $this->syncRowsSelection($rows);

        $receiveProcess                      = new Minder2_Model_SysScreen_ReceiveAllocate_Receive();
        $receiveProcess->grn                 = isset($allocateDetails['grn']) ? $allocateDetails['grn'] : '';
        $receiveProcess->totalReceivedUnits  = isset($allocateDetails['totalReceived']) ? intval($allocateDetails['totalReceived']) : 0;
        $receiveProcess->pickLabels          = $this->_fetchPickLabelNo($this->_getSelectedRowsCondition());
        $receiveProcess->orderNo             = isset($allocateDetails['purchaseOrder']) ? $allocateDetails['purchaseOrder'] : '';
        $receiveProcess->orderLineNo         = isset($allocateDetails['poLine']) ? $allocateDetails['poLine'] : '';
        $receiveProcess->printer             = Minder2_Environment::getCurrentPrinter()->DEVICE_ID;
        $receiveProcess->labelQty1           = isset($allocateDetails['totalLabelQty1']) ? $allocateDetails['totalLabelQty1'] : 0;
        $receiveProcess->unitPerLabel1       = isset($allocateDetails['unitsPerLabel1']) ? $allocateDetails['unitsPerLabel1'] : 0;
        $receiveProcess->labelQty2           = isset($allocateDetails['totalLabelQty2']) ? $allocateDetails['totalLabelQty2'] : 0;
        $receiveProcess->unitPerLabel2       = isset($allocateDetails['unitsPerLabel2']) ? $allocateDetails['unitsPerLabel2'] : 0;
        $receiveProcess->totalPickLabelUnits = isset($allocateDetails['totalPickLabelUnits']) ? $allocateDetails['totalPickLabelUnits'] : 0;
        $receiveProcess->printer             = isset($allocateDetails['printerId']) ? $allocateDetails['printerId'] : null;
        $receiveProcess->receiveLocation     = isset($allocateDetails['receiveLocation']) ? $allocateDetails['receiveLocation'] : null;

        return $receiveProcess->doReceive();
    }
}