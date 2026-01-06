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
class AuditProccessingController extends Minder_Controller_Action
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
                                 'end_date'        => 'ST_AUDIT_DATE <= ? AND ',
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
                        $lines     = $this->getRequest()->getPost('recs', array());
                        $this->__updateStocktakeAction($lines, $st_action);
                        break;
                    case 'APPLY VARIANCE':
                        $recs     = $this->getRequest()->getPost('recs', array());

                        if (!empty($recs)) {
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
                        } else {
                            $this->addError('No lines selected.');
                        }
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

    protected function _getMenuId()
    {
        return 'ADMIN';
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
        if (empty($lines)) {
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