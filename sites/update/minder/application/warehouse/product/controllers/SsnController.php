<?php
/**
 * Minder
 *
 * PHP version 5.2.5
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Warehouse_SsnController
 *
 * Action controller
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Warehouse_SsnController extends Minder_Controller_Action
{


    function init() {
        parent::init();
        $this->returnOrder = $this->session->returnOrder;
        
    }
    /**
     * Populates list and default values for SSN search screen
     * also process conditions while do searching
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_preProcessNavigation();
        $this->view->navigation =  $this->session->navigation[$this->_controller][$this->_action]; 
        
        // get param from ORDER screen(ADD PRODUCT ot ADD Non-Product)
        $productOptions = &$this->session->params['non_product_options'];
        
        if(isset($productOptions)){
            $this->session->from['pick_order']      =   $productOptions->pickOrder;
            $this->session->from['wh_id']           =   $productOptions->whId;
            $this->session->from['default_price']   =   $productOptions->defaultPrice;
            $this->session->from['qty']             =   $productOptions->qty;
             
             
            $this->getRequest()->setParam('from', $productOptions->from);   
            $this->getRequest()->setParam('company_id', $productOptions->company);    
            $this->getRequest()->setParam('required_qty',  $productOptions->qty);    
            
            if($productOptions->thisIs == 'description'){
                $this->getRequest()->setParam('ssn_description', $productOptions->nonProductName);    
                $this->getRequest()->setParam('ssn_id', '');    
            } elseif($productOptions->thisIs == 'issn') {
                $this->getRequest()->setParam('ssn_id', $productOptions->nonProductName);    
                $this->getRequest()->setParam('ssn_description', '');    
            }
           
        }
        
        $this->_setupConditions();
        $this->view->pageTitle      = 'Search SSN';
        $from = $this->getRequest()->getParam('from');
        
        // Prevent direct access.
        if ($from == 'pick-order2' && !isset($this->session->from['pick_order'])) {
            $this->_redirect();
        }
        $pickOrderNo = $this->session->from['pick_order'];
        // disable tab after search button  selected
        
        // get options list in an order to mark Loan Status, Safety, Calibrate fileds
        $this->view->colorsCodes            = $this->minder->getOptionsList('COLORS');

        $this->view->loanStatusColors       = $this->minder->getOptionsList('LOAN_STATU');
        $this->view->loanSafetyColors       = $this->minder->getOptionsList('LOAN_SAFET');
        $this->view->loanCalibrateColors    = $this->minder->getOptionsList('LOAN_CALIB');

        switch ($from) {
            case 'pick-order2':
                $this->view->from = $from;
                $this->view->pageTitle .= ': ORDER # = ' . $this->session->from['pick_order'];
                break;

            case 'transfer-order':
                $this->view->from = $from;
                $this->view->pageTitle .= ': ORDER # = ' . $this->session->from['pick_order'];
                break;

            case 'fso':
                unset($this->session->from);
                $this->view->from = $from;
                $this->view->pageTitle .= ': NEW ORDER';
                break;

            default:
                unset($this->session->from);
                $from = null;
        }

        $this->_preProcessNavigation();

        $allowed = array(
                            'ssn_id'          => 'SSN.SSN_ID LIKE ? AND ',
                            'company_id'      => 'SSN.COMPANY_ID LIKE ? AND ',
                            'cost_center'     => 'SSN.COST_CENTER LIKE ? AND ',
                            'supplier_id'     => 'SSN.SUPPLIER_ID LIKE ? AND ',
                            'division_id'     => 'SSN.DIVISION_ID LIKE ? AND ',
                            'department_id'   => 'SSN.DEPARTMENT_ID ? AND ',
                            'grn'             => 'SSN.GRN LIKE ? AND ',
                            'po_order'        => 'SSN.PO_ORDER LIKE ? AND ',
                            'po_line'         => 'SSN.PO_LINE LIKE ? AND ',
                            'ssn_type'        => 'SSN.SSN_TYPE LIKE ? AND ',
                            'generic'         => 'SSN.GENERIC LIKE ? AND ',
                            'ssn_sub_type'    => 'SSN.SSN_SUB_TYPE LIKE ? AND ',
                            'brand'           => 'SSN.BRAND LIKE ? AND ',
                            'model'           => 'SSN.MODEL LIKE ? AND ',
                            'serial_number'   => 'SSN.SERIAL_NUMBER LIKE ? AND ',
                            'ssn_description' => '',
                            'created_date_from' => 'SSN.CREATE_DATE >= ? AND ',
                            'created_date_to'   => 'SSN.CREATE_DATE <= ? AND ',
                            'alternate_name'  => 'SSN.ALT_NAME LIKE ? AND',
                            'legacy_id'       => 'SSN.LEGACY_ID LIKE ? AND ',
                            'old_ssn_no'      => 'SSN.OLD_SSN_ID LIKE ? AND ',
                            'parent_ssn'      => 'SSN.PARENT_SSN_ID LIKE ? AND ',
                            'other1'          => 'SSN.OTHER1 LIKE ? AND ',
                            'other2'          => 'SSN.OTHER2 LIKE ? AND ',
                            'other3'          => 'SSN.OTHER3 LIKE ? AND ',
                            'other4'          => 'SSN.OTHER4 LIKE ? AND ',
                            'other5'          => 'SSN.OTHER5 LIKE ? AND ',
                            'other6'          => 'SSN.OTHER6 LIKE ? AND ',
                            'other7'          => 'SSN.OTHER7 LIKE ? AND ',
                            'other8'          => 'SSN.OTHER8 LIKE ? AND ',
                            'other9'          => 'SSN.OTHER9 LIKE ? AND ',
                            'other10'         => 'SSN.OTHER10 LIKE ? AND ',
                            'other11'         => 'SSN.OTHER11 LIKE ? AND ',
                            'other12'         => 'SSN.OTHER12 LIKE ? AND ',
                            'other13'         => 'SSN.OTHER13 LIKE ? AND ',
                            'other14'         => 'SSN.OTHER14 LIKE ? AND ',
                            'other15_date'    => 'SSN.OTHER17_DATE LIKE ? AND ',
                            'other16_date'    => 'SSN.OTHER17_DATE LIKE ? AND ',
                            'other17_qty'     => 'SSN.OTHER17_QTY LIKE ? AND ',
                            'other18_qty'     => 'SSN.OTHER18_QTY LIKE ? AND ',
                            'other19'         => 'SSN.OTHER19 LIKE ? AND ',
                            'other20'         => 'SSN.OTHER20 LIKE ? AND ',
                            'last_calibrate_check_date_from' => 'SSN.LOAN_LAST_CALIBRATE_CHECK_DATE >= ? AND ',
                            'last_calibrate_check_date_to'   => 'SSN.LOAN_LAST_CALIBRATE_CHECK_DATE <= ? AND ',
                            'last_safety_check_date_from'    => 'SSN.LOAN_LAST_SAFETY_CHECK_DATE <= ? AND ',
                            'last_safety_check_date_to'      => 'SSN.LOAN_LAST_SAFETY_CHECK_DATE <= ? AND ',
                            'wh_id'          =>  'ISSN.WH_ID = ? AND '        
                        );

        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'ADD SSN':
       $params = array();
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('add',
                                         'ssn',
                                         'warehouse',
                                         $params);
               break;
            case 'CREATE SSN':
                $params = array();
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('create-ssn',
                                         'ssn',
                                         'warehouse',
                                         $params);
                break;

            case 'CANCEL ADD':
                unset($this->session->from);
                $this->_redirect($this->returnOrder . '/index');
                break;
            case 'PRODUCTS':
                $this->session->from['product'] = 1;
                $this->_redirect('warehouse/products/index' . (isset($from) ? '/from/' . $from : '') . ($this->_getParam('without') ? '/without/1' : ''));
                break;
            case 'NON-PRODUCTS':
                $this->session->from['product'] = 0;
                $this->_redirect('warehouse/ssn/index' . (isset($from) ? '/from/' . $from : '') . ($this->_getParam('without') ? '/without/1' : ''));
                break;

            case 'ADD':
                $conditions = $this->_setupConditions(null, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                // parse fields SSN_DESCRIPTION
                
                try{
            
                    $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                } catch(Exception $ex) {}
                
                try {
                    $result           = $this->minder->getSsns($clause, 'edit', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                    $this->view->ssns = $result['data'];
                } catch  (Exception $e) {
                    $this->addError('Error occured during add item.');
                }
                $data = array();
                foreach ($this->view->ssns as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        $tmpClause = array('SSN.SSN_ID = ? AND ' => $line->id);
                        $tmpData   = $this->minder->getSsnsNotForScreen($tmpClause, 'edit', 0, 1);
                        $data[$line->id] = current($tmpData['data'])->items;
                    }
                }
                if ($from != 'fso') {
                    // -----------------------
                    // --- ADD NON-PRODUCT ---
                    // -----------------------
                    $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('pick_order' => $pickOrderNo), $data, array('case' => 3, 'price' =>   $this->session->from['default_price'], 'required_qty' => $this->session->from['qty']));
                    $this->_forward('add-issn-items', $this->returnOrder, 'default', array('redirect' => $this->returnOrder));
                    return; // for _forward().
                } else {
                    // ------------------------
                    // --- Fast Sales Order ---
                    // ------------------------
                    if (!isset($this->session->params[$this->returnOrder]['index']['pick_items'])) {
                        $this->session->params[$this->returnOrder]['index']['pick_items'] = array();
                    }
                    $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge($this->session->params[$this->returnOrder]['index']['pick_items'], $data);
                    if($this->returnOrder == 'transfer-order') {
                        $this->_redirect( $this->returnOrder . '/new/pick_order_type/TO');
                    } else {
                        $this->_redirect( $this->returnOrder . '/new/pick_order_type/SO');
                    }
                }
            break;

            case 'ADD & CONTINUE':
                $conditions = $this->_setupConditions(null, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                // parse fields SSN_DESCRIPTION
                try{
            
                    $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
            
                } catch(Exception $ex) {}
                
                try {
                    $result           = $this->minder->getSsns($clause, 'edit', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                    $this->view->ssns = $result['data'];
                } catch  (Exception $e) {
                    $this->_helper->flashMessenger->addMessage('Error occured during add item.');
                }
                $data = array();
                foreach ($this->view->ssns as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        $data[$line->id] = $line->items;
                    }
                }
                if ($from != 'fso') {
                    // -----------------------
                    // --- ADD NON-PRODUCT ---
                    // -----------------------
                    $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('pick_order' => $pickOrderNo), $data, array('case' => 3, 'price' =>   $this->session->from['default_price'], 'required_qty' => $this->session->from['qty']));
                    $this->_forward('add-issn-items', $this->returnOrder, 'default', array('redirect' => 'warehouse/ssn/index/from/'. $this->returnOrder));
                    return; // for _forward().
                } else {
                    // ------------------------
                    // --- Fast Sales Order ---
                    // ------------------------
                    if (!isset($this->session->params[$this->returnOrder]['index']['pick_items'])) {
                        $this->session->params[$this->returnOrder]['index']['pick_items'] = array();
                    }
                    $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge($this->session->params[$this->returnOrder]['index']['pick_items'], $data);
                }
                $this->_redirect('warehouse/products/index'
                    . (isset($from) ? '/from/' . $from : ''));
            break;

          case 'VIEW AVAILABILITY':
                $conditions = $this->_setupConditions(null, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                
                // parse fields SSN_DESCRIPTION
                try{
            
                    $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
            
                } catch(Exception $ex) {}
                
                try {
                    $result = $this->minder->getSsns($clause, 'edit', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                    $lines  = $result['data'];
                } catch  (Exception $e) {
                    $this->addError('Error occured during add item.');
                }
                $data = array();
                foreach ($lines as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        $data[$line->id] = $line->id;
                    }
                }
                
                $this->session->params['draw']['items'] = array();
                $this->session->params['draw']['items'] = $data;
                $this->session->params['draw']['mode']  = 'ssnItem';
                $this->_helper->viewRenderer->setNoRender(true);
                break;

            case 'REPORT: GD':
                    
                    $conditions = $this->_setupConditions(null, $allowed);
                    $clause     = $this->_makeClause($conditions, $allowed);
                    // parse fields SSN_DESCRIPTION
                    try{
                
                        $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                        $outStr    =    $parserObj->parse();  

                        $clause[$outStr] = '';
                
                    } catch(Exception $ex) {}
                
                    try {
                        $result  = $this->minder->getSsns($clause, 'edit', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                        $lines   = $result['data'];
                    } catch  (Exception $e) {
                        $this->addError('Error occured during add item.');
                    }
                    $data = array();
                    foreach ($lines as $line) {
                        if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                            $data[$line->id] = $line->id;
                        }
                    }
                    
                    $this->view->data = $this->minder->getSsnItemDates($data);
                    $this->_processReportTo('REPORT: GD');
                    break;
                
            case 'SELECT ROWS':
                $conditions = $this->_setupConditions(null, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                // parse fields SSN_DESCRIPTION
                try{
            
                    $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
            
                } catch(Exception $ex) {}
                
                try {
                    $result = $this->minder->getSsns($clause, 'edit', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                    $ssns   = $result['data'];
                } catch (Exception $e) {
                    $this->addError('Error occured during add item.');
                }

                $this->_postProcessNavigation($ssns);

                $ssns = array_slice($ssns,
                                    $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
                                    $this->view->maxno);

                $reqQty = $this->getRequest()->getPost('required_qty');
                $markSsns = array();
                if($reqQty >= count($ssns)) {
                    for($i=0; $i<count($ssns); $i++ ) {
                        $conditions[$ssns[$i]->id] = $ssns[$i]->id;
                        $markSsns[] = $ssns[$i]->id;
                    }
                } else {
                     for($i=0; $i<$reqQty; $i++ ) {
                        $conditions[$ssns[$i]->id] = $ssns[$i]->id;
                        $markSsns[] = $ssns[$i]->id;
                    }
                }
                $this->_setConditions($conditions);
                echo json_encode($markSsns);

                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                break;
            case 'CANCEL ADD':
                unset($this->session->from);
                $this->_redirect($this->returnOrder);
                break;

            default:
                break;
        }

        // Save data array in view.
        if (!isset($data)) {
            $conditions = $this->_setupConditions(null, $allowed);
            $clause     = $this->_makeClause($conditions, $allowed);
            $clause     = $this->addedAdditionalConditions($clause);
            
            $this->_setupHeaders();
            
            try{
            
                $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                $outStr    =    $parserObj->parse();  

                $clause[$outStr] = '';
                
                // get param from ORDER screen(ADD PRODUCT ot ADD Non-Product)
                if(!isset($productOptions) || $productOptions->makeSearch){
                    $this->view->tabShow    =   true;
            
                    $result                 =   $this->minder->getSsns($clause, 'index', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                } else {
                    $this->view->tabShow        =   false;
                    $result['data']             =   array();
                    $productOptions->makeSearch =   true;
                }
                
                $this->view->ssns = $result['data'];
            
            } catch(Exception $ex) {
                $this->addError($ex->getMessage());
            }
             
        }
        
        $conditions = $this->_getConditions('calc');
        
        $lines      = $result['data'];
        $numRecords = count($lines);
        $count      = 0;
        $data       = array();
       
        for ($i = 0; $i < $numRecords; $i++) {
            if (false !== array_search($lines[$i]->id, $conditions, true )) {
                $count++;
                $data[] =   $lines[$i];   
            }
        }
        
        $this->view->allSelected = false;
        if($count === $numRecords) {
            $this->view->allSelected = true;
        }
       
        $this->view->data =  $data;
        if ($this->_processReportTo()) {
            return;
        }

        //-- preprocess 'other' fields
        $tempArray = array();
        for ($i=1; $i<21; $i++) {
            $v = $this->minder->getFieldFromSsnGroup('FIELD'.($i));
          
            if (!is_null($v) && !empty($v) && $v != ' ') {
                $tempArray[$i] = $v;
            } else {
                //$tempArray[$i] = 'Other ' . $i;
            }
        }
        $this->view->otherNames = $tempArray;
        
        // getting other X descriptions from SSN_TYPE table instead of SSN_GROUP 
        $ssnType    =   $this->_request->getParam('ssn_type');
        if (!empty($ssnType)) {
            $others_6_10 = $this->minder->getSsnTypeByCode($ssnType); 
         
            for ($i=1; $i<6; $i++) {
                $fieldId    = $i + 5;  
                $field_name = $others_6_10['FIELD' . $fieldId];
                if (!is_null($field_name) && !empty($field_name) && $field_name != ' ') {
                    $this->view->otherNames[$i+5] = $field_name;                                     
                }
            }
        }
        
        $tempArray = array();
        for ($i=1; $i<21; $i++) {
            $v = $this->minder->getFieldFromSsnGroup('DD_OTHER'.($i));
            if (!is_null($v)) {
                $tempArray[$i] = (strtoupper($v) == 'TRUE' ? true : false);
            } else {
                $tempArray[$i] = false;
            }
        }
        // dropdown or not. If true - dropdown
        $this->view->otherType = $tempArray;

        // items for dropdowns
        $tempArray = array();
        foreach ($this->view->otherNames as $key => $val) {
            $clause = array('MATCH_OPERATOR IS NULL' => null,
                            "OTHER_NO = '" . $key . "'" => null);
            $list = $this->minder->getListFromGlobalConditions($clause);
            $tempArray[$key] = minder_array_merge(array('' => ''), $list);
        }
        $this->view->otherDetails = $tempArray;
        //-- end preprocess 'other' fields
        $conditions =   $this->_getConditions();
   
        $this->view->ssnTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnTypeListFromSsnType());
        if (array_key_exists('ssn_type', $conditions)) {
            if ($conditions['ssn_type'] != '') {
                $this->view->varietyList = $this->fillList(null, 'getVarietyList', $conditions['ssn_type']);
                if (array_key_exists('generic', $conditions)) {
                    if ($conditions['generic'] != '') {
                        $this->view->ssnSubTypeList = $this->fillList(null, 'getSsnSubTypeList', array('SSN_SUB_TYPE.GENERIC'  => $conditions['generic'],
                                                                                                       'SSN_SUB_TYPE.SSN_TYPE' => $conditions['ssn_type']
                                                                                                       ));
                    } else {
                        $this->view->ssnSubTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnSubTypeList(array('SSN_SUB_TYPE.SSN_TYPE' => $conditions['ssn_type'])));
                    }
                } else {
                    $this->view->ssnSubTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnSubTypeList(array('SSN_SUB_TYPE.SSN_TYPE' => $conditions['ssn_type'])));
                }
            } else {
                $this->view->varietyList    = array('' => '');
                $this->view->ssnSubTypeList = array('' => '');
            }
        } else {
            $this->view->varietyList    = array('' => '');
            $this->view->ssnSubTypeList = array('' => '');
        }

        $this->view->brandList      = minder_array_merge(array('' => ''), $this->minder->getBrandList());
        $this->view->costCenterList = minder_array_merge(array('' => ''), $this->minder->getCostCentreList());

        $tempArray = $this->minder->getPersonList(array('CO', 'CS', 'IS'));
        if (count($tempArray) > 0) {
            $this->view->supplierIdList = minder_array_merge(array('' => ''), array_combine(array_keys($tempArray), array_keys($tempArray)));
        } else {
            $this->view->supplierIdList = array('' => '');
        }

        $this->view->companyIdList  = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

        $this->view->divisionList   = minder_array_merge(array('' => ''), $this->minder->getDivisionList());

        $this->view->departmentList = minder_array_merge(array('' => ''), $this->minder->getDepartmentList());
        $this->view->requiredQty1 = isset($this->session->conditions[$this->_controller][$this->_action]['required_qty'])
            ? $this->session->conditions[$this->_controller][$this->_action]['required_qty']
            : 0;

        $this->view->qtyProducts1 = isset($this->session->conditions[$this->_controller][$this->_action]['total_qty'])
            ? $this->session->conditions[$this->_controller][$this->_action]['total_qty']
            : 0;

        $this->view->ssnStatusList = $this->minder->getIssnStatusList();

        if($this->minder->isAdmin) {
         $tempArray = array('ssn_id'          => 'SSN',
                            'grn'             => 'GRN',
                            'po_order'        => 'Order No',
                            'po_line'         => 'Order Line No',
                            'brand'           => 'Brand',
                            'model'           => 'Model',
                            'serial_number'   => 'Serial Number',
                            'create_date'     => 'Created Date',
                            'alt_name'        => 'Alternate Name',
                            'legacy_id'       => 'Legacy ID',
                            'old_ssn_id'      => 'Old SSN No',
                            'parent_ssn_id'   => 'Parent SSN');

        } else {
                   $tempArray = array('brand'           => 'Brand',
                                      'model'           => 'Model'
                                     );
        }

        // add other fields to autogenerated autocomplete banding script
        foreach ($this->view->otherNames as $key => $val) {
            if (false == $this->view->otherType) {
                switch ($key) {
                    case 15:
                        $tempArray['other15_date'] = '';
                    break;
                    case 16:
                        $tempArray['other16_date'] = '';
                        break;
                    case 17:
                        $tempArray['other17_qty'] = '';
                        break;
                    case 18:
                        $tempArray['other18_qty'] = '';
                        break;
                    default:
                        $tempArray['other'.$key] = '';
                        break;
                }
            }
        }
        $this->view->searchFields = $tempArray;
        $this->view->conditions   = array_merge($this->_getConditions('calc'), $this->_getConditions());
        
        $this->_postProcessNavigation($result);
        
        $productOptions = null;
    }

    protected function _getSelectedIssns() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $totalSelected = $rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($totalSelected > 0) {
            /**
             * @var Minder_SysScreen_Model_Issn $issnModel
             */
            $issnModel = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            return $issnModel->selectSsn(0, $totalSelected);
        } else {
            return array();
        }
    }

    /**
     * Provides data for edit form and processing submited data
     *
     * @return void
     */
    public function editAction()
    {
        
        if (($from = $this->getRequest()->getPost('from')) && $from == 'issn-lines') {
            $this->_action = 'issn-lines';
            $this->_preProcessNavigation();
        }

        $this->view->formStyle = $this->_minderOptions()->getSsnEditFormStyle();

        $this->session->action          = 'edit';
        $this->session->controller      = 'ssn';
        $this->session->module          = 'warehouse';
        $savedParams                    = array();
        $this->session->disposedItems   = array();
        
        $ssn_id = $this->getRequest()->getParam('edit_ssn_ssn_id');

	$this->view->arrDynamicSsnWidget = $this->minder->getDynamicSsnWidget($ssn_id);
        
        $savedParams['edit_ssn_ssn_id'] = $ssn_id;
        $this->session->savedParams = $savedParams;
        $this->view->pageTitle      = "EDIT SSN:";
        $clause = array("SSN.SSN_ID LIKE ? AND " => $ssn_id);
        $this->view->ssnObj = $this->minder->getSsnsNotForScreen($clause, 'edit');
        $this->view->ssnObj = $this->view->ssnObj[0];
     
        $params = array();
        
        $this->view->issnHeaders = array('SSN_ID'          => 'ISSN',
                                         'WH_ID'           => 'WH.',
                                         'LOCN_ID'         => 'Location',
                                         'PROD_ID'         => 'Product ID',
                                         'CURRENT_QTY'     => 'Curr Qty',
                                         'ISSN_STATUS'     => 'Status',
                                         'INTO_DATE'       => 'Date Into', 
                                         'SSN_CREATE_DATE' => 'Created Date',
                                         'COMPANY_ID'      => 'Company ID',
                                         'SSN_DESCRIPTION' => 'SSN Description'
                                         );
        
        $this->view->headers = $this->view->issnHeaders;

        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'SAVE & RETURN':
                // disose selected items
                $clause     = array('ORIGINAL_SSN = ?' => $ssn_id);
                $conditions = $this->session->conditions[$this->_controller][$this->_action];
                $lines      = $this->minder->getIssns($clause);
                $numRecords = count($lines);
               
                $toDispose      = array();
                $source         = 'SSKKSSKSS';
                $TRILwhId       = 'XX';
                $TROLwhId       = 'FH';

                if($this->getRequest()->getParam('disposed') == 'T') {
                    $selectedIssns = $this->_getSelectedIssns();

                    for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                        if(in_array($lines[$i]->id, $selectedIssns)) {
                            try {
                                    $transaction            =   new Transaction_TROLA();
                                    $transaction->objectId  =   $lines[$i]['SSN_ID'];
                                    $transaction->whId      =   $TROLwhId;
                                    $transaction->locnId    =   $lines[$i]['LOCN_ID'];
                                    $transaction->reference =   'ISSN Disposed';

                                    if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) {
                                            throw new Exception('TROL');
                                    } else {

                                        $transaction            =   new Transaction_TRILA();
                                        $transaction->objectId  =   $lines[$i]['SSN_ID'];
                                        $transaction->whId      =   $TRILwhId;
                                        $transaction->locnId    =   $lines[$i]['LOCN_ID'];
                                        $transaction->reference =   'ISSN Disposed';
                                        if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) {
                                            throw new Exception('TRIL');
                                        }
                                        $toDispose[]    = $lines[$i]['SSN_ID'];
                                    }
                                } catch (Exception $e) {
                                    $this->addError('Error occured while ' . $e->getMessage() . ' transaction for SSN ' . $lines[$i]['SSN_ID']);
                                }
                        }
                    }
	            }
                
                // save disposed items in session
                $this->session->disposedItems = $toDispose;
                
                $params['edit_ssn_ssn_id'] = $ssn_id;
                $updateList = array();
                $tmp        = clone($this->view->ssnObj);
                
                $update = $this->getRequest()->getPost();
                
                if (isset($_FILES) && count($_FILES) > 0) {
                    foreach ($_FILES as $key => $item) {
                        if (null != $item['name']) {
                            $fileData     = file_get_contents($item['tmp_name']);
                            $update[$key] = $fileData;
                            unlink($item['tmp_name']);
                        }
                    }
                }

                $this->view->ssnObj->save($update);
                
                
                if (($listOfFields = $this->_checkSsnMandatoryFields($this->view->ssnObj))) {
                    $this->addWarning('Required fields ' . implode(", ", $listOfFields) . ' is EMPTY');
                    $log = Zend_Registry::get('logger');
                    $log->info('Required fields ' . implode(", ", $listOfFields) . ' is EMPTY');
                    $this->_redirector->setCode(303)
                                      ->goto('edit',
                                             'ssn',
                                             'warehouse',
                                             $params);
                    break;
                }
                foreach ($this->view->ssnObj->items as $key => $val) {
                    $updateList[$key] = $val;
                }
            
                if ($this->_saveSSN($updateList)) {
                    $this->_redirector->setCode(303)
                                      ->goto('index',
                                             'ssn2',
                                             'warehouse',
                                             $params);
                } else {
                    $this->_redirector->setCode(303)
                                      ->goto('edit',
                                             'ssn',
                                             'warehouse',
                                             $params);
                }
                break;
                
                
            case 'SAVE & PRINT ALL':
            	
		                // dispose selected items
		                $clause     = array('ORIGINAL_SSN = ?' => $ssn_id);
		                $conditions = $this->session->conditions[$this->_controller][$this->_action];
		                $lines      = $this->minder->getIssns($clause);
		                $numRecords = count($lines);
		               
		                $toDispose      = array();
                        $countDisposed  = 0;  
                        $source         = 'SSKKSSKSS';
		                $TRILwhId       = 'XX';
		                $TROLwhId       = 'FH';

		                if($this->getRequest()->getParam('disposed') == 'T') {
                            $selectedIssns  = $this->_getSelectedIssns();

                            for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                                if (in_array($lines[$i]->id, $selectedIssns)) {
                                    try {
                                            $transaction            =   new Transaction_TROLA();
                                            $transaction->objectId  =   $lines[$i]['SSN_ID'];
                                            $transaction->whId      =   $TROLwhId;
                                            $transaction->locnId    =   $lines[$i]['LOCN_ID'];
                                            $transaction->reference =   'ISSN Disposed';

                                            if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) {
                                                    throw new Exception('TROL');
                                            } else {

                                                $transaction            =   new Transaction_TRILA();
                                                $transaction->objectId  =   $lines[$i]['SSN_ID'];
                                                $transaction->whId      =   $TRILwhId;
                                                $transaction->locnId    =   $lines[$i]['LOCN_ID'];
                                                $transaction->reference =   'ISSN Disposed';
                                                if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) {
                                                    throw new Exception('TRIL');
                                                }
                                                $toDispose[]    = $lines[$i]['SSN_ID'];
                                            }
                                        } catch (Exception $e) {
                                            $this->addError('Error occured while ' . $e->getMessage() . ' transaction for SSN ' . $lines[$i]['SSN_ID']);
                                        }
                                }
                            }
		                }
		                
		                // save disposed items in session
		                $this->session->disposedItems = $toDispose;
		                
		                $params['edit_ssn_ssn_id'] = $ssn_id;
		                $updateList = array();
		                $tmp        = clone($this->view->ssnObj);
		                
		                $update = $this->getRequest()->getPost();
		                if (isset($_FILES) && count($_FILES) > 0) {
		                    foreach ($_FILES as $key => $item) {
		                        if (null != $item['name']) {
		                            $fileData     = file_get_contents($item['tmp_name']);
		                            $update[$key] = $fileData;
		                            unlink($item['tmp_name']);
		                        }
		                    }
		                }
		                $this->view->ssnObj->save($update);
		                if (($listOfFields = $this->_checkSsnMandatoryFields($this->view->ssnObj))) {
		                    $this->addWarning('Required fields ' . implode(", ", $listOfFields) . ' is EMPTY');
		                    $log = Zend_Registry::get('logger');
		                    $log->info('Required fields ' . implode(", ", $listOfFields) . ' is EMPTY');
		                    $this->_redirector->setCode(303)
		                                      ->goto('edit',
		                                             'ssn',
		                                             'warehouse',
		                                             $params);
		                    break;
		                }
		                foreach ($this->view->ssnObj->items as $key => $val) {
		                    $updateList[$key] = $val;
		                }
		               
		                
		                
		                $this->_saveSSN($updateList);
		                
		                
		                // get selected ssn's to print
		                $ssn_ids = array();
		                
		                $ssn_ids[] = $this->view->ssnObj['SSN_ID']; // add that we just edited
		                
		                $params = $this->getRequest()->getParams();
		                foreach ($params as $key => $value) {
		                	if ($key == $value) {
		                		$ssn_ids[] = $value;
		                	}
		                }
		                
		                $printerObj = $this->minder->getPrinter();
		                
						$clause = array('ORIGINAL_SSN = ?' => $this->view->ssnObj->id);
				        $issns  = $this->minder->getIssns($clause);		                
		                
				        $result = false;
				        $count  = 0;
						if($this->minder->defaultControlValues['GENERATE_LABEL_TEXT'] == 'T'){
                            foreach($issns as $line) {
                               try{
                                    $result    =    $printerObj->printIssnLabel($line);
                                    if(!$result){
                                        $this->addError('Error while print label(s)');
                                        break;     
                                    }             
                               } catch(Exception $ex){
                                       $this->addError($ex->getMessage());
                                       break;    
                               }
                               $count++;
                            }
                            if($result){
                                $this->addMessage($count . ' label(s) printed successfully');
                            }
                        } else {
                            foreach($issns as $line) {
                               $issnList = $this->minder->getIssnForPrint($line->id);
                               try{
                                    $result    =    $printerObj->printIssnLabel($issnList);
                                    if($result['RES'] < 0){
                                        $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
                                        break;     
                                    }             
                               } catch(Exception $ex){
                                       $this->addError($ex->getMessage());
                                       break;    
                               }
                               $count++;
                            }
                            if($result['RES'] >= 0){
                                $this->addMessage($count . ' label(s) printed successfully');
                            }
                        }
                        
                        $this->_redirector->setCode(303)->goto('index', 'ssn2', 'warehouse', array());
            	
            	break;
            
            case 'REPORT: CSV':
                
                $conditions =     $this->session->conditions[$this->_controller][$this->_action];
                $clause     =     array('ORIGINAL_SSN = ?' => $this->view->ssnObj->id);
                $issns      =     $this->minder->getIssns($clause);
                
                $data       =   array();
                foreach ($issns as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                         $data[]    = $line->items;
                    }
                }
                $this->view->data   =   $data;
                $this->_processReportTo('REPORT: CSV');
                break;
             
             case 'PRINT LABEL':
                $conditions =     $this->session->conditions[$this->_controller][$this->_action];
                $clause     =     array('ORIGINAL_SSN = ?' => $this->view->ssnObj->id);
                $issns      =     $this->minder->getIssns($clause);
                $printerObj =     $this->minder->getPrinter();
                $count        =     0;
           
                if($this->minder->defaultControlValues['GENERATE_LABEL_TEXT'] == 'T'){
                    foreach($issns as $line) {
                        if (false !== array_search($line->id, $conditions)) {
                           try{
                                $result    =    $printerObj->printIssnLabel($line);
                                if(!$result){
                                    $this->addError('Error while print label(s)');
                                    break;    
                                }             
                           } catch(Exception $ex){
                                   $this->addError($ex->getMessage());
                                   break;    
                           }
                           $count++;
                           }
                    }
                    
                    $this->addMessage($count . ' label(s) printed successfully');
                
                } else {
                    foreach($issns as $line) {
                        if (false !== array_search($line->id, $conditions)) {
                           $issnList = $this->minder->getIssnForPrint($line->id);
                           try{
                                $result    =    $printerObj->printIssnLabel($issnList);
                                if($result['RES'] < 0){
                                    $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
                                    break;    
                                }             
                           } catch(Exception $ex){
                                   $this->addError($ex->getMessage());
                                   break;    
                           }
                           $count++;
                        }
                    }
                    if($result['RES'] >= 0){
                        $this->addMessage($count . ' label(s) printed successfully');
                    }    
                }
                break;
            
            case 'CANCEL CHANGES':
                $from   =   $this->_getParam('from');
                $params =   array('from' => $from);
                
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'ssn2', 'warehouse', $params);
                break;
            default:
                break;
        }
        
        $this->view->loanSafetyTagColor = minder_array_merge(array('' => ''), $this->minder->getOptionsList('SAFETY_TAG'));
        if (empty($this->view->loanSafetyTagColor)) {
        	$this->view->loanSafetyTagColor = array();;
        }
   
        $this->_preProcessNavigation();
        //-- end process input navigation values

        $this->view->other1Name  = $this->minder->getFieldFromSsnGroup('FIELD21');

        $clause             = array('ORIGINAL_SSN = ?' => $this->view->ssnObj->id);
       
        $pageSelector = $this->session->navigation[$this->_controller]['issn-lines']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['issn-lines']['show_by'];
        
        if(is_null($pageSelector) || is_null($showBy)){
            $pageSelector = 0;
            $showBy       = 15;    
        }
        
        $data         = $this->minder->getIssns($clause, 'edit', $pageSelector, $showBy);
        
        // ssn conditions handler
        
        $this->view->ssnConditionHeaders = array('CODE'=>'Condition',
        										  'DESCRIPTION'=>'Description',
        										  'ORIGINAL_TEST'=>'Status',
        										  'LAST_UPDATE_DATE'=>'Date updated');
        
        $this->view->ssnConditionsA		  = $this->minder->getProductConditions($ssn_id, 'A');
        $this->view->ssnConditionsB		  = $this->minder->getProductConditions($ssn_id, 'B');
        $this->view->ssnConditionsC		  = $this->minder->getProductConditions($ssn_id, 'C');
        $this->view->ssnConditionsD		  = $this->minder->getProductConditions($ssn_id, 'D');
        $this->view->ssnConditionsE		  = $this->minder->getProductConditions($ssn_id, 'E');
        
        $this->view->ssnConditionNameA	  = $this->minder->getFieldFromSsnGroup('FIELD1');
        $this->view->ssnConditionNameB	  = $this->minder->getFieldFromSsnGroup('FIELD2');
        $this->view->ssnConditionNameC	  = $this->minder->getFieldFromSsnGroup('FIELD3');
        $this->view->ssnConditionNameD	  = $this->minder->getFieldFromSsnGroup('FIELD4');
        $this->view->ssnConditionNameE	  = $this->minder->getFieldFromSsnGroup('FIELD5');
        
       	$this->view->others = array(
       		null,
       		$this->minder->getFieldFromSsnGroup('DD_OTHER1'),
       		$this->minder->getFieldFromSsnGroup('DD_OTHER2'),
       		$this->minder->getFieldFromSsnGroup('DD_OTHER3'),
       		$this->minder->getFieldFromSsnGroup('DD_OTHER4'),
       		$this->minder->getFieldFromSsnGroup('DD_OTHER5'),
       	);
       	
       	        
        $this->view->ssnConditionDescriptions = $this->minder->getProductConditionTypes();
        
        $this->view->ssnType1Name = $this->minder->getFieldFromSsnGroup('FIELD_SSN_TYPE');  
        $this->view->ssnType2Name = $this->minder->getFieldFromSsnGroup('FIELD_GENERIC');  
        $this->view->ssnType3Name = $this->minder->getFieldFromSsnGroup('FIELD_SUB_TYPE');  
        $this->view->brandName    = $this->minder->getFieldFromSsnGroup('FIELD_BRAND');  
        $this->view->modelName    = $this->minder->getFieldFromSsnGroup('FIELD_MODEL');
        
        $this->view->dimensionList= minder_array_merge(array('' => ''), $this->minder->getUoms('DT'));  
        $this->view->weightList   = minder_array_merge(array('' => ''), $this->minder->getUoms('WT'));  
        
        // rest
        $this->_setupViewList($this->view->ssnObj->items['SSN_TYPE']);
        $this->_postProcessNavigation($data);
        
        $this->view->issns  =   $data['data'];
        $this->view->ssnId  =   $ssn_id;

        $this->view->wasErrors = false;
        $this->initIssnListAction();
        if (count($this->view->errors) > 0) {
            foreach ($this->view->errors as $errorMessage)
                $this->addError($errorMessage);
            $this->view->wasErrors = true;
        }

        // provide data for conditions types testing !!!
        $conditionsPaginator    = array();
        $conditionsMap          = array('A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5');

        foreach ($conditionsMap as $key => $val) {
            $paginator          = new stdClass();
            $paginator->total   = count($this->minder->getProductConditionsAll($ssn_id, $key));
            $paginator->from    = 1; //
            $paginator->to      = 5; // TODO: recalculate these fields due to current settings
            $paginator->view_by = 5; //
            $paginator->pages   = array();

            $pageCount = ceil($paginator->total / $paginator->view_by);
            for ($i = 0; $i < $pageCount; $i++) {
                $paginator->pages[$i] = $i + 1;
            }

            $conditionsPaginator[$key] = $paginator;
        }

        $this->view->conditionsPaginator = $conditionsPaginator;
   }

    protected function _initDatabase() {
        $dsn = Minder::$dbLiveDsn;
        $dsnArray = explode(':', $dsn);
        $name = isset($dsnArray[1]) ? $dsnArray[1] : $dsnArray[0];
        $host = isset($dsnArray[1]) ? $dsnArray[0] : 'localhost';
        $db = Zend_Db::factory('Firebird', array('adapterNamespace' => 'ZendX_Db_Adapter', 'host' => $host, 'username' => Minder::$dbUser, 'password' => Minder::$dbPass, 'dbname' => $name));
        Zend_Db_Table::setDefaultAdapter($db);
        Minder_Db_SysScreenTable::setDefaultAdapter(Zend_Db::factory('SysScreen', array('adapterNamespace' => 'Minder_Db_Adapter')));
    }

    public function edit2Action() {
        try {

            $this->view->pageTitle      = "EDIT SSN:";
            $this->initIssnListAction();

            $this->_initDatabase();

            $ssnId = $this->getRequest()->getParam('edit_ssn_ssn_id');

            $formBuilder = new Minder_Page_FormBuilder();
            $ssnEditForm = new Minder_Form($formBuilder->buildEditForm('SSN'));

            $formFiller  = new Minder_Page_FormFiller();
            $ssnEditForm = $formFiller->fillDefaults($ssnEditForm, 'SSN', Minder_Page::FORM_TYPE_EDIT_FORM);
            $ssnMapper   = new Minder_Page_SysScreenMapper_Default('SSN', Minder_Page::FORM_TYPE_EDIT_FORM);

            $findResult = $ssnMapper->find($ssnId);


            if ($findResult->count() > 0) {
                $foundSsn   = $findResult->current();
                $ssnEditForm->populate($foundSsn->toArray());
            } else {
                $foundSsn   = $ssnMapper->newRecord();
            }

            $ssnEditForm = $formFiller->fillMultiOptions($ssnEditForm, 'SSN', Minder_Page::FORM_TYPE_EDIT_FORM);

            $this->view->ssnId       = $ssnId;
            $this->view->ssnEditForm = $ssnEditForm;
            $this->view->foundSsn    = $foundSsn;

        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
    }
   
   public function addconditionAction()
   {
       $ssn_id         = $this->getRequest()->getParam('ssn_id');
       $code           = $this->getRequest()->getParam('code');
       $description    = $this->getRequest()->getParam('description');
       $original_test  = $this->getRequest()->getParam('original_test');

       $response = new Minder_JSResponse();

       if (empty($description))
           $description = '';

       $original_test = ($this->minder->isAdmin) ? 'O' : 'S';

       $row         = new stdClass();
       if (false === ($result = $this->minder->addCondition($ssn_id, $code, $description, $original_test))) {
           $response->errors[] = $this->minder->lastError;
       }
       else {
           $response->messages[] = 'Condition added';

           $conditionsMap   = array('A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5');
           $conditionsList  = $this->minder->getProductConditions($ssn_id, $code);
           $line            = $conditionsList[sizeof($conditionsList) - 1];
           $conditionName   = $this->minder->getFieldFromSsnGroup('FIELD' . $conditionsMap[$code]);

           $conditionStatus = '';

           if ($original_test == 'S')
               $conditionStatus = 'Subsequent';
           if ($original_test == 'O')
               $conditionStatus = 'Original';

           $row->id     = $line['SSN_ID'];
           $row->name   = $conditionName;
           $row->desc   = $line['DESCRIPTION'];
           $row->status = $conditionStatus;
           $row->date   = $line['LAST_UPDATE_DATE'];
           $row->code   = $line['CODE'];
       }

       $ssn    = current($this->minder->getSsnsNotForScreen(array("SSN.SSN_ID = ? AND " => $ssn_id), 'edit'));
       echo json_encode(array('response' => $response, 'row' => $row, 'ssn' => $ssn->items));
   }

    public function deleteconditionAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $response       = new Minder_JSResponse();

        $ssn_id         = $this->getRequest()->getParam('ssn_id');
        $code           = $this->getRequest()->getParam('code');
        $info           =  $this->getRequest()->getParam('info');

        $line = array();

        list($code, $line['DESCRIPTION'], $line['ORIGINAL_TEST']) = explode(':', $info);

        // try to perform delete condition
        if (false === ($result = $this->minder->deleteCondition($ssn_id, $code, $line['DESCRIPTION'], $line['ORIGINAL_TEST']))) {
            $response->errors[] = $this->minder->lastError;
        }

        else {
            $response->messages[] = 'Condition deleted';
        }

        $ssn = current($this->minder->getSsnsNotForScreen(array("SSN.SSN_ID = ? AND " => $ssn_id), 'edit'));;
        $response->ssn = $ssn->items;
        echo json_encode($response);
    }

    public function reportConditionsAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $type   = $this->getRequest()->getParam('type');
        $code   = $this->getRequest()->getParam('code');
        $ssn_id = $this->getRequest()->getParam('ssn_id');

        $conditionsMap  = array('A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5');
        $conditionsList = ($code == 'All') ? $this->minder->getProductConditions($ssn_id) :
                                             $this->minder->getProductConditions($ssn_id, $code);
        $reportData     = array();
        $row            = array();

        foreach($conditionsList as $node) {
            $row['CONDITION']   = $this->minder->getFieldFromSsnGroup('FIELD' . $conditionsMap[$node['CODE']]);
            $row['DESCRIPTION'] = $node['DESCRIPTION'];
            if ($node['ORIGINAL_TEST'] == 'O')
                $row['STATUS'] = 'Original';
            if ($node['ORIGINAL_TEST'] == 'S')
                $row['STATUS'] = 'Subsequent';
            $row['DATE'] = $node['LAST_UPDATE_DATE'];

            array_push($reportData, $row);
        }

        $this->view->data = $reportData;

        $this->view->headers = array(
            'CONDITION'     =>  'Condition',
            'DESCRIPTION'   =>  'Description',
            'STATUS'        =>  'Status',
            'DATE'          =>  'Date updated'
        );

        if ($type == 'CSV') {
            $this->getResponse()->setHeader('Content-Type', 'text/csv')
                 ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
            $this->render('report-csv');

            return true;
        }

        if ($type == 'XLS') {
            $xls = new Spreadsheet_Excel_Writer();
            $xls->send('report.xls');
            $this->view->xls = $xls;
            $this->render('report-xls');

            return true;
        }
        return false;
    }

    public function refreshDataAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $code       = $this->getRequest()->getParam('code');
        $ssn_id     = $this->getRequest()->getParam('ssn_id');
        $show_by    = $this->getRequest()->getParam('show_by');
        $pages      = $this->getRequest()->getParam('pages');

        $refreshList = ($code == 'All') ? $this->minder->getProductConditions($ssn_id, null, $show_by, $pages) :
                                          $this->minder->getProductConditions($ssn_id, $code, $show_by, $pages);

        $refreshData    = array();
        $conditionsMap  = array('A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5');

        foreach($refreshList as $item) {
            $refreshObj         = new stdClass();
            $refreshObj->id     = $item['SSN_ID'];
            $refreshObj->name   = $this->minder->getFieldFromSsnGroup('FIELD' . $conditionsMap[$item['CODE']]);
            $refreshObj->desc   = $item['DESCRIPTION'];

            if ($item['ORIGINAL_TEST'] == 'O') {
                $refreshObj->status = 'Original';
            }
            if ($item['ORIGINAL_TEST'] == 'S')
                $refreshObj->status = 'Subsequent';

            $refreshObj->date   = $item['LAST_UPDATE_DATE'];
            $refreshObj->code   = $item['CODE'];
            $refreshObj->test   = $item['ORIGINAL_TEST'];

            array_push($refreshData, $refreshObj);
        }

        // update pagination
        $paginator          = new stdClass();
        $paginator->total   = ($code == 'All') ? count($this->minder->getProductConditionsAll($ssn_id)) :
                                                 count($this->minder->getProductConditionsAll($ssn_id, $code));
        $offset             = ($paginator->total < $pages * $show_by) ? 0 : $pages * $show_by;
        $paginator->from    = $offset + 1;
        $to                 = ($paginator->from + $show_by) - 1;
        $paginator->to      = ($to < $paginator->total) ? $to : $paginator->total;
        $paginator->view_by = $show_by;
        $paginator->pages   = array();

        $pageCount = ceil($paginator->total / $paginator->view_by);
        for ($i = 0; $i < $pageCount; $i++) {
            $paginator->pages[$i] = $i + 1;
        }

        $paginator->selected = $pages;

        echo json_encode(array('rows' => $refreshData, 'paginator' => $paginator));
    }

    public function addAction()
    {
        $this->view->formStyle = $this->_minderOptions()->getSsnEditFormStyle();
  
        $this->view->pageTitle = "ADD SSN:";
        $this->view->action    = $this->getRequest()->getActionName();
        $tmp = new SsnLine();
        $this->minder->createSsnLine($tmp);
        $this->view->ssnObj    = $tmp;

        $params = array();
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'SAVE & RETURN':
                $updateList = array();
                $tmp = $this->view->ssnObj;
                $result = true;

                if (false != ($statusSsn = $this->minder->getListByField("CONTROL.NEW_SSN_STATUS"))) {
                    $statusSsn = $statusSsn['ST'];
                } else {
                    $this->view->flash = 'Required fields SSN_STATUS can\'t be read from CONTROL.NEW_SSN_STATUS';
                    break;
                }

	$new1 = array();
	$new1 = $this->getRequest()->getPost();

/***date update***/
            $session = new Zend_Session_Namespace();
            $tz_from=$session->BrowserTimeZone;


	 foreach($new1 as $key=>$val){
              
 if(DateTime::createFromFormat('Y-m-d H:i:s', $val)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val)!==FALSE) {


                            $datetimet=$val;
                            $tz_tot='UTC';
                            $format='Y-m-d H:i:s';
                            $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                            $dtt->setTimeZone(new DateTimeZone($tz_tot));

                           $new1[$key]=$dtt->format($format);

                             }

            }
              

/***date update***/
                $tmp->save($new1);

                $tmp->items['STATUS_SSN']  = $statusSsn;
                $tmp->items['CURRENT_QTY'] = $tmp->items['PURCHASE_QTY'];
                if ($listOfFields = $this->_checkSsnMandatoryFields($tmp)) {
                    $this->view->flash     = 'Required fields ' . implode(", ", $listOfFields) . ' is EMPTY';
                    break;
                }
                $transaction           = new Transaction_AUOBA();
                $transaction->ssnId = $tmp->items['SSN_ID'];
                $transaction->whId     = $tmp->items['WH_ID'];
                $transaction->locnId   = $tmp->items['LOCN_ID'];

                $currentResult         = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSSSSKS');
                $result                = $result && $currentResult;
                if (false == $currentResult) {
                    $this->view->flash = $this->minder->lastError;
                    break;
                } else {
                    //list($temp, $tmp->id) = explode("|", $currentResult);
                    $tmp->id            = $tmp->items['SSN_ID'];
                    $this->view->ssnObj = $tmp;
                }
                foreach ($tmp->items as $key => $val) {
                    if (null != $val) {
                        $updateList[$key] = $val;
                    }
                }

                if (false != $this->_saveSSN($updateList)) {
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('index',
                                             'ssn2',
                                             'warehouse',
                                             $params);
                } else {
                    $this->_redirector->setCode(303)
                                      ->goto('add',
                                             'ssn',
                                             'warehouse',
                                             $params);
                }
                break;
            case 'CANCEL CHANGES':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('index',
                                         'ssn2',
                                         'warehouse',
                                         $params);
                break;
            default:
                break;
        }
        $this->view->issns = array();

        $this->view->ssnType1Name = $this->minder->getFieldFromSsnGroup('FIELD_SSN_TYPE');
        $this->view->ssnType2Name = $this->minder->getFieldFromSsnGroup('FIELD_GENERIC');
        $this->view->ssnType3Name = $this->minder->getFieldFromSsnGroup('FIELD_SUB_TYPE');
        $this->view->brandName    = $this->minder->getFieldFromSsnGroup('FIELD_BRAND');
        $this->view->modelName    = $this->minder->getFieldFromSsnGroup('FIELD_MODEL');

        $this->_setupViewList();
//        $this->render('edit');
    }

    public function createSsnAction()
    {
        $this->view->formStyle = $this->_minderOptions()->getSsnEditFormStyle();
        $this->view->pageTitle = "CREATE SSN:";
        $this->view->action = $this->getRequest()->getActionName();
        $tmp = new SsnLine();
        
        $this->minder->createSsnLine($tmp);
        $this->view->ssnObj = $tmp;
        $params = array();
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'SAVE & RETURN':
                $updateList = array();
                $tmp = $this->view->ssnObj;
                $result = true;

                if (false != ($statusSsn = $this->minder->getListByField("CONTROL.NEW_SSN_STATUS"))) {
                    $statusSsn = $statusSsn['ST'];
                } else {
                    $this->view->flash = 'Required fields SSN_STATUS can\'t be read from CONTROL.NEW_SSN_STATUS';
                    break;
                }
$new = array();
$new = $this->getRequest()->getPost();


        

/***date update***/
            $session = new Zend_Session_Namespace();
            $tz_from=$session->BrowserTimeZone;


	 foreach($new as $key=>$val){  
             
 if(DateTime::createFromFormat('Y-m-d H:i:s', $val)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val)!==FALSE) {


                            $datetimet=$val;
                            $tz_tot='UTC';
                            $format='Y-m-d H:i:s';
                            $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                            $dtt->setTimeZone(new DateTimeZone($tz_tot));

                            $new[$key]=$dtt->format($format);

                             }

            }
   
/***date update***/



                $tmp->save($new);
                $tmp->items['STATUS_SSN']  = $statusSsn;
                $tmp->items['CURRENT_QTY'] = $tmp->items['PURCHASE_QTY'];
                if ($listOfFields = $this->_checkSsnMandatoryFields($tmp)) {
                    //$this->addError('Required fields ' . implode(", ", $listOfFields) . ' is EMPTY');
                    $this->view->flash = 'Required fields ' . implode(", ", $listOfFields) . ' is EMPTY';
                    break;
                }
                $transaction         = new Transaction_AUOBC();
                $transaction->whId   = $tmp->items['WH_ID'];
                $transaction->locnId = $tmp->items['LOCN_ID'];

                $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSSSSKS');
                $result                  = $result && $currentResult;
                if (false == $currentResult) {
                    $this->view->flash = $this->minder->lastError;
                    break;
                } else {
                    //list($temp, $tmp->id) = explode("|", $currentResult);
                    list($tmp->id, ) = explode("|", $currentResult);
                    $tmp->items['SSN_ID'] = $tmp->id;
                    $this->view->ssnObj   = $tmp;
                }

                foreach ($tmp->items as $key => $val) {
                    if ($val != null) {
                        $updateList[$key] = $val;
                    }
                }

                if (false != $this->_saveSSN($updateList)) {
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('index',
                                             'ssn2',
                                             'warehouse',
                                             $params);
                } else {
                    $params['edit_ssn_ssn_id'] = $tmp->id;
                    $this->_redirector->setCode(303)
                                      ->goto('edit',
                                             'ssn',
                                             'warehouse',
                                             $params);
                }
                break;
            case 'CANCEL CHANGES':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('index',
                                         'ssn2',
                                         'warehouse',
                                         $params);
                break;
            default:
                break;
        }
        $this->view->issns = array();

        $this->view->hazardStatus = array();
        $this->view->hazardStatus = $this->minder->getExtOptionsList('HAZ_STATUS');
        
        $this->view->dimensionList= minder_array_merge(array('' => ''), $this->minder->getUoms('DT'));  
        $this->view->weightList   = minder_array_merge(array('' => ''), $this->minder->getUoms('WT'));
        
        $this->_setupViewList();
        //$this->render('edit');
    }

    /**
     * Provides data for autocomplete fields
     *
     * @return void
     */
    public function lookupAction()
    {
        $tdata = array();
        $param = $this->getRequest()->getParam('q');

        $src = $this->getRequest()->getParam('field');
        $field = strtoupper($src);
        switch ($src) {
            case 'home_locn_id':
                $tdata = $this->minder->getLocationList($param);
                break;

            case 'ssn_id':
            case 'company_id':
            case 'cost_centre':
            case 'supplier_id':
            case 'division_id':
            case 'department_id':
            case 'grn':
            case 'po_order':
            case 'po_line':
            case 'ssn_type':
            case 'generic':
            case 'ssn_sub_type':
            case 'brand':
            case 'model':
            case 'serial_number':
            case 'ssn_description':
            case 'create_date':
            case 'alternate_name':
            case 'legacy_id':
            case 'old_ssn_no':
            case 'parent_ssn':
            default:
                $tdata = $this->minder->getFieldListFromSsn($field, $param);
                break;
        }
        /*
        if (count($tdata) > 10) {
            $tdata = array_slice($tdata, 0, 10, true);
        }
        */
        $this->view->data = $tdata;
    }

    /**
     * Provides data for dynamic updates page
     *
     * @return void
     */
    public function seekAction()
    {
        $tdata = array();
        $param = $this->getRequest()->getParam('q');

        $src = strtolower($this->getRequest()->getParam('field'));
        switch ($src) {
            case "wh_id":
                $tdata = $this->minder->getLocationListByWhID($this->getRequest()->getParam('wh_id'));
                break;
            case 'ssn_type':
                $tdata = $this->minder->getSsnTypeListFromSsnType(trim($param));
                break;
            case 'generic':
                $value = $this->getRequest()->getParam('ssn_type');
                if(!empty($value)) {
                    $tdata = $this->minder->getVarietyList($value);
                }
                break;
            case 'ssn_sub_type':
                $generic = $this->getRequest()->getParam('generic');
                $ssnType = $this->getRequest()->getParam('ssn_type');   
                if (!empty($generic) && !empty($ssnType)) {
                    $tdata = $this->minder->getSsnSubTypeList(array('SSN_SUB_TYPE.GENERIC '  => $this->getRequest()->getParam('generic'),
                                                                    'SSN_SUB_TYPE.SSN_TYPE ' => $this->getRequest()->getParam('ssn_type')
                                                                   ));
                }
                break;
            default:
                break;
        }
        if (strtolower($this->getRequest()->getParam('slice')) == 'yes') {
            if (count($tdata) > 10) {
                $tdata = array_slice($tdata, 0, 10, true);
            }
        }

        $this->view->data = $tdata;
    }

    public function calcAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $action = $this->getRequest()->getParam('inAction');
        $method = $this->getRequest()->getParam('method');
        $value  = $this->getRequest()->getParam('value');
        $from   = $this->getRequest()->getParam('from');

        switch (strtolower($action)) {
            case 'edit':
                $clause = array('ORIGINAL_SSN = ?' => $this->getRequest()->getParam('edit_ssn_ssn_id'));
                $lines  = $this->minder->getIssns($clause);
                break;
            case 'index':
                // Setup conditions.
               $allowed = array(
                                    'ssn_id'          => 'SSN.SSN_ID LIKE ? AND ',
                                    'company_id'      => 'SSN.COMPANY_ID LIKE ? AND ',
                                    'cost_center'     => 'SSN.COST_CENTER LIKE ? AND ',
                                    'supplier_id'     => 'SSN.SUPPLIER_ID LIKE ? AND ',
                                    'division_id'     => 'SSN.DIVISION_ID LIKE ? AND ',
                                    'department_id'   => 'SSN.DEPARTMENT_ID ? AND ',
                                    'grn'             => 'SSN.GRN LIKE ? AND ',
                                    'po_order'        => 'SSN.PO_ORDER LIKE ? AND ',
                                    'po_line'         => 'SSN.PO_LINE LIKE ? AND ',
                                    'ssn_type'        => 'SSN.SSN_TYPE LIKE ? AND ',
                                    'generic'         => 'SSN.GENERIC LIKE ? AND ',
                                    'ssn_sub_type'    => 'SSN.SSN_SUB_TYPE LIKE ? AND ',
                                    'brand'           => 'SSN.BRAND LIKE ? AND ',
                                    'model'           => 'SSN.MODEL LIKE ? AND ',
                                    'serial_number'   => 'SSN.SERIAL_NUMBER LIKE ? AND ',
                                    'ssn_description' => '',
                                    'created_date_from' => 'SSN.CREATE_DATE >= ? AND ',
                                    'created_date_to'   => 'SSN.CREATE_DATE <= ? AND ',
                                    'alternate_name'  => 'SSN.ALT_NAME LIKE ? AND',
                                    'legacy_id'       => 'SSN.LEGACY_ID LIKE ? AND ',
                                    'old_ssn_no'      => 'SSN.OLD_SSN_ID LIKE ? AND ',
                                    'parent_ssn'      => 'SSN.PARENT_SSN_ID LIKE ? AND ',
                                    'other1'          => 'SSN.OTHER1 LIKE ? AND ',
                                    'other2'          => 'SSN.OTHER2 LIKE ? AND ',
                                    'other3'          => 'SSN.OTHER3 LIKE ? AND ',
                                    'other4'          => 'SSN.OTHER4 LIKE ? AND ',
                                    'other5'          => 'SSN.OTHER5 LIKE ? AND ',
                                    'other6'          => 'SSN.OTHER6 LIKE ? AND ',
                                    'other7'          => 'SSN.OTHER7 LIKE ? AND ',
                                    'other8'          => 'SSN.OTHER8 LIKE ? AND ',
                                    'other9'          => 'SSN.OTHER9 LIKE ? AND ',
                                    'other10'         => 'SSN.OTHER10 LIKE ? AND ',
                                    'other11'         => 'SSN.OTHER11 LIKE ? AND ',
                                    'other12'         => 'SSN.OTHER12 LIKE ? AND ',
                                    'other13'         => 'SSN.OTHER13 LIKE ? AND ',
                                    'other14'         => 'SSN.OTHER14 LIKE ? AND ',
                                    'other15_date'    => 'SSN.OTHER17_DATE LIKE ? AND ',
                                    'other16_date'    => 'SSN.OTHER17_DATE LIKE ? AND ',
                                    'other17_qty'     => 'SSN.OTHER17_QTY LIKE ? AND ',
                                    'other18_qty'     => 'SSN.OTHER18_QTY LIKE ? AND ',
                                    'other19'         => 'SSN.OTHER19 LIKE ? AND ',
                                    'other20'         => 'SSN.OTHER20 LIKE ? AND ',
                                    'last_calibrate_check_date_from' => 'SSN.LOAN_LAST_CALIBRATE_CHECK_DATE >= ? AND ',
                                    'last_calibrate_check_date_to'   => 'SSN.LOAN_LAST_CALIBRATE_CHECK_DATE <= ? AND ',
                                    'last_safety_check_date_from'    => 'SSN.LOAN_LAST_SAFETY_CHECK_DATE <= ? AND ',
                                    'last_safety_check_date_to'      => 'SSN.LOAN_LAST_SAFETY_CHECK_DATE <= ? AND '
                                );
                                
                $conditions = $this->_getConditions('index');
                $clause     = $this->_makeClause($conditions, $allowed);
               
                $show_by      = $this->session->navigation[$this->_controller]['index']['show_by'];
                $pageselector = $this->session->navigation[$this->_controller]['index']['pageselector'];
                // parse fields SSN_DESCRIPTION
                try{
            
                    $parserObj =    new Parser($conditions['ssn_description'], 'SSN_DESCRIPTION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
            
                } catch(Exception $ex) {}
             
                //-- get appropriate lines
                $lines = $this->minder->getSsns($clause, 'edit', $pageselector, $show_by);
                $lines = $lines['data'];
     
            default:
                break;
        }

        $conditions =   $this->_markSelected($lines, $id, $value, $method, strtolower($action));
        $numRecords =   count($lines);
        $count      =   0;
        $totalQty   =   0;  

        $filterForTotalQty = array();
        // Calculate the number of selected items.
        for ($i = 0, $count = 0; $i < $numRecords; $i++) {
            if (array_key_exists($lines[$i]->id, $conditions) && false !== array_search($lines[$i]->id, $conditions, true )) {
                $count++;
                $totalQty   +=$lines[$i]->items['CURRENT_QTY'];   
                $filterForTotalQty[] = $lines[$i]->id;
            }
        }
        
        if (count($filterForTotalQty) > 0){
            $totalQty = $this->minder->getFieldSum('SSN', 'SSN.CURRENT_QTY', array('SSN.SSN_ID IN (' . substr(str_repeat('?, ', count($filterForTotalQty)), 0, -2) . ')' => $filterForTotalQty));
        } else {
            //nothing is selected
            $totalQty = 0;
        }
    
        $data = array();
        $data['selected_num']   = $count;
        $data['total_qty']      = $totalQty;
        $this->view->data       = $data;
  
    }

    /**
     * AJAX backend for check SSN
     */
    public function existSsnAction()
    {

        $ssnObj = $this->minder->getSsns(array("SSN.SSN_ID = ? AND " => $this->getRequest()->getParam('value')), 'edit');
        if ($ssnObj) {
            $this->view->data = $this->getRequest()->getParam('value');
        } else {
            $this->view->data = false;
        }
        $this->render('seek');
    }

    /**
     * Get list by Minder->$callbackFunction, with $callbackParams (if exists)
     * then check if not exists $valueToCheck - add it to list
     *
     * @param string $valueToCheck
     * @param string $callbackFunction
     * @param array  $callbackParams
     * @return array
     */
    private function fillList($valueToCheck, $callbackFunction, $callbackParams = null)
    {
        if ($callbackParams != null) {
            $tempArray = call_user_func_array(array($this->minder, $callbackFunction), array($callbackParams));
        } else {
            $tempArray = call_user_func(array($this->minder, $callbackFunction));
        }
        if ($valueToCheck != null) {
            if (!array_key_exists($valueToCheck, $tempArray)) {
                $tempArray = minder_array_merge(array($valueToCheck => $valueToCheck), $tempArray);
            }
        }
        $tempArray = minder_array_merge(array('' => ''), $tempArray);
        return $tempArray;
    }


    /**
     * Save changes in SSN
     *
     * @param array $updateList
     */
    private function _saveSSN(array $updateList)
    {

        $objMender = Minder::getInstance();
        foreach($updateList as $strKey => $strValue){
            if($objMender->isValidDate($strValue)){
                //echo $strValue." - "
                $updateList[$strKey] = $objMender->getFormatedDateToDb($strValue, "Y-m-d H:i:s");
                //die($updateList[$strKey]);
            }
        }

        $result = true;
        $log = Zend_Registry::get('logger');
        $log->info('fields to update - ' . implode(',', $updateList));

        //-- accordingly to ticket #325


       $this->minder->addIssnFromSsn($this->view->ssnObj->id);
        if (count($updateList) > 0) {
		$sql="UPDATE SSN SET ";
           foreach ($updateList as $key => $val) {
		if (!empty($val)){
               switch ($key) {
		    case 'SSN_ID':
			$sql .="".$key."='".$val."'";
			break;
                    case 'SUPPLIER_ID':
			$sql .=",".$key."='".$val."'";
                        break;
                
                    case 'PURCHASE_PRICE':
			$sql .=",".$key."='".$val."'";
                        break;
               
                    case 'SSN_TYPE':
			$sql .=",".$key."='".$val."'";
			break;

		    case 'GENERIC':
			$sql .=",".$key."='".$val."'";
			break;

                    case 'SSN_SUB_TYPE':
			$sql .=",".$key."='".$val."'";
			break;
                    
                    case 'MODEL':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'STATUS_CODE':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'PROD_ID':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'COST_CENTER':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'BRAND':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'SERIAL_NUMBER':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'LEGACY_ID':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'PRODUCT':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'NOTES':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'LEASED':
			$sql .=",".$key."='".$val."'";
                        break;
                        
                    case 'LOAN_SAFETY_CHECK':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'LOAN_CALIBRATE_CHECK':
			$sql .=",".$key."='".$val."'";
                        break;
                    
                    case 'DISPOSED':
                    	if ( $this->view->ssnObj->items[$key] == 'T') {
		                    $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
		                    $result        = $result && $currentResult;
		                    if (false == $currentResult) {
		                        $this->addError($this->minder->lastError);
		                    }
		                    
		                    if($this->view->ssnObj->items['DISPOSAL_DATE'] != null) {
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_DATE', $updateList['DISPOSAL_DATE']);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    } else {
		                        $val = date('Y-m-d H:i:s');
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_DATE', $val);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    }
		                    
		                    if($this->view->ssnObj->items['DISPOSAL_COST'] != null) {
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_COST', $updateList['DISPOSAL_COST']);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    } else {
		                        $val = 0;
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_COST', $val);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    }
		                    
		                    if($this->view->ssnObj->items['DISPOSAL_PRICE'] != null) {
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_PRICE', $updateList['DISPOSAL_PRICE']);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    } else {
		                        $val = 0;
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_PRICE', $val);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    }
		                    
		                    if($this->view->ssnObj->items['DISPOSAL_NOTES'] != null) {
		                        $str = '';
		                        foreach($this->session->disposedItems as $item) {
		                            if(!strstr($this->view->ssnObj->items['DISPOSAL_NOTES'], $item)) {
		                                if((strlen($this->view->ssnObj->items['DISPOSAL_NOTES'] . 'Disposed ISSN:') + strlen($str)) < 200) {
		                                    $str .= $item . '; ';
		                                } else {
		                                    $str = substr($str, 0 ,strlen($str)-1) . '+'; 
		                                    break;
		                                }
		                            }
		                        }
		                        $str = $this->view->ssnObj->items['DISPOSAL_NOTES'] . ';Disposed ISSN:' . $str; 
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_NOTES', $str);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    } else {
		                        $str = '';
		                        foreach($this->session->disposedItems as $item){
		                            if((strlen($this->view->ssnObj->items['DISPOSAL_NOTES'] . ';Disposed ISSN:') + strlen($str)) < 200) {
		                                $str .= $item . '; ';
		                            } else {
		                                $str = substr($str, 0 ,strlen($str)-1) . '+';
		                                break;
		                            }
		                        }
		                        if(!strstr($this->view->ssnObj->items['DISPOSAL_NOTES'], ';Disposed ISSN:')) {
		                            $str = $this->view->ssnObj->items['DISPOSAL_NOTES'] . ';Disposed ISSN:' . $str;
		                        } else {
		                            $str = $this->view->ssnObj->items['DISPOSAL_NOTES'] . $str; 
		                        }
		                        
		                        $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_NOTES', $str);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                            $this->addError($this->minder->lastError);
		                        }
		                    }
		                } elseif ($this->view->ssnObj->items[$key] == 'F') {
		                    $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
		                    $result        = $result && $currentResult;
		                    if (false == $currentResult) {
		                        $this->addError($this->minder->lastError);
		                    }
		                }
               		    break;
                    
                    case 'DISPOSAL_DATE':
                    
                        if ($this->view->ssnObj->items['DISPOSED'] != 'F') {
                        	if ($updateList['DISPOSAL_DATE']!='' && $updateList['DISPOSAL_DATE']!=null) {
                    			$val = $updateList['DISPOSAL_DATE'];
                        	} else {
                        		$val = date('Y-m-d H:i:s');
                        	}
                        } else {
                        	$val = null; 
                        }
                        
                   		$currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_DATE', $val);
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                        	$this->addError($this->minder->lastError);
                        }
                        
                        break;
                    
                    case 'DISPOSAL_COST':
                     
                        if ($this->view->ssnObj->items['DISPOSED'] != 'F') {
                    		$val = $updateList['DISPOSAL_COST'];
                        } else {
                        	$val = null;
                        }
                        
                   		$currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_COST', $val);
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                        	$this->addError($this->minder->lastError);
                        }                            
                            
                        break;
                    
                    case 'DISPOSAL_PRICE':
                            
               			if ($this->view->ssnObj->items['DISPOSED'] != 'F') {
                    		$val = $updateList['DISPOSAL_PRICE'];
                        } else {
                        	$val = null;
                        }
                        
                   		$currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_PRICE', $val);
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                        	$this->addError($this->minder->lastError);
                        }                            
                            
                    case 'DISPOSAL_NOTES':
                            
  	               		    if ($this->view->ssnObj->items['DISPOSED'] != 'F') {
		               			
									$str = '';
	                                foreach($this->session->disposedItems as $item) {
	                                    if(!strstr($this->view->ssnObj->items['DISPOSAL_NOTES'], $item)) {
	                                        if((strlen($this->view->ssnObj->items['DISPOSAL_NOTES'] . 'Disposed ISSN:') + strlen($str)) < 200) {
	                                            $str .= $item . '; ';
	                                        } else {
	                                            $str = substr($str, 0 ,strlen($str)-1) . '+'; 
	                                            break;
	                                        }
	                                    }     
	                                } 
	                                
	                                if(!strstr($this->view->ssnObj->items['DISPOSAL_NOTES'], ';Disposed ISSN:')) {
	                                    $str = $this->view->ssnObj->items['DISPOSAL_NOTES'] . ';Disposed ISSN:' . $str;
	                                } else {
	                                    $str = $this->view->ssnObj->items['DISPOSAL_NOTES'] . $str; 
	                                }
	                                 
		                        } else {
		                        	$str = null;
		                        }
		                        
		                   		$currentResult = $this->minder->updateSsn($this->view->ssnObj->id, 'DISPOSAL_NOTES', $str);
		                        $result        = $result && $currentResult;
		                        if (false == $currentResult) {
		                        $this->addError($this->minder->lastError);
		                    }                            
                                                        
                            
                            break;

                   case 'HOME_LOCN_ID':
                       if (!empty($val)) {
                           $newHomeLocation = Minder2_Environment::getCurrentWarehouse()->WH_ID . $val;
                           $transaction = new Transaction_NIHL($this->view->ssnObj->id, $newHomeLocation, $this->view->ssnObj->items['ORIGINAL_QTY'], $this->view->ssnObj->items['COMPANY_ID']);
                           try {
                               $this->minder->doTransactionResponseV6($transaction);
                           } catch (Exception $e) {
                               $this->addError('Error executing ' . $transaction->getTransCode() . $transaction->getTransClass() . ' transaction: ' . $e->getMessage());
                           }
                       }
                       break;

                   case 'LOT_BATCH_NO':
                       if (!empty($val)) {
                           $transaction = new Transaction_NILB(
                               $this->view->ssnObj->id,
                               $val,
                               $this->view->ssnObj->items['ORIGINAL_QTY'],
                               $this->view->ssnObj->items['WH_ID'],
                               $this->view->ssnObj->items['LOCN_ID'],
                               $this->view->ssnObj->items['COMPANY_ID']
                           );
                           try {
                               $this->minder->doTransactionResponseV6($transaction);
                           } catch (Exception $e) {
                               $this->addError('Error executing ' . $transaction->getTransCode() . $transaction->getTransClass() . ' transaction: ' . $e->getMessage());
                           }
                       }
                       break;

                   case 'BEST_BEFORE_DATE':
                       if (!empty($val)) {
                           $transaction = new Transaction_NIBB(
                               $this->view->ssnObj->id,
                               $val,
                               $this->view->ssnObj->items['ORIGINAL_QTY'],
                               $this->view->ssnObj->items['WH_ID'],
                               $this->view->ssnObj->items['LOCN_ID'],
                               $this->view->ssnObj->items['COMPANY_ID']
                           );
                           try {
                               $this->minder->doTransactionResponseV6($transaction);
                           } catch (Exception $e) {
                               $this->addError('Error executing ' . $transaction->getTransCode() . $transaction->getTransClass() . ' transaction: ' . $e->getMessage());
                           }
                       }
                       break;

                   case 'USE_BY_DATE':
                       if (!empty($val)) {
                           $transaction = new Transaction_NIUD(
                               $this->view->ssnObj->id,
                               $val,
                               $this->view->ssnObj->items['ORIGINAL_QTY'],
                               $this->view->ssnObj->items['WH_ID'],
                               $this->view->ssnObj->items['LOCN_ID'],
                               $this->view->ssnObj->items['COMPANY_ID']
                           );
                           try {
                               $this->minder->doTransactionResponseV6($transaction);
                           } catch (Exception $e) {
                               $this->addError('Error executing ' . $transaction->getTransCode() . $transaction->getTransClass() . ' transaction: ' . $e->getMessage());
                           }
                       }
                       break;

                    default:
                        $sql .=",".$key."='".$val."'";
                  
                        
                        break;
                }
		} else {
			switch($key){
				case 'DISPOSED':
				case 'DISPOSAL_DATE':
				case 'DISPOSAL_COST':
				case 'DISPOSAL_PRICE':
				case 'DISPOSAL_NOTES':
					break;
				default:
					$sql .=",".$key."=null";
					break;
			}
		}
            }

	    $ssnId=$this->view->ssnObj->id;
            $sql .=" WHERE SSN_ID='$ssnId'";

		$currentResult = $this->minder->updateSsnType($sql);

                    //  $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }		

             if ($result === true) {
                $this->addMessage('Record ' . $this->view->ssnObj->id . ' saved successfully');
            } else {
                $this->addError('Record ' . $this->view->ssnObj->id . '. Error occured when add new SSN');
            }
        }
            
        return $result;
    }

    private function _setupViewList($ssn_type = null)
    {
        //-- preprocess 'other' fields
        $tempArray =    array();
        $params    =    array(); 
        for ($i=1; $i<21; $i++) {
            $v = $this->minder->getFieldFromSsnGroup('FIELD'.($i));
            if (!is_null($v) && !empty($v)) {
               $tempArray[$i] = $v;
            } 
        }
        $this->view->otherNames = $tempArray;
     
        $tempArray = array();
        for ($i=1; $i<21; $i++) {
            $v = $this->minder->getFieldFromSsnGroup('DD_OTHER'.($i));
            if (!is_null($v)) {
                if ('true' == strtolower($v)) {
                    $tempArray[$i] = true;
                } else {
                    $tempArray[$i] = false;
                }
            } else {
                $tempArray[$i] = false;
            }
        }
        // dropdown or not. If true - dropdown
        $this->view->otherType = $tempArray;
        
        // getting other X descriptions from SSN_TYPE table instead of SSN_GROUP 
        if ($ssn_type!=null) {
            $others_6_10 = $this->minder->getSsnTypeByCode($ssn_type); 
         
            for ($i=1; $i<6; $i++) {
                $field_name = $others_6_10['FIELD' . $i];
                if (!is_null($field_name) && !empty($field_name) && $field_name != ' ') {
                    $this->view->otherNames[$i+5] = $field_name;                                     
                }
            }
        }
       // list items for dropdowns
        $tempArray = array();
        foreach ($this->view->otherNames as $key => $val) {
            $clause = array('MATCH_OPERATOR IS NULL' => null,
                            "OTHER_NO = '" . $key . "'" => null);
            $list = $this->minder->getListFromGlobalConditions($clause);
            $objKey = 'OTHER'.$key;
            switch ($key) {
                case 15:
                    $objKey .= '_DATE';
                    break;
                case 16:
                    $objKey .= '_DATE';
                    break;
                case 17:
                    $objKey .= '_QTY';
                    break;
                case 18:
                    $objKey .= '_QTY';
                    break;
                default:
                break;
            }
            $curValue = $this->view->ssnObj->items[$objKey];
            if (!array_key_exists($curValue, $list)) {
                $list = minder_array_merge(array($curValue => $curValue), $list);
            }
            $tempArray[$key] = $list;
        }
        $this->view->otherDetails = $tempArray;
        
                
        // DD lists for Other6-Other10
	
        $dd = $this->minder->getProductDescription($ssn_type);
        
        $tempArray = $this->view->otherDetails;
        $addArray = array();
		foreach ($dd as $value) {
			unset($tempArray[$value['FIELD_CODE']+5]);
			$addArray[$value['FIELD_CODE']+5][' ']=' ';
			$addArray[$value['FIELD_CODE']+5][$value['DESCRIPTION']] = $value['DESCRIPTION'];   
		}
		
		$this->view->otherDetails = $tempArray + $addArray;
        
        
        //-- end preprocess 'other' fields
         
        $this->view->searchFields = array('home_locn_id' => 'Home Location');

        $conditions = $this->_setupConditions();
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'RE-PACK':
                $this->session->conditions['re']['pack']['original'] = $conditions;
                return $this->_forward('pack-init', 're', 'warehouse');
            case 'RE-SORT':
                $this->session->conditions['re']['sort']['original'] = $conditions;
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('sort',
                                         're',
                                         'warehouse',
                                         $params);
                break;
            default:
                break;
        }
        //-- Fill list and trap incorrect values in ssnObj to add it to list.
        //-- So, user can change only some item, not all.
        
        $this->view->costCenterList   = $this->fillList($this->view->ssnObj->items['COST_CENTER'],
                                                      "getCostCentreList");
        
        $tempArray = $this->fillList($this->view->ssnObj->items['SUPPLIER_ID'],
                                     "getPersonList",
                                     array('CO', 'CS', 'IS'));
        
        $this->view->warehouseList = $this->fillList($this->view->ssnObj->items['WH_ID'],
                                                      "getWarehouseList");
        $this->view->locationList = $this->fillList($this->view->ssnObj->items['LOCN_ID'],
                                                    "getLocationListByWhID",
                                                    $this->view->ssnObj->items['WH_ID']);
        if (count($tempArray) > 0) {
            $this->view->supplierIdList = array_combine(array_keys($tempArray), array_keys($tempArray));
        } else {
            $this->view->supplierIdList = array('' => '');
        }
        
        $this->view->companyIdList    = $this->fillList($this->view->ssnObj->items['COMPANY_ID'],
                                                      "getCompanyList");

        $this->view->divisionList     = $this->fillList($this->view->ssnObj->items['DIVISION_ID'],
                                                      "getDivisionList");

        $this->view->departmentList   = $this->fillList($this->view->ssnObj->items['DEPARTMENT_ID'],
                                                      "getDepartmentList");

        $this->view->statusCodeList   = $this->fillList($this->view->ssnObj->items['STATUS_CODE'],
                                                      "getSsnStatusList");

        $this->view->reticulationList = $this->fillList($this->view->ssnObj->items['RETICULATION'],
                                                      "getReticulationList");

        $tempArray                    = $this->fillList($this->view->ssnObj->items['PROD_ID'],
                                                      "getProductList");
        if (count($tempArray) > 0) {
            $ta = array_combine(array_keys($tempArray), array_keys($tempArray));
            asort($ta);
            $this->view->productList  = $ta;
        } else {
            $this->view->productList  = array();
        }

        $this->view->ssnTypeList      = $this->fillList($this->view->ssnObj->items['SSN_TYPE'],
                                                      "getSsnTypeListFromSsnType");;

        $this->view->brandList        = $this->fillList($this->view->ssnObj->items['BRAND'],
                                                      "getBrandList");

        $this->view->labelLocationList     = $this->fillList($this->view->ssnObj->items['LABEL_LOCN'],
                                                      "getLabelLocationList");
        //$tempArray = array_keys($this->view->locationList);
        //$this->view->locationList = array_combine($tempArray, $tempArray);
        $this->view->locationList     = $this->fillList($this->view->ssnObj->items['HOME_LOCN_ID'],
                                                        "getLocationList");

        //-- @todo: unknown list until not verified use getSentByList()
        $this->view->leasorList       = $this->fillList($this->view->ssnObj->items['LEASOR'],
                                                        "getSentByList");
        //                                              "getLeasorList");

        //-- @todo: unknown list until not verified use array() of current value
        //$this->view->loanStatusList = array($this->view->ssnObj->items['LOAN_STATUS'] => $this->view->ssnObj->items['LOAN_STATUS']);
        $this->view->loanStatusList   = $this->fillList($this->view->ssnObj->items['LOAN_STATUS'],
                                                        "getLoanStatusList");


        $this->view->loanPeriodList   = $this->fillList($this->view->ssnObj->items['LOAN_PERIOD'],
                                                        "getLoanPeriodList");

        //$this->view->loanSafetyPeriodList = $this->fillList($this->view->ssnObj->items['LOAN_SAFETY_PERIOD'],
        //                                                    "getLoanPeriodList");

        //-- @todo: unknown list until not verified use array() of current value
        //$this->view->loanSafetyPeriodList = array($this->view->ssnObj->items['LOAN_SAFETY_PERIOD'] => $this->view->ssnObj->items['LOAN_SAFETY_PERIOD']);
        $this->view->loanSafetyPeriodList = $this->fillList(trim($this->view->ssnObj->items['LOAN_SAFETY_PERIOD']),
                                                            "getLoanSafetyPeriodList");
        //                                                    "getLoanPeriodList");

        //-- @todo: unknown list until not verified use array() of current value
        //$this->view->loanCalibratePeriodList = array($this->view->ssnObj->items['LOAN_CALIBRATE_PERIOD'] => $this->view->ssnObj->items['LOAN_CALIBRATE_PERIOD']);
        $this->view->loanCalibratePeriodList = $this->fillList(trim($this->view->ssnObj->items['LOAN_CALIBRATE_PERIOD']),
        //                                                    "getLoanPeriodList");
                                                      "getLoanCalibratePeriodList");
        //-- end trap

        $this->view->loanInspectPeriodList = $this->fillList(trim($this->view->ssnObj->items['LOAN_INSPECT_PERIOD']), "getLoanInspectPeriodList");

        if ($this->view->ssnObj->items['SSN_TYPE'] != null) {
            $this->view->varietyList    = minder_array_merge(array('' => ''), $this->minder->getVarietyList($this->view->ssnObj->items['SSN_TYPE']));
       } else {
            $this->view->varietyList    = array();
       }
       if($this->view->ssnObj->items['SSN_TYPE'] != null && $this->view->ssnObj->items['GENERIC'] != null ) {
            $this->view->ssnSubTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnSubTypeList(
                                                                                                                array('SSN_SUB_TYPE.SSN_TYPE ' => $this->view->ssnObj->items['SSN_TYPE'],
                                                                                                                      'SSN_SUB_TYPE.GENERIC '  => $this->view->ssnObj->items['GENERIC']
                                                                                                                      )
                                                                                                              ));
       } else {
            $this->view->ssnSubTypeList = array();
       }

        $this->view->ssnSubTypes = $this->minder->getSsnSubTypes();
    }

    private function _checkSsnMandatoryFields(SsnLine $obj)
    {
        $result = array();
        if (strtolower($this->getRequest()->getActionName()) == 'add') {
            if ($obj->items['SSN_ID']       == null) {
                $result[] = 'SSN_ID';
            }
        }
        if ($obj->items['WH_ID']        == null) {
            $result[] = 'WH_ID';
        }
     
        if ($obj->items['SSN_TYPE']     == null) {
            $result[] = 'SSN_TYPE';
        }
        if ($obj->items['CREATE_DATE']  == null) {
            $result[] = 'CREATE_DATE';
        }
        if ($obj->items['CREATED_BY']    == null) {
            $result[] = 'CREATED_BY';
        }
        if ($obj->items['ORIGINAL_QTY'] == null) {
            $result[] = 'ORIGINAL_QTY';
        }
        /*
        if ($obj->items['CURRENT_QTY']  == null) {
            $result[] = 'CURRENT_QTY';
        }
        */
        return $result;
    }

    protected function _setupHeaders()
    {
        if (!parent::_setupHeaders()) {
           $this->view->headers = $this->minder->getSelectField( "SSN");
           /*
           $this->view->headers = array(
                                            'SSN_ID'          => 'SSN',
                                            'SUPPLIER_ID'     => 'Supplier ID',
                                            'SSN_DESCRIPTION' => 'SSN Description',
                                            'CURRENT_QTY'     => 'Qty',   
                                            'CREATE_DATE'     => 'Created Date',
                                            'GRN'             => 'GRN'
                                        ); */
        } else {
           $this->view->headers = $this->minder->getSelectField( "SSN");
        }
        return true;
    }

//    protected function _setupShortcuts()
//    {
//        $request = $this->getRequest();
//
//        $minderNavigation = Minder_Navigation::getNavigationInstance('warehouse');
//
//        /**
//         * @var Zend_Navigation_Page_Mvc $ssnPage
//         */
//        $ssnPage = $minderNavigation->findOneBy('name', 'ssn');
//
//        $originalModule = $request->getParam('module');
//        $originalController = $request->getParam('controller');
//        $originalAction = $request->getParam('action');
//
//        if (!is_null($ssnPage)) {
//            $request->setParam('module', $ssnPage->getModule())
//                    ->setParam('controller', $ssnPage->getController())
//                    ->setParam('action', $ssnPage->getAction());
//        }
//
//
//        $this->view->shortcuts = $minderNavigation->buildMinderMenuArray('warehouse');
//
//        $request->setParam('module', $originalModule)
//                ->setParam('controller', $originalController)
//                ->setParam('action', $originalAction);
//
//        return $this;
//    }

    protected function _makeClause($conditions, $allowed)
    {
        $clause = parent::_makeClause($conditions, $allowed);
        
        return $clause;
    }
    
    public function historyAction() {
        
        $this->view->data = array();
        
        $this->view->headers = $headers =   array('TRN_DATE'    => 'Date',
                                                   'WH_ID'       => 'WH',
                                                   'LOCN_ID'     => 'Location',
                                                   'TRN_TYPE'    => 'Type',
                                                   'TRN_CODE'    => 'Code',
                                                   'REFERENCE'   => 'Transaction Reference',
                                                   'ERROR_TEXT'  => 'Transaction Result',
                                                   'PERSON_ID'   => 'User ID',
                                                   'DEVICE_ID'   => 'Device'
                                                  );
        $allowed = array();
        
        $ssnId  = $this->getRequest()->getParam('edit_ssn_ssn_id');
        $action = strtoupper($this->getRequest()->getParam('report'));
        
        switch($action) {
            case 'REPORT: TXT':
            case 'REPORT: CSV':
            case 'REPORT: XML':
            case 'REPORT: XLS':
                            $this->_preProcessNavigation();
                            $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
                            $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
                            $result             = $this->minder->getHistoryList($ssnId,  $pageSelector, $showBy);
                            $data               = $result['data'];  
                            $numRecords         = count($result['data']);
                            $conditions         = $this->_getConditions('history');
                            $this->view->data   = array();
                            
                            for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                                if (array_key_exists($data[$i]->id, $conditions)) {
                                    $this->view->data[] = $data[$i];
                                }
                            }
                            $this->_processReportTo($action);
                            break; 
            default:
            
        
        }
        $this->_preProcessNavigation();
        $conditions   = $this->_makeConditions($allowed);  
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        $this->_setupConditions();
        $result       = $this->minder->getHistoryList($ssnId,  $pageSelector, $showBy, true); // Midified by Ticket #407
        
        $this->_postProcessNavigation($result);
       
        $this->view->data       = $data = $result['data'];
        $numRecords = count($data);
        // get total selected items 
        $conditions =   $this->_getConditions('history');
        for ($i = 0, $count = 0; $i < $numRecords; $i++) {
            if (array_key_exists($data[$i]->id, $conditions)) {
                $count++;
            }
        }

        // check upper chekbox
        $this->view->upperChekbox = '';
        if($count == count($result['data'])) {
            $this->view->upperChekbox = 'checked';
        }

        $this->view->totalSelect = $count;
    }
    
    public function marklinesAction() {
        
        $this->_preProcessNavigation();
        
        $allowed      = array();
        $ssnId        = $this->getRequest()->getParam('edit_ssn_ssn_id');    
        $conditions   = $this->_makeConditions($allowed);  
        $pageSelector = $this->session->navigation[$this->_controller]['history']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['history']['show_by'];
        
        $this->_setupConditions();
        $result       = $this->minder->getHistoryList($ssnId,  $pageSelector, $showBy);
        $data         = $result['data'];
        $method       = $this->getRequest()->getParam('method');
        $id           = $this->getRequest()->getParam('id');
        $value        = $this->getRequest()->getParam('value');

        
        $conditions = $this->_markSelected2($data, $id, $value, $method, 'history');
        $numRecords = count($data);
        // Calculate the number of selected items.
        $count = 0;
        for ($i = 0, $count = 0; $i < $numRecords; $i++) {
            if (array_key_exists($data[$i]->id, $conditions)) {
                $count++;
            }
        }
        
        $data = array();
        $data['selected_num']   = $count;
        $this->view->data       = $data;  
    }
    
    /**
    * @desc added additional conditions to the clause
    * @param $clause - array of clause
    * 
    * @return $clause  
    */
    private function addedAdditionalConditions($clause) {
     
        foreach($clause as $key => $value) {
            $value = strtoupper($value);
     
            if(stristr($value, '=')) {
                $newKey = str_replace('LIKE ?', $value, $key);
                unset($clause[$key]);
                $clause[$newKey]    =   '';
           
            }
            if(stristr($value, '<')) {
                $newKey = str_replace('LIKE ?', $value, $key);
                unset($clause[$key]);
                $clause[$newKey]    =   '';
           
            }
            if(stristr($value, '>')) {
                $newKey = str_replace('LIKE ?', $value, $key);
                unset($clause[$key]);
                $clause[$newKey]    =   '';
           
            }
            if(stristr($value, '>=')) {
                $newKey = str_replace('LIKE ?', $value, $key);
                unset($clause[$key]);
                $clause[$newKey]    =   '';
           
            }
            if(stristr($value, '=<')) {
                $newKey = str_replace('LIKE ?', $value, $key);
                unset($clause[$key]);
                $clause[$newKey]    =   '';
           
            }
            if(stristr($value, '<>')) {
                $newKey = str_replace('LIKE ?', $value, $key);
                unset($clause[$key]);
                $clause[$newKey]    =   '';
           
            }
            
      
        }
        return $clause;
    }

//-------------------------ISSN LIST ACTIONS-----------------------
    const ISSN_MODEL_NAME = 'SSN_ISSN';
    const ISSN_DATASET_NAMESPACE = 'WAREHOUSE-SSN_ISSN';

    public function initIssnListAction() {
        $this->view->errors    = isset($this->view->errors)?   $this->view->errors   : array();
        $this->view->warnings  = isset($this->view->warnings)? $this->view->warnings : array();
        $this->view->messages  = isset($this->view->messages)? $this->view->messages : array();

        try {
            $this->view->issnSsName     = $this->view->searchFormSsName = self::ISSN_MODEL_NAME;
            $this->view->issnNamespace  = self::ISSN_DATASET_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();

            /**
            * @var Minder_SysScreen_Model_Issn
            */
            $issnModel   = $screenBuilder->buildSysScreenModel(self::ISSN_MODEL_NAME, new Minder_SysScreen_Model_Issn());
            $originalSsnId = $this->getRequest()->getParam('edit_ssn_ssn_id');
            if (empty($originalSsnId))
                $issnModel->addConditions(array('1 = 2' => array()));
            else
                $issnModel->addConditions(array('ORIGINAL_SSN = ?' => array($originalSsnId)));


            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getDatasetAction();
            $this->view->issnJsSearchResults = $this->view->jsSearchResult(
                                                    self::ISSN_MODEL_NAME,
                                                    self::ISSN_DATASET_NAMESPACE,
                                                    array('sysScreenCaption' => 'ISSN LIST', 'usePagination'    => true)
            );
            $this->view->issnJsSearchResultsDataset = $this->view->sysScreens[self::ISSN_DATASET_NAMESPACE];
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }

    public function getDatasetAction() {
        $datasets = array(
            self::ISSN_DATASET_NAMESPACE   => self::ISSN_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);
            if (isset($sysScreens[$namespace])) {
                $pagination= $this->fillPagination($pagination, $sysScreens[$namespace]);
            }

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelname, $this->view->dataset, $this->view->selectedRows, $this->view->paginator);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new stdClass();
        $result->errors   = array();
        $result->warnings = array();
        $result->messages = array();
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try{

            $sysScreens = $this->getRequest()->getParam('sysScreens', array());
            $pagination = $this->restorePagination(self::ISSN_DATASET_NAMESPACE);

            if (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator'])) {
                $pagination['selectedPage']  = (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectedPage']))  ? $sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectedPage']  : $pagination['selectedPage'];
                $pagination['showBy']        = (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['showBy']))        ? $sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['showBy']        : $pagination['showBy'];
                $pagination['selectionMode'] = (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectionMode'])) ? $sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectionMode'] : $pagination['selectionMode'];
            }

            if (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['rowId']) && isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['state'])) {
                $rowSelector->setSelectionMode($pagination['selectionMode'], self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                $rowSelector->setRowSelection($sysScreens[self::ISSN_DATASET_NAMESPACE]['rowId'], $sysScreens[self::ISSN_DATASET_NAMESPACE]['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

            $result->selectedRowsTotal = $rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            if ($result->selectedRowsTotal > 0) {
                $result->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, self::ISSN_DATASET_NAMESPACE);
                $result->selectedRowsOnPage = count($result->selectedRows);
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function printLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new stdClass();
        $response->errors   = array();
        $response->warnings = array();
        $response->messages = array();

        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector       = $this->_helper->getHelper('RowSelector');
        $selectedIssnCount = $rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedIssnCount < 1) {
            $this->view->warnings[] = 'No ISSNs selected. Please, select one.';
            return $this->_forward('get-dataset');
        }

        /**
        * @var Minder_SysScreen_Model_Issn $issnModel
        */
        $issnModel = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        $result = $issnModel->printLabels($this->minder->getPrinter());
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $result));
        $response->errors   = array_merge($response->errors, $result->errors);
        $response->messages = array_merge($response->messages, $result->messages);

        echo json_encode($response);
    }

    public function repackAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector       = $this->_helper->getHelper('RowSelector');
        /**
        * @var Minder_SysScreen_Model_Issn $issnModel
        */
        $issnModel = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController) > 0) {
            $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        } else {
            $issnModel->addConditions(array('1 = 2' => array()));
        }
        $ssns = $issnModel->selectSsn(0, count($issnModel));

        /**
        * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
        */
        $searchKeeper  = $this->_helper->searchKeeper;
        $screenBuilder = new Minder_SysScreen_Builder();
        list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ISSN_MODEL_NAME);

        $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
        $searchFields = $searchKeeper->getSearch($searchFields, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $selectConditions = $issnModel->makeConditionsFromSearch($searchFields);

        $clause = array();
        foreach ($selectConditions as $conditionStr => $conditionParams) {
            $clause[$conditionStr] = (is_array($conditionParams)) ? current($conditionParams) : $conditionParams;
        }

        $this->session->conditions['re']['pack']['original'] = array_combine($ssns, $ssns);
        $this->session->conditions['re']['pack']['clause']   = $clause;

        return $this->_forward('pack-init', 're', 'warehouse');

    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', self::ISSN_DATASET_NAMESPACE);
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');

    }
    protected function _getMenuId()
    {
        return 'SSN';
    }

}
