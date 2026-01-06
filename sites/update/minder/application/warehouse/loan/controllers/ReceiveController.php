<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Warehouse
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 */

/**
 * @category  Minder
 * @package   Warehouse
 * @author    Strelnikov Evgeniy <strelnikov.evgeniy@binary-studio.com@binary-studio.com>
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Warehouse_ReceiveController extends Minder_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->view->notShowLimit = 1;
        $this->_initSession('printer_id');
        
    }

    public function purchaseAction()
    {
        $this->view->pageTitle = 'Purchase Order';

        $action = strtolower($this->_getParam('do_action'));
        switch ($action) {
            case 'cancel':
                $this->session->conditions = null;
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;
        }

        $this->_preProcessNavigation();

        $this->view->purchaseOrderHeaders = array(
            'PURCHASE_ORDER'        => 'Order #',
            'PO_LEGACY_CONSIGNMENT' => 'Consignment #',
            'PO_DUE_DATE'           => 'Due Date',
            'PERSON_ID'             => 'Supplier ID',
            'COMPANY_ID'            => 'Owner ID',
            'PO_LEGACY_MEMO'        => 'Legacy Order Notes',
        );

        if ($action == 'search') {
            $conditions = $this->_makeConditions(array(
                'PURCHASE_ORDER.PURCHASE_ORDER' => 'PURCHASE_ORDER',
                'PO_LEGACY_CONSIGNMENT'         => 'PO_LEGACY_CONSIGNMENT',
                'PROD_ID'                       => 'PROD_ID'));
        } else {

            if(isset($this->session->conditions[$this->_controller][$this->_action])) {
                $conditions = $this->session->conditions[$this->_controller][$this->_action];
            } else {
              $conditions = array();
            }
        }

        if($action == 'get_order') {
            $poNumber = $this->_getParam('po_number');
            $this->getOrder($poNumber);
        }

        if (empty($conditions['PURCHASE_ORDER'])) {
            unset($conditions['PURCHASE_ORDER']);
        }
        if (empty($conditions['PO_LEGACY_CONSIGNMENT'])) {
            unset($conditions['PO_LEGACY_CONSIGNMENT']);
        }
        if (empty($conditions['PROD_ID'])) {
            unset($conditions['PROD_ID']);
        }
        $result = $this->minder->getPurchaseOrders($conditions,
                                                   $this->session->navigation[$this->_controller][$this->_action]['pageselector'],
                                                   $this->session->navigation[$this->_controller][$this->_action]['show_by']
                                                   );
        $this->view->purchaseOrders = $result['data'];
        $this->_postProcessNavigation($result);


        //$this->_postProcessNavigation($this->view->purchaseOrders);

        $this->view->purchaseOrder = $this->_request->getPost('purchase_order');
        $this->view->purchaseOrderOpts = $this->minder->getPurchaseOrdersList();
        $this->view->poLegacyConsignment = $this->_request->getPost('po_legacy_consignment');
        $this->view->poLegacyConsignmentOpts = $this->minder->getPoLegacyConsignmentList();

        $this->view->prod_id = $this->_request->getPost('prod_id');
        $this->view->prod_idOpts = $this->minder->getProductList();

        /*
        $this->view->purchaseOrders = array_slice($this->view->purchaseOrders,
            $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
            $this->view->maxno);
        */
    }

    public function showPurchaseOrderAction()
    {
        $action = strtolower($this->_getParam('do_action'));
        switch ($action) {
            case 'cancel':
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;
        }
        $id = $this->_getParam('id');
        $this->view->purchaseOrderId = $id;
        $this->view->purchaseOrder = $this->minder->getPurchaseOrderById($id);
    }

    public function orderLinesAction()
    {
        $action = strtolower($this->_getParam('do_action'));
        switch ($action) {
            case 'cancel':
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;

            case 'previous':
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;
        }

        $this->_preProcessNavigation();

        $this->view->purchaseOrderId = $this->_getParam('id');
        if (!$this->view->purchaseOrderId) {
            $this->view->purchaseOrderId = $this->session->params[$this->_controller]['purchase_order'];
        }

        try {
            $po = $this->minder->getPurchaseOrderById($this->view->purchaseOrderId);
            $this->session->params[$this->_controller]['po_legacy_consignment'] = $po->poLegacyConsignment;
        } catch (Exception $e) {
            $this->session->params[$this->_controller]['po_legacy_consignment'] = null;
        }

        $this->session->params[$this->_controller]['purchase_order'] = $this->view->purchaseOrderId;
        $this->view->consignmentNo = $this->session->params[$this->_controller]['po_legacy_consignment'];

        $this->view->purchaseOrderLineHeaders = array(
            'PURCHASE_ORDER'       => 'Order #',
            'PO_LINE'              => 'Line',
            'PO_LINE_STATUS'       => 'Status',
            'PO_LINE_QTY'          => 'Qty Rqd',
            'RECVD'                => 'Recvd',
            'PROD_ID'              => 'Product',
            'PO_LINE_CUSTOMER_NAME'=> 'Customer',
            'PO_LINE_DESCRIPTION' => 'Description',
        );
        
        $this->session->purchaseOrderId = $this->view->purchaseOrderId;
        $this->view->purchaseOrderLines = $this->minder->getPurchaseOrderLinesByPurchaseOrder($this->view->purchaseOrderId, null, true);
        
        $redirectToOrderList = true;
        $countOpenLines      = count($this->view->purchaseOrderLines);  
        foreach($this->view->purchaseOrderLines as $line) {
        	if($line['PO_LINE_STATUS'] !== 'CL'){
        		$redirectToOrderList = false;
        	}
            if($line['PO_LINE_STATUS'] != 'OP') {
                $countOpenLines--;
            }
        }
        
        $this->session->params[$this->_controller]['countOpenLines'] = $countOpenLines;
        
        if($redirectToOrderList) {
        	$this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
        }

        $this->session->totalLinesQty   = 0;
        foreach($this->view->purchaseOrderLines as $line) {
            $this->session->totalLinesQty += $line['PO_LINE_QTY'];
        }
        $this->_postProcessNavigation($this->view->purchaseOrderLines);
        $this->view->purchaseOrderLines = array_slice($this->view->purchaseOrderLines,
            $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
            $this->view->maxno);
        
        if(!isset($this->session->params[$this->_controller]['jumpStep'])) {
            $this->session->params[$this->_controller]['jumpStep'] = false;
        }
        
        $currentOrder = $this->_getParam('id'); 
        if(!isset($this->session->params[$this->_controller]['poOrder'])) {
            $this->session->params[$this->_controller]['poOrder']             = $currentOrder;
            $this->session->params[$this->_controller]['countViewToJsJump']   = 0;     
        }
        
        if($this->session->params[$this->_controller]['poOrder'] != $currentOrder) {
            $this->session->params[$this->_controller]['jumpStep']            = false;
            $this->session->params[$this->_controller]['poOrder']             = $currentOrder;
            $this->session->params[$this->_controller]['countViewToJsJump']   = 0;    
        }
            
    }

    public function grnDetailsAction()
    {
        $action = strtolower($this->_getParam('do_action'));
        switch ($action) {
            case 'cancel':
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;

            case 'previous':
                $this->_redirect('warehouse/receive/order-lines/?fmt=12-inch');
                break;

            case 'next':
            case 'hire':
            case 'variety':
                $errors = 0;
                if ($tmp = $this->minder->getPurchaseOrderById($this->session->params[$this->_controller]['purchase_order'])) {
                    $this->session->params[$this->_controller]['owner_id'] = $tmp->companyId;
                    $this->session->params[$this->_controller]['sent_by'] = $tmp->personId;
                } else {
                    $this->session->params[$this->_controller]['owner_id'] = null;
                    $this->session->params[$this->_controller]['sent_by'] = null;
                }

                $carrier = $this->_request->getPost('carrier');
                $carrierOpts = $this->minder->getCarriersList();
                if (!array_key_exists($carrier, $carrierOpts)) {
                    $this->addError('Invalid value of field "Carrier".');
                    $errors = 1;
                }

                $shippedDate = trim($this->_request->getPost('shipped_date'));
                if (!empty($shippedDate)) {
                    preg_match('#^(\d{4}).(\d{2}).(\d{2})$#si', $shippedDate, $match);
                    if (!count($match)) {
                        $this->addError('Invalid format of field "Shipped Date".');
                        $errors = 1;
                    } else {
                        if (!checkdate($match[2], $match[3], $match[1])) {
                            $this->addError('Invalid format of field "Shipped Date".');
                            $errors = 1;
                        }
                    }
                }

                $vehicleId = trim($this->_request->getPost('vehicle_id'));
                /*
                 if (empty($vehicleId)) {
                    $this->addError('Value of field "Vehicle Reg" is empty, but a non-empty value is required.');
                    $errors = 1;
                }
                */

                $awbConsignmentNo = trim($this->_request->getPost('awb_consignment_no'));
                /*
                if (empty($awbConsignmentNo)) {
                    $this->addError('Value of field "Connote/AWB #" is empty, but a non-empty value is requered.');
                    $errors = 1;
                }
                */

                $containerNo = $this->session->params[$this->_controller]['container_no']
                             = $this->_request->getPost('container_no');
                $receiptFlag = $this->_request->getPost('receipt_flag');

                if (!$errors && !$this->session->params[$this->_controller]['jumpStep']) {
                
                    $this->session->params[$this->_controller]['jumpStep'] = true;
                    
                    $grndb = new Transaction_GRNDB;
                    $grndb->carrierId = $carrier;
                    $grndb->shipDate = $shippedDate;
                    $grndb->vehicleRegistration = $vehicleId;
                    $grndb->orderNo = $this->session->params[$this->_controller]['purchase_order'];
                    $grndb->orderLineNo = $this->session->params[$this->_controller]['po_line'];
                    $grndb->conNoteNo = $awbConsignmentNo;
                    if ($containerNo) {
                        $grndb->hasContainer = 'Y';
                    }
//                  $this->conNoteQty          = '1'; ???
                    $grndb->deliveryTypeId = 'PO';

                    // This need for valid work of GRNDB transaction.
                    $grndb->palletQty = $grndb->crateQty = 0;

                    $packCrateQty = intval($this->_request->getPost('pack_crate_qty'));
                    $grndb->crateQty = $packCrateQty < 0 ? 0 : $packCrateQty;

                    if ($packCrateType = $this->_request->getPost('pack_crate_type')) {
                        $grndb->crateTypeId = $packCrateType;
                    }

                    if ($palletsYn = $this->_request->getPost('pallets_yn')) {
                        $grndb->palletOwnerId = $palletsYn;
                    }

                    if ($packCrateOwner = $this->_request->getPost('pack_crate_owner')) {
                        $grndb->crateOwnerId = $packCrateOwner;
                    }

                    $grnPalletQty = intval($this->_request->getPost('grn_pallet_qty'));
                    $grndb->palletQty = $grnPalletQty < 0 ? 0 : $grnPalletQty;

                    // @TODO: Add Invalid or closed order no??

                    // Save $_POST data in the session. If user want go to the "Next" or "Previous" page,
                    // we must retrieve data from session.
                    $this->session->params[$this->_controller][$this->_action] = array(
                        'carrier' => $carrier,
                        'shipped_date' => $shippedDate,
                        'vehicle_id' => $vehicleId,
                        'container_no' => $containerNo,
                        'awb_consignment_no' => $awbConsignmentNo,
                        'receipt_flag' => $receiptFlag,
                        'pallets_yn' => $palletsYn,
                        'grn_pallet_qty' => $grnPalletQty,
                        'pack_crate_owner' => $packCrateOwner,
                        'pack_crate_qty' => $packCrateQty,
                        'pack_crate_type' => $packCrateType,
                    );

                    if (false === ($grndbResult = $this->minder->doTransactionResponse($grndb))) {
                        // Debug.
                        /*
                        $this->addError('<div style="border: solid 2px #000; padding: 1em;">Debug information: <pre>'
                            . "Internal message: " . print_r($this->minder->lastError, 1)
                            . "\nTransaction:\n" . print_r($grndb, 1) . '</pre></div>');
                         */
                        $this->addError('Transaction GRNDB failed (' . $this->minder->lastError . ').');
                        $this->_redirect('warehouse/receive/grn-details/?fmt=12-inch');
                    } else {
                        $grndbResult = preg_split('#:|\|#si', $grndbResult);
                        $this->session->params[$this->_controller]['grndb'] = $grndbResult;
                        $this->session->params[$this->_controller]['grn'] = $grndbResult[1];
                        $this->addMessage('Transaction GRNDB successfully.');
                    }

                    unset($this->session->params[$this->_controller]['run_grndl']);

                    $this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                } else {
                    if ($this->session->params[$this->_controller]['jumpStep'])
                        $this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                } 
                
                break;
        }

        // Set default values.
        if (!isset($palletsYn)) {
            $palletsYn = 'C';
        }
        if (!isset($packCrateOwner)) {
            $packCrateOwner = 'C';
        }

        if (isset($this->session->params[$this->_controller][$this->_action])) {
            $_POST = array_unique(array_merge($this->session->params[$this->_controller][$this->_action], $_POST));
            unset($this->session->params[$this->_controller][$this->_action]);
        }
        $this->view->poLine = $this->_getParam('po_line');
        if (!$this->view->poLine) {
            $this->view->poLine = $this->session->params[$this->_controller]['po_line'];
        }
        $this->session->params[$this->_controller]['po_line'] = $this->view->poLine;
        
        $this->view->infProdInfo = '';
        $this->view->infReqQty = $this->view->infUom = 0;
        // Save information about PO line in session and view.
        if ($poLine = $this->minder->getPoLine($this->session->params[$this->_controller]['po_line'], $this->session->params[$this->_controller]['purchase_order'])) {
            $this->view->infUomOrder = $this->session->params[$this->_controller]['inf_uom'] = $poLine['UOM_ORDER'];
            //$poLine['PO_LINE_DESCRIPTION'];
            $this->view->infProdInfo = $this->session->params[$this->_controller]['inf_prod_info'] = $poLine['PROD_ID'] . ' ' . current($this->minder->getProductList($poLine['PROD_ID']));
            $this->view->infReqQty = $this->session->params[$this->_controller]['inf_req_qty'] = (empty($poLine['PO_LINE_QTY'])) ? 0 : $poLine['PO_LINE_QTY'];
            $this->session->params[$this->_controller]['PROD_ID'] = $poLine['PROD_ID'];
        
        } else {
            $this->session->params[$this->_controller]['inf_uom']
                = $this->session->params[$this->_controller]['inf_prod_info']
                = $this->session->params[$this->_controller]['inf_req_qty']
                = null;
        }
        
        $this->view->purchaseOrderId = $this->session->params[$this->_controller]['purchase_order'];
        $this->view->consignmentNo = $this->session->params[$this->_controller]['po_legacy_consignment'];
        $this->view->carrier = isset($carrier) ? $carrier : $this->_request->getPost('carrier');
        $this->view->carrierOpts = isset($carrierOpts) ? $carrierOpts : $this->minder->getCarriersList();
        $this->view->shippedDate = isset($shippedDate) ? $shippedDate : $this->_request->getPost('shipped_date');
        $this->view->vehicleId = isset($vehicleId) ? $vehicleId : $this->_request->getPost('vehicle_id');
        $this->view->containerNo = isset($containerNo) ? $containerNo : $this->_request->getPost('container_no');
        $this->view->awbConsignmentNo = isset($awbConsignmentNo) ? $awbConsignmentNo : $this->_request->getPost('awb_consignment_no');
        $this->view->receiptFlag = isset($receiptFlag) ? $receiptFlag : $this->_request->getPost('receipt_flag');

        // Hire.
        $this->view->palletsYn = isset($palletsYn) ? $palletsYn : $this->_request->getPost('pallets_yn');
        $this->view->palletsYnOpts = $this->minder->getPalletOwnerList();
        $this->view->grnPalletQty = isset($grnPalletQty) ? $grnPalletQty : intval($this->_request->getPost('grn_pallet_qty'));
        $this->view->packCrateOwner = isset($packCrateOwner) ? $packCrateOwner : $this->_request->getPost('pack_crate_owner');
        $this->view->packCrateOwnerOpts = $this->minder->getPackagingOwnerList();
        $this->view->packCrateQty = isset($packCrateQty) ? $packCrateQty : intval($this->_request->getPost('pack_crate_qty'));
        $this->view->packCrateType = isset($packCrateType) ? $packCrateType : $this->_request->getPost('pack_crate_type');
        $this->view->packCrateTypeOpts = $this->minder->getPackagingTypeList();
    }

    public function varietyAction()
    {
        try{
        if (!isset($this->session->params[$this->_controller]['grndb'])) {
            $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
        }

        if(isset($this->session->printer_id)) {
            $printerId = $this->session->printer_id;
        }

        $log = Zend_Registry::get('logger');
        $action = strtolower($this->_getParam('do_action'));
        
        switch ($action) {
            case 'cancel':
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;

            case 'previous':
                $this->_redirect('warehouse/receive/grn-details/?fmt=12-inch');
                break;

            case 'next':
            case 'print labels':
            case 'save & print':
                
                if(!isset($this->session->printer_id)) {
                    $printerId = $this->_request->getPost('printer_id');
                    $this->session->printer_id = $printerId;
                    $this->_saveEnvSession('printer_id');
                } elseif($this->session->printer_id != $this->_request->getPost('printer_id')) {
                    $printerId = $this->_request->getPost('printer_id');
                    $this->session->printer_id = $printerId;
                    $this->_saveEnvSession('printer_id');
                } else {
                    $printerId = $this->_request->getPost('printer_id');
                }
                $recvd = $this->_request->getPost('recvd');
                // Send GRNDL transaction only once (when we came from grn-details page).
                if (!isset($this->session->params[$this->_controller]['run_grndl'])) {
                    $grndl = new Transaction_GRNDL;
                    $grndl->grnNo = $this->session->params[$this->_controller]['grn'];
                    if ($this->session->params[$this->_controller]['owner_id']) {
                        $grndl->ownerId = $this->session->params[$this->_controller]['owner_id'];
                    }
                    if ($this->session->params[$this->_controller]['sent_by']) {
                        $grndl->supplierId = $this->session->params[$this->_controller]['sent_by'];
                    }
                    $grndl->deliveryTypeId = 'PO';
                    $grndl->orderNo = $this->session->params[$this->_controller]['purchase_order'];
                    $grndl->containerNo = $this->session->params[$this->_controller]['container_no'];
    //                $grndl->containerTypeId = $containerNo;
                    if ($printerId) {
                        $grndl->printerId = $printerId;
                    }
                    if ($recvd) {
                        $grndl->grnLabelQty = $recvd;
                    }
                    if (false === ($grndlResult = $this->minder->doTransactionResponse($grndl))) {
                        $this->addError('Transaction GRNDL failed (' . $this->minder->lastError . ').');
                    } else {
                        $this->addMessage('Transaction GRNDL successfully.');
                    }
                    $this->session->params[$this->_controller]['run_grndl'] = 1;
                }
                // Send GRNVP transaction.
                $grnvp = new Transaction_GRNVP;
                $grnvp->deliveryTypeId = 'PO';
                $grnvp->orderNo        = $this->session->params[$this->_controller]['purchase_order'];
                $grnvp->orderLineNo    = $this->session->params[$this->_controller]['po_line'];
                $grnvp->grnNo          = $this->session->params[$this->_controller]['grn'];
                if ($printerId) {
                    $grnvp->printerId = $printerId;
                }
                if ($qty1 = intval($this->_request->getPost('qty1'))) {
                    $grnvp->qtyOfLabels1 = $qty1 < 0 ? 0 : $qty1;
                } else {
                    $grnvp->qtyOfLabels1 = 0;
                }
                if ($qty2 = intval($this->_request->getPost('qty2'))) {
                    $grnvp->qtyOnLabels1 = $qty2 < 0 ? 0 : $qty2;
                } else {
                    $grnvp->qtyOnLabels1 = 0;
                }
                if ($qty3 = intval($this->_request->getPost('qty3'))) {
                    $grnvp->qtyOfLabels2 = $qty3 < 0 ? 0 : $qty3;
                } else {
                    $grnvp->qtyOfLabels2 = 0;
                }
                if ($qty4 = intval($this->_request->getPost('qty4'))) {
                    $grnvp->qtyOnLabels2 = $qty4 < 0 ? 0 : $qty4;
                } else {
                    $grnvp->qtyOnLabels2 = 0;
                }

                if ($prtQty = intval($this->_request->getPost('prt_qty'))) {
                    $grnvp->totalVerified = $prtQty < 0 ? 0 : $prtQty;
                } else {
                    $grnvp->totalVerified = 0;
                }

                if ($line = $this->minder->getPoLine($grnvp->orderLineNo, $grnvp->orderNo)) {
                    $grnvp->productId = $line['PROD_ID'];
                }

                if ($receiveLocation = $this->_request->getPost('receive_location')) {
                    $grnvp->locationId = $receiveLocation;
                }
                if (false === ($grnvpResult = $this->minder->doTransactionResponse($grnvp))) {
                    // Debug.
                    /*
                    $this->addError('<div style="border: solid 2px #000; padding: 1em;">Debug information: <pre>'
                        . "Internal message: " . print_r($this->minder->lastError, 1)
                        . "\nTransaction:\n" . print_r($grnvp, 1) . '</pre></div>');
                    */
                    $this->addError('Transaction GRNVP failed (' . $this->minder->lastError . ').');
                    $this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                } else {
                    $this->addMessage('Transaction GRNVP successfully.');
                    $grnvpResult = preg_split('#:|\|#si', $grnvpResult);
                    
                    // when generate label text = 'T'
                    // then size of the array will be 1
                    // so dont have the ssn that was created
                    //
                    // Send NIOBA and NIBCA transactions.
                    //
                    $grnNo = $this->session->params[$this->_controller]['grn'];
                    //if (false === ($ssnOriginal = $this->minder->getOriginalSsn($grnvpResult[0]))) {
                    if (false === ($ssnOriginal = count($grnvpResult) > 1 ? $this->minder->getOriginalSsn($grnvpResult[0]) : $this->minder->getLastSsnforGrn($grnNo))) { 
                        $this->addError('No ORIGINAL_SSN found for ' . $this->view->escape($grnvpResult[0]) . ' Grn ' . $grnNo . '. Can\'t continue.');
                        $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                    } else {
                        if ($generic = $this->_request->getPost('generic')) {
                            if ($generic = trim($generic) != '') {
                                $nioba = new Transaction_NIOBA;
                                $nioba->ssnId = $ssnOriginal;
                                $nioba->genericValue = $generic;
                                $this->minder->doTransactionResponse($nioba);
                                /*
                                if (false === ($niobaResult = $this->minder->doTransactionResponse($nioba))) {
                                     // Debug.
                                    $this->addError('<div style="border: solid 2px #000; padding: 1em;">Debug information: <pre>'
                                        . "Internal message: " . print_r($this->minder->lastError, 1)
                                        . "\nTransaction:\n" . print_r($nioba, 1) . '</pre></div>');
                                    $this->addError('Transaction NIOBA failed (' . $this->minder->lastError . ').');
                                } else {
                                    $this->addMessage('Transaction NIOBA successfully.');
                                }
                                */
                            }
                        }

                        if ($brand = $this->_request->getPost('brand')) {
                            if ($brand = trim($brand) != '') {
                                $nibca = new Transaction_NIBCA;
                                $nibca->ssnId = $ssnOriginal;
                                $nibca->brandCodeValue = $brand;
                                $this->minder->doTransactionResponse($nibca);
                                /*
                                if (false === ($nibcaResult = $this->minder->doTransactionResponse($nibca))) {
                                     // Debug.
                                    $this->addError('<div style="border: solid 2px #000; padding: 1em;">Debug information: <pre>'
                                        . "Internal message: " . print_r($this->minder->lastError, 1)
                                        . "\nTransaction:\n" . print_r($nibca, 1) . '</pre></div>');
                                    $this->addError('Transaction NIBCA failed (' . $this->minder->lastError . ').');
                                } else {
                                    $this->addMessage('Transaction NIBCA successfully.');
                                }*/
                            }
                        }

                        $thirdParty      = $this->_request->getPost('third_party');

                        // Save $_POST data in the session. If user want go to the "Next" or "Previous" page,
                        // we must retrieve data from session.
                        $this->session->params[$this->_controller][$this->_action] = array(
                            'generic'          => $generic,
                            'brand'            => $brand,
                            'recvd'            => $recvd,
                            'printer_id'       => $printerId,
                            'qty1'             => $qty1,
                            'qty2'             => $qty2,
                            'qty3'             => $qty3,
                            'qty4'             => $qty4,
                            'prt_qty'          => $prtQty,
                            'receive_location' => $receiveLocation,
                            'third_party'      => $thirdParty,
                        );
                        
                        // Print labels.
                        $line = new IssnLine();
                        $line->ssnId      = $grnvpResult[0];
                        $line->whId       = substr($receiveLocation, 2);
                        $line->currentQty = count($grnvpResult) > 1 ? $grnvpResult[2] : 0; 
                        $line->locnId     = $grnvp->locationId;
                        $line->prodId     = $grnvp->productId;
                        $line->pickOrder  = $this->session->params[$this->_controller]['purchase_order'];
                        $line->userId     = $this->minder->userId;
                      
                        if ($thirdParty) {
                            // Frank Leih 25.08.2010 when generate label text is T no 3rd party
                            if (count($grnvpResult) > 1) {
                                $this->session->params[$this->_controller]['complete'] = $this->_request->getPost('complete');
                                $this->session->params[$this->_controller]['grnvp'] = $grnvpResult;
                                // Save ISSN in the session for future processing in thirdPartyAction() method.
                                $this->session->params[$this->_controller]['issn'] = $line;
                                $this->_redirect('warehouse/receive/third-party/?fmt=12-inch');
                            } else {
                                $dirtyOrder = $this->session->params[$this->_controller]['purchase_order'];
                                // Is Delivery Complete?
                                if (($complete = $this->_request->getPost('complete')) && strtolower($complete) == 'n') {
                                    $this->session->params[$this->_controller]['countViewToJsJump']++;
                                    $this->_redirect('warehouse/receive/order-lines/id/' . $dirtyOrder . '?fmt=12-inch');
                                    //$this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                                } else {
                                    // Frank Leih 25.08.2010 when generate label text is T no 3rd party
                                    //$this->_redirect('warehouse/receive/order-lines/id/' . $dirtyOrder . '?fmt=12-inch');
                                    // if have netsuite
    				    // get items for this device
                                    $items = $this->minder->getPurchaseLineDetailId('RC', $this->minder->deviceId);
                                    // set po line details status to go to netsuite
     				    if (!$this->minder->updatePurchaseLineDetailStatus($items,'DL')) {
            				$log->info('failed to update po_line_details to DL ');
    				    }
                                    // release this po for others to use 
            			    $log->info('about to release po for others ');
            			    $log->info($dirtyOrder);
                                    $clause = array('PURCHASE_ORDER = ? ' => $dirtyOrder);
                                    if (!$this->minder->updatePurchaseOrderField($clause, 'PO_RECEIVER', '')) {
            			        $log->info('failed to release po user ');
                                        //$this->addError($this->minder->lastError);
                                    }
            		            $log->info('after release po to others ');
                                    // go to the next line
                                    $this->_redirect('/warehouse/receive/order-lines/id/' . $this->session->purchaseOrderId . '/?fmt=12-inch');
                                }
                            }
                        } else {
                            // Frank Leih 25.08.2010 when generate label text is T no print 
                            if (count($grnvpResult) > 1) {
                                // Frank Leih 25.08.2010 when generate label text is F so print 
                                // generate label text = F so print 
                                //-- [Sergey Boroday] 09.06.2008 fix error
                                //-- Error description: only one ISSN label was printed
                                $lines = array();
    
                                $product                        = $this->minder->getProductList($grnvp->productId);
    
                                $numberISSNs                    = (int)$grnvpResult[1];
                                $line                           = new IssnLine();
                                $line->items['SSN_ID']          = $grnvpResult[0];
                                $line->items['WH_ID']           = substr($receiveLocation, 2);
                                $line->items['CURRENT_QTY']     = $grnvpResult[2];
                                $line->items['LOCN_ID']         = $grnvp->locationId;
                                $line->items['PROD_ID']         = $grnvp->productId;
                                $line->items['SSN_DESCRIPTION'] = $product[$grnvp->productId];
                                $line->items['PICK_ORDER']      = $this->session->params[$this->_controller]['purchase_order'];
                                $line->items['USER_ID']         = $this->minder->userId;
    
                                // prepare list of ISSNs
                                for ($i = 0; $i < $numberISSNs; $i++) {
                                    $lines[] = $line;
                                    $line = clone($line);
                                    $nextISSN = substr($line->items['SSN_ID'], -2);
                                    $line->items['SSN_ID'] = substr($line->items['SSN_ID'], 0, -2) . str_pad(++$nextISSN, 2, '0', STR_PAD_LEFT);
                                }
    
                                $numberISSNs = (int)$grnvpResult[4];
                                $line                       = new IssnLine();
                                $line->items['SSN_ID']      = $grnvpResult[3];
                                $line->items['WH_ID']       = substr($receiveLocation, 2);
                                $line->items['SSN_DESCRIPTION'] = $product[$grnvp->productId];
                                $line->items['CURRENT_QTY'] = $grnvpResult[5];
                            
                                // prepare list of ISSNs
                                for ($i = 0; $i < $numberISSNs; $i++) {
                                    $lines[] = $line;
                                    $line = clone($line);
                                    $nextISSN = substr($line->items['SSN_ID'], -2);
                                    $line->items['SSN_ID'] = substr($line->items['SSN_ID'], 0, -2) . str_pad(++$nextISSN, 2, '0', STR_PAD_LEFT);
                                }
                                
                                $this->_printLine($printerId, $lines);
                                //-- [Sergey Boroday] end of fix
                                
                                //$this->sendItemReceipt($grnvpResult[0]);
                                $this->saveItemReceipt($grnvpResult[0], $grnvpResult[5]);
                            }
                            
                            $dirtyOrder = $this->session->params[$this->_controller]['purchase_order'];
                            // Is Delivery Complete?
                            if (($complete = $this->_request->getPost('complete')) && strtolower($complete) == 'n') {
                                $this->session->params[$this->_controller]['countViewToJsJump']++;
                                $this->_redirect('warehouse/receive/order-lines/id/' . $dirtyOrder . '?fmt=12-inch');
                                //$this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                            } else {
                                //$this->_redirect('warehouse/receive/order-lines/id/' . $dirtyOrder . '?fmt=12-inch');
				// get items for this device
				        
                                $items = $this->minder->getPurchaseLineDetailId('RC', $this->minder->deviceId);
                                // set po line details status to go to netsuite
				if (!$this->minder->updatePurchaseLineDetailStatus($items,'DL')) {
        				$log->info('failed to update po_line_details to DL ');
				}
                                // release this po for others to use 
        			$log->info('about to release po for others ');
        			$log->info($dirtyOrder);
                                $clause = array('PURCHASE_ORDER = ? ' => $dirtyOrder);
                                if (!$this->minder->updatePurchaseOrderField($clause, 'PO_RECEIVER', '')) {
        			        $log->info('failed to release po user ');
                                    	//$this->addError($this->minder->lastError);
                                }
        			$log->info('after release po to others ');
                                // go to the next line
                                $this->_redirect('/warehouse/receive/order-lines/id/' . $this->session->purchaseOrderId . '/?fmt=12-inch');
                            }
                        }
                    }
                }
                 
                break;
        }
        
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        if (isset($this->session->params[$this->_controller][$this->_action])) {
            $_POST = array_unique(array_merge($this->session->params[$this->_controller][$this->_action], $_POST));
            unset($this->session->params[$this->_controller][$this->_action]);
        }
        
        $prodId             = $this->session->params[$this->_controller]['PROD_ID'];
        $ssnType            = $this->minder->getSsnTypeLiestFromProdProfileForReceive($prodId);
        
        
        $this->view->poLine = $this->session->params[$this->_controller]['po_line'];
        $this->view->purchaseOrderId = $this->session->params[$this->_controller]['purchase_order'];
        $this->view->consignmentNo = $this->session->params[$this->_controller]['po_legacy_consignment'];
        $this->view->infUomOrder = $this->session->params[$this->_controller]['inf_uom'];
        $this->view->infProdInfo = $this->session->params[$this->_controller]['inf_prod_info'];
        $this->view->infReqQty = $this->session->params[$this->_controller]['inf_req_qty'];
        $this->view->generic = isset($generic) ? $generic : $this->_request->getPost('generic');
        $tmpVarieList = $this->minder->getVarietyList($ssnType);
        $tmpVarieList = is_array($tmpVarieList) ? $tmpVarieList : array();
        $this->view->genericOpts = minder_array_merge(array(' ' => ' '), $tmpVarieList);
        $this->view->brand = isset($brand) ? $brand : $this->_request->getPost('brand');
        $this->view->brandOpts = minder_array_merge(array('' => ''), $this->minder->getBrandList());
        $this->view->model = isset($model) ? $model : $this->_request->getPost('model');
        $this->view->modelOpts = null;
        $this->view->qty1 = isset($qty1) ? $qty1 : intval($this->_request->getPost('qty1'));
        $this->view->qty2 = isset($qty2) ? $qty2 : intval($this->_request->getPost('qty2'));
        $this->view->qty3 = isset($qty3) ? $qty3 : intval($this->_request->getPost('qty3'));
        $this->view->qty4 = isset($qty4) ? $qty4 : intval($this->_request->getPost('qty4'));
        $this->view->prtQty = isset($prtQty) ? $prtQty : intval($this->_request->getPost('prt_qty'));
        $this->view->recvd = isset($recvd) ? $recvd : 0;
        $this->view->thirdParty = isset($thirdParty) ? $thirdParty : $this->_request->getPost('third_party');
        $this->view->printerId = isset($printerId) ? $printerId : $this->_request->getPost('printer_id');
        $this->view->printerIdOpts = $this->minder->getPrinterList();
        $this->view->receiveLocation = isset($receiveLocation) ? $receiveLocation : $this->_request->getPost('receive_location');
        $this->view->receiveLocationOpts = $this->minder->getWhReceiveLocationList();
        $this->view->complete = isset($complete) ? $complete : $this->_request->getPost('complete');
        
        $this->view->totalLinesQty  = $this->session->totalLinesQty;
        $this->view->countOpenLines = $this->session->params[$this->_controller]['countOpenLines'];
        
        $this->view->isJsSump = $this->session->params[$this->_controller]['countViewToJsJump'];
        
    }

    public function thirdPartyAction()
    {
        $log = Zend_Registry::get('logger');
        if (!isset($this->session->params[$this->_controller]['grnvp']) || !isset($this->session->params[$this->_controller]['issn'])) {
            $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
        }

        list($issn1, $qty1, $qty2, $issn2, $qty3, $qty4, $printerId) = $this->session->params[$this->_controller]['grnvp'];

        if ($tmp = $this->_request->getPost('issn1')) {
            $issn1 = $tmp;
        }
        if ($tmp = $this->_request->getPost('issn2')) {
            $issn2 = $tmp;
        }
        $rQty1 = intval($this->_request->getPost('r_qty1'));
        $rQty2 = intval($this->_request->getPost('r_qty2'));

        $action = strtolower($this->_getParam('do_action'));
        switch ($action) {
            case 'save & print':
                // Process 1st result set.
                if ($qty1 > $rQty1) {
                    $rQty1++;
                    $ui01a = new Transaction_UIO1A;
                    if ($thirdNo = $this->_request->getPost('third_no')) {
                        $ui01a->other1Value = $thirdNo;
                    }
                    $ui01a->objectId = $issn1;
                    if (false === ($ui01aResult = $this->minder->doTransactionResponse($ui01a))) {
                        // Debug.
                        /*
                        $this->addError('<div style="border: solid 2px #000; padding: 1em;">Debug information: <pre>'
                            . "Internal message: " . print_r($this->minder->lastError, 1)
                            . "\nTransaction:\n" . print_r($ui01a, 1) . '</pre></div>');
                        */
                        $this->addError('Transaction UI01A failed (' . $this->minder->lastError . ').');
                    } else {
                        $this->addMessage('Transaction UI01A successfully.');
                    }

                    $line = $this->session->params[$this->_controller]['issn'];
                    $line->ssnId = $issn1;
                    $line->currentQty = $qty2;
                    if ($tmp = $this->_request->getPost('printer_id')) {
                       $printerId = $tmp;
                    }
                   
                    $this->_printLine($printerId, $line);

                    if (preg_match('#^0+#si', $issn1, $m)) {
                        $issn1++;
                        $issn1 = $m[0] . strval($issn1);
                    } else {
                        $issn1++;
                    }
                // Process 2nd result set.
                } elseif ($qty3 > $rQty2) {
                    $rQty2++;
                    $ui01a = new Transaction_UIO1A;
                    if ($thirdNo = $this->_request->getPost('third_no')) {
                        $ui01a->other1Value = $thirdNo;
                    }
                    $ui01a->objectId = $issn1;
                    if (false === ($ui01aResult = $this->minder->doTransactionResponse($ui01a))) {
                        // Debug.
                        /*
                        $this->addError('<div style="border: solid 2px #000; padding: 1em;">Debug information: <pre>'
                            . "Internal message: " . print_r($this->minder->lastError, 1)
                            . "\nTransaction:\n" . print_r($ui01a, 1) . '</pre></div>');
                         */
                        $this->addError('Transaction UI01A failed (' . $this->minder->lastError . ').');
                    } else {
                        $this->addMessage('Transaction UI01A successfully.');
                    }

                    $line = $this->session->params[$this->_controller]['issn'];
                    $line->ssnId = $issn2;
                    $line->currentQty = $qty4;
                    if ($tmp = $this->_request->getPost('printer_id')) {
                       $printerId = $tmp;
                    }

                    $this->_printLine($printerId, $line);

                    if (preg_match('#^0+#si', $issn2, $m)) {
                        $issn2++;
                        $issn2 = $m[0] . strval($issn2);
                    } else {
                        $issn2++;
                    }
                }
                $dirtyOrder = $this->session->params[$this->_controller]['purchase_order'];

                //$this->sendItemReceipt($issn1);
                $this->saveItemReceipt($issn1, $qty2);

                if ($rQty1 + $rQty2 >= $qty1 + $qty3) {
                    if (isset($this->session->params[$this->_controller]['complete']) && strtolower($this->session->params[$this->_controller]['complete']) == 'n') {
                        $this->_redirect('warehouse/receive/order-lines/id/' . $dirtyOrder . '?fmt=12-inch');
                        //$this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                    } else {
			// get items for this device
			$items = $this->minder->getPurchaseLineDetailId('RC', $this->minder->deviceId);
                       // set po line details status to go to netsuite
			if (!$this->minder->updatePurchaseLineDetailStatus($items, 'DL')) {
        			$log->info('failed to update po_line_details to DL ');
			}
                        $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                    }
                }
                break;

            case 'cancel':
// $grnxg = new Transaction_GRNXG($subLocation = '', $userId = '', $qty = 0)
                $this->_redirect('warehouse/receive/purchase/?fmt=12-inch');
                break;

            case 'previous':
                // GRNXG.

                $this->_redirect('warehouse/receive/variety/?fmt=12-inch');
                break;
        }

        $this->view->poLine = $this->session->params[$this->_controller]['po_line'];
        $this->view->purchaseOrderId = $this->session->params[$this->_controller]['purchase_order'];
        $this->view->consignmentNo = $this->session->params[$this->_controller]['po_legacy_consignment'];
        $this->view->infUomOrder = $this->session->params[$this->_controller]['inf_uom'];
        $this->view->infProdInfo = $this->session->params[$this->_controller]['inf_prod_info'];
        $this->view->infReqQty = $this->session->params[$this->_controller]['inf_req_qty'];

        $this->view->printerId = $printerId;
        $this->view->printerIdOpts = $this->minder->getPrinterList();
        $this->view->rQty1 = $rQty1;
        $this->view->rQty2 = $rQty2;
        $this->view->recorded = $rQty1 + $rQty2;
        $this->view->totalLabels = intval($qty1 + $qty3);
        $this->view->issn1 = $issn1;
        $this->view->issn2 = $issn2;
    }

    protected function _printLine($printerId, $lines) {
        
            
        $printerObj = $this->minder->getPrinter(null, $printerId);
        $count      = 0;
        foreach($lines as $line){
        
            $issnData = $this->minder->getIssnForPrint($line->items['SSN_ID']);
            
            try{
                $result    =    $printerObj->printIssnLabel($issnData);
                if($result['RES'] < 0){
                    $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
                    return false;     
                }             
            } catch(Exception $ex){
                   $this->addError($ex->getMessage());
                   return false;    
            }
            $count++;    
        }
        
        if($result['RES'] >= 0){
            $this->addMessage($count . ' label(s) printed successfully');
        }
        
        return true;
    }


    protected function saveItemReceipt($issnId, $qty)
    {
        $log = Zend_Registry::get('logger');

        $SOAPorderNo       = $this->session->params[$this->_controller]['purchase_order'];
        $orderLineNo       = $this->session->params[$this->_controller]['po_line'];

	// want to save issnId SOAPorderNo and orderLineNo and device for later request
	// in purchase_line_detail
        /*
        $log->info('start Save Item using ' . PHP_EOL
                   . $SOAPorderNo . PHP_EOL
                   . $orderLineNo . PHP_EOL
                   . $issnId . PHP_EOL);
        */

/*
requires
        PURCHASE_ORDER PURCHASE_ORDER NOT NULL, = $SOAPorderNo
        PO_LINE PO_LINE NOT NULL, = $orderLineNo
        SSN_ID SSN_ID NOT NULL, = $issnId
        PURCHASE_DETAIL_STATUS STATUS, = 'RC'
        QTY_RECEIVED QTY, = $qty
        USER_ID USER_ID, = $this->minder->userId
        DEVICE_ID DEVICE_ID,  = $this->minder->deviceId
        CREATE_DATE CREATE_DATE,
        LAST_UPDATE_DATE LAST_UPDATE_DATE,
*/
	$line                       = new PurchaseLineDetail();
	$line->items['PURCHASE_DETAIL_ID']      = 0;
	$line->items['PURCHASE_ORDER']      = $SOAPorderNo;
	$line->items['PO_LINE']             = $orderLineNo;
	$line->items['SSN_ID']              = $issnId;
	$line->items['PURCHASE_DETAIL_STATUS'] = 'RC';
	$line->items['QTY_RECEIVED']        = $qty;
	$line->items['USER_ID']             = $this->minder->userId;
	$line->items['DEVICE_ID']           = $this->minder->deviceId;
	$line->items['CREATE_DATE']         = 'NOW';
	$line->items['LAST_UPDATE_DATE']    = 'NOW';
	if (!$this->minder->addPurchaseLineDetails($line)) {
        	$log->info('failed to add to po_line_details ');
	}

    }


    protected function _saveEnvSession($name)
    {
        if (!$this->session instanceof Zend_Session_Namespace) {
            throw new Exception('Session is not instance of class Zend_Session_Namespace');
        }

        switch ($name) {
            case 'printer_id':
                $value = array('printer_id' => $this->session->printer_id);
            break;
            default:
                $value = $this->session->getIterator();
            break;
        }
        return $this->minder->saveEnvSession($this->minder->userId,
               $name,
               serialize($value));
    }

    /**
     * @desc get order by PO Number
     * @param int $poNumber
     */
    private function getOrder($poNumber) {

        $poNumber = strtoupper($this->_getParam('po_number'));
        $this->view->response = array('status' => '',
                                      'message' => '',
                                      'data' => array()
                                     );
        // check if JavaScrip is off
        if(!isset($poNumber) || empty($poNumber)) {
            $this->view->response['status'] = false;
            $this->view->response['message'] = 'Please, enter PO number';

            $this->render('get-order');
            return;
        }

        $SoapPassport = Zend_Registry::get('SoapPassport');

        $soap = new NetSuite_SoapWrapper();
        $soap->setSilentMode(true);
        $parser = new NetSuite_Parser();
        $soap->Passport = $SoapPassport;
        $syn  = new NetSuite_Synchronizer($soap, $this->minder->userId, $this->minder->deviceId);
        $syn->setSilentMode(true);


        if ($soap->login()) {
            $this->session->netSuiteCookie = NetSuite_SoapWrapper::$cookie;
        } else {
            $this->view->response['status'] = false;
            $this->view->response['message'] = 'SOAP - Can\'t login';

            $this->render('get-order');
            return;
        }

        // check is SOAP requests not blocked
        if(false == $soap->lockSoapTransaction()) {
            $this->view->response['status'] = false;
            $this->view->response['message'] = 'SOAP queries locked, try later';

        } else {

            // make SOAP request to NetSuite
            if(false == ($xmlResp = $soap->searchTransactionByTranId($poNumber))) {
                $this->view->response['status'] = false;
                $this->view->response['message'] = 'Order not found';

            } else {

                        $obj = $parser->parseSearch($xmlResp);                               
                        if(count($obj) == 0) {
                            $this->view->response['status'] = false;
                            $this->view->response['message'] = 'Order not found ';
                        } else {

                                    if($syn->importData($obj, false) == 0) {
                                        $this->view->response['status'] = false;
                                        $this->view->response['message'] = 'Order not passed by a filter' . $syn->lastError;

                                    } else {
                                                $this->view->response['status'] = true;
                                                $this->view->response['message'] = '';
                                                $this->view->response['data']['url'] =  $this->view->url(array('module' => 'warehouse', 'controller' => 'receive', 'action' => 'order-lines', 'id' => $poNumber), null, true) . '/?fmt=12-inch';

                                    }
                        }

            }
            $soap->unlockSoapTransaction();
        }

            $this->render('get-order');
            return;
    }
    
    public function ajaxAction() {
    
        $action = $this->getRequest()->getParam('do_action');
        
        switch(strtoupper($action)) {
            case 'CHANGE STATUS':
                $status = $this->getRequest()->getParam('status');
                $order  = $this->getRequest()->getParam('po_order');
                
                $result = $this->minder->updatePurchaseOrderStatus($order, $status);
                $this->view->data = array('result' => $result);
         
                break;
            default:
        
        }
    
    }
}
