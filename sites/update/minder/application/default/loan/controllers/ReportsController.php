<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Function ( minder_array_merge() );
 */
include "functions.php";

/**
 * ReportsOrderController
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class ReportsController extends Minder_Controller_Action
{
    public function init()
    {
        parent::init();
        
        $this->minder = Minder::getInstance();

        $this->_redirector = $this->_helper->getHelper('Redirector');

        if ($this->minder->userId == null) {
            $this->_redirector->setCode(303)
                              ->goto('login', 'user', '', array());
            return;
        }

        $this->initView();
        $this->view->minder = $this->minder;
        $this->view->addHelperPath(ROOT_DIR . '/includes/helpers/', 'Minder_View_Helper');
        $this->view->flashMessenger = $this->_helper->getHelper('flashMessenger');

        $this->session = new Zend_Session_Namespace('report');

        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index', 'module' => 'default'), '', true);
    }

    protected function _getSearchForm() {
        return new Zend_Form(new Zend_Config_Ini(APPLICATION_CONFIG_DIR . '/forms/reports-search.ini'));
    }

    /**
     * Diplay the reports homepage (/reports/index)
     *
     * This page lists the reports.
     *
     * @return void
     */
    public function indexAction()
    {

        $this->view->pageTitle = "Reports";
        $reportMenuType = $this->getRequest()->getParam('rm_type');
        if ($this->minder->isAdmin) {
            switch ($this->getRequest()->getPost('action')) {
                case 'NEW':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                         ->goto('new', 'reports', '', array('rm_type' => $reportMenuType));
                    break;
                case 'DELETE':
                    $reportIdList = array();
                    if (count($this->getRequest()->getPost('report_id')) > 0) {
                        foreach($this->getRequest()->getPost('report_id') as $key => $val) {
                            if ($val != '') {
                                $reportIdList[] = $val;
                            }
                        }
                    }
                    $this->session->reportIdList = $reportIdList;
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('delete', 'reports', '', array('confirm' => 'MASS DELETE', 'rm_type' => $reportMenuType));
                    break;
                default:
                break;
           }
        }

        $conditions = $this->_getConditions();
        if (strtoupper($this->getRequest()->getPost('action')) == 'SUBMIT SEARCH') {

            $conditions = $this->_makeConditions(array('REPORT_ID' => 'REPORT_ID', 'NAME' => 'NAME'));
       

        }

        //-- @todo: code need rewrite to optimize page by page navigation
        if ($this->getRequest()->getParam('old_start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('old_start_item');
        } else {
            $this->view->startItem = 0;
        }
        if ($this->view->startItem == 0 && $this->getRequest()->getParam('start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('start_item');
        }
        if ($this->getRequest()->getPost('show_by') !== null) {
            $this->view->showBy     = $this->getRequest()->getPost('show_by');
            $this->session->show_by = $this->getRequest()->getPost('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } elseif ($this->getRequest()->getParam('show_by') != null) {
            $this->view->showBy     = $this->getRequest()->getParam('show_by');
            $this->session->show_by = $this->getRequest()->getPost('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } else {
            $this->view->showBy = ($this->session->show_by == null) ? 15 : $this->session->show_by;
        }
        if ($this->getRequest()->getParam('pageselector') !== null) {
            if ($this->getRequest()->getParam('show_by') === $this->getRequest()->getParam('old_show_by')) {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->view->showBy;
                $this->view->pageSelector = $this->getRequest()->getParam('pageselector');
                $this->session->pageSelector = $this->view->pageSelector;
            } else {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->getRequest()->getParam('old_show_by');
                $this->view->pageSelector = floor($this->view->startItem / $this->view->showBy);
                $this->view->startItem    = $this->view->pageSelector * $this->view->showBy;
                $this->session->pageSelector = $this->view->pageSelector;
            }
        } else {
            //$this->view->pageSelector = floor($this->view->startItem /$this->view->showBy);
            $this->view->pageSelector = ($this->session->pageSelector == null) ? floor($this->view->startItem / $this->view->showBy) :
                                                                                 $this->session->pageSelector;
            $this->view->startItem = $this->view->pageSelector * $this->view->showBy;
        }
        settype($this->view->showBy, 'integer');
        settype($this->view->pageSelector, 'integer');
        //-- end process input navigation values
        $this->view->conditions = array();

        $this->view->headers = array('REPORT_ID'        => 'Report ID',
                                     'NAME'             => 'Name',
                                     'DESCRIPTION'      => 'Description',
                                     'COMPANY_ID'       => 'Company',
                                     'LAST_UPDATE_DATE' => 'Last Update',
                                     'LAST_UPDATE_BY'   => 'Last Update By');

        $reportMenuType = $this->getRequest()->getParam('rm_type', '');
        $reportType = $this->getRequest()->getParam('r_type', '');
        $this->view->reportMenuType = $reportMenuType;
        $this->view->reportType = $reportType;

        //EDITING FOR DATE

        $session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;

        foreach($conditions as $key1 => $val1){

            /*if (DateTime::createFromFormat('Y-m-d H:i:s', $val1) !== FALSE  || DateTime::createFromFormat('Y-m-d', $val1) !== FALSE) {*/
            if ($this->minder->isValidDate($val1)) {

                /*$datetimet = $val1;
                $tz_tot = 'UTC';
                $format = 'Y-m-d h:i:s';

                $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                $dtt->setTimeZone(new DateTimeZone($tz_tot));
                                                                                  
                $conditions[$key1]=$dtt->format($format);*/

                $conditions[$key1] = $this->minder->getFormatedDateToDb($val1, "", false);

            }                 

        }


        //EDITING FOR DATE

        $clause = $this->_makeClause($conditions, array('REPORT_ID' => 'REPORT_ID = ?', 'NAME' => 'NAME LIKE ?'));


        if (!empty($reportMenuType)) {
            $clause['REPORT_MENU_TYPE = ? '] = strval($reportMenuType);
        }
        if (!empty($reportType)) {
            $clause['REPORT_TYPE = ? '] = strval($reportType);
        }

        $reports = $this->minder->getReports($clause);
        if(!$this->minder->isAdmin) {
            $filteredReports = array();
            $pattern = "/(^|\s|;|\()(INSERT|UPDATE|DELETE)\s/i";
            foreach ($reports as $value) {
                if(!preg_match($pattern, $value->items['QUERY'])) {
                    $filteredReports[] = $value;
                }
            }
        } else {
            $filteredReports = $reports;
        }
        $this->view->reports = $filteredReports;

        $this->view->numRecords  = count($this->view->reports);

        //-- @todo: code need a tunning for logic
        //-- post process navigation
        if ($this->view->startItem > count($this->view->reports)) {
            $this->view->startItem = count($this->view->reports) - $this->view->showBy;
        }
        if ($this->view->startItem < 0) {
            $this->view->startItem = 0;
        }
        if (($this->view->startItem + $this->view->showBy) > count($this->view->reports)) {
            $this->view->maxno = count($this->view->reports) - $this->view->startItem;
        } else {
            $this->view->maxno = $this->view->showBy;
        }
        //-- end post process

        $this->view->numRecords = count($this->view->reports);
        $this->view->pages      = array();
        for ($i = 1; $i <= ceil($this->view->numRecords / $this->view->showBy); $i++) {
            $this->view->pages[] = $i;
        }
        $this->view->reports = array_slice($this->view->reports,
                                           $this->view->startItem,
                                           $this->view->maxno);

        $this->view->searchForm = $this->_getSearchForm()->populate($conditions);
    }

    /**
     * Create a new reports (/reports/new)
     *
     * Displays the form for creating a new report.
     *
     * @return void
     */
    public function newAction()
    {

        if ($this->minder->isAdmin) {
            $this->view->pageTitle = "New Report";

            switch ($this->getRequest()->getPost('action')) {
                case 'SAVE':
                    $new = new ReportLine();

                    $new->items['REPORT_ACTIVITY'] = $this->minder->getMaxReportActivity() + 1;
                    $new->save($this->getRequest()->getPost());
                    if ($this->minder->reportCreate($new)) {
                        $params = array('report_id' => $new->id);
                        $params['rm_type'] = $this->getRequest()->getParam('rm_type');
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('show', 'reports', '', $params);
                    } else {
                        $this->view->flashMessenger->addMessage('Unaible to create report. Try again.');
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $reportMenuType = $this->getRequest()->getParam('rm_type');
                        $this->_redirector->setCode(303)
                                          ->goto('new', 'reports', '', array('rm_type' => $reportMenuType));
                    }
                    break;
                case 'DISCARD':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $reportMenuType = $this->getRequest()->getParam('rm_type');
                    $this->_redirector->setCode(303)
                                      ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));
                    break;
                default:
                    break;
            }


            if ($this->session->report !== null) {
                $this->view->report = $this->session->report;
                $this->session->report = null;
            } else {
                $this->view->report = new ReportLine();
            }
        } else {
            $this->view->flashMessenger->addMessage('Only Admin allowed to create Report');
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $reportMenuType = $this->getRequest()->getParam('rm_type');
            $params = array('rm_type' => $reportMenuType);
            $this->_redirector->setCode(303)
                              ->goto('index', 'reports', '', $params);
        }
        $this->view->companyList = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
        $this->view->reportTypes = minder_array_merge(array('' => ''), $this->minder->getReportTypes());
        $this->view->reportMenuTypes = array_merge(array('' => ''), $this->minder->getReportMenuTypes());
    }

    /**
     * Run a single report specified by id
     * (/reports/show/report_id/id)
     *
     * Displays the table with report data.
     *
     * @return void
     */
    public function showAction()
    {
                $log = Zend_Registry::get('logger');
                $log->info(__FUNCTION__);
        
        if($this->session->firstRun){
            $this->minder->updateReportActivity($this->getRequest()->getParam('report_id'));
            $this->session->firstRun = false;
        }
        
        $this->view->report = $this->minder->getReport($this->getRequest()->getParam('report_id'));
        $this->view->pageTitle = "Report: #" . $this->view->report->id . ' - ' . $this->view->report->items['NAME'];
        $params = array('report_id'         => $this->view->report->id,
                        'rm_type'  => $this->getRequest()->getParam('rm_type'));
        $log->info(print_r($params, true));
        switch ($this->getRequest()->getPost('action')) {
            case 'COPY':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('copy', 'reports', '', $params);
                break;
            case 'DELETE':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('delete', 'reports', '', $params);
                break;
            case 'EDIT':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('edit', 'reports', '', $params);
                break;
        }
        //-- @todo: code need rewrite to optimize page by page navigation
        if ($this->getRequest()->getParam('old_start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('old_start_item');
        } else {
            $this->view->startItem = 0;
        }
        if ($this->view->startItem == 0 && $this->getRequest()->getParam('start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('start_item');
        }
        if ($this->getRequest()->getPost('show_by') !== null) {
            $this->view->showBy    = $this->getRequest()->getPost('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } elseif ($this->getRequest()->getParam('show_by') != null) {
            $this->view->showBy    = $this->getRequest()->getParam('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } else {
            $this->view->showBy = 15;
        }
        if ($this->getRequest()->getParam('pageselector') !== null) {
            if ($this->getRequest()->getParam('show_by') === $this->getRequest()->getParam('old_show_by')) {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->view->showBy;
                $this->view->pageSelector = $this->getRequest()->getParam('pageselector');
            } else {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->getRequest()->getParam('old_show_by');
                $this->view->pageSelector = floor($this->view->startItem / $this->view->showBy);
                $this->view->startItem    = $this->view->pageSelector * $this->view->showBy;
            }
        } else {
            $this->view->pageSelector = floor($this->view->startItem /$this->view->showBy);
        }
        settype($this->view->showBy, 'integer');
        settype($this->view->pageSelector, 'integer');
        //-- end process input navigation values

        //-- Try to handle exception if SQL incorrect.

        // restore original handlers
        //restore_exception_handler();
        //restore_error_handler();
        try {
            $listOfPatterns = array();
            $reportParams = array();
            $reportValues = "";
            $repParams = array(); 
            if (isset($this->session->reportPrompts)) {
            $log->info("session:" . print_r($this->session->reportPrompts , true));
                foreach ($this->session->reportPrompts as $key => $val) {
                    $reportDetail = $this->minder->getReportDetail($key);
                    $listOfPatterns[] = array($reportDetail->items['QUERY_FIELD'] => $val);
                    $reportParams [$reportDetail->items['QUERY_FIELD']] = $val;
                    if ($reportDetail->items['QUERY_PROMPT_TYPE'] == 'C')
                    {
                        $reportValues .= "_" . $val;
                    }
                }
                foreach ($this->minder->getReportDetails($this->view->report->id) as $reportDetail2) {
                    if ($reportDetail2->items['QUERY_PROMPT_TYPE'] == 'R') {
                        // have to copy from field
                        $reportParams [$reportDetail2->items['QUERY_FIELD']] = $reportParams[$reportDetail2->items['QUERY_COPY_FIELD']];
                        $listOfPatterns[] = array($reportDetail2->items['QUERY_FIELD'] => $reportParams[$reportDetail2->items['QUERY_COPY_FIELD']] );
                    }
                }
            }
            $log->info("reportParams:" . print_r($reportParams, true));
            $log->info("listOfPatterns:" . print_r($listOfPatterns, true));
/*
*/

            if(!$this->minder->isAdmin) {
                $report = $this->minder->getReport($this->view->report->id);
                $pattern = "/(^|\s|;|\()(INSERT|UPDATE|DELETE)\s/i";
                if(preg_match($pattern, $report->items['QUERY'])) {
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->view->flashMessenger->addMessage("This report does not exist");
                    $this->_redirector->setCode(303)
                                      ->goto('index', 'reports', '', $params);
                }


            }

            $log->info('report_id:' . $this->view->report->id);

            // get report
            $report = $this->minder->getReport( $this->view->report->id ); 

            $log->info('got Report Query to DB');

            if ($report->items['REPORT_TYPE'] == 'JS') {
                // a jasperserver report
                $reportParams['uri'] = $report->items['REPORT_URI'];
                $reportParams['format'] = $report->items['REPORT_FORMAT'];
                $reportType = $this->minder->getReportTypeList( $report->items['REPORT_TYPE'] ); 
		$reportUrl = $reportType->items['RT_URL'];
                $log->info(print_r($reportParams, true));

                $soap = new Jasper_SoapWrapper($this->minder->userId,$this->minder->deviceId, $reportUrl );
                $log->info('got soap object');
	
                // try a jasper login
                $soap->jasperLogin(); /* a dummy */
                if ($soap->jasperLogin()) {
                    //$this->view->flashMessenger->addMessage("Logged into Jasper OK." );
                    $log->info('logged into jasper OK');
                    // then execute the report
                    $data = $soap->jasperExecuteReport($reportParams); /* a dummy */
                    if (false !== ($data = $soap->jasperExecuteReport($reportParams))) {
                        //$log->info('run report response:' .print_r($data,true));
                        $this->getResponse()->setHeader('Content-Type', 'application/pdf');
        		//$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $this->view->report->id . '.pdf"');
        		//$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $this->view->report->name . '.pdf"');
        		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $this->view->report->name . $reportValues . '.pdf"');
                        echo  $data;
                        $this->render('show-pdf');
                        //$data = array();
                    } else {
                        // execute failed
                        $this->view->flashMessenger->addMessage($soap->lastError );
                        $log->info('failed to run report:' . $soap->lastError);
                        $this->_redirector->setCode(303)
                              ->goto('index', 'reports', '', array('rm_type' => $params['rm_type']));
                    }
                } else {
                    $this->view->flashMessenger->addMessage("Failed to Login to Jasper ." );
                    $log->info('failed to login to jasper ');
                    $this->_redirector->setCode(303)
                              ->goto('index', 'reports', '', array('rm_type' => $params['rm_type']));
                }
            } elseif ($report->items['REPORT_TYPE'] == 'RM') {
                if ($report->items['REPORT_FORMAT'] == 'PDF') {
                    $report = Minder_Report_Factory::makeReport($this->view->report->id);
                    foreach ($reportParams as $name => $value) {
                        $report->setQueryFieldValue($name, $value);
                    }

                    try {
                        $pdfImage = $report->getPdfImage();
                        $this->getResponse()->setHeader('Content-Type', 'application/pdf');
                        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $this->view->report->name . $reportValues . '.pdf"');

                        echo  $pdfImage;
                        $this->render('show-pdf');
                    } catch (Exception $e) {
                        $this->view->flashMessenger->addMessage($e->getMessage());
                        $log->info('failed to run report:' . $e->getMessage());
                        $this->_redirector->setCode(303)
                            ->goto('index', 'reports', '', array('rm_type' => $params['rm_type']));
                    }
                } elseif ($report->items['REPORT_FORMAT'] == 'CSV') {
                    // a reportman report
                    $reportParams['uri'] = $report->items['REPORT_URI'];
                    $reportParams['format'] = $report->items['REPORT_FORMAT'];
                    $reportType = $this->minder->getReportTypeList($report->items['REPORT_TYPE']);
                    $reportParams['url'] = $reportType->items['RT_URL'];
                    $reportParams['username'] = $reportType->items['RT_USER_ID'];
                    $reportParams['password'] = $reportType->items['RT_PASS_WORD'];
                    //$log->info('reportParams:' . print_r($reportParams,true));
                    $soap = new Reportman_Wrapper($this->minder->userId, $this->minder->deviceId);
                    // then execute the report

                    if (false !== ($data = $soap->reportmanExecuteReport($reportParams))) {
                        $this->getResponse()->setHeader('Content-Type', 'text/csv');
                        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $this->view->report->name . $reportValues . '.csv"');

                        echo $data;
                        $this->render('show-pdf');
                    } else {
                        // execute failed
                        $this->view->flashMessenger->addMessage($soap->lastError);
                        $log->info('failed to run report:' . $soap->lastError);
                        $this->_redirector->setCode(303)
                            ->goto('index', 'reports', '', array('rm_type' => $params['rm_type']));
                    }
                }
            } else {
                $data = $this->minder->reportRun($this->view->report->id, $listOfPatterns);
            }
        } catch (Exception $e) {
            // restore main handlers for project
            //set_exception_handler('exceptionHandler');
            //set_error_handler('errorHandler');
            if ($this->minder->isAdmin) {
                $this->view->flashMessenger->addMessage("Exception while REPORT execution. Please fix it." . $this->minder->lastError );
                $this->_redirector->setCode(303)
                                  ->goto('edit', 'reports', '', $params);
            } else {
                $this->view->flashMessenger->addMessage("Exception while REPORT execution. Please contact your Admin.");
                $this->_redirector->setCode(303)
                                  ->goto('index', 'reports', '', $params);
            }
        }
        if (count($data['table']) > 0) {
            $this->view->data = $data['table'];
        } else {
            $this->view->data = array();
        }
            $this->view->headers      = $data['fields'];
            $this->view->reportHeader = $data['report_header'];
            $this->view->reportFooter = $data['report_footer'];
            $this->view->numRecords   = count($this->view->data);

        //-- @todo: code need a tunning for logic
        //-- post process navigation
        if ($this->view->startItem > count($this->view->data)) {
            $this->view->startItem = count($this->view->data) - $this->view->showBy;
        }
        if ($this->view->startItem < 0) {
            $this->view->startItem = 0;
        }
        if (($this->view->startItem + $this->view->showBy) > count($this->view->data)) {
            $this->view->maxno = count($this->view->data) - $this->view->startItem;
        } else {
            $this->view->maxno = $this->view->showBy;
        }
        //-- end post process

        $this->view->numRecords = count($this->view->data);
        $this->view->pages      = array();
        for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->showBy); $i++) {
            $this->view->pages[] = $i;
        }

        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('report-csv');
                return;
                break;

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
                break;

            case 'REPORT: PDF':
                if ($this->_getParam('step2')) {
                    $this->_exportToPdf();
                } else {
                    $this->view->fonts = array(
                        'courier'      => 'Courier',
                        'courierB'     => 'Courier-Bold',
                        'courierI'     => 'Courier-Oblique',
                        'courierBI'    => 'Courier-BoldOblique',
                        'helvetica'    => 'Helvetica',
                        'helveticaB'   => 'Helvetica-Bold',
                        'helveticaI'   => 'Helvetica-Oblique',
                        'helveticaBI'  => 'Helvetica-BoldOblique',
                        'times'        => 'Times-Roman',
                        'timesB'       => 'Times-Bold',
                        'timesI'       => 'Times-Italic',
                        'timesBI'      => 'Times-BoldItalic',
                    );

                    $this->view->orientations = array(
                        'p' => 'Portrait',
                        'l' => 'Landscape',
                    );
                    $this->view->orientation = 'p';

                    $this->view->formats = array(
                        'a3' => 'A3',
                        'a4' => 'A4',
                        'a5' => 'A5',
                        'letter' => 'Letter',
                        'legal' => 'Legal',
                    );

                    $this->view->size = 11;
                    $this->view->sizes = array(
                        8  => 8,
                        9  => 9,
                        10 => 10,
                        11 => 11,
                        12 => 12,
                        14 => 14,
                        16 => 16,
                        18 => 18,
                        20 => 20,
                    );
                    $this->render('report-pdf');
                }
                return;

            default:
                break;
        }

        if ($this->getRequest()->getParam('fmt') == null) {
            $this->view->data = array_slice($this->view->data,
                                            $this->view->startItem,
                                            $this->view->maxno);
        } elseif (strtolower($this->getRequest()->getParam('fmt')) == 'txt')  {
        } elseif (strtolower($this->getRequest()->getParam('fmt')) == 'csv') {
        }
    }

    /**
     * Allows user to edit existing report specified by id
     * (/reports/edit/report_id/id)
     *
     * Displays the form for editing report.
     *
     * @return void
     */
    public function editAction()
    {
        if ($this->minder->isAdmin) {
            if (false !== ($this->view->report = $this->minder->getReport($this->getRequest()->getParam('report_id')))) {
                $this->view->pageTitle = "Edit Report: #" . $this->view->report->id . ' - ' . $this->view->report->items['NAME'];
                $params = array('report_id' => $this->view->report->id);
                switch ($this->getRequest()->getPost('action')) {
                    case 'SAVE':
                        $this->view->report->save($this->getRequest()->getPost());

                        if(($this->minder->userId != 'Admin' && !$this->minder->isAdmin)) {
                            $upperQuery = strtoupper($this->view->report->items['QUERY']);
                            $pattern = "/INSERT|DELETE|UPDATE/";
                            if(preg_match($pattern, $upperQuery)) {
                                $this->view->flashMessenger->addMessage('Unable to save report. Try again.' . $this->minder->lastError);
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)
                                                  ->goto('edit', 'reports', '', $params);
                            }
                        }

                        if ($this->minder->reportUpdate($this->view->report)) {
                            $this->_redirector = $this->_helper->getHelper('Redirector');
                            $params['rm_type'] = $this->getRequest()->getParam('rm_type');
                            $this->_redirector->setCode(303)
                                              ->goto('edit', 'reports', '', $params);
                        } else {
                            $this->view->flashMessenger->addMessage('Unable to save report. Try again.' . $this->minder->lastError);
                            $this->_redirector = $this->_helper->getHelper('Redirector');
                            $this->_redirector->setCode(303)
                                              ->goto('edit', 'reports', '', $params);
                        }
                        break;
                    case 'DISCARD':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $reportMenuType = $this->getRequest()->getParam('rm_type');
                        $this->_redirector->setCode(303)
                                          ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));
                        break;
                    case 'COPY':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $params['rm_type'] = $this->getRequest()->getParam('rm_type');
                        $this->_redirector->setCode(303)
                                          ->goto('copy', 'reports', '', $params);
                        break;
                    case 'DELETE':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $params['rm_type'] = $this->getRequest()->getParam('rm_type');
                        $this->_redirector->setCode(303)
                                          ->goto('delete', 'reports', '', $params);
                        break;
                    case 'SHOW':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $params['rm_type'] = $this->getRequest()->getParam('rm_type');
                        $this->_redirector->setCode(303)
                                          ->goto('show', 'reports', '', $params);
                        break;
                    default:
                        break;
                }
            } else {
                $this->view->flashMessenger->addMessage('No report with REPORT_ID = ' . $this->getRequest()->getParam('report_id'));
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $reportMenuType = $this->getRequest()->getParam('rm_type');
                $this->_redirector->setCode(303)
                                  ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));
            }
        } else {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $reportMenuType = $this->getRequest()->getParam('rm_type');
            $this->_redirector->setCode(303)
                              ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));
        }

        $this->view->companyList = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

        $this->view->headers = array('REPORT_DETAIL_ID'  => 'Prompt ID',
                                     'SEQUENCE'          => 'Sequence',
                                     'QUERY_FIELD'       => 'Placeholder #',
                                     'QUERY_PROMPT'      => 'Prompt Message',
                                     'QUERY_DB_FIELD'    => 'Fieldname',
                                     'QUERY_PROMPT_TYPE' => 'Type',
                                     'QUERY_COPY_FIELD' => 'Copy'
                                    );

        $this->view->prompts     = $this->minder->getReportDetails($this->getRequest()->getParam('report_id'));
        $this->view->reportTypes = minder_array_merge(array('' => ''), $this->minder->getReportTypes());
        $this->view->reportMenuTypes = array_merge(array('' => ''), $this->minder->getReportMenuTypes());
    }

    /**
     * Delete existing report specified by id
     * (/reports/delete/report_id/id)
     *
     * @return void
     */
    public function deleteAction()
    {
        $reportMenuType = $this->getRequest()->getParam('rm_type');
        if ($this->minder->isAdmin) {
            switch ($this->getRequest()->getParam('confirm')) {
                case 'YES':
                    $this->view->pageTitle = "Delete Report:" . $this->getRequest()->getParam('report_id');
                    if ($this->getRequest()->getParam('report_id') != null) {
                        $this->minder->reportDelete($this->getRequest()->getParam('report_id'));
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));

                    }
                    break;
                case 'MASS DELETE';
                    if (count($this->session->reportIdList) > 0) {
                        foreach($this->session->reportIdList as $key => $val) {
                            if (false === $this->minder->reportDelete($val)) {
                                $this->view->flashMessenger->addMessage('Unable to delete report with REPORT_ID ' . $val);
                            }
                        }
                    }
                    $this->session->reportIdList = array();
                    break;
                case 'NO':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));
                default:
                    $this->view->report = $this->minder->getReport($this->getRequest()->getParam('report_id'));
                    return;
                break;
            }
        }
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_redirector->setCode(303)
                          ->goto('index', 'reports', '', array('rm_type' => $reportMenuType));
    }

    /**
     * Copy existing report specified by id and
     * open it for editing as new report
     * (/reports/copy/report_id/id)
     *
     * @return void
     */
    public function copyAction()
    {
        $params = array('rm_type' => $this->getRequest()->getParam('rm_type'));
        if ($this->minder->isAdmin) {
            $new = $this->minder->getReport($this->getRequest()->getParam('report_id'));
            $this->session->report = $new;

            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('new', 'reports', '', $params);
        } else {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('index', 'reports', '', $params);
        }
    }

    public function savePromptAction()
    {
        $obj = new ReportDetail();
        $obj->save($this->getRequest()->getParams());
        $obj->id = $obj->items['REPORT_DETAIL_ID'];
        switch (strtolower($this->getRequest()->getParam('method'))) {
            case 'save':
                if (null != $obj->id) {
                    if ($this->minder->reportDetailUpdate($obj)) {
                        $data['message'] = 'Prompt updated successfully';
                        $data['action'] = 'update';
                        $data['id'] = $obj->id;
                        $data['data'] = $obj->items;
                    } else {
                        $data['message'] = 'Prompt not updated.' + "\n" + $this->minder->lastError;
                        $data['id'] = false;
                    }
                } else {
                    if ($this->minder->reportDetailCreate($obj)) {
                        $data['message'] = 'Prompt added successfully';
                        $data['action'] = 'insert';
                        $data['id'] = $obj->id;
                        $data['data'] = $obj->items;
                    } else {
                        $data['message'] = 'Prompt not added.' + "\n" + $this->minder->lastError;
                        $data['id'] = false;
                    }
                }
                break;
            case 'delete':
                if (null != $obj->id) {
                    if ($this->minder->reportDetailDelete($obj->id)) {
                        $data['message'] = 'Prompt deleted successfully';
                        $data['action'] = 'delete';
                        $data['id'] = $obj->id;
                        $data['data'] = $obj->items;
                    } else {
                        $data['message'] = 'Prompt not deleted.' + "\n" + $this->minder->lastError;
                        $data['id'] = false;
                    }
                } else {
                    $data['message'] = 'Prompt has no ID and can not be deleted.';
                    $data['id'] = false;
                }
                break;
            default:
                $data['message'] = 'Internal error occured.' . strtolower($this->getRequest()->getParam('action'));
                $data['id'] = false;
            break;
        }

        $this->view->data = $data;
    }

    public function getPromptsAction()
    {
        $report_id = $this->getRequest()->getParam('report_id');
        $prompts = $this->minder->getReportDetails($report_id);
        $this->view->prompts = $prompts;
        $this->view->report_id = $report_id;
    }

    public function setPromptsAction()
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $prompts = array();
        $data = array();
        if (count($this->getRequest()->getParams()) > 4) {
            $reportId = $this->getRequest()->getParam('report_id');

            if (empty($reportId)) {
                $data['response'] = false;
                $data['message'] = "No REPORT_ID given.";
            } else {
                $data['response'] = true;
                $data['message'] = "Validation success. Press 'Go' to run Report";

                $repParams = array(); 
                foreach ($this->minder->getReportDetails($reportId) as $reportDetail) {
                    $paramValue = $this->getRequest()->getParam('id' . $reportDetail->items['REPORT_DETAIL_ID'], null);
                    $log->info("paramId:" . print_r($reportDetail->items['REPORT_DETAIL_ID'] ,true));
                    $log->info("paramName:" . print_r($reportDetail->items['QUERY_FIELD'] ,true));
                    $log->info("paramValue:" . print_r($paramValue,true));
                    $repParams [ $reportDetail->items['REPORT_DETAIL_ID'] ] = $reportDetail->items['QUERY_FIELD'];

                    if ($reportDetail->items['QUERY_PROMPT_TYPE'] == 'L') {
                        $prompts[$reportDetail->items['REPORT_DETAIL_ID']] = ($paramValue ? 'T' : 'F');
                        /*if (DateTime::createFromFormat('Y-m-d H:i:s', $prompts[$reportDetail->items['REPORT_DETAIL_ID']]) !== FALSE  || DateTime::createFromFormat('Y-m-d', $prompts[$reportDetail->items['REPORT_DETAIL_ID']]) !== FALSE || DateTime::createFromFormat('Y/m/d', $prompts[$reportDetail->items['REPORT_DETAIL_ID']]) !== FALSE) {*/
                        if ($this->minder->isValidDate($prompts[$reportDetail->items['REPORT_DETAIL_ID']])) {
                            $prompts[$reportDetail->items['REPORT_DETAIL_ID']] = $this->minder->getFormatedDateToDb($prompts[$reportDetail->items['REPORT_DETAIL_ID']], "", false);
                        }
                    } else {
                        if ($reportDetail->items['QUERY_PROMPT_TYPE'] == 'R') {
                            // have to find the value for the copy from field
                            $wkIdx = array_search($reportDetail->items['QUERY_COPY_FIELD'] ,$repParams);
                            if ($wkIdx !== False)
                                $paramValue = $this->getRequest()->getParam('id' . $wkIdx, null);
                        } else {
                            if (empty($paramValue)){
                                $data['response'] = false;
                                $data['message'] = "Value '" . $reportDetail->items['QUERY_PROMPT'] . "' can't be EMPTY";
                                break;
                            } else {
                                $prompts[$reportDetail->items['REPORT_DETAIL_ID']] = $paramValue;
                                /*if (DateTime::createFromFormat('Y-m-d H:i:s', $prompts[$reportDetail->items['REPORT_DETAIL_ID']]) !== FALSE  || DateTime::createFromFormat('Y-m-d', $prompts[$reportDetail->items['REPORT_DETAIL_ID']]) !== FALSE || DateTime::createFromFormat('Y/m/d', $prompts[$reportDetail->items['REPORT_DETAIL_ID']]) !== FALSE) {*/
                                if ($this->minder->isValidDate($prompts[$reportDetail->items['REPORT_DETAIL_ID']])) {
                                    $prompts[$reportDetail->items['REPORT_DETAIL_ID']] = $this->minder->getFormatedDateToDb($prompts[$reportDetail->items['REPORT_DETAIL_ID']], "", false);
                                }
                            }
                        }
                    }
                }
            }

        } else {
            $data['response'] = true;
            $data['message'] = "No input required. Press 'Go' to run Report";
        }
        $firstRun = $this->getRequest()->getParam('first_run');
        if($firstRun){
            $this->session->firstRun = true;
        } else {
            $this->session->firstRun = false;
        }
        $this->session->reportPrompts = $prompts;
        $data['count'] = count($prompts);
        $this->view->data = $data;
        $this->render('save-prompt');
        $log->info("End of " . __FUNCTION__);
    }

    protected function _exportToPdf()
    {
        $pdf = new HtmlToPdf($this->_getParam('orientation'), 'mm', $this->_getParam('format'));

        // Append logo in the header.
        if ($logo = imagecreatefromstring(Minder::getInstance()->getLogo())) {
            $filename = rtrim(sys_get_temp_dir(), '\\/') . '/' . md5(uniqid(rand(), true)) . '.png';
            imagepng($logo, $filename);
        } else {
//            $this->view->flashMessenger->addMessage('The error occur during image execution.');
//            $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
			$logo = @imagecreate(1, 1)
			    or die("Cannot Initialize new GD image stream");
//			$background_color = imagecolorallocate($im, 0, 0, 0); 
        }
        $pdf->setLogo($filename);

        // Setup margins.
        $margins = (array) $this->_getParam('margins');
        if (!isset($margins['left'])) {
            $margins['left'] = $pdf->lMargin;
        } else {
            $margins['left'] = intval($margins['left']);
        }
        if (!isset($margins['right'])) {
            $margins['right'] = $pdf->rMargin;
        } else {
            $margins['right'] = intval($margins['right']);
        }
        if (!isset($margins['top'])) {
            $margins['top'] = $pdf->tMargin;
        } else {
            $margins['top'] = intval($margins['top']);
        }
        if (!isset($margins['bottom'])) {
            $margins['bottom'] = $pdf->bMargin;
        } else {
            $margins['bottom'] = intval($margins['bottom']);
        }
        $pdf->SetMargins($margins['left'], $margins['top'], $margins['right']);
        $pdf->SetAutoPageBreak(true, $margins['bottom']);   // Bottom margin.

        $pdf->addPage();

        // Setup font.
        if (!$fontSize = $this->_getParam('size')) {
            $fontSize = 11;
        }
        if (!$font = $this->_getParam('font')) {
            $font = 'Arial';
        }
        $pdf->SetFont($font, '', $fontSize);

        $headers = $this->view->headers;
        $i = 0;
        $width = array();
        foreach ($headers as $cell) {
            $width[$i] = $pdf->GetStringWidth($cell);
            $i++;
        }

        $data = array();
        foreach ($this->view->data as $row) {
            $newrow = array();
            $i = 0;
            foreach($row as $cell) {
                $newrow[] = $cell;
                $width[$i] = max($pdf->GetStringWidth($cell), $width[$i]);
                $i++;
            }
            $data[] = $newrow;
        }

        $sumWidth = array_sum($width);
        $ratio = $sumWidth / ($pdf->w - $pdf->lMargin - $pdf->rMargin);
        if ($ratio == 0) {
            $ratio = 1;
        }
        foreach ($width as $k => $v) {
            $width[$k] = $v / $ratio;
        }

        $fontRatio = ($sumWidth + count($width) * 6) / ($pdf->w - $pdf->lMargin - $pdf->rMargin);
        if ($fontRatio == 0) {
            $fontRatio = 1;
        }
        $pdf->SetFont($font, '', $fontSize);

        $this->_parseTags($this->view->reportHeader)
             ->_parseTags($this->view->reportFooter);

        // Output all content.
        $pdf->writeHTML($this->view->reportHeader);
        $pdf->writeHTML('<br>');
        $pdf->writeTable($headers, $data, $width);
        $pdf->writeHTML($this->view->reportFooter);

        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/octet-stream');
        $response->setHeader('Content-type', 'application/force-download');
        $response->setHeader('Content-Disposition', 'attachment; filename="report.pdf"');
        echo $pdf->render();
        $this->_helper->viewRenderer->setNoRender();
        @unlink($filename);
    }

    protected function _parseTags(&$html)
    {
        if (preg_match_all('#<subreport>(\d+?)</subreport>#i', $html, $matches)) {
            foreach ($matches[1] as $match) {
                try {
                    $subreport = $this->minder->reportRun($match);
                    if (false !== $subreport) {
                        if (is_array($subreport['table']) && ($count = count($subreport['table']))) {
                            if ($count > 1) {
                                $this->view->flashMessenger->addMessage('To many rows, must be only one.');
                                $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                            } else {
                                if (is_array($subreport['table'][0]) && ($count = count($subreport['table'][0]))) {
                                    if ($count > 1) {
                                        $this->view->flashMessenger->addMessage('To many columns, must be only one.');
                                        $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                                    } else {
                                        $result = $subreport['table'][0][key($subreport['table'][0])];
                                        $html = str_replace('<subreport>' . $match . '</subreport>', $result, $html);
                                    }
                                } else {
                                    $this->view->flashMessenger->addMessage('Empty result set.');
                                    $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                                }
                            }
                        } else {
                            $this->view->flashMessenger->addMessage('Empty result set.');
                            $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                        }
                    } else {
                        $this->view->flashMessenger->addMessage('Empty result set.');
                        $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                    }
                } catch (Exception $e) {
                    $this->view->flashMessenger->addMessage($this->view->escape('Invalid format of <subreport> tag.'));
                    $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                }
            }
        }
        return $this;
    }
}
