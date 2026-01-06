<?php

class Minder_SysScreen_Model_ChangeProdId extends Minder_SysScreen_Model {
    public function selectDataForGnnpTransaction($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression(
            $rowOffset,
            $itemCountPerPage,
            'DISTINCT PURCHASE_ORDER_LINE.PURCHASE_ORDER, PURCHASE_ORDER_LINE.PO_LINE, PURCHASE_ORDER_LINE.PO_LINE_QTY, PURCHASE_ORDER_LINE.PROD_ID'
        );
    }

    public function changeProdId($grn, $orderNo, $lineNo, $qty, $prodId) {
        $result = new Minder_JSResponse();

        $transaction = new Transaction_GNNPP();
        $transaction->oldGrn = $grn;
        $transaction->oldOrderNo = $orderNo;
        $transaction->oldLineNo = $lineNo;
        $transaction->comment = date($this->_getMinder()->getDateFormat())
            . ' Product code changed: PROD_ID was ' . $prodId
            . '; PO was ' . $orderNo . ' - ' . $this->_getMinder()->userId;

        foreach ($this->selectDataForGnnpTransaction(0, count($this)) as $data) {
            if ($qty < 0) {
                break;
            }

            $transaction->qty = min($qty, $data['PO_LINE_QTY']);
            $transaction->newOrderNo = $data['PURCHASE_ORDER'];
            $transaction->newLineNo  = $data['PO_LINE'];
            $transaction->newProdId  = $data['PROD_ID'];

            if (false === ($trnResult = $this->_getMinder()->doTransactionResponse($transaction))) {
                $result->errors[] = $this->_getMinder()->lastError;
                return $result;
            }

            $result->messages[] = $trnResult;
            $qty = $qty - $transaction->qty;
        }

        return $result;
    }
}