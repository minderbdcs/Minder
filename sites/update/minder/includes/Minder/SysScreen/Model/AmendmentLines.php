<?php

class Minder_SysScreen_Model_AmendmentLines extends Minder_SysScreen_Model {
    public function amendItems() {
        $selectedAmount = count($this);
        $transactionData = array();

        if ($this->_tableExists('PICK_ITEM_DETAIL')) {
            $transactionData = $this->selectArbitraryExpression(0, $selectedAmount, 'DISTINCT PICK_ITEM_DETAIL.PICK_LABEL_NO, PICK_ITEM_DETAIL.DEVICE_ID');
        }

        if (empty($transactionData)) {
            return 0;
        }

        $transaction = new Transaction_PKCND();
        $amendedRowsAmount = 0;

        foreach ($transactionData as $dataRow) {
            $transaction->deviceId      = $dataRow['DEVICE_ID'];
            $transaction->pickLabelNo   = $dataRow['PICK_LABEL_NO'];

            if (false === ($responseString = $this->_getMinder()->doTransactionResponse($transaction))) {
                throw new Exception('Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError);
            }

            $amendedRowsAmount++;
        }

        return $amendedRowsAmount;
    }
}