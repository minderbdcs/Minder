<?php
  
class Minder_ConnoteProccess {
    
    protected static $precision = 0;
    
    public $messages = array();
    public $warnings = array();

    public $orders   = array();
    public $lines    = array();
    
    public $ordersInvoiceDetails = array();
    public $linesInvoiceDetails  = array();
    
    public $connoteNo = '';
    
    protected $connoteSerialNo = '';
    
    public $accountNo = '';
    public $selectedOrder = '';
    public $selectedLocation = '';
    public $carrierId = '';
    public $palletQty = '';
    public $palletOwner = '';
    public $cartonQty = '';
    public $satchelQty = '';
    public $totalWeight = '';
    public $totalVolume = '';
    protected $serviceType = '';
    public $carrierServiceRecordId = '';
    public $printerId = '';
    public $qtyAddressLabel = '';
    public $payerFlag = '';
    public $pickBlock;
    
    public $packDims = array();

    public $skipLabelPrinting = false;
    
    protected $minder   = null;
    
    protected $totalWeightMaxLen = 0;
    protected $totalVolumeMaxLen = 0;

    /**
     * @var Minder_ConnoteProccess_LabelGenerator
     */
    protected $labelGenerator    = null;
    
    protected $needUpdatePickOrderShipVia = false;
    
    /**
    * DSOT transaction to use
    * 
    * @var Transaction_DSOTS
    */
    protected $DSOTtransaction = null;
    /**
     * @var Transaction_Response_DSOT
     */
    protected $DSOTResponse;

    protected $packIdToUpdate = array();

    protected $_despatchingOrderCollection;

    public function __construct() {
        $this->minder = Minder::getInstance();
    }
    
    protected function reducePrecision($val, $toFit, $startPrecision) {
        $precision = $startPrecision;
        while ($precision > 0) {
            $val = round($val, $precision--);
            if (strlen($val) <= $toFit)
                break;
        }
        
        if (strlen($val) > $toFit)
            return false;
        else
            return $val;
    }

    protected function getCarrierServiceByRecordId($carrierServiceRecordId) {
        foreach ($this->minder->getCarrierServiceTypes() as $serviceRecord) {
            if ($serviceRecord['RECORD_ID'] == $carrierServiceRecordId) {
                return $serviceRecord;
            }
        }

        return array(
            'RECORD_ID' => '',
            'CARRIER_ID' => '',
            'SERVICE_TYPE' => ''
        );
    }

    protected function getCarrierServiceRecordId($carrierId, $carrierServiceType) {
        foreach ($this->minder->getCarrierServiceTypes($carrierId) as $serviceRecord) {
            if ($serviceRecord['SERVICE_TYPE'] == $carrierServiceType) {
                return $serviceRecord['RECORD_ID'];
            }
        }

        return '';
    }
    
    protected function fillCarrierServiceType() {
        $this->serviceType = '';
        $carrierService = $this->getCarrierServiceByRecordId($this->carrierServiceRecordId);

        if ($this->carrierId != $carrierService['CARRIER_ID']) {

            $oldServiceRecordId = $this->carrierServiceRecordId;

            $defaultServiceTypes = $this->minder->getCarrirersDefaultServiceTypesList();
            $this->carrierServiceRecordId = isset($defaultServiceTypes[$this->carrierId]) ? $defaultServiceTypes[$this->carrierId] : '';
            $newCarrierService = $this->getCarrierServiceByRecordId($this->carrierServiceRecordId);
            $this->serviceType = $newCarrierService['SERVICE_TYPE'];

            $this->warnings[]  = 'Selected CARRIER_SERVICE #' . $oldServiceRecordId . ' does not belong to selected CARRIER #' . $this->carrierId . '. Will use default SERVICE_TYPE "' . $this->serviceType . '" for selected CARRIER.';
        } else {
            $this->serviceType = $carrierService['SERVICE_TYPE'];
        }

        if (empty($this->serviceType)) {
            $this->serviceType = 'GEN';
            $this->warnings[] = 'SERVICE_TYPE is empty. Will use default "GEN" for SERVICE_TYPE.';
            $this->carrierServiceRecordId = $this->getCarrierServiceRecordId($this->carrierId, $this->serviceType);
        }
    }

    protected function validateCarrierId() {
        $firstOrder = $this->minder->getPickOrder(current($this->orders));
        $orderCompany = $this->minder->getCompany($firstOrder->companyId);
        $defaultCarrierId = $this->minder->defaultControlValues['DEFAULT_CARRIER_ID'];
        $carriersList = $this->minder->getCarriersList();

        if (empty($this->carrierId) || !isset($carriersList[$this->carrierId])) {
            //No or unknown carrier, use from PICK_ORDER
            $this->carrierId = $firstOrder->shipVia;
        }

        if (empty($this->carrierId) || !isset($carriersList[$this->carrierId])) {
            //$shipVia is empty or unknown carrier, use from DEFAULT_CARRIER_ID
            $this->warnings[] = (empty($this->carrierId)) ? 'PICK_ORDER.SHIP_VIA is empty or unknown CARRIER. Will use company default CARRIER_ID: "' . $orderCompany->defaultCarrierId . '".' : 'PICK_ORDER.SHIP_VIA contains unknown CARRIER_ID: "' . $this->carrierId . '". Will use default CARRIER_ID: "' . $orderCompany->defaultCarrierId . ' instead".';
            $this->carrierId = $orderCompany->defaultCarrierId;
        }

        if (empty($this->carrierId) || !isset($carriersList[$this->carrierId])) {
            //$shipVia is empty or unknown carrier, use from DEFAULT_CARRIER_ID
            $this->warnings[] = (empty($this->carrierId)) ? 'Company Default CARRIER_ID is empty or unknown CARRIER. Will use Control default CARRIER_ID: "' . $defaultCarrierId . '".' : 'Company Default CARRIER_ID contains unknown CARRIER_ID: "' . $this->carrierId . '". Will use Control default CARRIER_ID: "' . $defaultCarrierId . ' instead".';
            $this->carrierId = $defaultCarrierId;
        }

        if (empty($this->carrierId))
            throw new Minder_ConnoteProccess_Exception('CONTROL.DEFAULT_CARRIER_ID is empty. Please check system setup.');

        if (!isset($carriersList[$this->carrierId]))
            throw new Minder_ConnoteProccess_Exception('CONTROL.DEFAULT_CARRIER_ID "' . $this->carrierId . '" was not found in CARRIER table. Please check system setup.');

        if ($this->carrierId !== $firstOrder->shipVia) {
            $this->needUpdatePickOrderShipVia = true;
            $this->warnings[] = 'PICK_ORDER.SHIP_VIA: "' . $firstOrder->shipVia . '" is differ from selected Ship Via: "' . $this->carrierId . '" . Will update PICK_ORDER.';
        }
    }

    protected function validateParams() {
        $this->validateCarrierId();
        $this->fillCarrierServiceType();
            
        if (empty($this->printerId))
            $this->printerId = $this->minder->defaultControlValues['DEFAULT_DESPATCH_PRINTER '];
            
        if (empty($this->payerFlag))
            $this->payerFlag = 'S';
            
        $uomConverter = new Minder_Controller_Action_Helper_UomConverter();
        
        $dimensionDefaults = $this->minder->getDimensionDefaults();

        $carrierUoms = $this->minder->getCarrierUoms($this->carrierId);
        if (empty($carrierUoms['DT'])) {
            throw new Minder_ConnoteProccess_Exception('Cannot find Carriers UOMs to convert: OPTIONS.PACK_ID_DT is not defined. Check system setup.');
        }
        
        if (empty($carrierUoms['WT'])) {
            throw new Minder_ConnoteProccess_Exception('Cannot find Carriers UOMs to convert: OPTIONS.PACK_ID_WT is not defined. Check system setup.');
        }
        
        if (empty($carrierUoms['VT'])) {
            throw new Minder_ConnoteProccess_Exception('Cannot find Carriers UOMs to convert: OPTIONS.PACK_ID_VT is not defined. Check system setup.');
        }
        
        $dsotUoms    = $this->minder->getDsotUoms();
        
        $uomConverter->getUoms(array(
            $carrierUoms['DT'],
            $carrierUoms['WT'],
            $carrierUoms['VT'],
            $dsotUoms['VT'],
            $dsotUoms['WT'],
            $dsotUoms['DT_FOR_VT']
        ));

        $enteredPallets    = 0;
        $enteredCatrons    = 0;
        $enteredSatchels   = 0;
        $this->totalVolume = 0;
        $this->totalWeight = 0;

        foreach ($this->packDims as &$dimension) {
            
            //if some dimensions not filled, use system defaults from OPTION table
            $dimension['L']  = empty($dimension['L']) ? (isset($dimensionDefaults[$dimension['TYPE']]) && isset($dimensionDefaults[$dimension['TYPE']]['L']) ? $dimensionDefaults[$dimension['TYPE']]['L'] : 0) : $dimension['L'];
            $dimension['W']  = empty($dimension['W']) ? (isset($dimensionDefaults[$dimension['TYPE']]) && isset($dimensionDefaults[$dimension['TYPE']]['W']) ? $dimensionDefaults[$dimension['TYPE']]['W'] : 0) : $dimension['W'];
            $dimension['H']  = empty($dimension['H']) ? (isset($dimensionDefaults[$dimension['TYPE']]) && isset($dimensionDefaults[$dimension['TYPE']]['H']) ? $dimensionDefaults[$dimension['TYPE']]['H'] : 0) : $dimension['H'];
            $dimension['WT'] = empty($dimension['WT']) ? (isset($dimensionDefaults[$dimension['TYPE']]) && isset($dimensionDefaults[$dimension['TYPE']]['WT']) ? $dimensionDefaults[$dimension['TYPE']]['WT'] : 0) : $dimension['WT'];
            
            $packDtFactor         = $uomConverter->convert(1, $dimension['DIMENSION_UOM'], $carrierUoms['DT']);
            $packWtFactor         = $uomConverter->convert(1, $dimension['PACK_WEIGHT_UOM'], $carrierUoms['WT']);
            
            $dsotDtForVtFactor   = $uomConverter->convert(1, $dimension['DIMENSION_UOM'], $dsotUoms['DT_FOR_VT']);
            $dsotWtFactor        = $uomConverter->convert(1, $dimension['PACK_WEIGHT_UOM'], $dsotUoms['WT']);
            
            //calc VOLUME and WEIGHT for PACK_ID using CARRIER UOMs
            $dimension['VOL']    = $dimension['L'] * $dimension['W'] * $dimension['H'] * $packDtFactor * $packDtFactor * $packDtFactor;
            $dimension['WT']     = $dimension['WT'] * $packWtFactor;
            
            //calc DSOT TOTAL_VOLUME and TOTAL_WEIGHT using DSOTs UOMs
            $this->totalVolume += $dimension['L'] * $dimension['W'] * $dimension['H'] * $dsotDtForVtFactor * $dsotDtForVtFactor * $dsotDtForVtFactor;
            $this->totalWeight += $dimension['WT'] * $dsotWtFactor;

            //now calc DIMENSIONS from PACK_ID using CARRIER UOMs
            $dimension['L']               *= $packDtFactor;
            $dimension['W']               *= $packDtFactor;
            $dimension['H']               *= $packDtFactor;
            $dimension['DIMENSION_UOM']    = $carrierUoms['DT'];
            $dimension['PACK_WEIGHT_UOM']  = $carrierUoms['WT'];
            $dimension['VOLUME_UOM']       = $carrierUoms['VT'];
            
            switch ($dimension['TYPE']) {
                case 'C':
                    $enteredCatrons  += $dimension['QTY'];
                    break;
                case 'P':
                    $enteredPallets  += $dimension['QTY'];
                    $dimension['VOL'] = 0;
                    break;
                case 'S':
                    $enteredSatchels += $dimension['QTY'];
                    break;
                default:
                    throw new Minder_ConnoteProccess_Exception("Unknown package type '" . $dimension['TYPE'] . "'.");
            }
        }
        
        if ($enteredPallets != $this->palletQty)
            throw new Minder_ConnoteProccess_Exception("Entered pallets qty (" . $enteredPallets . ") doesn not match total pallets qty (" . $this->palletQty . ").");
            
        if ($enteredCatrons != $this->cartonQty)
            throw new Minder_ConnoteProccess_Exception("Entered cartons qty (" . $enteredCatrons . ") doesn not match total cartons qty (" . $this->cartonQty . ").");
            
        if ($enteredSatchels != $this->satchelQty)
            throw new Minder_ConnoteProccess_Exception("Entered satchels qty (" . $enteredSatchels . ") doesn not match total satchels qty (" . $this->satchelQty . ").");
            
        if (($this->totalWeight == 0) && ($this->totalVolume == 0))
            throw new Minder_ConnoteProccess_Exception("Enter Weight or Volume information.");

        $this->totalWeight = round($this->totalWeight, self::$precision);
        $this->totalVolume = round($this->totalVolume, self::$precision);
        
        if (false === ($this->totalWeight = $this->reducePrecision($this->totalWeight, $this->totalWeightMaxLen, self::$precision)))
            throw new Minder_ConnoteProccess_Exception('Error while ' . $this->DSOTtransaction->transCode . $this->DSOTtransaction->transClass . ' transaction: Total Weight is greater then ' . str_repeat('9', $this->totalWeightMaxLen));
        
        if (false === ($this->totalVolume = $this->reducePrecision($this->totalVolume, $this->totalVolumeMaxLen, self::$precision)))
            throw new Minder_ConnoteProccess_Exception('Error while ' . $this->DSOTtransaction->transCode . $this->DSOTtransaction->transClass . ' transaction: Total Volume is greater then ' . str_repeat('9', $this->totalVolumeMaxLen));
        
        $this->labelGenerator->init($this->carrierId, $this->serviceType);
        
        if (empty($this->connoteNo)) {
            $this->connoteNo       = $this->labelGenerator->getNextConnote(current($this->orders));
            $this->connoteSerialNo = $this->labelGenerator->getSerialNo();
        }
            
        if (empty($this->connoteNo))
            //if connote still empty raise error
            throw new Minder_ConnoteProccess_Exception('Non empty "Consignment #" required.');
    }
    
    protected function runDSOTTransaction() {
        $this->DSOTtransaction->conNoteNo      =   $this->connoteNo;
        $this->DSOTtransaction->accountNo      =   $this->accountNo;

        if (get_class($this->DSOTtransaction) == 'Transaction_DSOTS') {
            $this->DSOTtransaction->orderNo    =   $this->selectedOrder; 
        } else {
            $this->DSOTtransaction->locationId =   $this->selectedLocation;
        }
            
        $this->DSOTtransaction->payerFlag      =   $this->payerFlag;
        $this->DSOTtransaction->carrierId      =   $this->carrierId;
        $this->DSOTtransaction->palletQty      =   $this->palletQty;
        $this->DSOTtransaction->palletOwnerId  =   $this->palletOwner;
        $this->DSOTtransaction->cartonQty      =   $this->cartonQty;
        $this->DSOTtransaction->satchelQty     =   $this->satchelQty;
        $this->DSOTtransaction->totalWeight    =   $this->totalWeight;
        $this->DSOTtransaction->totalVolume    =   $this->totalVolume;
        $this->DSOTtransaction->serviceType    =   $this->serviceType;
        $this->DSOTtransaction->serviceRecordId =  $this->carrierServiceRecordId;
        $this->DSOTtransaction->packType       =   $this->minder->defaultControlValues['DEFAULT_CONNOTE_PACK'];
        $this->DSOTtransaction->printerId      =   $this->printerId;
        $this->DSOTtransaction->labelQty       =   $this->qtyAddressLabel;
        
        if (false === ($result = $this->minder->doTransactionResponse($this->DSOTtransaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
            throw new Minder_ConnoteProccess_Exception('Error while ' . $this->DSOTtransaction->transCode . $this->DSOTtransaction->transClass . ' transaction: ' . $this->minder->lastError);
        }

        $this->DSOTResponse = $this->DSOTtransaction->parseResponse($result);
        $this->messages[] = 'Transaction '. $this->DSOTtransaction->transCode . $this->DSOTtransaction->transClass . ': ' . $result;
    }
    
    protected function runDSOLTransaction() {
        $transaction            =    new Transaction_DSOLO();
        
        $transaction->objectId  =    $this->DSOTResponse->getAwbConsignmentNo();
        $transaction->qty       =    $this->qtyAddressLabel;    
             
        if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSSKSS', '', 'MASTER    '))) { 
            throw new Minder_Exception('Error while ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->minder->lastError);
        }
        $this->messages[] = 'Transaction '. $transaction->transCode . $transaction->transClass . ': ' . $result;
    }
    
    protected function updatePackId() {
        $this->packIdToUpdate = $this->minder->getPackIdCretedByDSOL($this->connoteNo);

        //echo "pack id to update: ".$this->packIdToUpdate."<br/>";
        //print_R($this->packIdToUpdate);
        //echo "<br/>";        


        $this->minder->updatePackIdDimensions($this->packIdToUpdate, $this->packDims);
        
        $currentLabel = 1;
        
        $sql = "
            UPDATE PACK_ID SET
                PACK_ID.PACK_SERIAL_NO               = ?,
                PACK_ID.PACK_SEQUENCE_NO             = ?,
                PACK_ID.PACK_LAST_SEQUENCE_INDICATOR = ?,
                PACK_ID.DESPATCH_LABEL_NO            = ?
            WHERE
                PACK_ID.PACK_ID = ?
        ";

        $printTools = new Minder_PackIdPrintTools();

        foreach ($this->packIdToUpdate as $packIdRow) {
            $labelData = $printTools->getPackIdListForRePrint($packIdRow['PACK_ID']);
            $labelData = current($labelData);
            $labelData['PACK_ID.PACK_SERIAL_NO']               = $this->labelGenerator->getNextPackLabelNumber();
            $labelData['PACK_ID.PACK_SEQUENCE_NO']             = str_repeat('0', 2 - strlen($currentLabel)) . $currentLabel;
            $labelData['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR'] = ($currentLabel < $this->qtyAddressLabel) ? $labelData['CARRIER.NOT_LAST_PACK_INDICATOR'] : $labelData['CARRIER.LAST_PACK_INDICATOR'];
            $labelData['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR'] = trim($labelData['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR']);
            
            $this->labelGenerator->addFields($labelData);
            $generatedLabel = $this->labelGenerator->getPackIdLabel();
            
            $labelData['PACK_ID.DESPATCH_LABEL_NO'] = (empty($generatedLabel)) ? $labelData['PACK_ID.DESPATCH_LABEL_NO'] : $generatedLabel;
            
            $this->minder->execSQL(
                $sql,
                array(
                    $labelData['PACK_ID.PACK_SERIAL_NO'],
                    $labelData['PACK_ID.PACK_SEQUENCE_NO'],
                    $labelData['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR'],
                    $labelData['PACK_ID.DESPATCH_LABEL_NO'],
                    $packIdRow['PACK_ID']
                )
            );
            $currentLabel++;
        }
    }
    
    protected function getPickItemDetailsToUpdate() {
        $sql = "
            SELECT
                PICK_ITEM_DETAIL.PICK_LABEL_NO
            FROM
                PICK_DESPATCH
                JOIN PICK_ITEM_DETAIL ON PICK_ITEM_DETAIL.DESPATCH_ID = PICK_DESPATCH.DESPATCH_ID
            WHERE
                AWB_CONSIGNMENT_NO = ?
            AND
                PICK_ITEM_DETAIL.PACK_ID IS NULL
        ";



        
       return $this->minder->fetchAllAssoc($sql, $this->connoteNo);
        //$output=$this->minder->fetchAllAssoc($sql, $this->connoteNo);
        //echo "output is : <br/>";
       // print_r($output);
        //return $output;



    }
    
    protected function updatePickItemDetails() {
        $pickItemDetailsToUpdate = $this->getPickItemDetailsToUpdate();

       // echo "pick item details:".$pickItemDetailsToUpdate."<br/>";
        //print_r($pickItemDetailsToUpdate);

        
        $sql = "
            UPDATE PICK_ITEM_DETAIL SET
                PACK_ID = ?
            WHERE 
                PICK_LABEL_NO = ?
        ";


        foreach ($this->packIdToUpdate as $packIdRow) {
            if (false === ($currentPickItemDetail = current($pickItemDetailsToUpdate)))
                break;
                
            $this->minder->execSQL($sql, array($packIdRow['PACK_ID'], $currentPickItemDetail['PICK_LABEL_NO']));
            next($pickItemDetailsToUpdate);
        }

//echo "last current pick label no: ".$currentPickItemDetail['PICK_LABEL_NO'];
    }

    /**
     * @return array
     */
    protected function getPickDespatchCreatedByDsot() {
        $sql = "
            SELECT
                PICK_DESPATCH.DESPATCH_ID
            FROM
                PICK_DESPATCH
            WHERE
                PICK_DESPATCH.PICKD_SERVICE_RECORD_ID IS NULL
            AND
                AWB_CONSIGNMENT_NO = ?
        ";

        return $this->minder->fetchAllAssoc($sql, $this->connoteNo);
    }

    /**
     * @deprecated not used anymore as Updating PICKD_SERVICE_RECORD_ID has been added to DSOT transaction
     * @return void
     */
    protected function updatePickDespatchPickdServiceRecordId() {
        $sql = "
            UPDATE PICK_DESPATCH SET
                PICKD_SERVICE_RECORD_ID = ?
            WHERE
                DESPATCH_ID = ?
        ";

        foreach ($this->getPickDespatchCreatedByDsot() as $pickDespatchRow) {
            $this->minder->execSQL($sql, array($this->carrierServiceRecordId, $pickDespatchRow['DESPATCH_ID']));
        }
    }
    
    protected function printLabels() {
        if ($this->skipLabelPrinting) {
            return;
        }

        $printTools     = new Minder_PackIdPrintTools();
        $printResult    = $printTools->printLabels($this->_getLastCreatedDespatchId(), $this->minder->getPrinter());
        $this->warnings = array_merge($this->warnings, $printResult->warnings, $printResult->errors);
        $this->messages = array_merge($this->messages, $printResult->messages);
    }

    protected function _getLastCreatedDespatchId()
    {
        $sql = "SELECT FIRST 1 DESPATCH_ID FROM PICK_DESPATCH WHERE AWB_CONSIGNMENT_NO = ? ORDER BY CREATE_DATE DESC";
        $despatchId = $this->minder->findValue($sql, $this->connoteNo);
        return $despatchId;
    }

    protected function _getInvoiceLinesDetails() {
        $sql = '
            SELECT DISTINCT
                PICK_ITEM.PICK_ORDER,
                PICK_ITEM_DETAIL.DESPATCH_ID,

                PICK_ITEM.PICK_LABEL_NO,
                PICK_ITEM.PROD_ID,
                PICK_ITEM.SSN_ID,
                SUM(COALESCE(PICK_ITEM_DETAIL.QTY_PICKED, 0) * COALESCE(PICK_ITEM.SALE_PRICE, 0) * (1 - COALESCE(PICK_ITEM.DISCOUNT, 0) / 100)) AS LINE_TOTAL,
                SUM(COALESCE(PICK_ITEM_DETAIL.QTY_PICKED, 0) * COALESCE(PICK_ITEM.SALE_PRICE, 0) * (1 - COALESCE(PICK_ITEM.DISCOUNT, 0) / 100) * COALESCE(PICK_ITEM.TAX_RATE, 0) / 100) AS TAX_AMOUNT,
                MAX(PICK_ITEM.SALE_PRICE) AS SALE_PRICE,
                MAX(PICK_ITEM.DISCOUNT) AS DISCOUNT,
                SUM(PICK_ITEM_DETAIL.QTY_PICKED) AS QTY_PICKED,
                MAX(PICK_ITEM.TAX_RATE) AS TAX_RATE,
                MAX(PROD_PROFILE.STANDARD_COST) AS STANDARD_COST,
                MAX(PROD_PROFILE.SALE_PRICE) AS PROD_PROFILE_SALE_PRICE,
                MAX(SSN.PURCHASE_PRICE) AS PURCHASE_PRICE,
                PICK_ITEM.LEGACY_LEDGER_SALE_CODE,
                MAX(PICK_ITEM.PICK_ORDER_QTY) AS PICK_ORDER_QTY,
                PICK_ITEM.WARRANTY_TERM
            FROM
                PICK_ITEM_DETAIL
                LEFT OUTER JOIN PICK_ITEM ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
                LEFT OUTER JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
                LEFT OUTER JOIN SSN ON PICK_ITEM.SSN_ID = SSN.SSN_ID
                LEFT OUTER JOIN PROD_PROFILE ON PICK_ITEM.PROD_ID = PROD_PROFILE.PROD_ID
            WHERE
                PICK_ITEM_DETAIL.DESPATCH_ID = ?
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?
            GROUP BY
                PICK_ITEM.PICK_LABEL_NO,
                PICK_ITEM_DETAIL.DESPATCH_ID,
                PICK_ITEM.PICK_ORDER,
                PICK_ITEM.PROD_ID,
                PICK_ITEM.SSN_ID,
                PICK_ITEM.LEGACY_LEDGER_SALE_CODE,
                PICK_ITEM.WARRANTY_TERM
        ';



        return $this->minder->fetchAllAssoc($sql, $this->_getLastCreatedDespatchId(), 'DC');
    }

    protected function _getInvoiceDetails() {
        $sql = '
            SELECT DISTINCT
                PICK_ITEM_DETAIL.DESPATCH_ID,
                PICK_ORDER.PICK_ORDER,
                PICK_ORDER.LEGACY_LEDGER_ADMIN_FEE_CODE,
                PICK_ORDER.LEGACY_LEDGER_FREIGHT_CODE,
                PICK_ORDER.LEGACY_LEDGER_DEPOSIT_CODE,
                PICK_ORDER.FREIGHT,
                PICK_ORDER.FREIGHT_TAX_AMOUNT,
                PICK_ORDER.TAX_RATE,
                PICK_ORDER.TAX_AMOUNT,
                PICK_ORDER.NET_TOTAL_AMOUNT,
                PICK_ORDER.SUM_TOTAL_AMOUNT,
                PICK_ORDER.ADMIN_FEE_RATE,
                PICK_ORDER.ADMIN_FEE_AMOUNT,
                PICK_ORDER.AMOUNT_PAID,
                PICK_ORDER.COMPANY_ID,
                PICK_ORDER.SHIPPING_METHOD,
                PICK_ORDER.DUE_AMOUNT,
                PICK_ORDER.SUB_TOTAL_AMOUNT,
                PICK_ORDER.SUB_TOTAL_TAX,
                PICK_ORDER.FEES_AMOUNTS,
                PICK_ORDER.FEES_AMOUNTS_TAX_AMOUNT,
                PICK_ORDER.ADMIN_FEE_PERCENT_AMOUNT,
                PICK_ORDER.ADMIN_FEE_TAX_AMOUNT,
                PICK_ORDER.OTHER_NUM1,
                PICK_ORDER.OTHER_NUM2,
                PICK_ORDER.LAST_INVOICE_NO,
                PICK_ORDER.FREIGHT_EVERY_INVOICE,
                PICK_ORDER.WH_ID
            FROM
                PICK_ITEM_DETAIL
                LEFT OUTER JOIN PICK_ORDER ON PICK_ITEM_DETAIL.PICK_ORDER = PICK_ORDER.PICK_ORDER
            WHERE
                PICK_ITEM_DETAIL.DESPATCH_ID = ?
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?
        ';

        return $this->minder->fetchAllAssoc($sql, $this->_getLastCreatedDespatchId(), 'DC');
    }

    protected function createInvoice() {
        $linesInvoiceDetails  = $this->_getInvoiceLinesDetails();
        $ordersInvoiceDetails = $this->_getInvoiceDetails();


        if(empty($ordersInvoiceDetails)){

                            $this->warnings[]='No order with the required consignment no: and despatch id';

        }


        list($devWhId, $devLocnId)  = $this->minder->getDeviceWhAndLocation();
        $pinvcTransaction = new Transaction_PINVC();
        $pilncTransaction = new Transaction_PILNC();
        $createdInvoiceNo = '';
        
        foreach ($ordersInvoiceDetails as $orderDetail) {
            //first we need to calc some values for PINV C transaction from PICK_ITEM_DETAIL records
            //and as we will use most of this values in PILN C transactions
            //so create array of PILN C transactions and fill it with this calculated values
            
            $pilncTransArray  = array();

            foreach ($linesInvoiceDetails as $detailLine) {
                if ($detailLine['PICK_ORDER'] != $orderDetail['PICK_ORDER'])
                    continue;
                
                $pilncTransaction->fillValuesFromPickItemDetail($detailLine);
                $pilncTransArray[] = clone $pilncTransaction;

                $pinvcTransaction->addPickItemDetail($detailLine);
            }


        //    echo "Order Details Array: <br/>";
          //  print_r($orderDetail);
         //   exit;

            
            $pinvcTransaction->fillValuesFromPickOrderDetail($orderDetail);
            
            $pinvcTransaction->whId          = $devWhId;
            $pinvcTransaction->locationId    = $devLocnId;
            $pinvcTransaction->invoiceType   = 'TI';                        // All Invoices created following a 'DSOT' must only use INVOICE_TYPE = 'TI' - Tax Invoice.
            $pinvcTransaction->printerDevice = $this->minder->limitPrinter; //User must select from LIMIT Printer drop down.
            $pinvcTransaction->invoiceQty    = 1;                           //Use 1 until I get another CONTROL.DEFAULT_INVOICE_COPIES.
            $pinvcTransaction->setAwbConsignmentNo($this->connoteNo);
            
            if (false === ($result = $this->minder->doTransactionResponse($pinvcTransaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                throw new Minder_ConnoteProccess_Exception('Error while ' . $pinvcTransaction->transCode . $pinvcTransaction->transClass . ' transaction: ' . $this->minder->lastError);
            }
            $this->messages[]     = 'Transaction '. $pinvcTransaction->transCode . $pinvcTransaction->transClass . ': ' . $result;
            list(,,$createdInvoiceNo)   = $pinvcTransaction->parseResponse($result);

            /**
             * @var Transaction_PILNC $tmpPilncTransaction
             */
            foreach ($pilncTransArray as $tmpPilncTransaction) {
                $tmpPilncTransaction->whId          = $devWhId;
                $tmpPilncTransaction->locationId    = $devLocnId;
                $tmpPilncTransaction->invoiceNo     = $createdInvoiceNo;    //parse PINV transaction response to find invoiceNo created by PINV C transaction
                $tmpPilncTransaction->invoiceType   = $pinvcTransaction->invoiceType; //the same as in PINV C, as this is used to find INVOICE created by PINV C

                if (false === ($result = $this->minder->doTransactionResponse($tmpPilncTransaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                    throw new Minder_ConnoteProccess_Exception('Error while ' . $tmpPilncTransaction->transCode . $tmpPilncTransaction->transClass . ' transaction: ' . $this->minder->lastError);
                }
                $this->messages[]     = 'Transaction '. $tmpPilncTransaction->transCode . $tmpPilncTransaction->transClass . ': ' . $result;
            }
            
            //lets create Invoice PDF
/**
 * @author Sergey Golovin (2011-10-13)
 * Commented until e-mailing will be completed
 */
//            $invoiceReport                = Minder_Report_Factory::makeInvoiceReportForCompany($orderDetail['COMPANY_ID'], 'TI');
//            $invoiceReport->pickInvoiceNo = $createdInvoiceNo;
//            $invoiceReport->pickOrder     = $orderDetail['PICK_ORDER'];
//            $invoicePdf                   = $invoiceReport->getPdfImage();
//
//            $this->sendInvoiceMail($orderDetail, $invoicePdf);
        }
    }

    /**
     * @todo implement invoice e-mailing
     * @param $orderDetail
     * @param $invoicePdf
     * @return void
     */
    protected function sendInvoiceMail($orderDetail, $invoicePdf) {
        $mailer = new Zend_Mail();
        $mailer->setBodyText('Invoice attached.');
        $mailer->setFrom('test@example.com', 'Test Sender'); //need to find out where can I get return Email
        $toEmail = $this->minder->findInvoiceEmail($orderDetail['COMPANY_ID'], 'TI');
        $mailer->addTo($toEmail);
        $mailer->setSubject('Invoice.');
        $mailer->createAttachment($invoicePdf,
            'application/pdf',
            Zend_Mime::DISPOSITION_INLINE,
            Zend_Mime::ENCODING_8BIT);
        $mailer->send(new Minder_Mail_Transport_FileSystem());
    }
    
    /**
     * Check if we need to create invoice during Connote proccess
     *
     * @return bool
     */
    public function useInvoice() {

        return ($this->minder->defaultControlValues['USE_INVOICE'] == 'T');
    }
    
    public function run() {
        $log = Minder_Registry::getLogger()->startDetailedLog();
        $log->starting('checking despatch policy....');
        $this->_checkDespatchPolicy();
        $log->done();

        $log->starting('moving items to despatch location....');
        $itemMover = empty($this->pickBlock) ? new Minder_ConnoteProccess_ItemMover_Lines($this->lines) : new Minder_ConnoteProccess_ItemMover_PickBlock($this->lines);
        $itemMover->moveItemsToDespatchLocation();
        $log->done();

        try {
            $log->starting('checking line status and partial despatch....');

            if (true !== ($result = $this->minder->checkOrdersAndLinesForDespatch($this->orders, $this->lines)))
                throw new Minder_ConnoteProccess_Exception($result);

            $this->_checkPartialOrderPolicy();
            $log->done();

            $log->starting('preparing orders for despatch....');

            if (count($this->orders) > 1) {
                $holdBy                  = 'location';
                $this->DSOTtransaction   = new Transaction_DSOTL();
                $this->totalWeightMaxLen = Transaction_DSOTL::$totalWeightMaxLen;
                $this->totalVolumeMaxLen = Transaction_DSOTL::$totalVolumeMaxLen;
            } else {
                $holdBy                  = 'order';
                $this->DSOTtransaction   = new Transaction_DSOTS();
                $this->totalWeightMaxLen = Transaction_DSOTS::$totalWeightMaxLen;
                $this->totalVolumeMaxLen = Transaction_DSOTS::$totalVolumeMaxLen;
            }

            $this->labelGenerator = new Minder_ConnoteProccess_LabelGenerator();

            $this->validateParams();

            $args = $this->orders;
            array_unshift($args, $this->carrierId);

            if ($this->needUpdatePickOrderShipVia) {
                $this->minder->execSQL(
                    "
                        UPDATE PICK_ORDER SET
                            SHIP_VIA = ?
                        WHERE
                            PICK_ORDER IN (" . substr(str_repeat('?, ', count($this->orders)), 0, -2) . ")
                    ",
                    $args
                );
            }

            list($this->selectedOrder, $this->selectedLocation, $holdedPickItems, $holdedISSNs) = $this->minder->holdUnnededLines($this->lines, $holdBy); //todo: fix for big orders
            $log->done();
        } catch (Exception $innerException) {
            $log->doneWithErrors($innerException->getMessage());
            $log->starting('moving items to original locations and releasing held lines....');
            $itemMover->moveItemsToOriginalLocation();
            $log->done();
            throw $innerException;
        }

        try {
            $log->starting('executing transactions....');
            $this->runDSOTTransaction();
            $this->runDSOLTransaction();
            $log->done();
        } catch(Exception $e) {
            $log->doneWithErrors($e->getMessage());

            $log->starting('moving items to original locations and releasing held lines....');
            $itemMover->moveItemsToOriginalLocation();
            //always release holded lines
            $this->minder->releaseHoldedLines($holdedPickItems, $holdedISSNs);
            $log->done();
            throw $e;
        }
        
        try {
            $log->starting('updating packing details....');
            $this->updatePackId();
            $this->updatePickItemDetails();
            $this->printLabels();

            $this->minder->commitTransaction(true);
            $log->done();

            $log->starting('preparing invoice if needed....');

            if ($this->useInvoice()) {

                $this->createInvoice();
            }


            $log->done();

            $log->starting('printing packing slip if needed....');
            $response = $this->_getPackingSlipManager()->setOrders($this->_getDespatchedOrders())->create();
            $log->done();

            $this->warnings = array_merge($this->warnings, $response->warnings);
            $this->messages = array_merge($this->messages, $response->messages);
        } catch(Exception $e) {
            $log->doneWithErrors($e->getMessage());

            $log->starting('releasing held lines....');
            //always release holded lines
            $this->minder->releaseHoldedLines($holdedPickItems, $holdedISSNs);
            $log->done();
            throw $e;
        }

        $log->starting('releasing held lines....');
        $this->minder->releaseHoldedLines($holdedPickItems, $holdedISSNs);
        $log->done();
    }

    protected function _getPackingSlipManager() {
        return new Minder_ConnoteProccess_PackingSlipManager();
    }

    protected function _getDespatchedOrders() {
        $sql = '
            SELECT DISTINCT
                PICK_ITEM_DETAIL.PICK_ORDER
            FROM
                PICK_ITEM_DETAIL
            WHERE
                PICK_ITEM_DETAIL.DESPATCH_ID = ?
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?
        ';

        return $this->minder->fetchAllAssoc($sql, $this->_getLastCreatedDespatchId(), 'DC');
    }

    protected function _getDespatchingOrders() {
        if (empty($this->_despatchingOrderCollection)) {
            $this->_despatchingOrderCollection = $this->_fetchDespatchingOrders();
        }

        return $this->_despatchingOrderCollection;
    }

    protected function _fetchDespatchingOrders() {
        $pickOrders = $this->orders;
        $result = new Minder_PickOrder_Collection();

        if (!empty($pickOrders)) {
            $sql = "SELECT * FROM PICK_ORDER WHERE PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ")";
            array_unshift($pickOrders, $sql);
            $orders = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $pickOrders);

            if ($orders === false) {
                throw new Exception(Minder::getInstance()->lastError);
            } else {
                $result->fromArray($orders);
            }
        }

        return $result;
    }

    protected function _checkDespatchPolicy() {
        Minder_ConnoteProccess_DespatchPolicy::factory($this->_getDespatchingOrders(), $this->lines)->check();
    }

    protected function _checkPartialOrderPolicy() {
        Minder_ConnoteProccess_PartialOrderPolicy::factory($this->_getDespatchingOrders(), $this->lines)->check();
    }
}
