<?php

class Minder2_Model_Mapper_LocationNew extends Minder2_Model_Mapper_Abstract {

    public function get($locationId,$whId) {
        if (false !== ($result = $this->_getMinder()->fetchAssoc("SELECT LOCN_ID, LOCN_NAME , LOCN_OWNER , MOVE_STAT FROM LOCATION WHERE WH_ID = '$whId' AND LOCN_ID = ?", $locationId))) {
            return new Minder2_Model_LocationNew(array(
                'whId' => $whId,
                'locationId' => $result['LOCN_ID'],
                'locationName' => $result['LOCN_NAME'],
	        'locationOwner' => $result['LOCN_OWNER'],
		'moveStat' => $result['MOVE_STAT'],
                'existed' => true
            ));
           
        }

        return new Minder2_Model_LocationNew(array('locationId' => $locationId, 'existed' => false));
    }

    /**
     * @throws Minder_Exception
     * @param Minder2_Model_Borrower $borrower
     * @return Minder2_Model_Borrower
     */
    public function save(Minder2_Model_LocationNew $location) {
        $transaction = new Transaction_LINSL();
	$transaction->whId         = $location->whId;
        $transaction->locationId   = $location->locationId;
        $transaction->locationName = $location->locationName;
	$transaction->moveStat     = $location->moveStat;
        $transaction->date         = date('d-M-Y H:i:s');
	if($location->labelQty>0) {
	$transaction->labelDate    = $transaction->date;
	}
	else {
	$transaction->labelDate    = '';
	}

        if (false === $this->_getMinder()->doTransactionResponseV6($transaction))
            throw new Minder_Exception('Error ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->_getMinder()->lastError);

        return $location;
    }
}
