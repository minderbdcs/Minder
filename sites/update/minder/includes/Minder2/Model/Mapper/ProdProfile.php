<?php

class Minder2_Model_Mapper_ProdProfile extends Minder2_Model_Mapper_Abstract {

    /**
     * @throws Exception
     * @param Minder2_Model_ProdProfile $prodProfile
     * @param string $prodIdVia
     * @return void
     */
    public function createProdProfile(Minder2_Model_ProdProfile $prodProfile, $prodIdVia = 'K') {
        $transaction           		= new Transaction_PPPDP();
        $transaction->prodId 		= $prodProfile->PROD_ID;
        $transaction->productType   = $prodProfile->PROD_TYPE;
        $transaction->issueUom      = $prodProfile->ISSUE_UOM;
        $transaction->uom           = $prodProfile->ISSUE_UOM;
        $transaction->orderUom      = $prodProfile->ISSUE_UOM;
        $transaction->shortDesc     = $prodProfile->SHORT_DESC;
        $transaction->standardCost  = 0; //$qty;
        $source						= 'SS' . $prodIdVia . 'KKSKSK';

        if (false === $this->_getMinder()->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    ')) {
            throw new Exception($this->_getMinder()->lastError);
        }

        $sql = "
            UPDATE PROD_PROFILE SET
                SSN_TYPE = ?,
                DEFAULT_ISSUE_QTY = ?,
                NET_WEIGHT = ?,
                NET_WEIGHT_UOM = ?,
                PP_MATERIAL_SAFETY_DATA = ?,
                COMPANY_ID = ?
            WHERE
                PROD_ID = ?
        ";

        $sqlResult = $this->_getMinder()->execSQL($sql, array(
                                             $prodProfile->SSN_TYPE,
                                             $prodProfile->DEFAULT_ISSUE_QTY,
                                             $prodProfile->NET_WEIGHT,
                                             $prodProfile->ISSUE_UOM,
                                             $prodProfile->PP_MATERIAL_SAFETY_DATA,
                                             $prodProfile->COMPANY_ID,
                                             $prodProfile->PROD_ID
                                           ));

        if (false === $sqlResult)
            throw new Exception($this->_getMinder()->lastError);
    }

    /**
     * @param Minder2_Model_ProdProfile $prodProfile
     * @param $qty
     * @param int $labelQty
     * @return Transaction_Response_GRNVP
     */
    public function loadProduct(Minder2_Model_ProdProfile $prodProfile, $location, $qty, $labelQty = 1) {
        $grndb = new Transaction_GRNDB();
        $grndb->conNoteNo = '';
        $grndb->carrierId = 'UNKNOWN';
        $grndb->vehicleRegistration = 'OTC';
        $grndb->hasContainer = '';
        $grndb->palletOwnerId = '';

        $grndbResponse = $grndb->parseResponse($this->_getMinder()->doTransactionResponseV6($grndb));

        $grnvp = new Transaction_GRNVP();
        $grnvp->productId = $prodProfile->PROD_ID;
        $grnvp->grnNo = $grndbResponse->grnNo;
        $grnvp->locationId = $location;
        $grnvp->deliveryTypeId = 'LP';
        $grnvp->qtyOfLabels1 = $labelQty;
        $grnvp->qtyOnLabels1 = $qty;
        $grnvp->qtyOfLabels2 = 0;
        $grnvp->qtyOnLabels2 = 0;
        $grnvp->printerId = $this->_getMinder()->limitPrinter;
        $grnvp->totalVerified = $qty;
        $grnvp->orderType = 'LP';
        $grnvp->orderSubType = 'OT';

        return $grnvp->parseResponse($this->_getMinder()->doTransactionResponseV6($grnvp));
    }

    public function getNextProdId($companyId) {
        $log = Minder_Registry::getLogger()->startDetailedLog(__METHOD__);
        $sql = "SELECT PROD_ID, ERROR_TEXT FROM NEXT_PROD_ID ('" . $companyId . "')";
        $queryResult = $this->_getMinder()->fetchAssoc($sql);

        if (empty($queryResult['PROD_ID'])) {
            $log->error($queryResult['ERROR_TEXT']);
            throw new Exception($queryResult['ERROR_TEXT']);
        }

        $log->info($queryResult['ERROR_TEXT']);

        return $queryResult['PROD_ID'];
    }
}