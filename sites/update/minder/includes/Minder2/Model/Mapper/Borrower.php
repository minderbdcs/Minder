<?php

class Minder2_Model_Mapper_Borrower extends Minder2_Model_Mapper_Abstract {

    public function get($borrowerId) {
        if (false !== ($result = $this->_getMinder()->fetchAssoc("SELECT LOCN_ID, LOCN_NAME FROM LOCATION WHERE WH_ID = 'XB' AND LOCN_ID = ?", $borrowerId))) {
            return new Minder2_Model_Borrower(array(
                'borrowerId' => $result['LOCN_ID'],
                'borrowerName' => $result['LOCN_NAME'],
                'existed' => true
            ));
        }

        return new Minder2_Model_Borrower(array('borrowerId' => $borrowerId, 'existed' => false));
    }

    /**
     * @throws Minder_Exception
     * @param Minder2_Model_Borrower $borrower
     * @return Minder2_Model_Borrower
     */
    public function save(Minder2_Model_Borrower $borrower) {
        $transaction = new Transaction_NLBIB();
        $transaction->borrowerId   = $borrower->borrowerId;
        $transaction->companyId    = $borrower->companyId;
        $transaction->borrowerName = $borrower->borrowerName;
        $transaction->date         = date('d-M-Y H:i:s');

        if (false === $this->_getMinder()->doTransactionResponse($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $borrower;
    }
}