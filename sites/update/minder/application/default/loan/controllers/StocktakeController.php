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
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class StocktakeController extends Minder_Controller_Action
{
    public function init()
    {
        parent::init();
        if (!$this->minder->isStockAdjust) {
            $this->addWarning('You not allowed to STOCK ADJUSTMENT');
            $this->_redirector->setCode(302)
                              ->goto('index', 'issn', 'warehouse', array());
            return;
        }
    }

    public function indexAction()
    {
        //$tableId = $this->getRequest()->getParam('table');
        //$method  = $this->getRequest()->getParam('method');
        //Zend_Debug::dump($this->getRequest()->getParams(), 'POST PARAMS');

    }

    public function loadSsnsAction()
    {

        $this->view->companyIdList = minder_array_merge(array(''=>''), $this->minder->getCompanyList());
        $this->view->warehouseList = minder_array_merge(array(''=>''), $this->minder->getWarehouseList());
        $this->view->locationList  = minder_array_merge(array(''=>''), $this->minder->getLocationList());
        $this->view->statusList    = minder_array_merge(array(''=>''), $this->minder->getAudStatusList());

        $this->view->userIdList    = minder_array_merge(array(''=>''), $this->minder->getUserList());
        $this->view->deviceList    = minder_array_merge(array(''=>''), $this->minder->getDeviceList());
        $this->view->actionList    = minder_array_merge(array(''=>''), $this->minder->getAudActionList());

        $tableId = $this->getRequest()->getParam('table');
        $method  = $this->getRequest()->getParam('method');
        $this->view->noChildRefresh = $this->getRequest()->getParam('no-child-refresh');

        $this->view->autocompletes = array('st_locn_id', 'st_prod_id');
        switch ($tableId) {
            case 'ssns':
                $this->view->tableId = $tableId;

                if ($method == 'post') {
                    $showBy       = $this->getRequest()->getParam('show_by');
                    $pageselector = $this->getRequest()->getParam('pageselector');
                } else {
                    $showBy       = 10;
                    $pageselector = 0;
                }

                $this->view->navigation = array('show_by' => $showBy, 'pageselector' => $pageselector);

                $allowed = array('st_prod_id'      => 'PROD_ID LIKE ? AND ',
                                 'ssn_description' => 'SSN_DESCRIPTION = ?  AND ',
                                 'original_ssn'    => 'ORIGINAL_SSN = ? AND ',
                                 'st_count'        => 'ST_COUNT = ? AND ',
                                 'st_variance'     => 'ST_VARIANCE = ? AND ',
                                 'st_wh_id'        => 'WH_ID = ? AND ',
                                 'st_status'       => 'ST_STATUS = ? AND ',
                                 'st_action'       => 'ST_ACTION = ? AND ',
                                 'start_date'      => 'ST_AUDIT_DATE >= ? AND ',
                                 'end_date'        => 'ST_AUDIT_DATE =< ? AND ',
                                 'st_applied_by'   => 'ST_APPLIED_BY = ? AND ',
                                 'st_locn_id'      => 'ST_LOCN_ID LIKE ? AND '
                                 );

                $conditions = $this->_setupConditions(null, $allowed);
                $clause = $this->_makeClause($conditions, $allowed);
                if (!array_key_exists('recs', $conditions)) {
                    $conditions['recs'] = array();
                }
                $this->view->conditions = $conditions;
                $this->view->headers    = $this->_setupHeaders($tableId);

                try {
                    if ('' != $this->getRequest()->getParam('select_complete') && $method != 'post' && $method != '') {
                        $pageselector = 0;
                        $showBy = $this->getRequest()->getParam('total');
                    }
                    $result = $this->minder->getStocktakeSSNLines($clause,
                                                                  $pageselector,
                                                                  $showBy);
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                    $this->render('incorrect');
                    return;
                }
                $this->view->data       = $result['data'];
                $this->view->numRecords = $result['total'];


                switch (strtoupper($method)) {
                    case 'REPORT: CSV':
                        $this->_processReportTo($method);
                    return;
                    default:
                        break;
                }

                $this->view->pages      = array();
                for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
                    $this->view->pages[] = $i;
                }
                $this->render($tableId);
                return ;
            break;
            default:
                $this->addError('Incorrect table selected - "' . $tableId . '"');
                $this->render('incorrect');
                return;
            break;
        }
    }

    public function loadIssnsAction()
    {

        $tableId  = $this->getRequest()->getParam('table');
        $method   = $this->getRequest()->getParam('method');
        $ssnsList = $this->getRequest()->getParam('list_ssns');
        $keys     = (array)$this->getRequest()->getParam('keys');
        $values   = (array)$this->getRequest()->getParam('values');

        $this->view->statusList    = minder_array_merge(array(''=>''), $this->minder->getAudStatusList());
        $this->view->actionList    = minder_array_merge(array(''=>''), $this->minder->getAudActionList());


        $searchConditionsList = array();
        foreach ($values as $key => $val) {
            $searchConditionsList[$keys[$key]] = $val;
        }

        $action = strtoupper($this->getRequest()->getPost('action'));

        $this->view->reloadSsns = false;
        switch ($tableId) {
            case 'issns':
                $this->view->tableId    = $tableId;
                $this->view->actionList = minder_array_merge(array(''=>''), $this->minder->getAudActionList());
                if ($method == 'post') {
                    $showBy       = $this->getRequest()->getParam('show_by');
                    $pageselector = $this->getRequest()->getParam('pageselector');
                } else {
                    $showBy       = 10;
                    $pageselector = 0;
                }
                switch ($action) {
                    case 'DELETE COUNT':
                        $recs = $this->getRequest()->getPost('recs');
                        if (count($recs) > 0) {
                            $this->minder->deleteStocktakeCount($recs);
                        }
                        $this->view->reloadSsns = true;
                    break;
                    case 'UPDATE PENDING ACTION':
                        $st_action = $this->getRequest()->getPost('upd_st_action');
                        $lines     = $this->getRequest()->getPost('recs');
                        $this->__updateStocktakeAction($lines, $st_action);
                        break;
                    case 'APPLY VARIANCE':
                        $recs     = $this->getRequest()->getPost('recs');
                        $response = $this->minder->applyVariance($recs);
                        $lines    = array();
                        foreach ($response as $key => $message) {
                            if (false !== strpos($message, 'Processed success')) {
                                $lines[$key] = $key;
                                $this->addMessage('Apply variance ' . $key . ' - ' . $message);
                            } else {
                                $this->addError('Apply variance ' . $key . ' - ' . $message);
                            }
                        }
                        //$this->__updateStocktakeAction($lines, 'PA');
                        break;
                    default:
                        $first = false;
                        if ('' != $this->getRequest()->getPost('add_x')) {
                            foreach ($this->getRequest()->getPost('edit') as $key => $line) {
                                if (is_numeric($line['st_count']) && (int)$line['st_count'] > 0) {
                                    $whId   = $line['wh_id'];
                                    $locnId = $line['st_locn_id'];
                                    $issn   = $line['st_ssn_id'];
                                    $qty    = $line['st_count'];
                                    $this->__updateStocktake($whId, $locnId, $issn, $qty, $first);
                                    $this->addMessage('ISSN.# ' . $issn . ' new Actual = ' . $line['st_count']);
                                } else {
                                    $this->addError('Rec.# ' . $key . ' update failed - \'' . $line['st_count'] . '\' is not numeric or less then zero.');
                                }
                            }
                        } else {
                        }
                    break;
                }
                $this->view->navigation = array('show_by' => $showBy, 'pageselector' => $pageselector);
                $this->view->conditions = array('recs' => array());

                $this->view->headers = $this->_setupHeaders($tableId);
                if (!is_array($ssnsList)) {
                    $ssnsList = array('NO ISSN');
                    $this->render('incorrect');
                    return;
                }
                try {
                    $allowed = array('st_locn_id' => 'ST_LOCN_ID LIKE ? AND ',
                                     'st_prod_id'    => 'PROD_ID LIKE ? AND ');
                    $clause = $this->_makeClause($searchConditionsList, $allowed);
                    if ('' != $this->getRequest()->getParam('select_complete') && $method != 'post' && $method != '') {
                        $pageselector = 0;
                        $showBy = $this->getRequest()->getParam('total');
                    }
                    $result = $this->minder->getStocktakeISSNLines($clause,
                                                                   $pageselector,
                                                                   $showBy,
                                                                   $ssnsList);
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                    $this->render('incorrect');
                    return;
                }
                $ssnConditions = $this->_getConditions('load-ssns');
                //if (!isset($ssnConditions['recs'])) {
                    $ssnConditions['recs'] = array();
                //}
                foreach ($ssnsList as $id) {
                    $ssnConditions['recs'][$id] = $id;
                }
                $this->_setConditions($ssnConditions, 'load-ssns');

                $this->view->ssns       = $ssnsList;
                $this->view->data       = $result['data'];
                $this->view->numRecords = $result['total'];

                switch (strtoupper($method)) {
                    case 'REPORT: CSV':
                        $this->_processReportTo($method);
                    return;
                    default:
                        break;
                }

                $this->view->pages     = array();
                for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
                    $this->view->pages[] = $i;
                }
                $this->render($tableId);
                return ;
            break;
            default:
                $this->addError('Incorrect table selected - "' . $tableId . '"');
                $this->render('incorrect');
                return;
            break;
        }
    }

    public function loadStocktakeLocationsAction()
    {
        $tableId  = $this->getRequest()->getParam('table');
        $method   = $this->getRequest()->getParam('method');
        switch ($tableId) {
            case 'locations':
                $this->view->tableId = $tableId;

                if ($method == 'post') {
                    $showBy         = $this->getRequest()->getParam('show_by');
                    $pageselector   = $this->getRequest()->getParam('pageselector');

                } else {
                    $showBy         = 10;
                    $pageselector   = 0;
                }

                $this->view->navigation = array('show_by' => $showBy, 'pageselector' => $pageselector);

                $this->view->headers = $this->_setupHeaders($tableId);

                $allowed = array('locns' => '');

                $conditions = $this->_setupConditions(null, $allowed);
                if (!array_key_exists('locns', $conditions)) {
                    $conditions['locns'] = array();
                }
                $this->view->conditions = $conditions;
                $clause = array('LOCN_STAT = ?' => 'ST');
                try {
                    $result = $this->minder->getLocations($clause,
                                                          $pageselector,
                                                          $showBy);
                    if ($method == 'post') {
                        while ((list($key ,$obj) = each($result['data']))) {
                            if (array_key_exists($obj->id, $this->view->conditions['locns'])) {
                                $response = $this->minder->releaseLocation($obj['WH_ID'], $obj['LOCN_ID']);
                                if ($response != false) {
                                    $this->addMessage($obj['LOCN_ID'] . ' ' . $obj['LOCN_NAME'] . '. ' . $response[0]);
                                    unset($result['data'][$key]);
                                    $this->view->conditions['locns'][$obj->id] = false;
                                } else {
                                    $this->addError($this->minder->lastError);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                    $this->render('incorrect');
                    return;
                }
                $this->view->data       = $result['data'];
                $this->view->numRecords = $result['total'];

                $this->view->pages     = array();
                for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
                    $this->view->pages[] = $i;
                }
                $this->render($tableId);
                return ;
            break;
            default:
                $this->addError('Incorrect table selected - "' . $tableId . '"');
                $this->render('incorrect');
                return;
            break;
        }
    }

    public function loadCountAction()
    {
        $this->render('count');
    }

    public function loadCountLocationAction()
    {
        $this->view->conditions    = array();

        $this->view->warehouseList = minder_array_merge(array('' => ''), $this->minder->getWarehouseList());
        $this->view->locationList  = minder_array_merge(array('' => ''), $this->minder->getLocationList());
        $this->render('count-location');
    }

    public function loadCountIssnAction()
    {
        $this->view->conditions      = array();
        $this->view->issnsInLocation = $this->getRequest()->getParam('in_location');
        $this->view->issnsCounted    = (int)$this->getRequest()->getParam('counted');
        $this->view->locnId          = $locnId = $this->getRequest()->getParam('locn_id');
        $this->view->whId            = $whId   = $this->getRequest()->getParam('wh_id');
        $this->view->reloadSsns      = false;
        $this->view->showRecount     = false;
        $this->view->stCount         = 0;
        if ('' == $this->getRequest()->getParam('init')){
            $action = strtoupper($this->getRequest()->getPost('action'));
            switch ($action) {
                case 'SAVE COUNT':
                    $first = true;
                    $qty   = (int)$this->getRequest()->getParam('ci_count');
                    if (0 != $this->getRequest()->getParam('ci_recount')) {
                        $qty   = (int)$this->getRequest()->getParam('ci_recount');
                        $first = false;
                    }
                    $issn = $this->getRequest()->getParam('ci_st_ssn_id');

                    $this->__updateStocktake($whId, $locnId, $issn, $qty, $first);

                    $counted                = $this->session->counted;
                    $counted[$issn]         = $issn;
                    $this->session->counted = $counted;
                    $this->view->issnsCounted = count($counted);
                    break;
                case 'EXIT COUNT':
                    break;
                default:
                    break;
            }
        } else {
            $this->session->counted = array();
        }

        $this->render('count-issn');
    }

    public function countIssnAction()
    {

    }

    public function freezeLocationAction()
    {
        $whId   = $this->getRequest()->getParam('wh_id');
        $locnId = $this->getRequest()->getParam('locn_id');

        $response = $this->minder->freezeLocation($whId, $locnId);
        $result = new stdClass();

        if ($response !== false) {
            $this->addMessage($whId . ' ' . $locnId . ' ' . $response[0]);

            $result->message = $response[0];
            $result->data    = $response[1];
            $result->status  = true;
        } else {
            $result->message = $this->minder->lastError;
            $result->status  = false;
        }

        $this->_helper->json($result);
    }

    public function fillLocationListAction()
    {
        $result = new stdClass();

        $whId   = $this->getRequest()->getParam('wh_id');

        if ($whId != '') {
            try {
                $response = $this->minder->getLocationListByClause(array('WH_ID = ?' => $whId));
                if ($response !== false) {
                    $result->status  = true;
                    $result->data    = $response;
                    if ($response != array()) {
                        $result->message = 'Success';
                    } else {
                        $result->message = 'No Location for this WH_ID';
                    }
                } else {
                    $result->status  = false;
                    $result->message = $this->minder->lastError;
                }
            } catch (Exception $e) {
                $result->status  = false;
                $result->message = $e->getMessage();
            }
        } else {
            $result->status = false;
            $result->message = 'No WH_ID specified.';
        }
        $this->_helper->json($result);
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
        $whId   = $this->getRequest()->getParam('wh_id');
        $src = $this->getRequest()->getParam('field');

        $log = Zend_Registry::get('logger');
        switch ($src) {
            case 'cl_st_locn_id':
                if ($whId != '') {
                    try {
                        $response = $this->minder->getLocationListByClause(array('LOCN_ID LIKE ?' => trim(strtoupper($param), '%') . '%', 'WH_ID = ?' => $whId));
                        if ($response !== false) {
                            $tdata    = $response;
                        }
                    } catch (Exception $e) {
                        $log->info($e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage());
                    }
                }
                break;
            case 'st_locn_id':
                try {
                    $response = $this->minder->getLocationListByClause(array('LOCN_ID LIKE ?' => trim(strtoupper($param), '%') . '%'));
                    if ($response !== false) {
                        $tdata    = $response;
                    }

                } catch (Exception $e) {
                    $log->info($e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage());
                }
                break;
            case 'st_prod_id':
                try {
                    $response = $this->minder->getProductList($param);
                    if ($response !== false) {
                        $tdata    = $response;
                    }
                } catch (Exception $e) {
                    $log->info($e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage());
                }
                break;
            default:
                $tdata = array();
                break;
        }

        $this->view->data = $tdata;
    }

    public function seekAction()
    {

    }

    public function _setupShortcuts()
    {
        $shortcuts = array(
    	
    		'Audit'					=>	array(	
    											'AUDIT_CODE'		=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'audit_code'), '', true),
    											'AUDIT PROCESSING'	=>  $this->view->url(array('controller' => 'stocktake', 'action' => 'index', 'module'     => 'default'), '', true),	
    											'LEGACY_ADJUSTMENT'	=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'legacy_adjustment'), '', true),
    											'STOCKTAKE'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'stocktake'), '', true)
    	
    		),
    		'Import Data'			=>	array(
                                                'IMPORTING DATA'    =>    $this->view->url(array('controller' => 'admin', 'action' => 'import-clipboard'), '', true),
    											'CREATE IMPORT MAP'	=>	  $this->view->url(array('controller' => 'admin', 'action' => ''), '', true)
    											
    		
    		),
    		'Master Data'			=>	array(
    											'BRAND'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'brand'), '', true),
    											'CARRIER'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'carrier'), '', true),
    											'CARRIER_SERVICE'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'carrier_service'), '', true),
    											'COST_CENTRE'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'cost_centre'), '', true),
    											'COMPANY'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'company'), '', true),
    											'DEPARTMENT'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'department'), '', true),
    											'DIVISION'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'division'), '', true),
    											'GROUP_COPY'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'group_copy'), '', true),
    											'LABEL_LOCATION'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'label_location'), '', true),
    											'LOAN_RATE'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'loan_rate'), '', true),
    											'MODEL'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'model'), '', true),
    											'PRODUCT_CONDITION'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_condition'), '', true),
    											'PRODUCT_DESCRIPTION'		=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_description'), '', true),
    											'PROJECT'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'project'), '', true),
    											'RETICULATION'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'reticulation'), '', true),
    											'STATUS_DEFS'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'status_defs'), '', true),
    											'TEST_QUESTIONS'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'test_questions'), '', true),
    											'TURNOVER (TOG)'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'tog'), '', true),
    											'TYPE I (SSN_TYPE)'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_type'), '', true),
    											'TYPE II (GENERIC)'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'generic'), '', true),
    											'TYPE III (SSN_SUB_TYPE)'	=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_sub_type'), '', true),
    											'UNIT OF MEASURE (UOM)'		=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'uom'), '', true),
    											'UOM_TYPE'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'uom_type'), '', true),
    											'WARRANTY'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'warranty'), '', true),
    											'ZONE'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'zone'), '', true),
    											'REPLACE MASTER DATA'		=>	$this->view->url(array('controller' => 'replace', 'action' => 'index', 'module' => 'warehouse'), '', true)
    											
    		),
    		'Orders'				=>	array(
    											'ORDER_TYPE'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'order_type'), '', true),
    											'PAYMENT_METHOD'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'payment_methods'), '', true),
    											'TERMS'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'terms'), '', true),
    											'PICK_ORDER'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_order'), '', true),
    											'PICK_ITEM'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_item'), '', true),
    											'PICK_ITEM_DETAIL'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_item_detail'), '', true),
    											'PICK_DESPATCH'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_despatch'), '', true),
    											'PACK_ID'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pack_id'), '', true),
    											'PURCHASE_ORDER'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'purchase_order'), '', true),
    											'PURCHASE_ORDER_LINE'		=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'purchase_order_line'), '', true),
    											
    		),
    		'Product Profiles'		=>	array(
    											'PRODUCTS (PROD_PROFILE)'	=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'prod_profile'), '', true),
    											'KIT'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'kit'), '', true),
    											'PRODUCT_KIT'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_kit'), '', true),
    											'PROD_EAN'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'prod_ean'), '', true),
    											'PRODUCT TYPE'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'prod_type'), '', true),
    											'PALLET CONFIGURATION'		=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pallet_cfg'), '', true),
    											'SLOTTING PRODUCTS'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'slotting'), '', true),
    															
    		),
    		'Person Profiles'		=>	array(
    											'PERSON'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'person'), '', true),
    											'PERSON_ADDRESS'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'person_address'), '', true),
    											'PERSON_COMPANY'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'person_company'), '', true),
    											'ACCESS_COMPANY'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'access_company'), '', true),
    											'ACCESS_USER'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'access_user'), '', true),
    											'USERS (SYS_USER)'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_user'), '', true),
    											
    		),
    		'Employee Profiles'		=>	array(
    											'EMPLOYEE'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'employee'), '', true),
    											'EMPLOYEE_IMAGE'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'employee_image'), '', true),
    											'EMPLOYEE_ISSUE'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'employee_issue'), '', true),
    											'OCCUPATION'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'occupation'), '', true),
    											'SKILL'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'skill'), '', true),
    										
    		),
    		'System Tables'			=>	array(
    											'ARCHIVING (ARCHIVE_LAYOUT)'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'archive_layout'), '', true),
    											'CONTROL'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'control'), '', true),
    											'DATA IDENTIFIERS (PARAM)'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'param'), '', true),
    											'GLOBAL_CONDITIONS'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'global_conditions'), '', true),
    											'OPTIONS'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'options'), '', true),
    											'SSN OTHER TITLES (SSN_GROUP)'			=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_group'), '', true),
    											'SSN DESCRIPTION (DESCRIPTION_LAYOUT)'	=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'description_layout'), '', true),
    											'SYS_EQUIP'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_equip'), '', true),
    											'SYS_HH'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_hh'), '', true),
    											'SYS_MOVES'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_moves'), '', true),
    											'SYS_LABEL'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'print-sys'), '', true),
    											'SYS_LABEL_VAR'							=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_label_var'), '', true),
    											'PRINT_REQUESTS'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'print_requests'), '', true),
    											'PRINT_REQUESTS_ARCHIVE'				=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'print_requests_archive'), '', true),
    											'LOCATION'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'location'), '', true),
    											'LOCATION_RANGE'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'location-generate'), '', true),
    											'WAREHOUSE'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'warehouse'), '', true),
    		),
    		'System Transactions'	=>	array(
    											'Test V3.9 Transaction'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'test-transaction'), '', true),
    											'Test V4 Transaction'					=>	$this->view->url(array('controller' => 'admin', 'action' => 'test-transaction'), '', true),
    											'SOAP_CLI Status'		 				=>	$this->view->url(array('controller' => 'admin', 'action' => 'check-soap-cli'), '', true),
    											'USER_ENV'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'user_env'), '', true),
    											'SSN_HIST'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_hist'), '', true),
    											'SSN_TEST'								=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_test'), '', true),
    											'SSN_TEST_RESULTS'						=>	$this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_test_results'), '', true)
    		)
    	);
    	
    	$tooltip	=	array('Audit'				=>	'View/Edit Audit Results from Stocktaking',
    							'AUDIT_CODE'		=>	'Add/Edit Audit Codes and Colours used for displaying Stocktaking Results',
    							'AUDIT PROCESSING'	=>	'Displays Results from STOCKTAKE Table and allow Stock Adjustments', 
    							'LEGACY_ADJUSTMENT'	=>	'View LEGACY_ADJUSTMENT table using CRUD format',
    							'STOCKTAKE'			=>	'View STOCKTAKE table using CRUD format ', 
    		 				 'Import Data'			 =>	'Import with Copy and Paste, from .CSV files',
    							'IMPORTING DATA'	 =>	'Import Data into Minder Tables - Import from Clipboard using Copy & Paste and import using .CSV files', 
    						    'CREATE IMPORT MAP'  =>  '',
                              'Master Data'			 =>	'Maintain SSN Descriptions and Conditions',
    						  	'BRAND'				 =>	'Brand Names and Codes',
    							'CARRIER'			 =>	'Carrier Names and Codes',
    							'CARRIER_SERVICE'	 =>	'Carrier Service options',
    				 			'COST_CENTRE'		 =>	'Cost Centre Descriptions and Codes',
    				 			'COMPANY'			 =>	'Company and Inventory Owner Names and Codes',
    							'DEPARTMENT'		 =>	'Department Names and Codes',
    							'DIVISION'			 =>	'Division Names and Codes',
    							'GROUP_COPY'		 =>	'Each GROUP_CODE Description and Code- updates up to 12xSSN Master Data Fields as one process',
    							'LABEL_LOCATION'	 =>	'Codes used to indicate location of ISSN Labels',
    							'LOAN_RATE'			 =>	'Loan Hire Costs by Loan Period by SSN_TYPE by Company',
    							'MODEL'				 =>	'Model Numbers and Codes - recommend not using',
    							'PRODUCT_CONDITION'  =>	'Conditions and Codes used for SSN.OTHER1 to 5 Drop down Lists',
    							'PRODUCT_DESCRIPTION'=>	'DESCRIPTION Codes used for SSN_TYPE.FIELD1 to 5 Drop down Lists',
    							'PROJECT'			 => 'Project Names and Codes',
    							'RETICULATION'		 => 'Reticulation Names and Codes',
    							'STATUS_DEFS'		 => 'User defined SSN Status Descriptions and Codes',
    							'TEST_QUESTIONS'	 => 'Lists Test Questions for each SSN_TYPE, used as part of ASSET*MINDER Refurbishment Module',
    							'TURNOVER (TOG)'	 => 'Inventory Turnover Classifications',
    							'TYPE I (SSN_TYPE)'  => 'Type I (SSN_TYPE) Descriptions and Codes',
    							'TYPE II (GENERIC)'  =>	'Type II (GENERIC) Descriptions and Codes',
    							'TYPE III (SSN_SUB_TYPE)' => 'Type III (SSN_SUB_TYPE) Descriptions and Codes',
    							'UNIT OF MEASURE (UOM)' => 'Units of Measure Descriptions and Codes',
    							'UOM_TYPE'			 => 'Types of Units of Measure Descriptions and Codes',
    							'WARRANTY'			 => 'Sales Order Warranty Descriptions for drop down lists',
    							'ZONE'				 =>	'Picking Zone Descriptions and Codes',
    							'REPLACE MASTER DATA'=> 'Replaces SSN Master Data',
    						  'Orders'				=>	'"View/Edit Order Tables',
    							'ORDER_TYPE'		 =>	'Order Types includes - Sales, Transfer, Work, Replenish, Purchase, Returns',
    							'PAYMENT_METHOD'	 => 'Sales Order Payment Methods for drop down list',
    							'TERMS'				 =>	'Sales Order Payment Terms for drop down list',
    							'PICK_ORDER'		 =>	'Header Records for Orders',
    							'PICK_ITEM'			 => 'Line Records for Orders',
    							'PICK_ITEM_DETAIL'	 =>	"Records for PICK_ITEM's that have been Picked to Despatch",
    							'PICK_DESPATCH'		 => 'Records for each Consignment Note',
    							'PACK_ID'			 => 'Records for each Package Despatched on a PICK_DESPATCH',
    							'PURCHASE_ORDER'	 => 'Header Records for Purchase Orders',
    							'PURCHASE_ORDER_LINE'=> 'Line Records for Purchase Orders',
    						  'Product Profiles'	=>	'"View/Edit Product Details',
    						    'PRODUCTS (PROD_PROFILE)' =>	'Lists Product Descriptions and Properties',
    							'KIT'					  =>	'Kit Descriptions and Codes',
    							'PRODUCT_KIT'			  =>	'Lists Compositions of each KIT',
    							'PROD_EAN'				  =>	'Lists GS1 EAN13/14 Barcode Numbers and Barcode Extensions for each Product',
    							'PRODUCT TYPE'			  =>	'Lists Types of Products for Drop Down Lists',
    							'PALLET CONFIGURATION'	  =>	'List Pallet stacking details for each product',
    							'SLOTTING PRODUCTS'		  =>	'Used to place (Slot) each Product into Storage Location',
    						  'Person Profiles'		=>	'"View/Edit Person Contact Address and Minder Access Details',
    							'PERSON'				 =>		'Add/Edit Contact Details for each Person (Corporations and Individuals) plus add Address Records into PERSON_ADDRESS',
    							'PERSON_ADDRESS'		 =>		'Lists Address Details for PERSON - Office, Mail To, Deliver To',
    							'PERSON_COMPANY'		 =>		'Add/Edit which Companies a PERSON is able to view in MINDER',
    							'ACCESS_COMPANY'		 =>		'Add/Edit which Companies SYS_USER is able to view in MINDER',
    							'ACCESS_USER'			 =>		'Add/Edit which Warehouses SYS_USER is able to view in MINDER',
    							'USERS (SYS_USER)'		 =>		'Add/Edit Minder Users Details - User_ID, Password, SYS_ADMIN, INVENTORY_OPERATOR',
    						  'Employee Profiles'	=>	'"View/Edit Employee Details - used by TIME*MINDER Module',
    						  	'EMPLOYEE'				 =>		'EMPLOYEE details - Contact, Occupation, Union Membership, Induction etc.',
    							'EMPLOYEE_IMAGE'		 =>		'Images of employees. Used for ID Card Printing',
    							'EMPLOYEE_ISSUE'		 =>		'Issued KIT numbers to Employees for Personal Protective Equipment and Tools',
    							'OCCUPATION'			 =>		'Employee OCCUPATION details',
    							'SKILL'					 =>		'Skill Descriptions and Codes plus Training Results for EMPLOYEEs',
    						  'System Tables'		=>	'"View/Edit Minder Configuration details',
    						  	'ARCHIVING (ARCHIVE_LAYOUT)'	=>	'Add/Edit which Tables and the period before Archiving records',
    							'CONTROL'						=>	'Main MINDER Configuration Settings Table',
    							'DATA IDENTIFIERS (PARAM)'		=>	'Details used to identify scanned input - Symbology, Length, Expression types',
    							'GLOBAL_CONDITIONS'				=>	'Defines Drop down Descriptions and Codes for SSN.OTHERx fields',
    							'OPTIONS'						=>	'Lists most Drop down list details plus other Configuration settings not in CONTROL table',
    							'SSN OTHER TITLES (SSN_GROUP)'	=>	'SSN.OTHERx Field Titles and if drop down lists (see GLOBAL_CONDITIONS) or single input',
    							'SSN DESCRIPTION (DESCRIPTION_LAYOUT)'	=>	'Define construction of SSN_DESCRIPTION using SSN and Master Data Tables',
    							'SYS_EQUIP'						=>	'Lists details of each Minder Equipment - DEVICE_ID, IP_ADDRESS etc.',
    							'SYS_HH'						=>	'Used to configure Remote Hand Held FTP details',
    							'SYS_MOVES'						=>	'Used to configure SSN Allowed/Not Allowed Movements and Inventory Status updates',
    							'SYS_LABEL'						=>	'Used to import and edit Native Label Printer Commands with Placeholders, Print Test Labels',
    							'SYS_LABEL_VAR'					=>	'Lists all SYS_LABEL Placeholders, edit Data Expressions, Test Label Default values',
    							'PRINT_REQUESTS'				=>	'Lists each Print Label request. Use for Reprints',
    							'PRINT_REQUESTS_ARCHIVE'		=>	'Lists each archived Print Label request',
    							'LOCATION'						=>	'Lists every Storage Location controlled by Minder System',
    							'LOCATION_RANGE'				=>	'Used to generate one or more LOCATION records each with the same Location Profile',
    							'WAREHOUSE'						=>	'Lists every Warehouse or Repository controlled by Minder System',
    						  'System Transactions'	=>	'"View/Edit Minder SOAP Interface, Transactions, SSN History',
    							'Test V3.9 Transaction'			=>	'Input and Test v3.9 Transaction - uses TRANSACTIONS and TRANSACTIONS_ARCHIVE Tables',
    							'Test V4 Transaction'			=>	'Input and Test v4 Transaction - uses TRANSACTIONS4 and TRANSACTIONS4_ARCHIVE Tables',
    							'SOAP_CLI Status'				=>	'Check Legacy Interface - SOAP-CLI',
    							'USER_ENV'						=>	'User Session details -Use with care this maybe a very large table',
    							'SSN_HIST'						=>	"Lists History of SSN's - Use with care this maybe a very large table",
    							'SSN_TEST'						=>	'Lists details of SSN Test Start and Finish and Test Status',
    							'SSN_TEST_RESULTS'				=>	'Lists details of SSN Test Questions and Responses'
    	);


        if (!$this->minder->isStockAdjust) {
             unset($shortcuts['Audit']['STOCKTAKE']);
        }
        $this->view->shortcuts = $shortcuts;
        return true;
    }

    protected function _setupHeaders($tableId)
    {
        $headers = array();
        switch ($tableId) {
            case 'ssns':
                $headers = array('PROD_ID'         => 'Product #',
                                 'SSN_DESCRIPTION' => 'ISSN Description',
                                 'ORIGINAL_SSN'    => 'SSN',
                                 'ST_COUNT'        => 'Actual',
                                 'ST_VARIANCE'     => 'Variance',
                                 'WH_ID'           => 'WH ID'
                                 );
                break;
            case 'issns':
                $headers = array('RECORD_ID'       => 'Rec',
                                 'ST_SSN_ID'       => 'ISSN',
                                 'SSN_DESCRIPTION' => 'ISSN Description',
                                 'PROD_ID'         => 'Product #',
                                 'ST_COUNT'        => 'Actual',
                                 'ST_VARIANCE'     => 'Variance',
                                 'ST_STATUS'       => 'Status',
                                 'ST_ACTION'       => 'Pending',
                                 'ST_AUDIT_DATE'   => 'Count Date',
                                 'WH_ID'           => 'WH ID',
                                 'ST_LOCN_ID'      => 'Loc.',
                                 'ST_AUDIT_BY'     => 'User ID'
                                 );
                break;
            case 'locations':
                $headers = array('WH_ID'             => 'WH ID',
                                 'LOCN_ID'           => 'Location ID',
                                 'LOCN_NAME'         => 'Location Description',
                                 'LOCN_OWNER'        => 'Device ID',
                                 'LAST_AUDITED_DATE' => 'Audit Started Date'
                                 );
                break;
            default:
                ;
            break;
        }
        return $headers;
    }

    private function __updateStocktake($whId, $locnId, $issn, $qty, $first)
    {
        $check = $this->minder->getIssns(array('SSN_ID = ?' => $issn));
        $issnLine = current($check);
        $warning = false;
        if ($whId != $issnLine['WH_ID']) {
            $this->addWarning('ISSN ' . $issnLine['SSN_ID'] .  ' not in Warehouse ' . $this->view->whId);
            $warning = true;
        }
        if ($locnId != $issnLine['LOCN_ID']) {
            $this->addWarning('ISSN ' . $issnLine['SSN_ID'] .  ' not in Location ' . $this->view->locnId);
            $warning = true;
        }

        if (!$warning) {
            $response = $this->minder->updateStocktake($whId, $locnId, $issn, $qty, $first);
            if ($response) {
                $this->view->stVariance  = $response[3];
                $this->view->stCount     = $qty;
                $this->view->reloadSsns  = true;
                $this->view->showRecount = true;
            } else {
                $this->addError($this->minder->lastError);
            }
        } else {
            $this->addError('Can\'t count');
        }
    }

    private function __updateStocktakeAction(array $lines, $st_action)
    {
        $flag = true;
        $msg  = '';

        if (null == $st_action) {
            $flag = false;
            $msg .= 'No action specified.';
        }
        if (null == $lines) {
            $flag = false;
            $msg .= 'No lines to update.';
        }

        if ($flag) {
            $result = $this->minder->updatePendingStocktake($lines, $st_action);
            foreach ($result as $id => $text) {
                if (is_array($text)) {
                    $text = current($text);
                    $this->addError($id . ' - ' . $text);
                } else {
                    $this->addMessage('Update pending for ' . $id . ' - ' . $text);
                }
            }
        } else {
            $msg .= 'Can\'t update Pending.';
            $this->addWarning($msg);
        }
    }
}