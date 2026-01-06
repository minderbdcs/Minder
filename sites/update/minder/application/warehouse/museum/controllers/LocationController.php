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
 * Warehouse_LocationController
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
class Warehouse_LocationController extends Minder_Controller_Action
{
    /**
     * Populates list and default values for Location search screen
     * also process conditions while do searching
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->pageTitle = 'Search Location';
        $this->view->conditions = array();

        $this->_preProcessNavigation();

        //-- setup conditions
        $allowed = array('locn_id'                => 'locn_id like ?',
                         'wh_id'                  => 'wh_id like ?',
                         'move_stat'              => 'move_stat = ?',
                         'locn_type'              => 'locn_type = ?',
                         'store_area'             => 'store_area = ?',
                         'locn_name'              => 'locn_name like ?',
                         'prod_id'                => 'prod_id = ?',
                         'locn_stat'              => 'locn_stat = ?',
                         'store_type'             => 'store_type = ?',
                         'store_meth'             => 'store_meth = ?',
                         'moveable_locn'          => 'moveable_locn = ?',
                         'current_empty_locn'     => 'COUNT_ISSN = 0',
                         'replenish'              => 'replenish = ?',
                         'perm_level'             => 'perm_level = ?',
                         'temperature_zone'       => 'temperature_zone = ?',
                         'from_last_audited_date' => 'last_audited_date >= ?',
                         'to_last_audited_date'   => 'last_audited_date <= ?'
                         );

        $conditions = $this->_setupConditions(null, $allowed);

        $clause = $this->_makeClause($conditions, $allowed);

        $this->_setupHeaders();

        $allowed4update = array('move_stat_upd'        => 'MOVE_STAT = ?',
                                'unit_type_upd'        => 'LOCN_TYPE = ?',
                                'store_type_upd'       => 'STORE_TYPE = ?',
                                'perm_level_upd'       => 'PERM_LEVEL = ?',
                                'locn_stat_upd'        => 'LOCN_STAT = ?',
                                'store_area_upd'       => 'STORE_AREA = ?',
                                'store_meth_upd'       => 'STORE_METH = ?',
                                'temperature_zone_upd' => 'TEMPERATURE_ZONE = ?');

        $this->_preProcessNavigation();
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
       
        $result = $this->minder->getLocationLines($clause, $pageSelector, $showBy);
        $lines  = $this->view->lines  = $result['data'];
        $data = array();
        foreach ($lines as $line) {
            if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                $data[$line->id] = $line->items;
            }
        }
        $this->view->data = $data;
        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('report-csv');
                return;

            case 'REPORT: XML':
                $response = $this->getResponse();
                $response->setHeader('Content-type', 'application/octet-stream');
                $response->setHeader('Content-type', 'application/force-download');
                $response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
                $this->render('report-xml');
                return;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('report-xls');
                return;

            case 'REPORT: TXT':
                $this->getResponse()->setHeader('Content-Type', 'text/plain')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.txt"');
                $this->render('report-txt');
                return;
            
            case 'PRINT LABEL':
            
                foreach ($lines as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        $data[$line->id] = $line;
                    }
                }
            
                if(count($data) > 0){
                   $printerObj    =    $this->minder->getPrinter();
                      $result       =    false;
                      $count        =    0;
                   
                   if($this->minder->defaultControlValues['GENERATE_LABEL_TEXT'] == 'T'){
                        foreach($data as $location) {
                        
                            $result         =    $printerObj->printLocationLabel($location);
                            if($result['RES'] < 0){
                                $this->view->flash = 'Error while print label(s):' . $result['ERROR_TEXT'];
                                break;    
                            }

                            $count++;
                        }
                        if($result['RES'] >= 0){
                           $this->view->flash = $count . ' label(s) printed successfully';
                        } 
                   } else {
                       foreach($data as $location) {
                            $locationList   =   $this->minder->getLocationForPrint($location['LOCN_ID']);
                            $result         =   $printerObj->printLocationLabel($locationList);
                            if($result['RES'] < 0){
                                $this->view->flash = 'Error while print label(s):' . $result['ERROR_TEXT'];
                                break;    
                            }
                            $count++;
                       }
                       if($result['RES'] >= 0){
                           $this->view->flash = $count . ' label(s) printed successfully';
                       }     
                   }
                } else {
                    $this->view->flash = 'Missing data for printing';
                }
                     
               break;
        
            case 'MASS UPDATE':
                $flash = array();
                $fieldsToUpdate = array();
                $locationToUpdate = array();
                $currentResult = true;
                $result = true;
                foreach ($this->getRequest()->getPost() as $key => $val) {
                    if (array_key_exists($key, $allowed4update) && null != $val) {
                        $fieldsToUpdate[$allowed4update[$key]] = $val;
                    }
                }
                if (count($fieldsToUpdate) > 0 ) {
                    foreach ($lines as $line) {
                        if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                            $location = array('LOCN_ID' => $line->items['LOCN_ID'],
                                                          'WH_ID'   => $line->items['WH_ID']);
                            $currentResult = $this->minder->updateLocationSelectively($fieldsToUpdate, $location);
                            $result = $result && $currentResult;
                            if (false == $currentResult) {
                                $flash[] = $location['LOCN_ID'] . ' ' . $location['WH_ID'] . ' update failed - ' . $this->minder->lastError;
                            } else {
                                //-- unset checkbox after success update
                                $conditions[$line->id] = 'off';
                                $flash[] = $location['LOCN_ID'] . ' ' . $location['WH_ID'] . ' - ' . $this->minder->lastError;
                            }
                        }
                    }
                    $this->session->conditions['location'] = $conditions;
                    $this->view->conditions                = $conditions;
                    
                    $this->_preProcessNavigation();
                    $pageSelector       = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
                    $showBy             = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
                    $result             = $this->minder->getLocationLines($clause, $pageSelector, $showBy);
                    $this->view->lines  = $response['data'];
                } else {
                    $flash[] = 'No fields to update.';
                }
                $this->view->flash = $flash;
                break;
        }
        $this->view->searchFields = array('prod_id_srch'      => 'Product ID',
                                          'short_desc_srch'   => 'Short Description',
                                          'long_desc_srch'    => 'Long Description');

        $this->view->companyIdList       = $this->fillList('', "getCompanyList");
        $this->view->productIdList       = $this->fillList('', "getProductList");
        $this->view->locnStatList        = $this->fillList('', "getLocationStatusList");
        $this->view->moveStatList        = $this->fillList('', "getMoveStatusList");
        $this->view->locnTypeList        = $this->fillList('', "getLocationTypeList");
        $this->view->storeTypeList       = $this->fillList('', "getStoreTypeList");
        $this->view->storeAreaList       = $this->fillList('', "getStoreAreaList");
        $this->view->storeMethList       = $this->fillList('', "getStoreMethodList");
        $this->view->warehouses          = $this->fillList('', 'getWarehouseList');
        $this->view->temperatureZoneList = $this->fillList('', 'getTemperatureZoneList');
        $this->view->permLevelList       = $this->fillList('', 'getPermissionLevelList');
        $this->view->unitTypeList        = array();

        $tempArray = $this->fillList('',
                                     "getPersonList",
                                     array(array('CO', 'CS', 'IS', 'RP')));

        if (count($tempArray) > 0) {
            $this->view->supplierIdList = array_combine(array_keys($tempArray), array_keys($tempArray));
        } else {
            $this->view->supplierIdList = array('' => '');
        }

        $this->_postProcessNavigation($result);

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
        switch ($src) {
            case 'prod_id_srch':
                $tdata = $this->minder->getProductList($param);
                break;
            case 'company_id_srch':
                $tdata = $this->minder->getCompanyList($param);
                break;
            case 'alternate_id_srch':
                $tdata = $this->minder->getAlternateProductList($param);
                break;
                case 'short_desc_srch':
                $tdata = $this->minder->getProductShortDescriptionList($param);
                break;
            case 'long_desc_srch':
                $tdata = $this->minder->getProductLongDescriptionList($param);
                break;
            default:
                $tdata = array();
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
        $this->view->data = $tdata;
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

        switch (strtolower($action)) {
            case 'show':
                if (isset($this->session->conditions['location']['show'])) {
                    $conditions = $this->session->conditions['location']['show'];
                } else {
                    $conditions = array();
                }
                $issnClause = array('ISSN.LOCN_ID = ?' => $this->getRequest()->getParam('locn_id'),
                                    'ISSN.WH_ID = ?'   => $this->getRequest()->getParam('wh_id'));
                $lines = $this->minder->getIssns($issnClause);
                $conditions = $this->_markSelected($lines, $id, $value, $method, $action);
            break;
            default:
                $allowed = array('locn_id'                => 'locn_id like ?',
                                 'wh_id'                  => 'wh_id like ?',
                                 'move_stat'              => 'move_stat = ?',
                                 'locn_type'              => 'locn_type = ?',
                                 'store_area'             => 'store_area = ?',
                                 'locn_name'              => 'locn_name like ?',
                                 'prod_id'                => 'prod_id = ?',
                                 'locn_stat'              => 'locn_stat = ?',
                                 'store_type'             => 'store_type = ?',
                                 'store_meth'             => 'store_meth = ?',
                                 'moveable_locn'          => 'moveable_locn = ?',
                                 'current_empty_locn'     => 'COUNT_ISSN = 0',
                                 'replenish'              => 'replenish = ?',
                                 'perm_level'             => 'perm_level = ?',
                                 'temperature_zone'       => 'temperature_zone = ?',
                                 'from_last_audited_date' => 'last_audited_date >= ?',
                                 'to_last_audited_date'   => 'last_audited_date <= ?');
                $conditions = $this->_setupConditions($action, $allowed);
                $clause = $this->_makeClause($conditions, $allowed);

                $this->_preProcessNavigation();
                $pageSelector       = $this->session->navigation[$this->_controller][$action]['pageselector'];
                $showBy             = $this->session->navigation[$this->_controller][$action]['show_by'];
                $result             = $this->minder->getLocationLines($clause, $pageSelector, $showBy);
                $lines              = $result['data'];  
                
                $conditions = $this->_markSelected($lines, $id, $value, $method, $action);
            break;
        }

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
                for ($i = 0; $i < $numRecords; $i++) {
                    if (false !== array_search($lines[$i]->id, $conditions, true )) {
                        $count++;
                    }
                }
                break;
        }


        $data = array();
        $data['selected_num'] = $count;
        $data['total_qty'] = $calculatedValue;
        $this->view->data = $data;
    }

    public function showAction()
    {
        $this->session->action     = 'show';
        $this->session->controller = 'location';
        $this->session->module     = 'warehouse';
        $savedParams = array();
        foreach ($this->getRequest()->getParams() as $key => $val) {
            if ('module' != $key && 'controller' != $key && 'action' != $key) {
                $savedParams[$key] = $val;
            }
        }
        $this->session->savedParams = $savedParams;

        //-- preprocess input of navigation values
        if (isset($this->session->navigation['location']['show'])) {
            foreach ($this->session->navigation['location']['show'] as $key => $val) {
                if (null != $this->getRequest()->getParam($key)) {
                    $this->session->navigation['location']['show'][$key] = (int)$this->getRequest()->getParam($key);
                }
            }
        } else {
            $this->session->navigation['location']['show']['show_by']      = 15;
            $this->session->navigation['location']['show']['pageselector'] = 0;
        }

        $this->view->navigation = $this->session->navigation['location']['show'];
        //-- end process input navigation values

        //-- setup conditions
        $allowed = array('locn_id'                => 'locn_id like ?',
                           'wh_id'                  => 'wh_id like ?',
                           'move_stat'              => 'move_stat = ?',
                           'locn_type'              => 'locn_type = ?',
                           'store_area'             => 'store_area = ?',
                           'locn_name'              => 'locn_name like ?',
                           'prod_id'                => 'prod_id = ?',
                           'locn_stat'              => 'locn_stat = ?',
                           'store_type'             => 'store_type = ?',
                           'store_meth'             => 'store_meth = ?',
                           'moveable_locn'          => 'moveable_locn = ?',
                           'current_empty_locn'     => 'COUNT_ISSN = 0',
                           'replenish'              => 'replenish = ?',
                           'perm_level'             => 'perm_level = ?',
                           'temperature_zone'       => 'temperature_zone = ?',
                           'from_last_audited_date' => 'last_audited_date >= ?',
                           'to_last_audited_date'   => 'last_audited_date <= ?'
                           );

        if (isset($this->session->conditions['location']['show'])) {
            $conditions = $this->session->conditions['location']['show'];
        } else {
            $conditions = array();
        }
        $this->session->conditions['location']['show'] = $conditions;
        $this->view->conditions            = $conditions;
        //-- End setup conditions
        //-- convert screen filter conditions to Minder acceptable format for preparing query
        $clause = array();
        foreach ($conditions as $key => $val) {
            if (array_key_exists($key, $allowed)) {
                if (null != $val) {
                    $clause[strtoupper($allowed[$key])] = $val;
                }
            }
        }
        //-- apply current LOCN_ID and WH_ID from url
        foreach ($this->getRequest()->getParams() as $key => $val) {
            if (array_key_exists($key, $allowed)) {
                $clause[strtoupper($allowed[$key])] = $val;
            }
        }
        //-- end conversion

        $line = $this->minder->getLocations($clause);
        $line = $line[0];
        $this->view->line = $line;
        $this->view->pageTitle = "Location: " . $line->items['WH_ID'] . ' ' . $line->items['LOCN_ID'];
        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        $this->view->issnHeaders = array('SSN_ID'          => 'ISSN',
                                         'WH_ID'           => 'WH.',
                                         'LOCN_ID'         => 'Location',
                                         'PROD_ID'         => 'Product ID',
                                         'CURRENT_QTY'     => 'Curr Qty',
                                         'SSN_CREATE_DATE' => 'Created Date',
                                         'COMPANY_ID'      => 'Company ID',
                                         'ISSN_STATUS'     => 'Status',
                                         'SSN_DESCRIPTION' => 'SSN Description',
                                         'OTHER1'          => $this->view->other1Name);

        $issnClause = array('ISSN.LOCN_ID = ?' => $this->getRequest()->getParam('locn_id'),
                            'ISSN.WH_ID = ?'   => $this->getRequest()->getParam('wh_id'));
        $this->view->issns = $this->minder->getIssns($issnClause);

        $lines = $this->view->issns;
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
            case 'ADD NEW LOCATION':
                $params = array('locn_id' => $this->getRequest()->getParam('locn_id'),
                                'wh_id'   => $this->getRequest()->getParam('wh_id'));
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('add',
                                         'location',
                                         'warehouse',
                                         $params);
                break;
            case 'EDIT LOCATION':
                $params = array('locn_id' => $this->getRequest()->getParam('locn_id'),
                                'wh_id'   => $this->getRequest()->getParam('wh_id'));
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('edit',
                                         'location',
                                         'warehouse',
                                         $params);
                break;
            case 'DELETE LOCATION':
                $params = array();
                $flash = array();
                if (count($lines) > 0) {
                    $flash[] = 'Must TRANSFER ISSN first';
                    $this->view->flash = $flash;
                    break;
                } else {
                    if (false == $this->minder->deleteLocation($line)) {
                        $this->view->flashMessenger->addMessage('Location NOT deleted. ' . $this->minder->lastError);
                    } else {
                        $this->view->flashMessenger->addMessage('Location successfully deleted.');
                    }
                }
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('index',
                                         'location',
                                         'warehouse',
                                         $params);
                break;
            case 'RETURN':
                $params = array();
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('index',
                                         'location',
                                         'warehouse',
                                         $params);
                break;
            case 'REPORT: CSV':
                $data = array();
                foreach ($lines as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        $data[$line->id] = $line->items;
                    }
                }
                $this->view->data = $data;
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('report-csv');
                return;
                break;
            case 'TRANSFER':
                $flash = array();
                foreach ($lines as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        if (null != $this->getRequest()->getPost('wh_id_upd')) {
                            $flash[] = 'Transaction TRIL is not implemented yet';
                            /*$transaction = new Transaction_TRILA();
                            $transaction->objectId    = $line->id;
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            if (false == $currentResult) {
                                $flash[] = $val . ' update Product ID failed - ' . $this->minder->lastError;
                            } else {
                                $flash[] = $val . ' - ' . $this->minder->lastError;
                            }*/
                        }
                        if (null != $this->getRequest()->getPost('locn_id_upd')) {
                            $flash[] = 'Transaction TROL is not implemented yet';
                            /*$transaction = new Transaction_TROLA();
                            $transaction->objectId  = $line->id;
                            $currentResult          = $this->minder->doTransactionResponse($transaction);
                            if (false == $currentResult) {
                                $flash[] = $val . ' update Company ID failed - ' . $this->minder->lastError;
                            } else {
                                $flash[] = $val . ' - ' . $this->minder->lastError;
                            }*/
                        }
                    }
                }
                if (count($flash) > 0) {
                    $this->view->flash = $flash;
                }
                $this->view->issns = $this->minder->getIssns($issnClause);
                break;
            case 'MASS ISSN UPDATE':
                $flash = array();
                foreach ($lines as $line) {
                    if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                        if (null != $this->getRequest()->getPost('prod_id_upd')) {
                            $transaction = new Transaction_UIPCA();
                            $transaction->objectId    = $line->id;
                            $transaction->prodIdValue = $this->getRequest()->getPost('prod_id_upd');
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            If (false == $currentResult) {
                                $flash[] = $val . ' update Product ID failed - ' . $this->minder->lastError;
                            } else {
                                $flash[] = $val . ' - ' . $this->minder->lastError;
                            }
                        }
                        if (null != $this->getRequest()->getPost('company_id_upd')) {
                            $transaction = new Transaction_UICOA();
                            $transaction->objectId  = $line->id;
                            $transaction->companyId = $this->getRequest()->getPost('company_id_upd');
                            $currentResult          = $this->minder->doTransactionResponse($transaction);
                            If (false == $currentResult) {
                                $flash[] = $val . ' update Company ID failed - ' . $this->minder->lastError;
                            } else {
                                $flash[] = $val . ' - ' . $this->minder->lastError;
                            }
                        }
                        if (null != $this->getRequest()->getPost('issn_stat_upd')) {
                            $transaction = new Transaction_UISTA();
                            $transaction->objectId   = $line->id;
                            $transaction->issnStatus = $this->getRequest()->getPost('issn_stat_upd');
                            $currentResult           = $this->minder->doTransactionResponse($transaction);
                            If (false == $currentResult) {
                                $flash[] = $val . ' update ISSN Status failed - ' . $this->minder->lastError;
                            } else {
                                $flash[] = $val . ' - ' . $this->minder->lastError;
                            }
                        }
                    }
                }
                if (count($flash) >0) {
                    $this->view->flash = $flash;
                }
                $this->view->issns = $this->minder->getIssns($issnClause);
            default:
               break;
        }

        $this->view->companyIdList  = $this->fillList('', "getCompanyList");
        $this->view->productIdList  = $this->fillList('', "getProductList");
        $this->view->issnStatusList = $this->fillList('', "getIssnStatusList");
        $this->view->locationList   = $this->fillList('', 'getLocationList');
        $this->view->warehouseList  = $this->fillList('', 'getWarehouseList');

        $this->view->searchFields = array();

        //-- post process navigation
        $this->view->numRecords = count($this->view->issns);

        if ($this->view->numRecords <= $this->view->navigation['show_by']) {
            $this->session->navigation['location']['show']['pageselector'] = 0;
        }
        $this->view->navigation = $this->session->navigation['location']['show'];

        if (($this->view->navigation['show_by'] * ($this->view->navigation['pageselector'] + 1)) > $this->view->numRecords) {
            $this->view->navigation['pageselector'] = $this->session->navigation['location']['show']['pageselector']
                                                    = (int)floor($this->view->numRecords / $this->view->navigation['show_by']);
            $this->view->maxno = $this->view->numRecords - ($this->view->navigation['show_by'] * $this->view->navigation['pageselector']);
        } else {
            $this->view->maxno = $this->view->navigation['show_by'];
        }
        //-- end post process

        $this->view->pages      = array();
        for ($i = 1; $i <= ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
            $this->view->pages[] = $i;
        }
        $this->view->issns = array_slice($this->view->issns,
                                         $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
                                         $this->view->maxno);
    }

    public function editAction()
    {
        $params = array('locn_id' => $this->getRequest()->getParam('locn_id'),
                        'wh_id'   => $this->getRequest()->getParam('wh_id'));

        $clause = array('LOCN_ID = ? ' => $this->getRequest()->getParam('locn_id'),
                        'WH_ID = ? '   => $this->getRequest()->getParam('wh_id'));
        $this->view->locationObj = current($this->minder->getLocations($clause));
        switch ($this->getRequest()->getPost('action')) {
            case 'SAVE & RETURN':
                $tmp = clone($this->view->locationObj);
                $this->view->locationObj->save($this->getRequest()->getPost());

                //-- Validate mandatory Input
                $notValid = array();
                if (null == $this->view->locationObj->items['WH_ID']) {
                    $notValid[] = 'Warehouse ID: should not be empty';
                }
                if (strlen($this->view->locationObj->items['LOCN_ID']) != 8 &&
                    false == (strlen($this->view->locationObj->items['LOCN_ID']) == 2 && $this->view->locationObj->items['WH_ID'] == 'SY')) {
                    $notValid[] = 'Location ID: shoud be 2 or 8 characters.';
                }
                if (null == $this->view->locationObj->items['MOVE_STAT']) {
                    $notValid[] = 'Transfer Status: should not be empty.';
                }
                if (null == $this->view->locationObj->items['STORE_TYPE']) {
                    $notValid[] = 'Storage Type Code: should not be empty.';
                }
                if (null == $this->view->locationObj->items['STORE_AREA']) {
                    $notValid[] = 'Inventory Area Code: should not be empty.';
                }
                if ('SY' != $this->view->locationObj->items['WH_ID'] &&
                    trim($this->view->locationObj->items['INSTANCE_ID'], ' ') != 'MASTER') {
                    $notValid[] = 'Instance ID: should be \'MASTER\' for WH_ID = \'' . $this->view->locationObj->items['WH_ID'] . '\'';
                } elseif ('SY' != $this->view->locationObj->items['WH_ID']) {
                    $this->view->locationObj->items['INSTANCE_ID'] = 'MASTER    ';
                }
                if (count($notValid) > 0) {
                    $this->view->flash = $notValid;
                    break;
                } else {
                    if (false == $this->minder->updateLocation($this->view->locationObj)) {
                        $this->view->flash[] = $this->minder->lastError;
                        break;
                    } else {
                        $this->view->flashMessenger->addMessage('Location successfully updated.');
                        ;
                    }
                    /*foreach ($this->view->locationObj->items as $key => $val) {
                        if ($tmp->items[$key] != $val) {
                            $updateList[$key] = $val;
                        }
                    }*/
                }
                //-- end of Validation
            case 'CANCEL CHANGES':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('show',
                                         'location',
                                         'warehouse',
                                         $params);
                break;
            default:
                break;
        }
        // list of fields with autocomplete
        $this->view->searchFields = array();

        $this->view->warehouseList       = $this->fillList('', 'getWarehouseList');
        $this->view->companyIdList       = $this->fillList('', "getCompanyList");
        $this->view->productIdList       = $this->fillList('', "getProductList");
        $this->view->locnStatList        = $this->fillList('', "getLocationStatusList");
        $this->view->moveStatList        = $this->fillList('', "getMoveStatusList");
        $this->view->locnTypeList        = $this->fillList('', "getLocationTypeList");
        $this->view->storeTypeList       = $this->fillList('', "getStoreTypeList");
        $this->view->storeAreaList       = $this->fillList('', "getStoreAreaList");
        $this->view->storeMethList       = $this->fillList('', "getStoreMethodList");
        $this->view->warehouses          = $this->fillList('', 'getWarehouseList');
        $this->view->temperatureZoneList = $this->fillList('', 'getTemperatureZoneList');
        $this->view->permLevelList       = $this->fillList('', 'getPermissionLevelList');
        $this->view->zoneList            = $this->fillList('', 'getZoneList');
        $this->view->locationMetricList  = $this->fillList('', 'getLocationMetricList');
        $this->view->costCenterList      = $this->fillList('', 'getCostCentreList');
        $this->view->togList             = $this->fillList('', 'getTurnoverGroupList');
        $this->view->packTypeList        = $this->fillList('', 'getPackagingTypeList');
        $this->view->locationList        = $this->fillList('', 'getLocationList');
    }

    public function addAction()
    {
        $params = array('locn_id' => $this->getRequest()->getParam('locn_id'),
                        'wh_id'   => $this->getRequest()->getParam('wh_id'));

        $this->view->locationObj = new Location();
        switch ($this->getRequest()->getPost('action')) {
            case 'SAVE & RETURN':
                $updateList = array();
                $tmp = clone($this->view->locationObj);
                $this->view->locationObj->save($this->getRequest()->getPost());

                //-- Validate mandatory Input
                $notValid = array();
                if (null == $this->view->locationObj->items['WH_ID']) {
                    $notValid[] = 'Warehouse ID: should not be empty';
                }
                if (strlen($this->view->locationObj->items['LOCN_ID']) != 8 &&
                false == (strlen($this->view->locationObj->items['LOCN_ID']) == 2 && $this->view->locationObj->items['WH_ID'] == 'SY')) {
                    $notValid[] = 'Location ID: shoud be 2 or 8 characters.';
                }
                if (null == $this->view->locationObj->items['MOVE_STAT']) {
                    $notValid[] = 'Transfer Status: should not be empty.';
                }
                if (null == $this->view->locationObj->items['STORE_TYPE']) {
                    $notValid[] = 'Storage Type Code: should not be empty.';
                }
                if (null == $this->view->locationObj->items['STORE_AREA']) {
                    $notValid[] = 'Inventory Area Code: should not be empty.';
                }
                if ('SY' != $this->view->locationObj->items['WH_ID'] &&
                    trim($this->view->locationObj->items['INSTANCE_ID'], ' ') != 'MASTER') {
                    $notValid[] = 'Instance ID: should be \'MASTER\' for WH_ID = \'' . $this->view->locationObj->items['WH_ID'] . '\'';
                } elseif ('SY' != $this->view->locationObj->items['WH_ID']) {
                    $this->view->locationObj->items['INSTANCE_ID'] = 'MASTER    ';
                }
                if (count($notValid) > 0) {
                    $this->view->flash = $notValid;
                    break;
                } else {
                    if (false === $this->minder->createLocation($this->view->locationObj)) {
                        $this->view->flash = $this->minder->lastError;
                        break;
                    } else {
                        $this->view->flashMessenger->addMessage('Location successfully added.');
                        $params = array('locn_id' => $this->view->locationObj->items['LOCN_ID'],
                                        'wh_id' => $this->view->locationObj->items['WH_ID']);
                    }
                }
                //-- end of Validation
            case 'CANCEL CHANGES':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('index',
                                         'location2',
                                         'warehouse');
                break;
            default:
                break;
        }
        $this->view->searchFields = array();

        $this->view->warehouseList          = $this->fillList('', 'getWarehouseList');
        $this->view->companyIdList       = $this->fillList('', "getCompanyList");
        $this->view->productIdList       = $this->fillList('', "getProductList");
        $this->view->locnStatList        = $this->fillList('', "getLocationStatusList");
        $this->view->moveStatList        = $this->fillList('', "getMoveStatusList");
        $this->view->locnTypeList        = $this->fillList('', "getLocationTypeList");
        $this->view->storeTypeList       = $this->fillList('', "getStoreTypeList");
        $this->view->storeAreaList       = $this->fillList('', "getStoreAreaList");
        $this->view->storeMethList       = $this->fillList('', "getStoreMethodList");
        $this->view->warehouses          = $this->fillList('', 'getWarehouseList');
        $this->view->temperatureZoneList = $this->fillList('', 'getTemperatureZoneList');
        $this->view->permLevelList       = $this->fillList('', 'getPermissionLevelList');
        $this->view->zoneList             = $this->fillList('', 'getZoneList');
        $this->view->locationMetricList     = $this->fillList('', 'getLocationMetricList');
        $this->view->costCenterList         = $this->fillList('', 'getCostCentreList');
        $this->view->togList             = $this->fillList('', 'getTurnoverGroupList');
        $this->view->packTypeList         = $this->fillList('', 'getPackagingTypeList');
        $this->view->locationList         = $this->fillList('', 'getLocationList');
    }

    /**
     * Get list by $callbackFunction, with $callbackParams (if exists)
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
            $tempArray = call_user_func_array(array($this->minder, $callbackFunction), $callbackParams);
        } else {
            $tempArray = call_user_func(array($this->minder, $callbackFunction));
        }
        if ($valueToCheck != null) {
            if (!array_key_exists($valueToCheck, $tempArray)) {
                $tempArray = array($valueToCheck => $valueToCheck) + $tempArray;
            }
        } else {
            $tempArray = minder_array_merge(array('' => ''), $tempArray);
        }
        return $tempArray;
    }

    protected function _setupHeaders()
    {
         if (!parent::_setupHeaders()) {
           $this->session->headers[$this->_controller][$this->_action][$this->view->tableId] =
                $this->view->headers = array('LOCN_ID'           => 'Location ID',
                                             'WH_ID'             => 'WH',
                                             'PROD_ID'           => 'Product ID',
                                             'DESCRIPTION'       => 'Description',
                                             'QTY'               => 'Qty',
                                             'LOCN_STAT'         => 'Status',
                                             'MOVE_STAT'         => 'Trans.',
                                             'STORE_AREA'        => 'Area',
                                             'STORE_TYPE'        => 'Type',
                                             'TEMPERATURE_ZONE'  => 'Temp',
                                             'LAST_AUDITED_DATE' => 'Loc.Last Audited',
                                             'COUNT_ISSN'        => '# ISSN\'s');
            $settings['session'] = $this->session;
        }
        return true;
    }
}
