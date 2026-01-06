<?php
/**
* @desc  ObjectContrell
* @author Denis Obuhov odionysus@gmail.com
* @copyright Binary-Studio.com 
* 
*/



class ObjectController extends Minder_Controller_Action
{
    
    public function init() {
        parent::init();
        
        $this->_setupShortcuts();
        
        $this->returnOrder = $this->session->returnOrder;
    }
    public function indexAction() {
    
    
    }
	
	public function locationAction()
	{
        
        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        $this->view->other2Name = $this->minder->getFieldFromSsnGroup('FIELD22');
        
        $this->view->pageTitle     = 'Object Location';
        $this->session->action     = 'location';
        $this->session->controller = 'object';
        $this->session->module     = 'default';
        $savedParams = array();
        foreach ($this->getRequest()->getParams() as $key => $val) {
            if ('module' != $key && 'controller' != $key && 'action' != $key) {
                $savedParams[$key] = $val;
            }
        }
        $this->session->savedParams = array();

        //-- preprocess input of navigation values
        if (isset($this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()])) {
            foreach ($this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()] as $key => $val) {
                if (null != $this->getRequest()->getParam($key)) {
                    $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()][$key] = (int)$this->getRequest()->getParam($key);
                }
            }
        } else {
            $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()]['show_by']      = 15;
            $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()]['pageselector'] = 0;
        }     

        $this->view->navigation = $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()];

        //-- end process input navigation values
        
        $allowed =  array('ssn_id'        => 'SSN_ID LIKE ?',
                          'legacy_id'     => 'LEGACY_ID LIKE ?',
                          'company_id'    => 'COMPANY_ID = ?',
                          'object_number' => 'OBJECT_NUMBER LIKE ?',
                          'issn_status'   => 'ISSN_STATUS = ?',
                          'home_locn_id'  => 'HOME_LOCN_ID = ?',
                          'locn_id'       => 'LOCN_ID = ?',
                          'prev_locn_id'  => 'PREV_LOCN_ID = ?',
                          'prev_prev_locn_id'   => 'PREV_PREV_LOCN_ID = ?',
                          'audited'       => 'AUDITED = ?',
                          'audit_date'    => 'AUDIT_DATE LIKE ?',
                          'max_audit_date'=> 'AUDIT_DATE < ?',
                          'other1'        => 'OTHER1 LIKE ?',
                          'other2'        => 'OTHER2 LIKE ?',
                          'pick_order'    => 'PICK_ORDER LIKE ?');

        $conditions = $this->_setupConditions(null, $allowed);

        $clause = $this->_makeClause($conditions, $allowed);  
        
        $this->_setupHeaders();
        
        $action = strtoupper($this->getRequest()->getPost('action'));
        switch ($action) {
            case 'REPORT: TXT':
            case 'REPORT: XLS':
            case 'REPORT: XML':
            case 'REPORT: CSV':
                            $this->_preProcessNavigation();
                            $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
                            $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
                            
                            $data         = $this->minder->getIssns($clause, $pageSelector, $showBy);
                            
                            $data         = $data['data'];  
                            $numRecords   = count($data);
                            $this->view->data   = array();
                            $this->_setupHeaders();  
                            for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                                if (array_key_exists($data[$i]->id, $conditions) && false !== array_search($data[$i]->id, $conditions, true )) {
                                    $this->view->data[] = $data[$i];
                                }
                            }
                            $this->_processReportTo($action);
        
        }
        $this->view->companyIdList = array_merge(array(''=>''), $this->minder->getCompanyList());
        $this->view->issnStatusIdList = array_merge(array(''=>''), $this->minder->getOptionsList('ISSN_STATUS'));
        $_keys = array_keys($this->minder->getLocationList());
        $this->view->locnIdList = array_merge(array(''=>''), array_combine($_keys, $_keys));
        $this->view->auditedIdList = array_merge(array(''=>''),  $this->minder->getOptionsList('AUDIT_ISSN'));
        
        $response = $this->minder->getIssns($clause, $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
        
        if (is_array($response)) {
            $this->view->issns      = $response['data'];
            $this->view->numRecords = $response['total'];
        }
        
           
        if (($this->view->navigation['show_by'] * ($this->view->navigation['pageselector'] + 1)) > $this->view->numRecords) {
            $this->view->maxno = $this->view->numRecords - ($this->view->navigation['show_by'] * $this->view->navigation['pageselector']);
        } else {
            $this->view->maxno = $this->view->navigation['show_by'];
        }
        $this->view->pages     = array();
        for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
            $this->view->pages[] = $i;
        }
	}
	
	public function descriptionAction()
	{
        $this->view->pageTitle     = 'Search Object-Description';
        $this->session->action     = 'description';
        $this->session->controller = 'object';
        $this->session->module     = 'default';
        $savedParams = array();
        
        // get param from ORDER screen(ADD PRODUCT ot ADD Non-Product)
        $productOptions = &$this->session->params['non_product_options'];
       
        if(isset($productOptions)){
            $this->session->from['pick_order']  =   $productOptions->pickOrder;
            $this->session->from['wh_id']       =   $productOptions->whId;
            $this->view->required_qty           =   $productOptions->qty;
            $this->session->required_qty        =   $productOptions->qty;
             
            $this->getRequest()->setParam('from', $productOptions->from);   
            $this->getRequest()->setParam('company_id', $productOptions->company);    
            
            if($productOptions->thisIs == 'description'){
                $this->getRequest()->setParam('collection_name', $productOptions->nonProductName);    
                $this->getRequest()->setParam('ssn_id', '');    
            } elseif($productOptions->thisIs == 'issn') {
                $this->getRequest()->setParam('ssn_id', $productOptions->nonProductName);    
                $this->getRequest()->setParam('collection_name', '');    
            }
            
        }
        $from           =   $this->getRequest()->getParam('from');
        $pickOrderNo    =   $this->session->from['pick_order'];
        switch ($from) {
            case 'transfer-order':
                $this->view->from = $from;
                $this->view->pageTitle .= ': ORDER # = ' . $pickOrderNo;
                break;
      
            default:
                unset($this->session->from);
                $from = null;
        }
        
        foreach ($this->getRequest()->getParams() as $key => $val) {
            if ('module' != $key && 'controller' != $key && 'action' != $key) {
                $savedParams[$key] = $val;
            }
        }
        $this->session->savedParams = array();
        //-- preprocess input of navigation values
        if (isset($this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()])) {
            foreach ($this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()] as $key => $val) {
                if (null != $this->getRequest()->getParam($key)) {
                    $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()][$key] = (int)$this->getRequest()->getParam($key);
                }
            }
        } else {
            $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()]['show_by']      = 15;
            $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()]['pageselector'] = 0;
        }     

        $this->view->navigation = $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()];

        $allowed =  array(
                            'ssn_id'        => 'SSN.SSN_ID LIKE ? AND ',
                            'company_id'    => 'SSN.COMPANY_ID = ? AND ',
                            'cost_center'   => 'SSN.COST_CENTER = ? AND ',
                                          
                            'ssn_type'      => 'SSN.SSN_TYPE = ? AND ',
                            'generic'       => 'SSN.GENERIC = ? AND ',
                            'ssn_sub_type'  => 'SSN.SSN_SUB_TYPE = ? AND ',
                                          
                            'object_number' => 'SSN.OBJECT_NUMBER LIKE ? AND ',
                            'file_number'   => 'SSN.FILE_NUMBER LIKE ? AND ',
                            'create_date'   => 'SSN.CREATE_DATE LIKE ? AND ',
                                          
                            'object_name'   => '',
                            'created_by'     => 'SSN.CREATED_BY LIKE ? AND ',
                                          
                            'collection_name' => '',
                            'collection_type' => 'SSN.COLLECTION_TYPE = ? AND ',
                                          
                            'accession'     => '',
                            'status_code'   => 'SSN.STATUS_CODE = ? AND ',
                                          
                            'legacy_id'     => 'SSN.LEGACY_ID LIKE ? AND ',
                            'old_ssn_id'    => 'SSN.OLD_SSN_ID LIKE ? AND ',
                            'parent_ssn_id' => 'SSN.PARENT_SSN_ID LIKE ? AND '
                         );
        
        
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'ADD SSN':
                    $this->_redirect('/warehouse/ssn/add/');
                break;
            
            case 'ADD':
                $conditions = $this->_setupConditions(null, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                
                try{
                    
                    $parserObj =    new Parser($conditions['object_name'], 'OBJECT_NAME', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                    $parserObj =    new Parser($conditions['collection_name'], 'COLLECTION_NAME', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                    $parserObj =    new Parser($conditions['accession'], 'ACCESSION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                
                } catch(Exception $ex) {}
                
                try {
                    $result           = $this->minder->getSsns($clause, $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                    $this->view->ssns = $result['data'];
                } catch  (Exception $e) {
                    $this->addError('Error occured during add item.');
                }
                $data = array();
                foreach ($this->view->ssns as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        $data[$line->id] = $line->items;
                    }
                }
                
                $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('pick_order' => $pickOrderNo), $data);
            
                unset($this->session->from);
                $this->_forward('add-issn-items', $this->returnOrder, 'default', array('redirect' => $this->returnOrder));
                
                return;
               
            break;

            case 'ADD & CONTINUE':
                
                $conditions = $this->_setupConditions(null, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                
                try{
                    
                    $parserObj =    new Parser($conditions['object_name'], 'OBJECT_NAME', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                    $parserObj =    new Parser($conditions['collection_name'], 'COLLECTION_NAME', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                    $parserObj =    new Parser($conditions['accession'], 'ACCESSION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                
                } catch(Exception $ex) {}
                
                try {
                    $result           = $this->minder->getSsns($clause, $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
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
                
                $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('pick_order' => $pickOrderNo), $data);
                
                $this->_forward('add-issn-items', $this->returnOrder, 'default', array('redirect' => 'object/description/from/'. $this->returnOrder));
                return; 
                
            break;
            
            case 'CANCEL ADD':
                unset($this->session->from);
                $this->_redirect($this->returnOrder);
                break;
            default:
                break;
        }
        
        //-- end process input navigation values
        
        $conditions = $this->_setupConditions(null, $allowed);
        
        $clause = $this->_makeClause($conditions, $allowed);   
        
        try{
            
            $parserObj =    new Parser($conditions['object_name'], 'OBJECT_NAME', 'SSN', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';
            
            $parserObj =    new Parser($conditions['collection_name'], 'COLLECTION_NAME', 'SSN', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';
            
            $parserObj =    new Parser($conditions['accession'], 'ACCESSION', 'SSN', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';
        
        } catch(Exception $ex) {}
         
        $this->_setupHeaders();
    
        $action = strtoupper($this->getRequest()->getPost('action'));
        switch ($action) {
            case 'REPORT: TXT':
            case 'REPORT: XLS':
            case 'REPORT: XML':
            case 'REPORT: CSV':
                            $this->_preProcessNavigation();
                            $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
                            $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
                            
                            $data         = $this->minder->getSsns($clause, $pageSelector, $showBy);
                            $data         = $data['data'];  
                            $numRecords   = count($data);
                            $this->view->data   = array();
                            $this->_setupHeaders();  
                            for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                                if (array_key_exists($data[$i]->id, $conditions) && false !== array_search($data[$i]->id, $conditions, true )) {
                                    $this->view->data[] = $data[$i];
                                }
                            }
                            $this->_processReportTo($action);
        }
        
        $this->view->companyIdList = array_merge(array(''=>''), $this->minder->getCompanyList());
        $this->view->costCenterIdList = array_merge(array(''=>''),  $this->minder->getCostCentreList());
        $this->view->ssnTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnTypeListFromSsnType());
        $this->view->statusCodeList = minder_array_merge(array('' => ''), $this->minder->getSsnStatusList());
        if (array_key_exists('ssn_type', $conditions)) {
            if ($conditions['ssn_type'] != '') {
                $this->view->varietyList = $this->fillList(null, 'getVarietyList', $conditions['ssn_type']);
                if (array_key_exists('generic', $conditions)) {
                    if ($conditions['generic'] != '' && $conditions['ssn_type'] != '') {
                        $this->view->ssnSubTypeList = $this->fillList(null, 'getSsnSubTypeList', array('SSN_SUB_TYPE.GENERIC'  => $conditions['generic'],
                                                                                                       'SSN_SUB_TYPE.SSN_TYPE' => $conditions['ssn_type']
                                                                                                       ));
                    } else {
                        $this->view->ssnSubTypeList = $this->view->ssnSubTypeList = array('' => '');
                    }
                } else {
                    $this->view->ssnSubTypeList = $this->view->ssnSubTypeList = array('' => '');
                }
            } else {
                $this->view->varietyList    = array('' => '');
                $this->view->ssnSubTypeList = array('' => '');
            }
        } else {
            $this->view->varietyList    = array('' => '');
            $this->view->ssnSubTypeList = array('' => '');
        }
        
        if(!isset($productOptions) || $productOptions->makeSearch){
            $this->view->tabShow    =   true;
            if(isset($from)){
                $statuses               =   eregi_replace('([a-z]+)', "'\\1'", trim($this->minder->defaultControlValues['PICK_IMPORT_SSN_STATUS'], ','));
                $clause                 =   array_merge($clause, array(sprintf('ISSN.ISSN_STATUS IN(%s) AND ', $statuses) => '', 
                                                                               'ISSN.CURRENT_QTY > 0 AND '                => '',
                                                                       sprintf('ISSN.WH_ID = "%s"', $this->session->from['wh_id'])));
            }
        
            $response                   =   $this->minder->getSsns($clause, $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
        } else {
            $this->view->tabShow        =   false;
            $response['data']           =   array();
            $productOptions->makeSearch =   true;
        }
        
        $this->view->ssns           = $response['data'];
        $this->view->numRecords     = $response['total'];
        
        if(!empty($_POST['required_qty'])){
             $this->view->required_qty  =   $_POST['required_qty'];    
        } elseif(!empty($this->session->required_qty)) {
            $this->view->required_qty   =   $this->session->required_qty;   
        } else {
            $this->view->required_qty   =   0;    
        }
           
        if (($this->view->navigation['show_by'] * ($this->view->navigation['pageselector'] + 1)) > $this->view->numRecords) {
            $this->view->maxno = $this->view->numRecords - ($this->view->navigation['show_by'] * $this->view->navigation['pageselector']);
        } else {
            $this->view->maxno = $this->view->navigation['show_by'];
        }
        $this->view->pages     = array();
        for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
            $this->view->pages[] = $i;
        }
        
        $productOptions = null;
    }
    
    private function fillList($valueToCheck, $callbackFunction, $callbackParams = null)
    {
        if ($callbackParams != null) {
            $tempArray = call_user_func_array(array($this->minder, $callbackFunction), $callbackParams);
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
	
    public function addAction()
    {
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

                $tmp->save($this->getRequest()->getPost());
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

                    $this->_redirect('/object/description/');
                } else {

                    $this->_redirect('/object/description/');                                             
                }
                break;
            case 'CANCEL CHANGES':

                $this->_redirect('/object/description/');
                break;
            default:
                break;
        }
        $this->view->issns = array();
        $this->_setupViewList();
        $this->render('edit');

    }

    private function _checkSsnMandatoryFields(SsnLine $obj)
    {
        $result = array();
        if (strtolower($this->getRequest()->getActionName()) == 'add') {
            if ($obj->items['SSN_ID']       == null) {
                $result[] = 'Barcode';
            }
        }
        if ($obj->items['WH_ID']        == null) {
            $result[] = 'WH_ID';
        }
        if ($obj->items['LOCN_ID']      == null) {
            $result[] = 'LOCN_ID';
        }
        if ($obj->items['SSN_TYPE']     == null) {
            $result[] = 'SSN_TYPE';
        }
        if ($obj->items['STATUS_SSN']   == null) {
            $result[] = 'STATUS_SSN';
        }
        if ($obj->items['CREATE_DATE']  == null) {
            $result[] = 'CREATE_DATE';
        }
        if ($obj->items['CREATED_BY']    == null) {
            $result[] = 'CREATED_BY';
        }
        /*
        if ($obj->items['CURRENT_QTY']  == null) {
            $result[] = 'CURRENT_QTY';
        }
        */
        return $result;
    }
    
    private function _setupViewList()
    {
        //-- preprocess 'other' fields
        $tempArray = array();
        for ($i=1; $i<21; $i++) {
            $v = $this->minder->getFieldFromSsnGroup('FIELD'.($i));
            if (!is_null($v)) {
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
        //-- end preprocess 'other' fields

        $this->view->searchFields = array('home_locn_id' => 'Home Location');

        $conditions = $this->_setupConditions();
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'RE-PACK':
                $this->session->conditions['re']['pack']['original'] = $conditions;
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('pack',
                                         're',
                                         'warehouse',
                                         $params);
                break;
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
                                     array(array('CO', 'CS', 'IS')));

        $this->view->warehouseList = $this->fillList($this->view->ssnObj->items['WH_ID'],
                                                      "getWarehouseList");
        $this->view->locationList = $this->fillList($this->view->ssnObj->items['LOCN_ID'],
                                                    "getLocationListByWhID",
                                                    array($this->view->ssnObj->items['WH_ID']));
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
            sort($ta);
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
        $this->view->loanSafetyPeriodList = $this->fillList($this->view->ssnObj->items['LOAN_SAFETY_PERIOD'],
                                                            "getLoanSafetyPeriodList");
        //                                                    "getLoanPeriodList");

        //-- @todo: unknown list until not verified use array() of current value
        //$this->view->loanCalibratePeriodList = array($this->view->ssnObj->items['LOAN_CALIBRATE_PERIOD'] => $this->view->ssnObj->items['LOAN_CALIBRATE_PERIOD']);
        $this->view->loanCalibratePeriodList = $this->fillList($this->view->ssnObj->items['LOAN_CALIBRATE_PERIOD'],
        //                                                    "getLoanPeriodList");
                                                      "getLoanCalibratePeriodList");
        //-- end trap

        if ($this->view->ssnObj->items['SSN_TYPE'] != null) {
            $this->view->varietyList    = minder_array_merge(array('' => ''), $this->minder->getVarietyList($this->view->ssnObj->items['SSN_TYPE']));
            $this->view->ssnSubTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnSubTypeList($this->view->ssnObj->items['SSN_TYPE']));
        } else {
            $this->view->varietyList    = array();
            $this->view->ssnSubTypeList = array();
        }
    }
    
        /**
     * Save changes in SSN
     *
     * @param array $updateList
     */
    private function _saveSSN(array $updateList)
    {
        $result = true;
        $log = Zend_Registry::get('logger');
        $log->info('fields to update - ' . implode(',', $updateList));

        //-- accordingly to ticket #325

        $this->minder->addIssnFromSsn($this->view->ssnObj->id);
        if (count($updateList) > 0) {
            foreach ($updateList as $key => $val) {
                $tryToChange = true;

                switch ($key) {
                    case 'SUPPLIER_ID':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NISUA is not implemented yet.');*/
                        break;
                    case 'PURCHASE_PRICE':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NIPPA is not implemented yet.');*/
                        break;
                    case 'SSN_TYPE':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                        /*$transaction               = new Transaction_NITPA();
                        $transaction->objectId     = $this->view->ssnObj->id;
                        $transaction->ssnTypeValue = $val;
                        $currentResult             = $this->minder->doTransactionResponse($transaction);
                        $result                    = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }*/
                        break;
                    case 'SSN_SUB_TYPE':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                        /*$transaction                  = new Transaction_NID3A();
                        $transaction->objectId        = $this->view->ssnObj->id;
                        $transaction->ssnSubTypeValue = $val;
                        $currentResult                = $this->minder->doTransactionResponse($transaction);
                        $result                       = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }*/
                        break;
                    case 'MODEL':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NIMOA is not implemented yet.');*/
                        break;
                    case 'STATUS_CODE':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }

                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NISTA is not implemented yet.');*/
                        break;
                    case 'PROD_ID':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }

                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NIPCA is not implemented yet.');*/
                        break;
                    case 'COST_CENTER':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NICCA is not implemented yet.');*/
                        break;
                    case 'GENERIC':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        /*$transaction               = new Transaction_NIOBA();
                        $transaction->objectId     = $this->view->ssnObj->id;
                        $transaction->genericValue = $val;
                        $currentResult             = $this->minder->doTransactionResponse($transaction);
                        $result                    = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }*/
                        break;
                    case 'BRAND':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        /*$transaction                 = new Transaction_NIBCA();
                        $transaction->objectId       = $this->view->ssnObj->id;
                        $transaction->brandCodeValue = $val;
                        $currentResult               = $this->minder->doTransactionResponse($transaction);
                        $result                      = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }*/
                        break;
                    case 'SERIAL_NUMBER':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NISNA is not implemented yet.');*/
                        break;
                    case 'LEGACY_ID':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NILGA is not implemented yet.');*/
                        break;
                    case 'PRODUCT':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NIPDA is not implemented yet.');*/
                        break;
                    case 'NOTES':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        /*$currentResult = false;
                        $result        = $result && $currentResult;
                        $this->view->flashMessenger->addMessage('This transaction NINBA is not implemented yet.');*/
                        break;
                        //-- non transaction update
                    case 'LEASED':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        break;
                    case 'LOAN_SAFETY_CHECK':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        break;
                    case 'LOAN_CALIBRATE_CHECK':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        break;
                    case 'DISPOSED':
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        break;
                    default:
                        if ($this->view->ssnObj->items[$key] != null) {
                            $currentResult = $this->minder->updateSsn($this->view->ssnObj->id, $key, $val);
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                        }
                        break;
                }
            }
            if ($tryToChange == true) {
                if ($result === true) {
                    $this->addMessage('Record ' . $this->view->ssnObj->id . ' saved successfully');
                } else {
                    $this->addError('Record ' . $this->view->ssnObj->id . '. Error occured when add new SSN');
                }
            }
        }
        return $result;
    }
    
	public function noteAction() {
		
	}
    
    public function searchAction() {
    
    }
    
    public function lookupAction() {
        try{
                $field  =   $this->_getParam('field');
                $value  =   $this->_getParam('q');
                $length = (int) $this->_getParam('limit'); 
                switch($field) {
                    case 'home_locn_id':
                                        $_keys = array_keys($this->minder->getLocationList($value));
                                        $_keys = array_slice($_keys, 0, $length);
                                        if (empty($_keys)) {
                                            $this->view->data = array('  '=>'   ');
                                            return;
                                        }
                                        $result = array();
                                        foreach ($_keys as $_key) {
                                            $result[' '.$_key] = $_key;
                                        }
                                        $this->view->data = array_merge(array(' '=>' '), $result);
                                        
                                    break;
                    default:
                                    break;
                
                }
        } catch(Exception $e) {
        
        }
    }
    
     /**
     * Provides data for dynamic updates page
     *
     * @return void
     */
    public function seekAction() {
        
        $tdata = array();
        $param = $this->getRequest()->getParam('q');

        $src = strtolower($this->getRequest()->getParam('field'));
        switch ($src) {
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
    
    public function marklinesAction() {
        
        $calculatedValue = 0; // calculated value
        $count           = 0; // number of elements used for calculation
        
        $field  = strtoupper($this->getRequest()->getParam('field'));
        $method = $this->getRequest()->getParam('method');
        $id     = $this->getRequest()->getParam('id');
        $value  = $this->getRequest()->getParam('value');
        $action = $this->getRequest()->getParam('inAction');

        switch (strtolower($action)) {
            default:
               //-- setup conditions
               $allowed =  array(
                          'ssn_id'              => 'SSN_ID LIKE ?',
                          'legacy_id'           => 'LEGACY_ID LIKE ?',
                          'company_id'          => 'COMPANY_ID = ?',
                          'object_number'       => 'OBJECT_NUMBER LIKE ?',
                          'issn_status'         => 'ISSN_STATUS = ?',
                          'home_locn_id'        => 'HOME_LOCN_ID = ?',
                          'locn_id'             => 'LOCN_ID = ?',
                          'prev_locn_id'        => 'PREV_LOCN_ID = ?',
                          'prev_prev_locn_id'   => 'PREV_PREV_LOCN_ID = ?',
                          'audited'             => 'AUDITED = ?',
                          'audit_date'          => 'AUDIT_DATE LIKE ?',
                          'max_audit_date'      => 'AUDIT_DATE < ?',
                          'other1'              => 'OTHER1 LIKE ?',
                          'other2'              => 'OTHER2 LIKE ?',
                          'pick_order'          => 'PICK_ORDER LIKE ?');

                $conditions = $this->_setupConditions($action, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);

                $pageSelector = $this->session->navigation[$this->_controller][$action]['pageselector'];
                $showBy       = $this->session->navigation[$this->_controller][$action]['show_by'];  
                            
                //-- get appropriate lines
                $lines = $this->minder->getIssns($clause, $pageSelector, $showBy);
                $lines = $lines['data'];
                $conditions = $this->_markSelected($lines, $id, $value, $method, $action);
                $numRecords = count($lines);

                switch ($field) {
                    case 'QTY':
                        for ($i = 0; $i < $numRecords; $i++) {
                            if (false !== array_search($lines[$i]->id, $conditions, true )) {
                                $count++;
                            }
                        }
                        break;
                    default:
                    break;
                }
            break;
            
            case 'description':
               //-- setup conditions
               $allowed =  array(
                          'ssn_id'        => 'SSN.SSN_ID LIKE ? AND ',
                          'company_id'    => 'SSN.COMPANY_ID = ? AND ',
                          'cost_center'   => 'SSN.COST_CENTER = ? AND ',
                          
                          'ssn_type'      => 'SSN.SSN_TYPE = ? AND ',
                          'generic'       => 'SSN.GENERIC = ? AND ',
                          'ssn_sub_type'  => 'SSN.SSN_SUB_TYPE = ? AND ',
                          
                          'object_number' => 'SSN.OBJECT_NUMBER LIKE ? AND ',
                          'file_number'   => 'SSN.FILE_NUMBER LIKE ? AND ',
                          'create_date'   => 'SSN.CREATE_DATE LIKE ? AND ',
                          
                          'object_name'    => '',
                          'created_by'     => 'SSN.CREATED_BY LIKE ? AND ',
                          
                          'collection_name' => '',
                          'collection_type' => 'SSN.COLLECTION_TYPE = ? AND ',
                          
                          'accession'     => '',
                          'status_code'   => 'SSN.STATUS_CODE = ? AND ',
                          
                          'legacy_id'     => 'SSN.LEGACY_ID LIKE ? AND ',
                          'old_ssn_id'    => 'SSN.OLD_SSN_ID LIKE ? AND ',
                          'parent_ssn_id' => 'SSN.PARENT_SSN_ID LIKE ? AND ');

                $conditions = $this->_getConditions('description');
                $clause     = $this->_makeClause($conditions, $allowed);

                try{
                    
                    $parserObj =    new Parser($conditions['object_name'], 'OBJECT_NAME', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                    $parserObj =    new Parser($conditions['collection_name'], 'COLLECTION_NAME', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                    
                    $parserObj =    new Parser($conditions['accession'], 'ACCESSION', 'SSN', 'AND');
                    $outStr    =    $parserObj->parse();  

                    $clause[$outStr] = '';
                
                } catch(Exception $ex) {}
                
                $resultsPerPage = $this->session->navigation[$this->_controller][$action]['show_by'];
                $pageNo = $this->session->navigation[$this->_controller][$action]['pageselector'];
                
                //-- get appropriate lines
                $_result    = $this->minder->getSsns($clause, $pageNo, $resultsPerPage);
                $lines      = $_result['data'];
                
                $conditions = $this->_markSelected($lines, $id, $value, $method, $action);
                $numRecords = count($_result['data']);   
                $count      =   0;
                $totalQty   =   0;
                
                switch ($field) {
                    case 'QTY':
                        for ($i = 0; $i < $numRecords; $i++) {
                            if (false !== array_search($lines[$i]->id, $conditions, true )) {
                                $count++;
                                $totalQty +=$lines[$i]->items['CURRENT_QTY'];
                            }
                        }
                        break;
                    default:
                    break;
                }
            break;
        }

        $data = array();
        $data['selected_num'] = $count;
        $data['total_qty']    = $totalQty; 
        $this->view->data     = $data;
    }
    
    
	protected function _setupShortcuts() {         
		
        switch($this->_action) {
            case 'location':
                $this->view->shortcuts = array(
                                        '<Object-Location>'  => $this->view->url(array('controller' => 'object', 'action' => 'location'), '', true),
                                        'Object-Description' => $this->view->url(array('controller' => 'object', 'action' => 'description'), '', true),
                                        'Transfer'           => 
                                                             array(
                                                                    'Place ISSN Into'         => $this->view->url(array('controller' => 'transfer', 'action' => 'into'), null, true),
                                                                    'Transfer Whole Location' => $this->view->url(array('controller' => 'transfer', 'action' => 'whole'), null, true),
                                                                    'Transfer Moveable'       => $this->view->url(array('controller' => 'transfer', 'action' => 'moveable'), null, true),
                                                             ),
                                   
                                        'Location' => array (
                                            'Location List' => $this->view->url(array('module'=>'warehouse', 'controller' => 'location', 'action' => 'index'), '', true),
                                            'Add new Location' => $this->view->url(array('module'=>'warehouse', 'controller' => 'location', 'action' => 'add'), '', true),
                                        ),
                                    );
                
                                    break;
            case 'description':
            $this->view->shortcuts = array(
                                        'Object-Location'      => $this->view->url(array('controller' => 'object', 'action' => 'location'), '', true),
                                        '<Object-Description>' => $this->view->url(array('controller' => 'object', 'action' => 'description'), '', true),
                                        'Transfer'             => 
                                                                 array(
                                                                        'Place ISSN Into'         => $this->view->url(array('controller' => 'transfer', 'action' => 'into'), null, true),
                                                                        'Transfer Whole Location' => $this->view->url(array('controller' => 'transfer', 'action' => 'whole'), null, true),
                                                                        'Transfer Moveable'       => $this->view->url(array('controller' => 'transfer', 'action' => 'moveable'), null, true),
                                                                 ),
                                        'Location' => array (
                                            'Location List' => $this->view->url(array('module'=>'warehouse', 'controller' => 'location', 'action' => 'index'), '', true),
                                            'Add new Location' => $this->view->url(array('module'=>'warehouse', 'controller' => 'location', 'action' => 'add'), '', true),
                                        ),
                                     );
                                     break;
            case '':
            default:
            $this->view->shortcuts = array(
                                        'Object-Location'    => $this->view->url(array('controller' => 'object', 'action' => 'location'), '', true),
                                        'Object-Description' => $this->view->url(array('controller' => 'object', 'action' => 'description'), '', true),
                                        'Transfer'           => 
                                                             array(
                                                                    'Place ISSN Into'         => $this->view->url(array('controller' => 'transfer', 'action' => 'into'), null, true),
                                                                    'Transfer Whole Location' => $this->view->url(array('controller' => 'transfer', 'action' => 'whole'), null, true),
                                                                    'Transfer Moveable'       => $this->view->url(array('controller' => 'transfer', 'action' => 'moveable'), null, true),
                                                             ),
                                   
                                        'Location' => array (
                                            'Location List' => $this->view->url(array('module'=>'warehouse', 'controller' => 'location', 'action' => 'index'), '', true),
                                            'Add new Location' => $this->view->url(array('module'=>'warehouse', 'controller' => 'location', 'action' => 'add'), '', true),
                                        ),
                                    );
                    
        }
       
	}
    
    protected function _setupHeaders() {
        switch($this->_action) {
            case 'location': 
                                $this->session->headers[$this->_controller][$this->_action][$this->view->tableId] =
                                $this->view->headers = array('SSN_ID'          => 'Barcode#',
                                                             'OBJECT_NUMBER'   => 'Object#',
                                                             'OBJECT_NAME'     => 'Object Name',
                                                             'LEGACY_ID'       => 'Legacy ID',
                                                             'COMPANY_ID'      => 'Owner ID',
                                                             'ISSN_STATUS'     => 'Status',
                                                             'WH_ID'           => 'WH',
                                                             'LOCN_ID'         => 'Location',
                                                             'HOME_LOCN_ID'    => 'Home Location');
                                break;
            case 'description': 
                                $this->session->headers[$this->_controller][$this->_action][$this->view->tableId] =
                                $this->view->headers = array('SSN_ID'          => 'Barcode#',
                                                             'OBJECT_NUMBER'   => 'Object#',
                                                             'LEGACY_ID'       => 'Legacy #',
                                                             'OBJECT_NAME'     => 'Object Name',
                                                             'STATUS_CODE'     => 'Object Status');
                                break;
            
            default:            parent::_setupHeaders();
                                break;
            
        }
    }
    
    protected function _makeClause($conditions, $allowed)
    {
        $clause = parent::_makeClause($conditions, $allowed);
        
        $from = $this->getRequest()->getParam('from');
        if (!empty($from)) {
            $clause["(SSN.PROD_ID IS NULL OR SSN.PROD_ID = '' OR SSN.PROD_ID = '0') AND "] = '';
        }
        return $clause;
    }
}
?>