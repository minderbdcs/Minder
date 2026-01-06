<?php
/**
 * Minder
 *
 * PHP version 5.2.4
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
 * Warehouse_IssnController
 *
 * Action controller
 *
 * @todo: saving of information in a database, and preparation of
 *           information to the show on the screen should be separated
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Warehouse_IssnController extends Minder_Controller_Action
{
	function init() {
        parent::init();
    }
    /**
     * Prepare data for showing ISSN list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->pageTitle     = 'Search ISSN';
        $this->session->action     = 'index';
        $this->session->controller = 'issn';
        $this->session->module     = 'warehouse';
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

        $allowed =  array('issn_no'         => 'SSN_ID LIKE ?',
                          'location_id'     => 'LOCN_ID LIKE ?',
                          'product_id'      => 'PROD_ID LIKE ?',
                          'status'          => 'ISSN_STATUS = ?',
                          'company_id'      => 'COMPANY_ID LIKE ?',
                          'original_ssn'    => 'ORIGINAL_SSN LIKE ?',
                          'create_date_from'=> 'CREATE_DATE >= ?',
                          'create_date_to'  => 'CREATE_DATE <= ?',
                          'other1'          => 'OTHER1 LIKE ?',
                          'other2'          => 'OTHER2 LIKE ?',
                          'location_id'     => 'LOCN_ID LIKE ?', 
                          'product_id'      => 'PROD_ID LIKE ?' 
                         );
        $conditions = $this->_setupConditions(null, $allowed);
        $clause     = $this->_makeClause($conditions, $allowed);
    
        $lines      = $this->minder->getIssns($clause, 'index', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
        $lines      = $lines['data'];
        $numRecords = count($lines);
        $count      = 0;
        for ($i = 0; $i < $numRecords; $i++) {
            if (false !== array_search($lines[$i]->id, $conditions, true )) {
                $count++;
            }
        }
        $this->view->allSelected = false;
        if($count === $numRecords) {
            $this->view->allSelected = true;
        }

        $params = array();
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'PRE-PACK':
                $this->session->conditions['re']['pre-pack']['original'] = $conditions;
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('pre-pack',
                                         're',
                                         'warehouse',
                                         $params);
                break;
            case 'RE-PACK':
                $this->session->conditions['re']['pack']['original'] = $conditions;
                $this->session->conditions['re']['pack']['clause']   = $clause;
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
            case 'MASS UPDATE':
                if (null != $this->getRequest()->getPost('mass_product_id') ||
                    null != $this->getRequest()->getPost('mass_company_id')) {
                    $temp = $this->minder->getIssns($clause, 'index', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
                    $temp = $temp['data'];
                    $lines = array();
                    foreach ($temp as $line) {
                        if (false !== ($key = array_search($line->id, $conditions))) {
                            if ($conditions[$key] != 'off') {
                                $lines[] = $line;
                            }
                        }
                    }

                    foreach ($lines as $line) {
                        if (null != $this->getRequest()->getPost('mass_product_id')) {
                            $transaction = new Transaction_UIPCA();
                            $transaction->prodIdValue = $this->getRequest()->getPost('mass_product_id');
                            $transaction->objectId    = $line->items['SSN_ID'];
                            $transaction->locnId      = $line->items['LOCN_ID'];
                            $transaction->whId        = $line->items['WH_ID'];
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            If (false == $currentResult) {
                                $this->addError($line->items['SSN_ID'] . ' update Product ID failed - ' . $this->minder->lastError);
                            } else {
                                $this->addError($line->items['SSN_ID'] . ' - ' . $this->minder->lastError);
                            }
                        }
                        if (null != $this->getRequest()->getPost('mass_company_id')) {
                            $transaction = new Transaction_UICOA();
                            $transaction->companyId = $this->getRequest()->getPost('mass_company_id');
                            $transaction->objectId  = $line->items['SSN_ID'];
                            $transaction->locnId    = $line->items['LOCN_ID'];
                            $transaction->whId      = $line->items['WH_ID'];
                            $currentResult          = $this->minder->doTransactionResponse($transaction);
                            If (false == $currentResult) {
                                $this->addError($line->items['SSN_ID'] . ' update Company ID failed - ' . $this->minder->lastError);
                            } else {
                                $this->addError($line->items['SSN_ID'] . ' - ' . $this->minder->lastError);
                            }
                        }
                    }
                }
                unset($temp);
                unset($lines);
                $params = array();
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index',
                                                       'issn',
                                                       'warehouse',
                                                       $params);
                break;
            case 'PRINT LABEL':
            	$this->minder->limitPrinter =	$this->minder->checkPrinterLimited();
                $_prn						=	$this->minder->limitPrinter;    
            	
		      	$printerObj		=	$this->minder->getPrinter(null, $_prn);
				$result			=	false;
				$count			=	0;
				
                if($this->minder->defaultControlValues['GENERATE_LABEL_TEXT'] == 'T'){
                    foreach($lines as $line) {
                        if (false !== array_search($line->id, $conditions, true )) {
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
                    foreach($lines as $line) {
                        if (false !== array_search($line->id, $conditions, true )) {
                           
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
                
		        $params = array();
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index',
                                                       'issn',
                                                       'warehouse',
                                                       $params); 
            	break;
            default:
                break;
        }



        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        $this->view->other2Name = $this->minder->getFieldFromSsnGroup('FIELD22');
        
        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        $this->view->other2Name = $this->minder->getFieldFromSsnGroup('FIELD22');
        
        $this->view->ifDd1   =  $ifDd1 = $this->minder->getFieldFromSsnGroup('DD_OTHER21');
        $this->view->ifDd2   =  $ifDd2 = $this->minder->getFieldFromSsnGroup('DD_OTHER22');
        
        if($ifDd1 == 'TRUE' || $ifDd1 == 'T'){
            $productDescriptionList1 =   $this->minder->getProductDescription('U');
            $addArray                =   array();
            foreach($productDescriptionList1 as $value) {
                $addArray[$value['DESCRIPTION']] = $value['DESCRIPTION'];
            }
            $this->view->productDescriptionList1   =   minder_array_merge(array('' => ''), $addArray);       
        }
        
        if($ifDd2 == 'TRUE' || $ifDd2 == 'T'){
            $productDescriptionList2 =   $this->minder->getProductDescription('V');
            $addArray                =   array();
            foreach($productDescriptionList2 as $value) {
                $addArray[$value['DESCRIPTION']] = $value['DESCRIPTION'];
            }
            $this->view->productDescriptionList2   =   minder_array_merge(array('' => ''), $addArray);    
        }
     
        $this->_setupHeaders();

        $this->view->productList = minder_array_merge(array('' => ''), $this->minder->getProductList());
        $this->view->statusList  = minder_array_merge(array('' => ''), $this->minder->getIssnStatusList());
        $this->view->companyList = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

        $tempArray = $this->minder->getPersonList(array('CS'));
        $tk = array_keys($tempArray);
        if (count($tk) > 0) {
            $tempArray = array_combine($tk, $tk);
        } else {
            $tempArray = array();
        }

        $this->view->supplierIdList = minder_array_merge(array('' => ''), $tempArray);

        $tempArray                  = $this->minder->getLocationListFromIssn();
        $this->view->locationIdList = minder_array_merge(array('' => ''), $tempArray);

        if (key($this->view->productList) != null) {
            $this->view->ssnTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnTypeListFromProdProfile(current($this->view->productList)));
        } else {
            $this->view->ssnTypeList = minder_array_merge(array('' => ''), $this->minder->getSsnTypeListFromSsnType());
        }
        $this->view->brandList = minder_array_merge(array('' => ''), $this->minder->getBrandList());

        if (array_key_exists('ssn_type', $conditions)) {
            $this->view->varietyList = minder_array_merge(array('' => ''), array());
        } else {
            $this->view->varietyList = minder_array_merge(array('' => ''), array());
        }
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'REPORT: CSV':
            case 'REPORT: XML':
            case 'REPORT: XLS':
            case 'REPORT: TXT':
                
                $data = array();
            
                foreach($lines as $line){
                    if(array_search($line->items['SSN_ID'], $conditions)){
                        $data[] =   $line;   
                    }
                }
                
                $this->view->data = $data;
                $this->_processReportTo(strtoupper($this->getRequest()->getPost('action')));
                break;
        }
        $response = $this->minder->getIssns($clause, 'index', $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
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

    /**
     * Performs lookup for Autocomplete fields
     *
     * @return void
     */
    public function lookupAction()
    {
        $tdata = array();
        switch ($this->getRequest()->getParam('field')) {
            case 'product_id':
                $tdata = $this->minder->getProductList($this->getRequest()->getParam('q'));
                break;
            case 'issn_no':
                $tdata = $this->minder->getIssnList($this->getRequest()->getParam('q'));
                break;
            case 'other_1':
                $tdata = $this->minder->getFieldListFromIssn('ISSN.OTHER1', $this->getRequest()->getParam('q'));
                break;
            case 'other_2':
                $tdata = $this->minder->getFieldListFromIssn('ISSN.OTHER2', $this->getRequest()->getParam('q'));
                break;
            case 'company_id':
                $tdata = $this->minder->getFieldListFromIssn('ISSN.COMPANY_ID', $this->getRequest()->getParam('q'));
                break;
            case 'mass_company_id':
                $tdata = $this->minder->getCompanyList($this->getRequest()->getParam('q'));
                break;
            case 'original_ssn':
                $tdata = $this->minder->getFieldListFromIssn('ISSN.ORIGINAL_SSN', $this->getRequest()->getParam('q'));
                break;
            case 'location_id':
                //$tdata = $this->minder->getFieldListFromIssn('ISSN.LOCN', $this->getRequest()->getParam('q'));
                try {
                    $tdata = $this->minder->getLocationList($this->getRequest()->getParam('q'));
                } catch (Exception $e) {

                }
                break;
            default:
                break;
        }
        /*
        if ($this->getRequest()->getParam('truncate') != 'no') {
            if (count($tdata) > 10) {
                $tdata = array_slice($tdata, 0, 10, true);
            }
        }
        */
        $this->view->data = $tdata;
    }

    /**
     * Perform prepare data for editing and save data.
     *
     * @return void
     */
    public function editAction()
    {
        $this->view->pageTitle = 'ISSN Edit';
        $this->view->issnObj   = current($this->minder->getRawIssns(array('ISSN.SSN_ID = ?' => $this->getRequest()->getParam('edit_issn_ssn_id')), 'edit'));
    
        //$this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        //$this->view->other2Name = $this->minder->getFieldFromSsnGroup('FIELD22');
        //$this->view->other3Name = $this->minder->getFieldFromSsnGroup('FIELD23');
        //$this->view->other4Name = $this->minder->getFieldFromSsnGroup('FIELD24');

        $this->view->arrDynamicLabel = $this->minder->getLabelsFromSsnGroup(array(
                                            "OTHER1" => "FIELD21",
                                            "OTHER2" => "FIELD22",
                                            "OTHER3" => "FIELD23",
                                            "OTHER4" => "FIELD24"
                                        ));

        $tempArray = array_keys($this->minder->getProductListSortedById()); 
        $tempArray = minder_array_merge(array('' => ' '), array_combine($tempArray, $tempArray));
        if (!array_key_exists($this->view->issnObj->items['PROD_ID'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['PROD_ID'] => $this->view->issnObj->items['PROD_ID']), $tempArray);
        }
        $this->view->productList =  $tempArray;

        $tempArray = minder_array_merge(array('' => ' '), $this->minder->getIssnStatusList());
        if (!array_key_exists($this->view->issnObj->items['ISSN_STATUS'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['ISSN_STATUS'] => $this->view->issnObj->items['ISSN_STATUS']), $tempArray);
        }
        $this->view->issnStatusList = $tempArray;

        $tempArray = minder_array_merge(array('' => ' '), $this->minder->getDivisionList());
        if (!array_key_exists($this->view->issnObj->items['DIVISION_ID'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['DIVISION_ID'] => $this->view->issnObj->items['DIVISION_ID']), $tempArray);
        }
        $this->view->divisionList = $tempArray;

        $tempArray = $this->minder->getCompanyList();
        if (!array_key_exists($this->view->issnObj->items['COMPANY_ID'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['COMPANY_ID'] => $this->view->issnObj->items['COMPANY_ID']), $tempArray);
        }
        $this->view->companyList = $tempArray;

        $this->view->warehouseList = $this->minder->getWarehouseListLimited();

        $tempArray = $this->minder->getAuditCodeList();
        if (!array_key_exists($this->view->issnObj->items['AUDITED'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['AUDITED'] => $this->view->issnObj->items['AUDITED']), $tempArray);
        }
        $this->view->auditCodeList = $tempArray;

        $tempArray = $this->minder->getSsnStatusList();
        if (!array_key_exists($this->view->issnObj->items['STATUS_CODE'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['STATUS_CODE'] => $this->view->issnObj->items['STATUS_CODE']), $tempArray);
        }
        $this->view->ssnStatusList = $tempArray;

        $this->view->packageTypeList = array_merge(array('' => ''), $this->minder->getPackageTypeList());

        if (key($this->view->productList) != null) {
            $this->view->ssnTypeList = $this->minder->getSsnTypeListFromProdProfile(current($this->view->productList));
        } else {
            $this->view->ssnTypeList = $this->minder->getSsnTypeListFromSsnType();
        }
        $this->view->brandList   = minder_array_merge(array('' => ''), $this->minder->getBrandList());
        $this->view->varietyList = minder_array_merge(array('' => ''), array());
        

        if (count($this->getRequest()->getPost('action')) > 0) {
    
            switch ($this->getRequest()->getPost('action')) {
                case 'DISCARD':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto($this->session->action,
                                             $this->session->controller,
                                             $this->session->module,
                                             $this->session->savedParams);
                    return;
                    break;
                case 'SAVE':
                    $result      = true;
                    $tryToChange = false;
                    
                    if(!$this->minder->isAdjustIssn && !$this->minder->isAdmin){
                        $this->addError('You do not have premissions to update ISSN.');
                        
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)->goto($this->session->action, $this->session->controller, $this->session->module, $this->session->savedParams);
                    }
                    
                    if ($this->getRequest()->getPost('edt_issn_original_qty') != $this->view->issnObj->items['ORIGINAL_QTY']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.ORIGINAL_QTY',
                                                                   $this->getRequest()->getPost('edt_issn_original_qty'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    $editIssnIssnStatus = isset($this->view->issnStatusList[$this->getRequest()->getPost('edt_issn_issn_status')]) ? $this->getRequest()->getPost('edt_issn_issn_status') : $this->view->issnObj->items['ISSN_STATUS'];
                    if ($editIssnIssnStatus != $this->view->issnObj->items['ISSN_STATUS']) {
                        $transaction             = new Transaction_UISTA();
                        $transaction->objectId   = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId       = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId     = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->issnStatus = $editIssnIssnStatus;
                        $transaction->qty        = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult       = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    $editIssnProdId = isset($this->view->productList[$this->getRequest()->getPost('edt_issn_prod_id')]) ? $this->getRequest()->getPost('edt_issn_prod_id') : $this->view->issnObj->items['PROD_ID'];
                    if ($editIssnProdId != $this->view->issnObj->items['PROD_ID']) {

                        $transaction                = new Transaction_UIPCA();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->prodIdValue   = $editIssnProdId;
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange              = true;
                        $result                   = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    $editIssnCompanyId = isset($this->view->companyList[$this->getRequest()->getPost('edt_issn_company_id')]) ? $this->getRequest()->getPost('edt_issn_company_id') : $this->view->issnObj->items['COMPANY_ID'];
                    if ($editIssnCompanyId != $this->view->issnObj->items['COMPANY_ID']) {

                        $transaction            = new Transaction_UICOA();
                        $transaction->objectId  = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId      = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId    = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->companyId = $editIssnCompanyId;
                        $transaction->qty       = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult      = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange            = true;
                        $result                 = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    $editIssnDivisionId = isset($this->view->divisionList[$this->getRequest()->getPost('edt_issn_division_id')]) ? $this->getRequest()->getPost('edt_issn_division_id') : $this->view->issnObj->items['DIVISION_ID'];
                    if ($editIssnDivisionId != $this->view->issnObj->items['DIVISION_ID']) {

                        $transaction             = new Transaction_UIDIA();
                        $transaction->objectId   = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId       = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId     = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->divisionId = $editIssnDivisionId;
                        $transaction->qty        = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult       = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other1') != $this->view->issnObj->items['OTHER1']) {

                        $transaction                = new Transaction_UIO1A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other1Value   = $this->getRequest()->getPost('edt_issn_other1');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other2') != $this->view->issnObj->items['OTHER2']) {

                        $transaction                = new Transaction_UIO2A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other2Value   = $this->getRequest()->getPost('edt_issn_other2');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    /**/
                    if ($this->getRequest()->getPost('edt_issn_serial_number') != $this->view->issnObj->items['SERIAL_NUMBER']) {

                        $transaction                = new Transaction_UISNA();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locationId    = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->serialNo      = $this->getRequest()->getPost('edt_issn_serial_number');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    $editIssnStatusCode = isset($this->view->ssnStatusList[$this->getRequest()->getPost('edt_issn_status_code')]) ? $this->getRequest()->getPost('edt_issn_status_code') : $this->view->issnObj->items['STATUS_CODE'];
                    if ($editIssnStatusCode != $this->view->issnObj->items['STATUS_CODE']) {
                        $transaction             = new Transaction_UISCA();
                        $transaction->objectId   = $this->view->issnObj->items['SSN_ID'];
                        $transaction->statusCode = $editIssnStatusCode;
                        try {
                            $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                       'ISSN.STATUS_CODE',
                                                                       $editIssnStatusCode);
                            //-- commented until problem with response from ADD_TRANSACTION_RESPONSE not solved
                            //$currentResult           = $this->minder->doTransactionResponse($transaction);
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_kitted') != $this->view->issnObj->items['KITTED']) {

                        $transaction              = new Transaction_UIKTA();
                        $transaction->objectId    = $this->view->issnObj->items['SSN_ID'];;
                        $transaction->kittedValue = $this->getRequest()->getPost('edt_issn_kitted');
                        try {
                            $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                       'ISSN.KITTED',
                                                                       $this->getRequest()->getPost('edt_issn_kitted'));
                            //-- commented until problem with response from ADD_TRANSACTION_RESPONSE not solved
                            //$currentResult           = $this->minder->doTransactionResponse($transaction);
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    $editIssnCurrentQty = $this->getRequest()->getPost('edt_issn_current_qty', 'none');
                    if ($editIssnCurrentQty != 'none' && $editIssnCurrentQty != $this->view->issnObj->items['CURRENT_QTY']) {
                        
                        if($this->minder->isStockAdjust){
                            
                            $transaction                  = new Transaction_UICQA();
                            $transaction->whId            = $this->view->issnObj->items['WH_ID'];
                            $transaction->locationId      = $this->view->issnObj->items['LOCN_ID'];
                            $transaction->objectId        = $this->view->issnObj->items['SSN_ID'];
                            $transaction->currentQty      = $editIssnCurrentQty;
                            
                            try {
                                $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                            } catch (exception $e) {
                                $currentResult = false;
                            }
                        
                            $tryToChange   = true;
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                                
                        } else {
                            $this->addError('You do not have premissions to update CURRENT_QTY.');
                        }
                    }

                    $editIssnWhId = isset($this->view->warehouseList[$this->getRequest()->getPost('edt_issn_wh_id')]) ? $this->getRequest()->getPost('edt_issn_wh_id') : $this->view->issnObj->items['WH_ID'];
                    if ($editIssnWhId != $this->view->issnObj->items['WH_ID'] || $this->getRequest()->getPost('edt_issn_locn_id') != $this->view->issnObj->items['LOCN_ID']) {
                        
                        $transaction            =   new Transaction_TROLA();
                        $transaction->objectId  =   $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId      =   $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId    =   $this->view->issnObj->items['LOCN_ID'];
                        $transaction->reference =   'Transferred by ISSN Edit';
                        
                        try{
                            $currentResult = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (Exception $ex){
                            $currentResult = false;    
                        }
                        
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                        
                        $transaction            =   new Transaction_TRILA();
                        $transaction->objectId  =   $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId      =   $editIssnWhId;
                        $transaction->locnId    =   $this->getRequest()->getPost('edt_issn_locn_id');
                        $transaction->reference =   'Transferred by ISSN Edit';
                        
                        try{
                            $currentResult = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    '); 
                        } catch(Exception $ex){
                            $currentResult = false;    
                        }
                        
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_into_date') != $this->view->issnObj->items['INTO_DATE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.INTO_DATE',
                                                                   $this->getRequest()->getPost('edt_issn_into_date'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_pick_order') != $this->view->issnObj->items['PICK_ORDER']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PICK_ORDER',
                                                                   $this->getRequest()->getPost('edt_issn_pick_order'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    $editIssnAudited = isset($this->view->auditCodeList[$this->getRequest()->getPost('edt_issn_audited')]) ? $this->getRequest()->getPost('edt_issn_audited') : $this->view->issnObj->items['AUDITED'];
                    if ($editIssnAudited != $this->view->issnObj->items['AUDITED']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.AUDITED',
                                                                   $editIssnAudited);
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_prev_package_type') != $this->view->issnObj->items['ISSN_PREV_PACKAGE_TYPE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.ISSN_PREV_PACKAGE_TYPE',
                                                                   $this->getRequest()->getPost('edt_issn_prev_package_type'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    $editIssnPackageType = isset($this->view->packageTypeList[$this->getRequest()->getPost('edt_issn_package_type')]) ? $this->getRequest()->getPost('edt_issn_package_type') : $this->view->issnObj->items['ISSN_PACKAGE_TYPE'];
                    if ($editIssnPackageType != $this->view->issnObj->items['ISSN_PACKAGE_TYPE']) {
                        
                        $transaction                  = new Transaction_UIPTA();
                        $transaction->whId            = $this->view->issnObj->items['WH_ID'];
                        $transaction->locationId      = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->objectId        = $this->view->issnObj->items['SSN_ID'];
                        $transaction->qty             = $this->view->issnObj->items['CURRENT_QTY'];
                        $transaction->issnPackageType = $editIssnPackageType;
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                    
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_pack_id') != $this->view->issnObj->items['PACK_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PACK_ID',
                                                                   $this->getRequest()->getPost('edt_issn_pack_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_despatch_id') != $this->view->issnObj->items['DESPATCH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.DESPATCH_ID',
                                                                   $this->getRequest()->getPost('edt_issn_despatch_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_prev_prod_id_update') != $this->view->issnObj->items['PREV_PREV_PROD_ID_UPDATE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PREV_PROD_ID_UPDATE',
                                                                   $this->getRequest()->getPost('prev_prev_prod_id_update'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('despatched_date') != $this->view->issnObj->items['DESPATCHED_DATE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.DESPATCHED_DATE',
                                                                   $this->getRequest()->getPost('despatched_date'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_reserved_no') != $this->view->issnObj->items['PREV_PICK_ORDER']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PICK_ORDER',
                                                                   $this->getRequest()->getPost('prev_reserved_no'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('picked_qty') != $this->view->issnObj->items['PICKED_QTY']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PICKED_QTY',
                                                                   $this->getRequest()->getPost('picked_qty'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_prev_resvd_no') != $this->view->issnObj->items['PREV_PREV_PICK_ORDER']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PREV_PICK_ORDER',
                                                                   $this->getRequest()->getPost('prev_prev_resvd_no'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_pack_id') != $this->view->issnObj->items['PREV_PACK_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PACK_ID',
                                                                   $this->getRequest()->getPost('prev_pack_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_despatch_id') != $this->view->issnObj->items['PREV_DESPATCH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_DESPATCH_ID',
                                                                   $this->getRequest()->getPost('prev_despatch_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_prev_desp_id') != $this->view->issnObj->items['PREV_PREV_DESPATCH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PREV_DESPATCH_ID',
                                                                   $this->getRequest()->getPost('prev_prev_desp_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('issn_description') != $this->view->issnObj->items['ISSN_DESCRIPTION']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.ISSN_DESCRIPTION',
                                                                   $this->getRequest()->getPost('issn_description'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                   
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other4') != $this->view->issnObj->items['OTHER4']) {

                        $transaction                = new Transaction_UIO4A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other4Value   = $this->getRequest()->getPost('edt_issn_other4');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other3') != $this->view->issnObj->items['OTHER3']) {

                        $transaction                = new Transaction_UIO3A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other3Value   = $this->getRequest()->getPost('edt_issn_other3');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    

                    if ($tryToChange == true) {
                        if ($result === true) {
                            $this->addMessage('Record ' . $this->view->issnObj->items['SSN_ID'] . ' updated successfully');
                        } else {
                            $this->addError('Record ' . $this->view->issnObj->items['SSN_ID'] . '. Error occured during update');
                        }
                    }
                    break;

            }

            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto($this->session->action,
                                     $this->session->controller,
                                     $this->session->module,
                                     $this->session->savedParams);
            return;
        }

    }

    public function calcAction()
    {
        $calculatedValue = 0; // calculated value
        $count           = 0; // number of elements used for calculation

        $field  = strtoupper($this->getRequest()->getParam('field'));
        $method = $this->getRequest()->getParam('method');
        $id     = $this->getRequest()->getParam('id');
        $value  = $this->getRequest()->getParam('value');
        $action = $this->getRequest()->getParam('inAction');

		$show_by = $this->session->navigation[$this->_request->getControllerName()]['index']['show_by'];
        $pageselector = $this->session->navigation[$this->_request->getControllerName()]['index']['pageselector'];
        
        
        switch (strtolower($action)) {
            default:
               //-- setup conditions

                $allowed =  array('issn_no'         => 'SSN_ID LIKE ?',
                                  'location_id'     => 'LOCN_ID LIKE ?',
                                  'product_id'      => 'PROD_ID LIKE ?',
                                  'status'          => 'ISSN_STATUS = ?',
                                  'company_id'      => 'COMPANY_ID LIKE ?',
                                  'original_ssn'    => 'ORIGINAL_SSN LIKE ?',
                                  'create_date_from'=> 'CREATE_DATE >= ?',
                                  'create_date_to'  => 'CREATE_DATE <= ?',
                                  'other1'          => 'OTHER1 LIKE ?',
                                  'other2'          => 'OTHER2 LIKE ?' 
                                 );

                $conditions = $this->_setupConditions($action, $allowed);
                $clause     = $this->_makeClause($conditions, $allowed);
                
                $parserObj = new ParserSql('ISSN.PROD_ID');

                if(!empty($conditions['product_id'])) {
                    $parserObj->setupStr($conditions['product_id']);
                    $parserObj->parse();
                    if(!$parserObj->lastError) {
                        $clause[$parserObj->parsedStr] = '';
                    } else {
                        $this->view->parseProdIdErr = $parserObj->errorMsg;
                    }
                }
                
                $parserObj = new ParserSql('ISSN.LOCN_ID');
                
                if(!empty($conditions['location_id'])) {
                    $parserObj->setupStr($conditions['location_id']);
                    $parserObj->parse();
                    if(!$parserObj->lastError) {
                        $clause[$parserObj->parsedStr] = '';
                    } else {
                        $this->view->parseLocationIdErr = $parserObj->errorMsg;
                    }
                }

                //-- get appropriate lines
                $lines = $this->minder->getIssns($clause, 'edit', $pageselector, $show_by);
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
        }

        $data = array();
        $data['selected_num'] = $count;
        $data['total_qty'] = $calculatedValue;
        $this->view->data = $data;
    }

    protected function _setupShortcuts()
    {
        $shortcuts = array(
            '<ISSN>'              => $this->view->url(array('action' => 'index', 'controller' => 'issn', 'module' => 'warehouse'), '', true),
            'SSN'                 => $this->view->url(array('action' => 'index', 'controller' => 'ssn2', 'module' => 'warehouse'), '', true),
            'Transfer'            => 
                                     array(
                                            'Place ISSN Into'         => $this->view->url(array('controller' => 'transfer', 'action' => 'into'), null, true),
                                            'Transfer Whole Location' => $this->view->url(array('controller' => 'transfer', 'action' => 'whole'), null, true),
                                            'Transfer Moveable'       => $this->view->url(array('controller' => 'transfer', 'action' => 'moveable'), null, true),
                                     ),
            'Products'            => $this->view->url(array('action' => 'index', 'controller' => 'products2', 'module' => 'warehouse'), '', true),
            'Product Details'     => $this->view->url(array('action' => 'index', 'controller' => 'product-details', 'module' => 'warehouse'), '', true),
            'Location'            =>
                                     array(
                                        'Locations List'    => $this->view->url(array('action' => 'index', 'controller' => 'location2', 'module' => 'warehouse'), '', true),
                                     )
         
        );
        
        $this->view->shortcuts = $shortcuts;
        
        return true;
    }

    protected function _setupHeaders()
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        if (!parent::_setupHeaders()) {
           $this->session->headers[$this->_controller][$this->_action][$this->view->tableId] =
                $this->view->headers = $this->minder->getSelectField( "ISSN");
            /*                           array('SSN_ID'          => 'ISSN',
                                             'WH_ID'           => 'WH.',
                                             'LOCN_ID'         => 'Location',
                                             'PROD_ID'         => 'Product ID',
                                             'CURRENT_QTY'     => 'Curr Qty',
                                             'SSN_CREATE_DATE' => 'Created Date',
                                             'COMPANY_ID'      => 'Company ID',
                                             'ISSN_STATUS'     => 'Status',
                                             'SSN_DESCRIPTION' => 'SSN Description',
                                             'OTHER1'          => $this->view->other1Name,
                                             'PICK_ORDER'      => 'Order #',
                                             'PICK_LABEL_NO'   => 'Label #',
                                             'REASON'          => 'Reason');*/
            //$log->info('headers:' . print_r($this->view->headers,true));
            //$sqlFields =  $this->minder->getSelectField( "ISSN");
            //$log->info('sqlFields:' . print_r($sqlFields,true));
        }
        return true;
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
            case 'variety':
                $tdata = $this->minder->getVarietyList($this->getRequest()->getParam('ssn_type'));
                break;
            default:
                break;
        }
        if (strtolower($this->getRequest()->getParam('slice')) == 'yes') {
            if (count($tdata) > 10) {
                $tdata = array_slice($tdata, 0, 10, true);
            }
        }
        //var_dump($tdata);
        $this->view->data = $tdata;
    }
}

