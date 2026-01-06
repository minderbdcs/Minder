<?php
class Minder_SysScreen_Model_Issn extends Minder_SysScreen_Model
{
    public function __construct()
    {
        $this->useDistinct = false;
        parent::__construct();
    }


    public function selectSsn($rowOffset, $itemCountPerPage) {
        $result = array();
        if (false !== ($ssns = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ISSN.SSN_ID'))) 
            $result = array_map(create_function('$item', 'return $item["SSN_ID"];'), $ssns);
        
        return $result;
    }

    public function selectSsnAndLocation($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ISSN.SSN_ID, ISSN.LOCN_ID, ISSN.WH_ID');
    }
    
    public function printLabels($printerObj) {
        $result = new stdClass();
        $result->messages = array();
        $result->errors   = array();

        $ssns = $this->selectSsn(0, count($this));
        
        if (count($ssns) < 1)
            return $result;


        $issnLabelPrinter = new Minder_LabelPrinter_Issn();
        return $issnLabelPrinter->doPrint($ssns, $this->_getMinder()->getPrinter());
    }

    /**
     * @param $issns
     * @param Minder_JSResponse $response
     * @return void
     */
    protected function _deleteIssn($issns, $response) {
        $trol = new Transaction_TROLA();
        $trol->reference = 'Deleted by ' . $this->_getMinder()->userId . ' from device ' . $this->_getMinder()->deviceId;

        $tril = new Transaction_TRILA();
        $tril->locnId = '00000000';
        $tril->whId = 'XX';
        $tril->reference = 'Deleted by ' . $this->_getMinder()->userId . ' from device ' . $this->_getMinder()->deviceId;

        $deleted = 0;

        foreach ($issns as $issn) {
            $trol->ssnId = $issn['SSN_ID'];
            $trol->locnId = $issn['LOCN_ID'];
            $trol->whId = $issn['WH_ID'];

            if (false === $this->_getMinder()->doTransactionResponse($trol)) {
                $response->errors[] = 'Error deleting ISSN #' . $issn['SSN_ID'] . ': ' . $this->_getMinder()->lastError;
            } else {
                $tril->ssnId = $issn['SSN_ID'];

                if (false === $this->_getMinder()->doTransactionResponse($tril)) {
                    $response->errors[] = 'Error deleting ISSN #' . $issn['SSN_ID'] . ': ' . $this->_getMinder()->lastError;
                } else {
                    $deleted++;
                }
            }
        }

        $response->messages[] = $deleted . ' ISSN(s) deleted.';
    }

    public function deleteIssn() {
        $response = new Minder_JSResponse();

        $issns = $this->selectSsnAndLocation(0, count($this));

        if (count($issns) < 1)
            return $response;

        $this->_deleteIssn($issns, $response);

        return $response;
    }
}
