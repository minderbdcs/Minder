<?php

class Minder_SysScreen_Model_TransferIssn extends Minder_SysScreen_Model {

    public $transferCompleted = false;

    public function filterIssnId($issnIds) {

        if (count($issnIds) < 1) return;

        if (false == ($tableDesc = $this->_tableExists('ISSN'))) return;

        $fieldName = $tableDesc['SST_ALIAS'] . '.SSN_ID';
        $this->setConditions(array($fieldName . ' IN (' . substr(str_repeat('?, ', count($issnIds)), 0, -2) . ')' => $issnIds));
    }

    public function findIssn($ssnId) {
        $result = array();
        if (false == ($tableDesc = $this->_tableExists('ISSN'))) return $result;

        $fieldName = $tableDesc['SST_ALIAS'] . '.SSN_ID';

        $this->setConditions(array($fieldName .' = ?' => array($ssnId)));

        $result = $this->getItems(0, 1);

        return $result;
    }

    public function selectSsnId($rowOffset, $itemCountPerPage) {
        $result = array();

        if (false == ($tableDesc = $this->_tableExists('ISSN'))) return $result;

        $fieldName = $tableDesc['SST_ALIAS'] . '.SSN_ID';

        if (false !== ($queryResult = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ' . $fieldName))) {
            foreach ($queryResult as $resultRow)
                $result[] = $resultRow['SSN_ID'];
        }

        return $result;
    }

    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false)
    {
        $items = parent::getItems($rowOffset, $itemCountPerPage, $getPKeysOnly);

        $fromLocnSourceAlias = $this->_getFromLocnIdSourceAlias();
        $intoLocnSourceAlias = $this->_getIntoLocnIdSourceAlias();

        if (empty($intoLocnSourceAlias) && empty($fromLocnSourceAlias))
            return $items; //no source fields

        $fromLocnTargetAlias = $this->_getAlias('FROM_LOCN_ID');
        $intoLocnTargetAlias = $this->_getAlias('INTO_LOCN_ID');

        if (empty($fromLocnTargetAlias) && empty($intoLocnTargetAlias))
            return $items; //no target fields

        foreach ($items as &$itemsRow) {
            if (!empty($fromLocnSourceAlias) && !empty($fromLocnTargetAlias))
                $itemsRow[$fromLocnTargetAlias] = $itemsRow[$fromLocnSourceAlias];

            if (!empty($intoLocnSourceAlias) && !empty($intoLocnTargetAlias))
                $itemsRow[$intoLocnTargetAlias] = $itemsRow[$intoLocnSourceAlias];
        }

        return $items;
    }

    protected function _transferIssnToLocation($ssnId, $targetLocation) {
        $minder = Minder::getInstance();
        if (false === ($foundIssn = $minder->fetchAssoc('SELECT WH_ID, LOCN_ID FROM ISSN WHERE SSN_ID = ?', $ssnId)))
            throw new Minder_Exception('ISSN #' . $ssnId . ' not found.');

        $trolTransaction = new Transaction_TROLA();
        $trolTransaction->ssnId = $ssnId;
        $trolTransaction->locnId = $foundIssn['LOCN_ID'];
        $trolTransaction->whId   = $foundIssn['WH_ID'];

        if (false === ($minder->doTransactionResponse($trolTransaction)))
            throw new Minder_Exception('Error executing ' . $trolTransaction->transClass. ' ' . $trolTransaction->transCode . ': ' . $minder->lastError);

        $trilTransaction = new Transaction_TRILA();
        $trilTransaction->ssnId = $ssnId;
        $trilTransaction->location = $targetLocation;

        if (false === ($minder->doTransactionResponse($trilTransaction)))
            throw new Minder_Exception('Error executing ' . $trilTransaction->transClass. ' ' . $trilTransaction->transCode . ': ' . $minder->lastError);
    }

    public function transferToLocnId($targetLocation) {

        $transferedSsnId = array();
        $totalRows = count($this);

        if ($totalRows < 1) return $transferedSsnId;

        foreach ($this->selectSsnId(0, $totalRows) as $ssnId) {
            $this->_transferIssnToLocation($ssnId, $targetLocation);
            $transferedSsnId[$ssnId] = $ssnId;
        }

        return $transferedSsnId;
    }

    protected function _getIntoLocnIdSourceAlias()
    {
        return ($this->transferCompleted) ? $this->_getFieldAlias('LOCN_ID') : '';
    }

    protected function _getFromLocnIdSourceAlias() {
        return ($this->transferCompleted) ? $this->_getFieldAlias('PREV_LOCN_ID') : $this->_getFieldAlias('LOCN_ID');
    }

    protected function _getAlias($alias) {
        if (false !== ($fieldDescription = $this->_aliasExists($alias)))
            return $fieldDescription['SSV_ALIAS'];

        return '';
    }

    protected function _getFieldAlias($fieldName)
    {
        if (false !== ($fieldDescription = $this->_fieldExists($fieldName)))
            return $fieldDescription['SSV_ALIAS'];

        return '';
    }
}