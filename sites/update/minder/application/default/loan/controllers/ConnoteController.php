<?php
class ConnoteController extends Minder_Controller_Action
{
    protected static $precision = 0;
    
    //set of UOMs which is used at Connote form to show data for User
    //and, untill individual UOM set will be avalable for each Carrier,
    //is used to store in PACK_ID
    protected static $connoteDT      = 'CM';
    protected static $connoteWT      = 'KG';
    protected static $connoteVT      = 'M3';
    protected static $connoteDTforVT = 'MT';
    
    //set of UOMS wich is used to convert data for DSOT S transaction
    protected static $dsotsWT        = 'KG';
    protected static $dsotsDTForVt   = 'CM';
    
    
    public function init() {
        parent::init();
        $this->view->pageTitle = 'CONNOTE';
        
//        if (!isset($this->session->connote['callback']))
//            $this->session->connote['callback'] = array();
    }
    
    public function indexAction() {
        try {
            
        $instanceId = uniqid('connote_instance', true);
        
        $orderSelectionNamespace      = $this->getRequest()->getParam('order_selection_namespace', 'default');
        $orderSelectionActionName     = $this->getRequest()->getParam('order_selection_action', 'index');
        $orderSelectionControllerName = $this->getRequest()->getParam('order_selection_controller', 'index');
        $orderSelectionFieldName      = $this->getRequest()->getParam('order_selection_field', 'default');

        $sessionInstance = array(
            'order_selection' => array(
                'namespace' =>       $orderSelectionNamespace,
                'action_name' =>     $orderSelectionActionName,
                'controller_name' => $orderSelectionControllerName,
                'field_name' =>      $orderSelectionFieldName
            ),
            
            'lines_selection' => array(
                'namespace' =>       $this->getRequest()->getParam('lines_selection_namespace', 'default'),
                'action_name' =>     $this->getRequest()->getParam('lines_selection_action', 'index'),
                'controller_name' => $this->getRequest()->getParam('lines_selection_controller', 'index'),
                'field_name' =>      $this->getRequest()->getParam('lines_selection_field', 'default')
            ),
            
            'callback' => $this->getRequest()->getParam('callback', array())
        );
        
        $this->session->connote[$instanceId] = $sessionInstance;
        $this->view->instanceId              = $instanceId;
        
        $rowSelector = $this->_helper->getHelper('RowSelector');
        $totalOrders = $rowSelector->getTotalCount($orderSelectionNamespace, $orderSelectionActionName, $orderSelectionControllerName);
        $tmpSelectedOrders = $rowSelector->getSelected(0, $totalOrders, true, $orderSelectionNamespace, $orderSelectionActionName, $orderSelectionControllerName);
        
        $selectedOrders = array();
        foreach ($tmpSelectedOrders as $order) {
            $selectedOrders[$order[$orderSelectionFieldName]] = $order[$orderSelectionFieldName];
        }
        
//        $linesSelectionNamespace      = $this->getRequest()->getParam('order_selection_namespace', 'default');
//        $linesSelectionActionName     = $this->getRequest()->getParam('order_selection_namespace', 'default');
//        $linesSelectionControllerName = $this->getRequest()->getParam('order_selection_namespace', 'default');
//        $linesSelectionModuleName     = $this->getRequest()->getParam('order_selection_namespace', 'default');


        if (reset($selectedOrders) !== false) {
            $orderNo    = current($selectedOrders);
            $orderData  = $this->minder->getPickOrder($orderNo, 'ALL');
            
            if (empty($orderData->shipVia)) {
                $orderData->shipVia = $this->minder->defaultControlValues['DEFAULT_CARRIER_ID '];
            }
        
            $this->session->connote['orderNo'] = $orderNo;
        
            $this->view->dTitle         = empty($orderData->dFirstName)               ? $orderData->pTitle        : $orderData->dTitle;
            $this->view->firstName      = empty($orderData->dFirstName)               ? $orderData->pFirstName    : $orderData->dFirstName;
            $this->view->lastName       = empty($orderData->dFirstName)               ? $orderData->pLastName     : $orderData->dLastName;
            $this->view->firstName      = empty($this->view->dTitle)                  ? $this->view->firstName    : $this->view->dTitle . ' ' . $this->view->firstName . ' ' . $this->view->lastName;
            $this->view->lastName       = empty($this->view->dTitle)                  ? $this->view->lastName     : '';
            $this->view->addresLine1    = empty($orderData->dFirstName)               ? $orderData->pAddressLine1 : $orderData->dAddressLine1;
            $this->view->addresLine2    = empty($orderData->dFirstName)               ? $orderData->pAddressLine2 : $orderData->dAddressLine2;
            $this->view->dCity          = empty($orderData->dFirstName)               ? $orderData->pCity         : $orderData->dCity;
            $this->view->dState         = empty($orderData->dFirstName)               ? $orderData->pState        : $orderData->dState; 
            $this->view->dPostCod       = empty($orderData->dFirstName)               ? $orderData->pPostCode     : $orderData->dPostCode;
            $this->view->dState         = empty($orderData->dFirstName)               ? $this->view->dState       : $this->view->dState;    
            $this->view->dCountry       = empty($orderData->dFirstName)               ? $orderData->pCountry      : $orderData->dCountry;
            $this->view->dCountry       = (strtoupper($this->view->dCountry) == 'AU') ? 'AUSTRALIA'               : $this->view->dCountry;
            $this->view->contactName    = $orderData->contactName;
            $this->view->dPhone         = empty($orderData->dFirstName)               ? $orderData->pPhone        : $orderData->dPhone;
            $this->view->customerPoWo   = $orderData->customerPoWo;
            $this->view->remarks1       = $orderData->remarks1;
            $this->view->remarks2       = $orderData->remarks2;
        
                                        
            $this->view->specialInstructions1   = $orderData->specialInstructions1;
            $this->view->specialInstructions2   = $orderData->specialInstructions2;
            $this->view->inclusionsOrder        = $orderData->supplierList;
            $this->view->partialPick            = $orderData->partialPickAllowed;
            $this->view->invoiceOrder           = $orderData->invWithGoods;
        
            $shipList                           = $this->minder->getTrnTypeShipViaList();
        
            $this->view->despatchType      = '';
            if(isset($shipList[$orderData->shipVia])) {
                $this->view->despatchType       = $shipList[$orderData->shipVia];
            }
        
            $this->view->shipServiceList        = $orderData->shipVia;
            if(!empty($orderData->shipVia)) {
                $carrierList                        = $this->minder->getCarrierByClause(array('CARRIER_ID = ? AND ' => $orderData->shipVia));
                if(isset($carrierList[0]) &&  $carrierList[0]['DEFAULT_CONNOTE_ISSO'] == 'T') {
                    $this->view->consignment            = $orderData->pickOrder;
                } else {
                    $this->view->consignment            = '';
                }
            } else {
                $this->view->consignment            = $orderData->pickOrder;
            }
            
            $carrierDescription                 = $this->minder->getCarrierByClause(array());
            if (!is_array($carrierDescription))
                $carrierDescription = array();
            foreach ($carrierDescription as &$carrier) {
                $carrier['SEVICE_LIST'] = $this->minder->getShipServiceList($carrier['CARRIER_ID']);
                if (!is_array($carrier['SEVICE_LIST']))
                    $carrier['SEVICE_LIST'] = array();
            }
            
            $this->view->carrierDescription     = $carrierDescription;
            
            $this->view->carrier                = $orderData->shipVia;
            $this->view->carrierList            = minder_array_merge(array('' => '') , $this->minder->getCarriersList());
            $this->view->payerList              = array('S'    => 'SENDER',
                                                        'R'  => 'RECEIVER');
            $this->view->printAddressList       = minder_array_merge(array('' => '') , $this->minder->getOptionsList('ADDRESSES'));        
        
        
            $this->view->defaultWeight          = $this->minder->defaultControlValues['DEFAULT_CONNOTE_WEIGHT'];
            $this->view->defaultPackType        = $this->minder->defaultControlValues['DEFAULT_CONNOTE_PACK'];
            $this->view->defaultPackQty         = $this->minder->defaultControlValues['DEFAULT_CONNOTE_PACK_QTY'];
        
            $this->view->payer                  = 'SENDER';
            $this->view->carrierServiceList     = $this->minder->getShipServiceList($this->view->carrier);
            $this->view->carrierService         = 'GEN';        
            $this->view->palletOwnerList        = minder_array_merge(array('NONE' => 'NONE'), $this->minder->getPalletOwnerList());
            $this->view->palletOwner            = 'NONE';
            $this->view->totalWeight            = $this->view->defaultWeight;
            $this->view->qtyAddressLabels       = $this->minder->defaultControlValues['DEFAULT_CONNOTE_QTY_LABELS'];
            
            switch ($this->view->defaultPackType) {
                case 'S':
                    $this->view->totalSatchels   = $this->view->defaultPackQty;
                    break;
                case 'C':
                    $this->view->totalCartons    = $this->view->defaultPackQty;
                    break;
                case 'P':
                    $this->view->palletQty       = $this->view->defaultPackQty;
                    break;
                default:
                    $this->view->defaultPackType = 'S';
                    $this->view->totalSatchels   = $this->view->defaultPackQty;
            }
        }
        
        } catch(Exception $e) {
        }

        $this->view->connoteWTCode      = self::$connoteWT;
        $this->view->connoteVTCode      = self::$connoteVT;
        $this->view->connoteDTForVTCode = self::$connoteDTforVT;
    }
    
    public function printAddressAction() {
        $addressType    =   $this->getRequest()->getParam('address_type');
        $labelName      =   $this->getRequest()->getParam('label_name');
                    
        $orderNo    =   $this->session->connote['orderNo'];
        $orderData  =   $this->minder->getPickOrder($orderNo, 'ALL');
                    
        $jsonResponse   =   array();
        $printData      =   array();
                    
        $printData      = $this->minder->getAddressLabelData($orderNo);

        switch(strtoupper($addressType)){
//now will pass all data from PICK_ORDER and PURCHASE_ORDER tables regardless address type
//            case 'MT':
//                $printData['PICK_ORDER.PERSON_ID']      =   $orderData->pPersonId;
//                $printData['PICK_ORDER.PERSON_TYPE']    =   $orderData->pPersonType;
//                $printData['PICK_ORDER.FIRST_NAME']     =   $orderData->pFirstName;
//                $printData['PICK_ORDER.LAST_NAME']      =   $orderData->pLastName;
//                $printData['PICK_ORDER.CITY']           =   $orderData->pCity;
//                $printData['PICK_ORDER.STATE']          =   $orderData->pState;
//                $printData['PICK_ORDER.POST_CODE']      =   $orderData->pPostCode;
//                $printData['PICK_ORDER.COUNTRY']        =   $orderData->pCountry;
//                $printData['PICK_ORDER.PHONE']          =   $orderData->pPhone;
//                $printData['PICK_ORDER.ADDRESS_LINE1']  =   $orderData->pAddressLine1;
//                $printData['PICK_ORDER.ADDRESS_LINE2']  =   $orderData->pAddressLine2;
//                $printData['PICK_ORDER.ADDRESS_LINE3']  =   $orderData->pAddressLine3;
//                $printData['PICK_ORDER.ADDRESS_LINE4']  =   $orderData->pAddressLine4;
//                $printData['PICK_ORDER.ADDRESS_LINE5']  =   $orderData->pAddressLine5;
//                $printData['PICK_ORDER.TITLE']          =   $orderData->pTitle;
//                break;

//except OF, when add prevoisly saved OFFICE_ADDRESS data
            case 'OF':
                $instanceId = $this->getRequest()->getParam('instance_id', null);
                if (is_null($instanceId)) {
                    $jsonResponse['result']  = false;
                    $jsonResponse['message'] = 'No Instance Id found for this Connote form.';    
                    echo json_encode($jsonResponse);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;
                }
            
                if (isset($this->session->connote[$instanceId]['OFFICE_ADDRESS'])) {
                    //if we already saved office address in session takes it from there
                    $orderData = $this->session->connote[$instanceId]['OFFICE_ADDRESS'];
                }            
            
                $printData['PICK_ORDER.S_PERSON_ID']      =   $orderData->sPersonId;
                $printData['PICK_ORDER.S_PERSON_TYPE']    =   $orderData->sPersonType;
                $printData['PICK_ORDER.S_FIRST_NAME']     =   $orderData->sFirstName;
                $printData['PICK_ORDER.S_LAST_NAME']      =   $orderData->sLastName;
                $printData['PICK_ORDER.S_CITY']           =   $orderData->sCity;
                $printData['PICK_ORDER.S_STATE']          =   $orderData->sState;
                $printData['PICK_ORDER.S_POST_CODE']      =   $orderData->sPostCode;
                $printData['PICK_ORDER.S_COUNTRY']        =   $orderData->sCountry;
                $printData['PICK_ORDER.S_PHONE']          =   $orderData->sPhone;
                $printData['PICK_ORDER.S_ADDRESS_LINE1']  =   $orderData->sAddressLine1;
                $printData['PICK_ORDER.S_ADDRESS_LINE2']  =   $orderData->sAddressLine2;
                $printData['PICK_ORDER.S_ADDRESS_LINE3']  =   $orderData->sAddressLine3;
                $printData['PICK_ORDER.S_ADDRESS_LINE4']  =   $orderData->sAddressLine4;
                $printData['PICK_ORDER.S_ADDRESS_LINE5']  =   $orderData->sAddressLine5;
                $printData['PICK_ORDER.S_TITLE']          =   $orderData->sTitle;
                break;
//            case 'DT':
//                $printData['PICK_ORDER.PERSON_ID']      =   $orderData->dPersonId;
//                $printData['PICK_ORDER.FIRST_NAME']     =   $orderData->dFirstName;
//                $printData['PICK_ORDER.LAST_NAME']      =   $orderData->dLastName;
//                $printData['PICK_ORDER.CITY']           =   $orderData->dCity;
//                $printData['PICK_ORDER.STATE']          =   $orderData->dState;
//                $printData['PICK_ORDER.POST_CODE']      =   $orderData->dPostCode;
//                $printData['PICK_ORDER.COUNTRY']        =   $orderData->dCountry;
//                $printData['PICK_ORDER.PHONE']          =   $orderData->dPhone;
//                $printData['PICK_ORDER.ADDRESS_LINE1']  =   $orderData->dAddressLine1;
//                $printData['PICK_ORDER.ADDRESS_LINE2']  =   $orderData->dAddressLine2;
//                $printData['PICK_ORDER.ADDRESS_LINE3']  =   $orderData->dAddressLine3;
//                $printData['PICK_ORDER.ADDRESS_LINE4']  =   $orderData->dAddressLine4;
//                $printData['PICK_ORDER.ADDRESS_LINE5']  =   $orderData->dAddressLine5;
//                $printData['PICK_ORDER.TITLE']          =   $orderData->dTitle;
//                break;
        }
        
        try {
            $printerObj =    $this->minder->getPrinter();
            $result     =    $printerObj->printAddressLabel($printData, strtoupper($labelName));
            
            if(intval($result['RES']) >= 0){
                 $jsonResponse['result']  =   true;
            } else {
                 $jsonResponse['result']  =   false;    
                 $jsonResponse['message'] =   'Error while print label(s): ' . $result['ERROR_TEXT'];    
            }
        }catch (Exception $e) {
            $jsonResponse['result']  =   false;    
            $jsonResponse['message'] =   $e->getMessage();    
        }
        
        echo json_encode($jsonResponse);
        $this->_helper->viewRenderer->setNoRender(true);
        return;        
    }
    
    public function loadAddressDataAction() {
        $fieldName  =   $this->getRequest()->getParam('field_name');
        $fieldType  =   explode('|', $fieldName);
        $fieldType  =   $fieldType[0];
        
        $orderNo    =   $this->session->connote['orderNo'];
        $orderData  =   $this->minder->getPickOrder($orderNo, 'ALL');
        
        $jsonData           =   array();
        $jsonData['type']   =   strtoupper($fieldType);   
        
        switch(strtoupper($fieldType)){
            case 'MT':
                $jsonData['PERSON_ID']      =   $orderData->pPersonId;
                $jsonData['PERSON_TYPE']    =   $orderData->pPersonType;
                $jsonData['FIRST_NAME']     =   $orderData->pFirstName;
                $jsonData['LAST_NAME']      =   $orderData->pLastName;
                $jsonData['CITY']           =   $orderData->pCity;
                $jsonData['STATE']          =   $orderData->pState;
                $jsonData['POST_CODE']      =   $orderData->pPostCode;
                $jsonData['COUNTRY']        =   $orderData->pCountry;
                $jsonData['PHONE']          =   $orderData->pPhone;
                $jsonData['ADDRESS_LINE1']  =   $orderData->pAddressLine1;
                $jsonData['ADDRESS_LINE2']  =   $orderData->pAddressLine2;
                $jsonData['ADDRESS_LINE3']  =   $orderData->pAddressLine3;
                $jsonData['ADDRESS_LINE4']  =   $orderData->pAddressLine4;
                $jsonData['ADDRESS_LINE5']  =   $orderData->pAddressLine5;
                $jsonData['TITLE']          =   $orderData->pTitle;
                $jsonData['CONTACT_NAME']   =   $orderData->contactName;
                $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo; 
                break;
            
            case 'OF':
                $instanceId = $this->getRequest()->getParam('instance_id', null);
                if (is_null($instanceId)) {
                    $jsonResponse['result'] =   false;
                    echo json_encode($jsonResponse);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;
                }
                
                if (isset($this->session->connote[$instanceId]['OFFICE_ADDRESS'])) {
                    //if we already saved office address in session takes it from there
                    $orderData = $this->session->connote[$instanceId]['OFFICE_ADDRESS'];
                } else {
                    //if this is firs time call - get data from DB and save it in session
                    $this->session->connote[$instanceId]['OFFICE_ADDRESS'] = $orderData;
                }


                $jsonData['PERSON_ID']      =   $orderData->sPersonId;
                $jsonData['PERSON_TYPE']    =   $orderData->sPersonType;
                $jsonData['FIRST_NAME']     =   $orderData->sFirstName;
                $jsonData['LAST_NAME']      =   $orderData->sLastName;
                $jsonData['CITY']           =   $orderData->sCity;
                $jsonData['STATE']          =   $orderData->sState;
                $jsonData['POST_CODE']      =   $orderData->sPostCode;
                $jsonData['COUNTRY']        =   $orderData->sCountry;
                $jsonData['PHONE']          =   $orderData->sPhone;
                $jsonData['ADDRESS_LINE1']  =   $orderData->sAddressLine1;
                $jsonData['ADDRESS_LINE2']  =   $orderData->sAddressLine2;
                $jsonData['ADDRESS_LINE3']  =   $orderData->sAddressLine3;
                $jsonData['ADDRESS_LINE4']  =   $orderData->sAddressLine4;
                $jsonData['ADDRESS_LINE5']  =   $orderData->sAddressLine5;
                $jsonData['TITLE']          =   $orderData->sTitle;
                $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo;
                break;
            
            case 'DT':
                $jsonData['PERSON_ID']      =   $orderData->dPersonId;
//                $jsonData['PERSON_TYPE']    =   $orderData->dPersonType;
                $jsonData['FIRST_NAME']     =   $orderData->dFirstName;
                $jsonData['LAST_NAME']      =   $orderData->dLastName;
                $jsonData['CITY']           =   $orderData->dCity;
                $jsonData['STATE']          =   $orderData->dState;
                $jsonData['POST_CODE']      =   $orderData->dPostCode;
                $jsonData['COUNTRY']        =   $orderData->dCountry;
                $jsonData['PHONE']          =   $orderData->dPhone;
                $jsonData['ADDRESS_LINE1']  =   $orderData->dAddressLine1;
                $jsonData['ADDRESS_LINE2']  =   $orderData->dAddressLine2;
                $jsonData['ADDRESS_LINE3']  =   $orderData->dAddressLine3;
                $jsonData['ADDRESS_LINE4']  =   $orderData->dAddressLine4;
                $jsonData['ADDRESS_LINE5']  =   $orderData->dAddressLine5;
                $jsonData['TITLE']          =   $orderData->dTitle;
                $jsonData['CONTACT_NAME']   =   $orderData->contactName;
                $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo;  
                break;
            case 'OA':
                $jsonData['PERSON_ID']      =   $orderData->pPersonId;
                $jsonData['PERSON_TYPE']    =   '';
                $jsonData['FIRST_NAME']     =   '';
                $jsonData['LAST_NAME']      =   '';
                $jsonData['CITY']           =   '';
                $jsonData['STATE']          =   '';
                $jsonData['POST_CODE']      =   '';
                $jsonData['COUNTRY']        =   '';
                $jsonData['PHONE']          =   '';
                $jsonData['ADDRESS_LINE1']  =   '';
                $jsonData['ADDRESS_LINE2']  =   '';
                $jsonData['ADDRESS_LINE3']  =   '';
                $jsonData['ADDRESS_LINE4']  =   '';
                $jsonData['ADDRESS_LINE5']  =   '';
                $jsonData['TITLE']          =   '';
                $jsonData['CONTACT_NAME']   =   $orderData->contactName;
                $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo; 
                
                $addresses = $this->minder->getAddresses('MT', $orderData->pPersonId);
                
                if (false === ($currentAddress = reset($addresses))) {
                    $jsonData['PERSON_TYPE']    =   $currentAddress->type;
                    $jsonData['FIRST_NAME']     =   $currentAddress->firstName;
                    $jsonData['LAST_NAME']      =   $currentAddress->lastName;
                    $jsonData['CITY']           =   $currentAddress->city;
                    $jsonData['STATE']          =   $currentAddress->state;
                    $jsonData['POST_CODE']      =   $currentAddress->postcode;
                    $jsonData['COUNTRY']        =   $currentAddress->country;
                    $jsonData['PHONE']          =   $currentAddress->phone;
                    $jsonData['ADDRESS_LINE1']  =   $currentAddress->line1;
                    $jsonData['ADDRESS_LINE2']  =   $currentAddress->line2;
                    $jsonData['TITLE']          =   $currentAddress->title;
                }
                break;
        }
        
        echo json_encode($jsonData);
        $this->_helper->viewRenderer->setNoRender(true);
        return;
        
    }
    
    public function saveAddressDataAction() {
        $fieldName  =   $this->getRequest()->getParam('field_name');
        $fieldType  =   explode('|', $fieldName);
        $fieldType  =   $fieldType[0];
        
        $pickOrder  =   new stdClass();
        
        $pickOrder->pickOrder   =   $this->session->connote['orderNo'];
        
        
        $jsonData   =   array();
        switch(strtoupper($fieldType)){
            case 'MT':
                $pickOrder->pPersonId       =   $this->getRequest()->getParam('person_id');
                $pickOrder->pPersonType     =   $this->getRequest()->getParam('person_type');
                $pickOrder->pFirstName      =   $this->getRequest()->getParam('first_name');
                $pickOrder->pLastName       =   $this->getRequest()->getParam('last_name');
                $pickOrder->pCity           =   $this->getRequest()->getParam('city');
                $pickOrder->pState          =   $this->getRequest()->getParam('state');
                $pickOrder->pPostCode       =   $this->getRequest()->getParam('post_code');
                $pickOrder->pCountry        =   $this->getRequest()->getParam('country');
                $pickOrder->pPhone          =   $this->getRequest()->getParam('phone');
                $pickOrder->pAddressLine1   =   $this->getRequest()->getParam('address_line1');
                $pickOrder->pAddressLine2   =   $this->getRequest()->getParam('address_line2');
                $pickOrder->pAddressLine3   =   $this->getRequest()->getParam('address_line3');
                $pickOrder->pAddressLine4   =   $this->getRequest()->getParam('address_line4');
                $pickOrder->pAddressLine5   =   $this->getRequest()->getParam('address_line5');
                $pickOrder->pTitle          =   $this->getRequest()->getParam('title');
                break;
            case 'OF':

//don't save Office address in PICK_ORDER table, but just save it in session to print if needed
                $instanceId = $this->getRequest()->getParam('instance_id', null);
                if (is_null($instanceId)) {
                    $jsonResponse['result'] =   false;
                    echo json_encode($jsonResponse);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;
                }
                
                if (isset($this->session->connote[$instanceId]['OFFICE_ADDRESS'])) {
                    $pickOrder = $this->session->connote[$instanceId]['OFFICE_ADDRESS'];
                } else {
                    $pickOrder = new stdClass();
                }

                $pickOrder->sPersonId       =   $this->getRequest()->getParam('person_id');
                $pickOrder->sPersonType     =   $this->getRequest()->getParam('person_type');
                $pickOrder->sFirstName      =   $this->getRequest()->getParam('first_name');
                $pickOrder->sLastName       =   $this->getRequest()->getParam('last_name');
                $pickOrder->sCity           =   $this->getRequest()->getParam('city');
                $pickOrder->sState          =   $this->getRequest()->getParam('state');
                $pickOrder->sPostCode       =   $this->getRequest()->getParam('post_code');
                $pickOrder->sCountry        =   $this->getRequest()->getParam('country');
                $pickOrder->sPhone          =   $this->getRequest()->getParam('phone');
                $pickOrder->sAddressLine1   =   $this->getRequest()->getParam('address_line1');
                $pickOrder->sAddressLine2   =   $this->getRequest()->getParam('address_line2');
                $pickOrder->sAddressLine3   =   $this->getRequest()->getParam('address_line3');
                $pickOrder->sAddressLine4   =   $this->getRequest()->getParam('address_line4');
                $pickOrder->sAddressLine5   =   $this->getRequest()->getParam('address_line5');
                $pickOrder->sTitle          =   $this->getRequest()->getParam('title');
                
                $this->session->connote[$instanceId]['OFFICE_ADDRESS'] = $pickOrder;
                
                $jsonResponse['result'] =   true;
                echo json_encode($jsonResponse);
                $this->_helper->viewRenderer->setNoRender(true);
                return;
            case 'DT':
                $pickOrder->dPersonId       =   $this->getRequest()->getParam('person_id');
                $pickOrder->dFirstName      =   $this->getRequest()->getParam('first_name');
                $pickOrder->dLastName       =   $this->getRequest()->getParam('last_name');
                $pickOrder->dCity           =   $this->getRequest()->getParam('city');
                $pickOrder->dState          =   $this->getRequest()->getParam('state');
                $pickOrder->dPostCode       =   $this->getRequest()->getParam('post_code');
                $pickOrder->dCountry        =   $this->getRequest()->getParam('country');
                $pickOrder->dPhone          =   $this->getRequest()->getParam('phone');
                $pickOrder->dAddressLine1   =   $this->getRequest()->getParam('address_line1');
                $pickOrder->dAddressLine2   =   $this->getRequest()->getParam('address_line2');
                $pickOrder->dAddressLine3   =   $this->getRequest()->getParam('address_line3');
                $pickOrder->dAddressLine4   =   $this->getRequest()->getParam('address_line4');
                $pickOrder->dAddressLine5   =   $this->getRequest()->getParam('address_line5');
                $pickOrder->dTitle          =   $this->getRequest()->getParam('title');
                break;
        }
        
        $result         = $this->minder->updatePickOrder($pickOrder, 'despatch');
        $jsonResponse   = array();  
        if($result){
            $jsonResponse['result'] =   true;    
        } else {
            $jsonResponse['result'] =   false;    
        }
           
        echo json_encode($jsonResponse);
        $this->_helper->viewRenderer->setNoRender(true);
        return;
    }
    
    public function getAddressAction() {
        $orderNo    =   $this->getRequest()->getParam('contact_instance_id');
        $jsonData   =   array();
        if(!empty($orderNo)){
         
            // get MESSAGE_ID_GEN
            $messageId  =   $this->minder->runDbGenerator('MESSAGE_ID_GEN');
            
            // populate WEB_REQUESTS
            $result     =   $this->minder->populateWebRequests($messageId, 'GCNA');
            if($result){
                // call ADD_TRAN_V4
            
                $transaction    =   new Transaction_GCNAS();
                $transaction->reference =   '<ContactInstanceID>' . $orderNo . $this->minder->defaultControlValues['LABEL_FIELD_WRAPPER'];
                
                $result =   $this->minder->doTransactionV4Response($transaction, $messageId); 
                // update WEB_REQUESTS
                $result     =   $this->minder->updateWebRequests($messageId, array('REQUEST_STATUS' => 'WS'));
                // starting listning for DB event
                if($result){
                    
                }
            }
        } else {
            $jsonData['result']  =   false;
            $jsonData['message'] =   'Please enter Order No.';
        }
        
        echo json_encode($jsonData);
        $this->_helper->viewRenderer->setNoRender(true);
        return;
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
    
    public function acceptAction() {
        $jsonData = array('success' => false, 'messages' => array(), 'errors' => array());
        
        $instanceId = $this->getRequest()->getParam('instance_id', null);
        if (is_null($instanceId)) {
            $jsonData['errors'][] = 'No Instance Id found for this Connote form.';
            echo json_encode($jsonData);
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        $instanceSessionData = $this->session->connote[$instanceId];
        $rowSelector         = $this->_helper->getHelper('RowSelector');
        
        $orderSelection      = $instanceSessionData['order_selection'];
        $totalOrders         = $rowSelector->getTotalCount($orderSelection['namespace'], $orderSelection['action_name'], $orderSelection['controller_name']);
        $selectedOrders      = $rowSelector->getSelected(0, $totalOrders, true, $orderSelection['namespace'], $orderSelection['action_name'], $orderSelection['controller_name']);
        
        if (count($selectedOrders) < 1) {
            $jsonData['errors'][] = 'No Orders selected for despatch.';
            echo json_encode($jsonData);
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        $linesSelection      = $instanceSessionData['lines_selection'];
        $totalLines          = $rowSelector->getTotalCount($linesSelection['namespace'], $linesSelection['action_name'], $linesSelection['controller_name']);
        $selectedLines       = $rowSelector->getSelected(0, $totalLines, false, $linesSelection['namespace'], $linesSelection['action_name'], $linesSelection['controller_name']);
        
        if (count($selectedLines) < 1) {
            $jsonData['errors'][] = 'No Lines selected for despatch.';
            echo json_encode($jsonData);
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        $ordersIds = array();
        foreach ($selectedOrders as $rowId => $row)
            $ordersIds[$row[$orderSelection['field_name']]] = $row[$orderSelection['field_name']];
        
        $linesIds = array();
        foreach ($selectedLines as $rowId => $row)
            $linesIds[$row[$linesSelection['field_name']]] = $row[$linesSelection['field_name']];
        
        if (true !== ($result = $this->minder->checkOrdersAndLinesForDespatch($ordersIds, $linesIds))) {
            $jsonData['errors'][] = $result;
            echo json_encode($jsonData);
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        
        $totalWeightMaxLen = 0;
        $totalVolumeMaxLen = 0;
        if (count($selectedOrders) > 1) {
            $holdBy = 'location';
            $transaction       = new Transaction_DSOTL();
            $totalWeightMaxLen = Transaction_DSOTL::$totalWeightMaxLen;
            $totalVolumeMaxLen = Transaction_DSOTL::$totalVolumeMaxLen;
        } else {
            $holdBy            = 'order';
            $transaction       = new Transaction_DSOTS();
            $totalWeightMaxLen = Transaction_DSOTS::$totalWeightMaxLen;
            $totalVolumeMaxLen = Transaction_DSOTS::$totalVolumeMaxLen;
        }
        
        try {
            list($selectedOrder, $selectedLocation, $holdedPickItems, $holdedISSNs) = $this->minder->holdUnnededLines($linesIds, $holdBy);
        } catch (Exception $e) {
            $jsonData['errors'][] = $e->getMessage();
            echo json_encode($jsonData);
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        $palletQty = $this->_request->getParam('palletQty');
        $cartonQty = $this->_request->getParam('totalCartons');
        $satchelQty = $this->_request->getParam('totalSatchels');

        $payerFlag = $this->_request->getParam('payer');
        if(!empty($payerFlag)) {
            $payerFlag = substr($payerFlag, 0, 1);
            $transaction->payerFlag = $payerFlag;
        }

        $palletOwner = $this->_request->getParam('palletOwner'); 

        $carrierId = $this->_request->getParam('carrier');
        if(empty($carrierId)) {
            $carrierId = $this->minder->defaultControlValues['DEFAULT_CARRIER_ID'];  
        }
        
        $serviceType = $this->_request->getParam('carrierService');
        if (empty($serviceType)) {
            $serviceType = 'GEN';
        }
        
        $printerId = $this->minder->limitPrinter;
        if (empty($printerId)) {
            $printerId = $this->minder->defaultControlValues['DEFAULT_DESPATCH_PRINTER '];  
        }
        
        $qtyAddressLabel =   $this->getRequest()->getParam('qtyAddressLabels');
        
        $accountNo = $this->_request->getParam('accountNo');
        $conNoteNo = $this->_request->getParam('consignment');
        
        try {
            $packDimensions = $this->getRequest()->getParam('dimentions', array());
            array_walk_recursive($packDimensions, 'trim');
            
            //check given data
            
            $enteredPallets  = 0;
            $enteredCatrons  = 0;
            $enteredSatchels = 0;
            $totalVolume     = 0;
            $totalWeight     = 0;
            
            $uomConverter    = $this->_helper->UomConverter;
            $uomConverter->getUoms(array(self::$connoteDT, self::$connoteDTforVT, self::$connoteVT, self::$connoteWT, self::$dsotsWT, self::$dsotsDTForVt));
            
            foreach ($packDimensions as &$dimension) {
                $dtFactor         = $uomConverter->convert(1, $dimension['DIMENSION_UOM'], self::$connoteDT);
                $wtFactor         = $uomConverter->convert(1, $dimension['PACK_WEIGHT_UOM'], self::$connoteWT);
                $dtForVtFactor    = $uomConverter->convert(1, $dimension['DIMENSION_UOM'], self::$connoteDTforVT);
                
                $dimension['VOL'] = $dimension['L'] * $dimension['W'] * $dimension['H'] * $dtForVtFactor * $dtForVtFactor * $dtForVtFactor;
                $dimension['WT']  = $dimension['WT'] * $wtFactor;
                
                $dimension['L']               *= $dtFactor;
                $dimension['W']               *= $dtFactor;
                $dimension['H']               *= $dtFactor;
                $dimension['DIMENSION_UOM']    = self::$connoteDT;
                $dimension['PACK_WEIGHT_UOM']  = self::$connoteWT;
                $dimension['VOLUME_UOM']       = self::$connoteVT;
                
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
                        throw new Minder_Exception("Unknown package type '" . $dimension['TYPE'] . "'.");
                }
                $dimension['TOTAL_WT']  = round($dimension['TOTAL_WT'], self::$precision);
                $dimension['TOTAL_VOL'] = round($dimension['TOTAL_VOL'], self::$precision);
                
                //separatly compute Total Weight and Total Volume for DSOT S transaction
                $wtFactor         = $uomConverter->convert(1, $dimension['PACK_WEIGHT_UOM'], self::$dsotsWT);
                $dtForVtFactor    = $uomConverter->convert(1, $dimension['DIMENSION_UOM'], self::$dsotsDTForVt);
                
                $totalWeight += $dimension['QTY'] * $dimension['WT'] * $wtFactor;
                $totalVolume += $dimension['QTY'] * $dimension['L'] * $dimension['W'] * $dimension['H'] * $dtForVtFactor * $dtForVtFactor * $dtForVtFactor;
            }
            
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $totalWeight, $totalVolume));
            
            if ($enteredPallets != $palletQty)
                throw new Minder_Exception("Entered pallets qty ($enteredPallets) doesn not match total pallets qty ($palletQty).");
                
            if ($enteredCatrons != $cartonQty)
                throw new Minder_Exception("Entered cartons qty ($enteredCatrons) doesn not match total cartons qty ($cartonQty).");
                
            if ($enteredSatchels != $satchelQty)
                throw new Minder_Exception("Entered satchels qty ($enteredSatchels) doesn not match total satchels qty ($satchelQty).");
                
            if (($totalWeight == 0) && ($totalVolume == 0))
                throw new Minder_Exception("Enter Weight or Volume information.");

            $totalWeight = round($totalWeight, self::$precision);
            $totalVolume = round($totalVolume, self::$precision);
            
            if (false === ($totalWeight = $this->reducePrecision($totalWeight, $totalWeightMaxLen, self::$precision)))
                throw new Minder_Exception('Error while ' . $transaction->transCode + $transaction->transClass . ' transaction: Total Weight is greater then ' . str_repeat('9', $totalWeightMaxLen));
            
            if (false === ($totalVolume = $this->reducePrecision($totalVolume, $totalVolumeMaxLen, self::$precision)))
                throw new Minder_Exception('Error while ' . $transaction->transCode + $transaction->transClass . ' transaction: Total Volume is greater then ' . str_repeat('9', $totalVolumeMaxLen));

            $transaction->conNoteNo      =   $conNoteNo;
            $transaction->accountNo      =   $accountNo;

            if (get_class($transaction) == 'Transaction_DSOTS') {
                $transaction->orderNo    =   $selectedOrder; 
            } else {
                $transaction->locationId =   $selectedLocation;
            }
            
            $transaction->carrierId      =   $carrierId;
            $transaction->palletQty      =   $palletQty;
            $transaction->palletOwnerId  =   $palletOwner;
            $transaction->cartonQty      =   $cartonQty;
            $transaction->satchelQty     =   $satchelQty;
            $transaction->totalWeight    =   $totalWeight;
            $transaction->totalVolume    =   $totalVolume;
            $transaction->serviceType    =   $serviceType;
            $transaction->packType       =   $this->minder->defaultControlValues['DEFAULT_CONNOTE_PACK'];
            $transaction->printerId      =   $printerId;
            $transaction->labelQty       =   $qtyAddressLabel;
            
            if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                throw new Minder_Exception('Error while ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->minder->lastError);
            }
            $jsonData['messages'][] = 'Transaction '. $transaction->transCode . $transaction->transClass . ': ' . $result;
            
            $transaction            =    new Transaction_DSOLO();
        
            $transaction->objectId  =    $conNoteNo;
            $transaction->qty       =    $qtyAddressLabel;    
             
            if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSSKSS', '', 'MASTER    '))) { 
                throw new Minder_Exception('Error while ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $this->minder->lastError);
            }
            $jsonData['messages'][] = 'Transaction '. $transaction->transCode . $transaction->transClass . ': ' . $result;
                    
            $this->minder->updatePackIdDimensions($this->minder->getPackIdCretedByDSOL($conNoteNo), $packDimensions);
            if ($this->minder->defaultControlValues['USE_INVOICE'] == 'T') {
                
                $linesModel   = $rowSelector->getModel($linesSelection['namespace'], $linesSelection['action_name'], $linesSelection['controller_name']);
                $tmpImplements = class_implements($linesModel);
                if (!isset($tmpImplements['Minder_SysScreen_Model_ConnoteLine_Interface']))
                    throw new Exception('Error Minder_SysScreen_Model_ConnoteLines_Interface unimplemented: Each Lines Model which is used at Connote screen should implement Minder_SysScreen_Model_ConnoteLines_Interface.');
                
                $linesModel->addConditions($rowSelector->getSelectConditions($linesSelection['namespace'], $linesSelection['action_name'], $linesSelection['controller_name']));
                $linesCount       = count($linesModel);
                
                $linesLabelNo     = $linesModel->selectPickLabelNo(0, $linesCount);
                $linesLabelNo     = array_map(create_function('$item', 'return $item["PICK_LABEL_NO"];'), $linesLabelNo);
                $linesDetails     = $this->minder->getLineDetailsForInvoice($linesLabelNo);
                $pickOrderDetails = $this->minder->getPickOrderDetailsForInvoice($linesLabelNo);
                
                list($devWhId, $devLocnId)  = $this->minder->getDeviceWhAndLocation();
                $pinvcTransaction = new Transaction_PINVC();
                $pilncTransaction = new Transaction_PILNC();
                $createdInvoiceNo = '';
                
                foreach ($pickOrderDetails as $orderDetail) {
                    //first we need to calc some values for PINV C transaction from PICK_ITEM_DETAIL records
                    //and as we will use most of this values in PILN C transactions
                    //so create array of PILN C transactions and fill it with this calculated values
                    
                    $pilncTransArray  = array();

                    foreach ($linesDetails as $detailLine) {
                        if ($detailLine['PICK_ORDER'] != $orderDetail['PICK_ORDER'])
                            continue;
                        
                        $pilncTransaction->fillValuesFromPickItemDetail($detailLine);
                        $pilncTransArray[] = clone $pilncTransaction;
                    }
                    
                    $pinvcTransaction->fillValuesFromPickOrderDetail($orderDetail);
                    
                    $pinvcTransaction->whId          = $devWhId;
                    $pinvcTransaction->locationId    = $devLocnId;
                    $pinvcTransaction->invoiceType   = 'TI';                        // All Invoices created following a 'DSOT' must only use INVOICE_TYPE = 'TI' - Tax Invoice.
                    $pinvcTransaction->printerDevice = $this->minder->limitPrinter; //User must select from LIMIT Printer drop down.
                    $pinvcTransaction->invoiceQty    = 1;                           //Use 1 until I get another CONTROL.DEFAULT_INVOICE_COPIES.
                    
                    if (false === ($result = $this->minder->doTransactionResponse($pinvcTransaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                        throw new Minder_Exception('Error while ' . $pinvcTransaction->transCode . $pinvcTransaction->transClass . ' transaction: ' . $this->minder->lastError);
                    }
                    $jsonData['messages'][]     = 'Transaction '. $pinvcTransaction->transCode . $pinvcTransaction->transClass . ': ' . $result;
                    list(,,$createdInvoiceNo)   = $pinvcTransaction->parseResponse($result);
                    
                    foreach ($pilncTransArray as $tmpPilncTransaction) {
                        $tmpPilncTransaction->whId          = $devWhId;
                        $tmpPilncTransaction->locationId    = $devLocnId;
                        $tmpPilncTransaction->invoiceNo     = $createdInvoiceNo;    //parse PINV transaction response to find invoiceNo created by PINV C transaction
    
                        if (false === ($result = $this->minder->doTransactionResponse($tmpPilncTransaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                            throw new Minder_Exception('Error while ' . $tmpPilncTransaction->transCode . $tmpPilncTransaction->transClass . ' transaction: ' . $this->minder->lastError);
                        }
                        $jsonData['messages'][]     = 'Transaction '. $tmpPilncTransaction->transCode . $tmpPilncTransaction->transClass . ': ' . $result;
                    }
                    
                    //lets create Invoce PDF
                    $invoiceReport              = Minder_Report_Factory::makeInvoiceReportForCompany($orderDetail['COMPANY_ID'], 'TI');
                    $invoiceReport->pickInvoice = $createdInvoiceNo;
                    $invoiceReport->pickOrder   = $orderDetail['PICK_ORDER'];
                    $invoicePdf                 = $invoiceReport->getPdfImage();
/*

require coalesce(person.email,'') <> ''
        person.email_invoice_ti = 'T'
        company.email not null
        company.invoice_ti_report_id not null

*/
/*                    
                    $mailTr = new Zend_Mail_Transport_Sendmail('-fbdcs@barcoding.com.au');
                    // return address
                    Zend_Mail::setDefaultTransport($mailTr);
*/
                    $mailer           = new PHPMailer(True); // defaults to using php "mail()"

                    $mailer->IsSMTP(); // telling the class to use SMTP
                    $mailer->Host       = "127.0.0.1"; // SMTP server
                    $mailer->SMTPDebug  = 2;  // enables SMTP debug information (for testing)

/*
                    $mailer = new Zend_Mail();
                    $mailer->setBodyText('Invoice attached.');
*/
                    // want to specify the subject
                    // from list
                    // to list
                    // cc list
                    // bcc list
                    // include jpgs
                    // use one of their emails for html template
                    // header 
                    // trailer
/*
                    $mailer->setFrom('support@barcoding.com.au', 'Test Sender'); //need to find out where can I get return Email
                    $toEmail = $this->minder->findInvoiceEmail($orderDetail['COMPANY_ID'], 'TI');
                    // that to list is for the company only
                    // we want one for each person_id for the order ie client
                    //$mailer->addTo($toEmail);
                    $mailer->addTo("xx@barcoding.com.au");
                    // get copy to from company.invoice_cc_email
                    // if not null add to email
                    $mailer->setSubject('Invoice.');
                    // add subject  order no before  and customer_po_wo after desc  perhaps invoice no 
                    $mailer->createAttachment($invoicePdf,
                        'application/pdf',
                        Zend_Mime::DISPOSITION_INLINE,
                        Zend_Mime::ENCODING_8BIT);
                    $mailer->send();
*/
                    // get files in /etc/minder/mail for  body parts
                    $imageExt = array("jpg", "gif", "jpeg", "svg", "bmp", "JPG"); // valid extensions
                    $htmlExt = array("html" ); // valid extensions
                    $txtExt = array("txt" ); // valid extensions
                    $mailDir = "/etc/minder/mail";

                    $mailer->AltBody = 'Invoice attached.';
                    $wk_body =  'Invoice attached. ';
                    $wk_body_html =  '';
                    // for the txt  in /etc/minder/mail append into body
                    foreach (new DirectoryIterator($mailDir) as $fileInfo) { // interator
                        if (in_array($fileInfo->getExtension(), $txtExt) ) { // in $txtExt
                            $wk_body .= PHP_EOL . file_get_contents($fileInfo->getFilename()));
                        }
                    }
                        // for the html in /etc/minder/mail
                        //$mailer->MsgHTML(file_get_contents('contents.html'));
                    foreach (new DirectoryIterator($mailDir) as $fileInfo) { // interator
                        if (in_array($fileInfo->getExtension(), $htmlExt) ) { // in $htmlExt
                            //echo $fileInfo->getFilename() . "<br>\n"; // do something here
                            $wk_body_html .=  file_get_contents($fileInfo->getFilename()));
                        }
                    }
                    if (empty($wk_body_html)) {
                        $mailer->Body = $wk_body;
                        $mailer->IsHTML ( False);
                    } else {
                        $mailer->IsHTML ( True);
                        $mailer->MsgHTML($wk_body_html);
                        $mailer->AltBody = $wk_body;
                        $mailer->Body = $wk_body_html;
                    }
                    $fromEmail = $this->minder->findInvoiceFromEmail($orderDetail['COMPANY_ID'], 'TI');
                    $ccEmail = $this->minder->findInvoiceCCEmail($orderDetail['COMPANY_ID'], 'TI');
                    $toEmail = $this->minder->findInvoiceEmail($orderDetail['COMPANY_ID'], 'TI',
                                                               $orderDetail['PERSON_ID'] );
                    if (empty($fromEmail) or empty($toEmail)) {
                        // no from email or to email so no send
                    } else {

                        $mailer->SetFrom($fromEmail); // return Email = company.email

                        $mailer->AddAddress($toEmail ); // to address

                        $mailer->AddReplyTo($fromEmail); // reply to and from set to company.email
                        // get copy to from company.invoice_cc_email
                        // if not null add to email
                        if (!empty($ccEmail)) {
                            $mailer->AddCC($ccEmail);
                        }
                        $mailer->Subject    = $orderDetail['PICK_ORDER'] . " Invoice attached. " . $orderDetail['CUSTOMER_PO_WO'] ;
                        // add subject  order no before  and customer_po_wo after desc  perhaps invoice no 
                        // add invoice pdf
                        $mailer->AddAttachment($invoicePdf, $name = 'Invoice',  $encoding = 'base64', $type = 'application/pdf');
                        // for the jpg or JPG in /etc/minder/mail
                        foreach (new DirectoryIterator($mailDir) as $fileInfo) { // interator
                            if (in_array($fileInfo->getExtension(), $imageExt ) ) { // in $imageExt
                                //echo $fileInfo->getFilename()  ; 
                                $mailer->AddAttachment($fileInfo->getFilename());      // attachment
                            }
                        }
                        if(!$mailer->Send()) {
                            echo "Mailer Error: " . $mailer->ErrorInfo;
                            $mailResult = "Mailer Error:" . $mailer->ErrorInfo;
                        } else {
                            echo "Message sent!";
                            $mailResult = "Message Sent!" ;
                        }

                    } // of of populated email
                    $mailer->ClearAddresses();
                    $mailer->ClearAttachments();
                }
            }
        
//             update Order data
            $pickOrder  =   new stdClass();
        
            $pickOrder->pickOrder   =   $this->session->connote['orderNo'];
            $pickOrder->remarks1    =   $this->_request->getParam('label_comment1');      
            $pickOrder->remarks2    =   $this->_request->getParam('label_comment2');
        
            $result =   $this->minder->updatePickOrder($pickOrder, 'despatch');
            if(!$result){
                $jsonData['errors'][] = 'Error while update order data';    
            }
        } catch (Exception $e) {
            $jsonData['errors'][] = $e->getMessage();
        }
        
        if (count($jsonData['errors']) < 1) {
            $jsonData['success'] = true;
        }

        //always release holded PICK_ITEMs and ISSNs nevermind was transaction success or not
        $this->minder->releaseHoldedLines($holdedPickItems, $holdedISSNs);
        
        echo json_encode($jsonData);
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    public function successAction() {
        $this->view->errors = array();
        
        $instanceId = $this->getRequest()->getParam('instance_id', null);
        if (is_null($instanceId)) {
            $this->view->errors[] = 'No Instance Id found for this Connote form.';
            return;
        }
        
        $instanceSessionData = $this->session->connote[$instanceId];
        $rowSelector         = $this->_helper->getHelper('RowSelector');
        
        $linesSelection      = $instanceSessionData['lines_selection'];
        $linesModel          = $rowSelector->getModel($linesSelection['namespace'], $linesSelection['action_name'], $linesSelection['controller_name']);
        $totalLines          = count($linesModel);
        $allLines            = $linesModel->getItems(0, $totalLines, true);
        
        $linesIds = array();
        foreach ($allLines as $rowId => $row)
            $linesIds[$row[$linesSelection['field_name']]] = $row[$linesSelection['field_name']];
            
        if ($this->minder->undespatchedLinesLeft($linesIds)) {
            if (!isset($instanceSessionData['callback']['on_resume'])) {
                $this->view->errors[] = 'No action specified for on_resume event.';
                return;
            }
            
            $callback = $instanceSessionData['callback']['on_resume'];
        }
        else {
            if (!isset($instanceSessionData['callback']['on_success'])) {
                $this->view->errors[] = 'No action specified for on_success event.';
                return;
            }
            
            $callback = $instanceSessionData['callback']['on_success'];
        }
        
        
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $action =     isset($callback['action']) ? $callback['action'] : 'index';
        $controller = isset($callback['controller']) ? $callback['controller'] : 'index';
        $module =     isset($callback['module']) ? $callback['module'] : 'default';
        if (isset($callback['action'])) unset($callback['action']);
        if (isset($callback['controller'])) unset($callback['controller']);
        if (isset($callback['module'])) unset($callback['module']);
        $this->_redirector->setCode(303)
                          ->goto($action, $controller, $module, $callback);
        
    }
    
    protected function setupPackDimensionsView() {
        $this->view->headers = array(
            'TYPE' => 'TYPE',
            'QTY' => 'QTY',
            'L' => 'L (CM)',
            'W' => 'W (CM)',
            'H' => 'H (CM)',
            'VOL' => 'VOL (CU.M)',
            'WT' => 'WT (KGS)',
            'TOTAL_WT' => 'TOT.WT',
            'TOTAL_VOL' => 'TOT.VOL'
        );
        
        $this->view->packageTypes = array('' => '');
    }
}
