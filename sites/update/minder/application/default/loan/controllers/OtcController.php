<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Denis Obuhov 
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 *
 */


class OtcController extends Minder_Controller_Action
{
    /**
     * @var Minder_Log_Otc
     */
    protected $_otcLogger = null;

    public function init()
    {
        parent::init();
    }
    public function indexAction()
    {
        $this->view->giParams = array();

        $dataIds = array(
            'DESCRIPTION',
            'SCREEN_BUTTON',
            'BADGE_CODE',
            'COST_CENTRE_CODE',
            'COST_CENTER_CODE',
            'LOCATION',
            'BARCODE',
            'SSN_CODE',
            'NON_UNIQUE_SSN_CODE',
            'ALTBARCODE',
            'ALT_BARCODE',
            'PROD_13',
            'PRODUCT_CODE',
            'PROD_INTERNAL',
            'PROD_UPC12',
            'ALT_PROD',
            'QTY_CODE',
            'SCREEN_BUTTON'
        );

        $this->view->giParams = $this->_paramMangerHelper()->generateSymbologyPrefixDescriptors($dataIds, array('PROD_ID'));

        $issuesProcess = $this->_getProcess(Minder_OtcProcess::ISSUES);
        $this->view->issuesStatus = $issuesProcess->resetProcess();
        $this->view->defaultCostCenter = Minder_OtcProcess_Issue::getDefaultCostCenter();


        if (!empty($this->view->defaultCostCenter)) {
            $this->view->issuesStatus = $issuesProcess->setCostCenter($this->view->defaultCostCenter, 'S');
        }

        $this->view->defaultReturnLocation = Minder_OtcProcess_Return::getDefaultReturnLocation();

        $returnsProccess = $this->_getProcess(Minder_OtcProcess::RETURNS);
        $this->view->returnsStatus = $returnsProccess->resetProcess();

        if (!empty($this->view->defaultReturnLocation)) {
            $this->view->returnsStatus = $returnsProccess->setLocation($this->view->defaultReturnLocation, 'S');
        }

        $this->view->auditStatus = $this->_getProcess(Minder_OtcProcess::AUDIT)->resetProcess();

    	unset($this->session->field1);
    	unset($this->session->field2);
    	unset($this->session->field3);
    	unset($this->session->field4);
    	
    	$headers1 = array('Date Issued'=>'Date Issued', 'Product Code'=>'Product Code', 'Tool ID (SSN)'=>'Tool ID (SSN)', 'Qty'=>'Qty', 'Product/Tool Description'=>'Product/Tool Description', 'Cost Centre'=>'Cost Centre', 'Location' => 'Location');
    	$headers2 = array('Date Returned'=>'Date Returned', 'Product Code'=>'Product Code', 'Tool ID (SSN)'=>'Tool ID (SSN)', 'Qty'=>'Qty', 'Product/Tool Description'=>'Product/Tool Description', 'Returned To'=>'Returned To');
    	
        $this->view->headers1 = $headers1;
    	$this->view->headers2 = $headers2; 
    	
    	$this->view->data = array();
    	
    	$this->view->navigation_issues  = array('show_by'=>10, 'pageselector'=>1);
    	$this->view->navigation_returns = array('show_by'=>10, 'pageselector'=>1);
    	$this->view->pages              = array(1);
    	
    	$this->view->barcode_issues     = ']A0-ISSUE';
    	$this->view->barcode_returns    = ']A0-RETURN';
    	$this->view->barcode_save       = ']A0-SAVE';
    	$this->view->barcode_cancel     = ']A0-CANCEL';
    	
    	
    	// get compaines array (company_id=>'company_id')
    	$compaines = array();
    	$company_ids = $this->minder->getCompanyList();
    	foreach ($company_ids as $id) {
    		$compaines[$id] = $id;
    	}
    	$this->view->compaines = $compaines;

    	
//     	for add tool popup
        $this->view->ssnTypes    = array_merge(array('' => ''), $this->minder->getSsnTypeListFromSsnType());
		$this->view->generics    = $this->_getGenericMapper()->fetchAll(Minder2_Model_Mapper_Abstract::FETCH_MODE_ARRAY);
		$this->view->ssnSubTypes = $this->_getSsnSubTypeMapper()->fetchAll(Minder2_Model_Mapper_Abstract::FETCH_MODE_ARRAY);
        $this->view->brands      = array_merge(array('' => ''), $this->minder->getBrandList());
        $this->view->ssnPurchasePrice = $this->_getSsnDefaultPurchasePrice();

        $this->view->loanCheckDefaults = $this->_getLoanCheckDefaults();
        $this->view->loanCheckPeriodList = array_merge(array('' => ''), $this->minder->getLoanCalibratePeriodList());

        $this->view->defaultBorrowerLabelQty = $this->_getDefaultBorrowerLabelQty();
		
// 		for add product popup

		$productTypes = $this->minder->getProductTypeList();
		$this->view->productTypes = $productTypes;
		
		$uoms = $this->minder->getUoms('UT');
		$this->view->uoms = $uoms;

        $this->view->prodIdGenerator = $this->_getProdIdGenerator();
        $this->view->ssnSubTypes = $this->minder->getSsnSubTypes();
//    	Zend_Debug::dump($ssnType);
    }

    protected function _getLoanCheckDefaults() {
        $tools = new Minder_OtcProcess_Tools();
        return $tools->getLoanDefaults();
    }

    protected function _getSsnDefaultPurchasePrice() {
        $optionsList = $this->minder->getOptionsList('SSN_PUR_PR');
        return (count($optionsList)) ? key($optionsList) : 100;
    }

    protected function _initReport($reportType) {
        $this->view->headers = array ('Borrower ID'              => 'LOCATION',
                                      'Borrower Name'            => 'LOCATION_NAME',
                                      'Date Issued / Returned'   => 'DATETIME',
                                      'Tool ID(SSN)'             => 'SSN_ID',
                                      'Action'                   => 'FIELD_00',
                                      'Qty'                      => 'QTY',
                                      'Tool Description'         => 'DESCRIPTION');

        switch ($reportType) {
            case 'REPORT:CONSUMABLES: CSV':
                $this->view->headers = array (
                    'Borrower ID'              => 'LOCATION',
                    'Borrower Name'            => 'LOCATION_NAME',
                    'Date Issued'              => 'DATETIME',
                    'Consumable ID (PROD_ID)'  => 'PROD_ID',
                    'Action'                   => 'FIELD_00',
                    'Qty'                      => 'QTY',
                    'Product/Tool Description' => 'DESCRIPTION',
                    'User'                     => 'PERSON_ID',
                    'Device'                   => 'DEVICE_ID',
                    'Charge To'                => 'REFERENCE'
                );
            case 'ISSUES TODAY: CSV':
            case 'REPORT:TOOLS: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                 ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->view->filePointer = fopen("php://temp/", 'r+');
                return true;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('report-xls-header');
                return true;

            default:
                return false;
         }
    }

    protected function _renderReportPart($reportType, $reportData) {
        $this->view->data = $reportData;
        switch ($reportType) {
             case 'REPORT:CONSUMABLES: CSV':
             case 'ISSUES TODAY: CSV':
             case 'REPORT:TOOLS: CSV':
                 $this->render('report-csv');
                 return true;

             case 'REPORT: XLS':
                 $this->render('report-xls');
                 return true;
             default:
                 return false;
         }
    }

    protected function _completeReport($reportType) {
        switch ($reportType) {
             case 'REPORT:CONSUMABLES: CSV':
             case 'ISSUES TODAY: CSV':
             case 'REPORT:TOOLS: CSV':
                 rewind($this->view->filePointer);
                 echo stream_get_contents($this->view->filePointer);
                 fclose($this->view->filePointer);
                 return true;

             case 'REPORT: XLS':
                 $this->render('report-xls-footer');
                 return true;
             default:
                 return false;
         }
    }
//function edited by SUjith
      protected function issuesReportAction() {
         $session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;
        $this->_helper->viewRenderer->setNoRender(true);

        $from_date   = $this->getRequest()->getParam('from_date');
                $datetimef = $from_date;
                $tz_tof = 'UTC';
                $format = 'Y-m-d h:i:s';

                $dtf = new DateTime($datetimef, new DateTimeZone($tz_from));
                //print_r($dt); exit();
                $dtf->setTimeZone(new DateTimeZone($tz_tof));
                $utcf=$dtf->format($format) ;
                $from_date=$utcf;

        $to_date     = $this->getRequest()->getParam('to_date');
               $datetimet = $to_date;
                $tz_tot = 'UTC';
                //$format = 'Y-m-d h:i:s';

                $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                //print_r($dt); exit();
                $dtt->setTimeZone(new DateTimeZone($tz_tot));
                $utct=$dtt->format($format) ;
                $to_date=$utct;  



        $report_type = $this->getRequest()->getParam('report_type');
        $wh_id       = $this->getRequest()->getParam('wh_id');

        if (!$this->_initReport($report_type)) {
            $this->_redirector->gotoSimple('index');
            return;
        }

        if ($report_type == 'ISSUES TODAY: CSV')
            $to_date = $from_date = date('Y-m-d');

        $isConsumable = ($report_type == 'REPORT:CONSUMABLES: CSV');
        $dataCursor   = $this->minder->getIssues($from_date, $to_date, $wh_id, $isConsumable);
        $data         = $this->minder->fetchNext($dataCursor, 10000);

        while (count($data) > 0) {
            $this->_renderReportPart($report_type, $data);
            $data = $this->minder->fetchNext($dataCursor, 10000);
        }

        $this->minder->freeCursor($dataCursor);
        $this->_completeReport($report_type);
    }

    protected function _getProdIdGenerator() {
        return $this->_minderOptions()->getProdIdGenerator();
    }

    /**
     * @return Minder2_Model_Mapper_Generic
     */
    protected function _getGenericMapper() {
        return new Minder2_Model_Mapper_Generic();
    }

    /**
     * @return Minder2_Model_Mapper_SsnSubType
     */
    protected function _getSsnSubTypeMapper() {
        return new Minder2_Model_Mapper_SsnSubType();
    }
    
    public function printBorrowerAjaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $response   = new Minder_JSResponse();
        $borrower = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState()->issueTo;

    	if ($borrower->existed) {
            $result     =    $this->_getPrinter()->printBorrowerLabel($borrower->getLocnId());

    	    if($result['RES'] < 0)
                $response->errors[] = 'Error while print label(s): ' . $result['ERROR_TEXT'];
            else
                $response->messages[] = $result['ERROR_TEXT'];
    	} else {
            $response->warnings[] = 'No "Issue To" scanned.';
        }

        echo json_encode($response);
    }
    
    public function printToolAjaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();
    	$tool_id  = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState()->item->id;
    	
    	if (!empty($tool_id)) {
            $result     =    $this->_getPrinter()->printToolLabel($this->minder->getIssnForPrint($tool_id));
           
    	    if($result['RES'] < 0)
                $response->errors[] = 'Error while print label(s): ' . $result['ERROR_TEXT'];
            else
                $response->messages[] = $result['ERROR_TEXT'];
    	} else {
            $response->warnings[] = 'No "Tool Id" scanned.';
        }

        echo json_encode($response);
    }

    public function printProductAjaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();
    	$prodId  = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState()->item->id;

    	if (!empty($prodId)) {
            if (false === $prodProfile = $this->minder->getProd($prodId)) {
                $response->errors[] = 'Product Profile not found';
            } else {
                $data = $this->minder->selectProdLabelData($prodProfile['PROD_ID'], $prodProfile['COMPANY_ID']);
                $result     =    $this->_getPrinter()->printProductLabel($data);

                if($result['RES'] < 0)
                    $response->errors[] = 'Error while print label(s): ' . $result['ERROR_TEXT'];
                else
                    $response->messages[] = $result['ERROR_TEXT'];
            }
    	} else {
            $response->warnings[] = 'No "Prod Id" scanned.';
        }

        echo json_encode($response);
    }

    protected function _buildBorrowerObject($params) {
        $borrower = new Minder2_Model_Borrower();
        $borrower->borrowerId   = (isset($params['borrower_id'])) ? $params['borrower_id'] : '';
        $borrower->borrowerName = (isset($params['borrower_name'])) ? $params['borrower_name'] : '';
        $borrower->companyId    = (isset($params['company_id'])) ? $params['company_id'] : '';

        return $borrower;
    }

    /**
     * @param Minder2_Model_Borrower $borrower
     * @param Minder_JSResponse $response
     * @return boolean
     */
    protected function _borrowerIsValid($borrower, &$response) {
        if (empty($borrower->borrowerId))
            $response->errors[] = '"Borrower Id" is empty';

        if (empty($borrower->borrowerName))
            $response->errors[] = '"Borrower Name" is empty';

        if (empty($borrower->companyId))
            $response->errors[] = '"Company Id" is empty';

        return empty($response->errors);
    }

    protected function _getBorrowerMapper() {
        return new Minder2_Model_Mapper_Borrower();
    }
    
    public function addBorrowerAjaxAction()
    {
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();

        $newBorrower = $this->_buildBorrowerObject($this->getRequest()->getParams());
        $confirmRename = ($this->getRequest()->getParam('confirmRename', 'false') == 'true');
        if (!$this->_borrowerIsValid($newBorrower, $response)) {
            echo json_encode($response);
            return;
        }

        try {
            $existedBorrower = $this->_getBorrowerMapper()->get($newBorrower->borrowerId);

            if ($confirmRename || (!$existedBorrower->existed) || ($existedBorrower->borrowerName == $newBorrower->borrowerName)) {
                $this->_getBorrowerMapper()->save($newBorrower);
                $response->messages[] = 'Borrower added.';
                $response->borrowerAdded = true;
                $response->borrower = $newBorrower->getFields();
            } else {
                $response->confirmRename = true;
                $response->borrower = $existedBorrower->getFields();
            }

        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        if ($response->borrowerAdded && $this->getRequest()->getParam("print_label") =='true') {
            $intNoCopyToPrint = $this->getRequest()->getParam('label_qty', 1);
            $result   = $this->_getPrinter()->printBorrowerLabel($newBorrower->borrowerId, $intNoCopyToPrint);

            if($result['RES'] < 0) {
                $response->errors[] =   'Error while print label(s): ' . $result['ERROR_TEXT'];
            }

            $response->messages[] = $intNoCopyToPrint . " Label(s) where printed.";
        }

        echo json_encode($response);
        return;
    }


/**********************edit**********************/
    protected function _buildLocationObject($params) {
        $location = new Minder2_Model_LocationNew();
	$location->whId         = (isset($params['wh_id'])) ? $params['wh_id'] : '';
        $location->locationId   = (isset($params['locationnew_id'])) ? $params['locationnew_id'] : '';
        $location->locationName = (isset($params['location_name'])) ? $params['location_name'] : '';
        $location->locationOwner= '';
	$location->moveStat	= (isset($params['move_stat'])) ? $params['move_stat'] : '';
	$location->labelQty     = (isset($params['label_qty'])) ? $params['label_qty'] : '';
        return $location;
    }

    protected function _locationIsValid($location, &$response) {
        if (empty($location->locationId))
            $response->errors[] = '"Location Id" is empty';

        if (empty($location->locationName))
            $response->errors[] = '"Location Name" is empty';

        return empty($response->errors);
    }

    protected function _getLocationMapper() {
        return new Minder2_Model_Mapper_LocationNew();
    }

    public function addLocationAjaxAction()
    {   
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();

        $newLocation = $this->_buildLocationObject($this->getRequest()->getParams());
        $confirmEdit = ($this->getRequest()->getParam('confirmEdit', 'false') == 'true');
        if (!$this->_locationIsValid($newLocation, $response)) {
            echo json_encode($response);
            return;
        }

        try {
            $existedLocation = $this->_getLocationMapper()->get($newLocation->locationId,$newLocation->whId);

            if ($confirmEdit || (!$existedLocation->existed) || ($existedLocation->locationName == $newLocation->locationName)) {
                $this->_getLocationMapper()->save($newLocation);
                $response->messages[] = 'Location added.';
                $response->locationAdded = true;
                $response->location = $newLocation->getFields();
            } else {
                $response->confirmEdit = true;
                $response->location = $existedLocation->getFields();
            }

        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        if ($response->locationAdded && $this->getRequest()->getParam("print_label") =='true') {
            $intNoCopyToPrint = $this->getRequest()->getParam('label_qty', 1);
	        $locationList   =   $this->minder->getLocationForPrint($newLocation->locationId,$newLocation->whId); 	
            $result   = $this->_getPrinter()->printLocationLabel($locationList, $intNoCopyToPrint);

            if($result['RES'] < 0) {
                $response->errors[] =   'Error while print label(s): ' . $result['ERROR_TEXT'];
            }

            $response->messages[] = $intNoCopyToPrint . " Label(s) where printed.";
        }

        echo json_encode($response);
        return;
    }

/************************edit***********************/


    public function addCostCentreAction() {
        $this->_viewRenderer()->setNoRender();

        $costCentreData = $this->_costCentreHelper()->prepareCreateData($this->getRequest());
        $response = $this->_costCentreHelper()->validateCreateData($costCentreData);

        if ($response->hasErrors()) {
            echo json_encode($response);
            return;
        }

        try {
            $this->_costCentreHelper()->createCostCentre($costCentreData);
            $response->costCentre = $costCentreData;
            $response->addMessages('Cost Centre Is Saved Successfully');

        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        if (!$response->hasErrors()) {
            $printRequest = $this->getRequest()->getParam('printRequest', array('printLabel' => 'false', "labelQty" => 0));
            //$labelQty = intval($printRequest['labelQty']);
            $labelQty = $_POST["printRequest"]["labelQty"];

            if ($printRequest['printLabel'] == 'true' && $labelQty > 0) {
                try {

                    $printResult = $this->_printLabelHelper()->printCostCentreLabel($costCentreData, $labelQty);

                    $response->addMessages($printResult->messages);
                    $response->addWarnings($printResult->warnings);
                    $response->addWarnings($printResult->errors);
                } catch (Exception $e) {
                    $response->addWarnings('Error printing label: ' . $e->getMessage());
                }
            }
        }

        echo json_encode($response);
    }

    /**
     * @param $params
     * @return Minder2_Model_Tool
     */
    protected function _buildToolObject($params) {
        $toolObject                 = new Minder2_Model_Tool();
        $toolObject->SSN_ID         = isset($params['ssn_id']) ? $params['ssn_id'] : '';
        $toolObject->SSN_TYPE       = isset($params['type_1']) ? $params['type_1'] : '';
        $toolObject->GENERIC        = isset($params['type_2']) ? $params['type_2'] : '';
        $toolObject->SSN_SUB_TYPE   = isset($params['type_3']) ? $params['type_3'] : '';
        $toolObject->BRAND          = isset($params['brand']) ? $params['brand'] : '';
        $toolObject->MODEL          = isset($params['model']) ? $params['model'] : '';
        $toolObject->SERIAL_NUMBER  = isset($params['serial_number']) ? $params['serial_number'] : '';
        $toolObject->LEGACY_ID      = isset($params['legacy_id']) ? $params['legacy_id'] : '';
        $toolObject->COMPANY_ID     = isset($params['company_id']) ? $params['company_id'] : '';
        $toolObject->ALT_NAME       = isset($params['alt_name']) ? $params['alt_name'] : '';
        $toolObject->PURCHASE_PRICE = isset($params['ssnPurchasePrice']) ? $params['ssnPurchasePrice'] : 0;
        $toolObject->PO_ORDER       = isset($params['poOrder']) ? $params['poOrder'] : 0;

        $toolObject->LOAN_SAFETY_CHECK              = (isset($params['loanSafetyCheck']) && strtolower($params['loanSafetyCheck']) == 'true') ? 'T' : 'F';
        $toolObject->LOAN_LAST_SAFETY_CHECK_DATE    = isset($params['loanLastSafetyCheckDate']) ? $params['loanLastSafetyCheckDate'] : '';
        $toolObject->LOAN_SAFETY_PERIOD_NO          = isset($params['loanSafetyPeriodNo']) ? $params['loanSafetyPeriodNo'] : '';
        $toolObject->LOAN_SAFETY_PERIOD             = isset($params['loanSafetyPeriod']) ? $params['loanSafetyPeriod'] : '';

        $toolObject->LOAN_CALIBRATE_CHECK           = (isset($params['loanCalibrateCheck']) && strtolower($params['loanCalibrateCheck']) == 'true') ? 'T' : 'F';
        $toolObject->LOAN_LAST_CALIBRATE_CHECK_DATE = isset($params['loanLastCalibrateCheckDate']) ? $params['loanLastCalibrateCheckDate'] : '';
        $toolObject->LOAN_CALIBRATE_PERIOD_NO       = isset($params['loanCalibratePeriodNo']) ? $params['loanCalibratePeriodNo'] : '';
        $toolObject->LOAN_CALIBRATE_PERIOD          = isset($params['loanCalibratePeriod']) ? $params['loanCalibratePeriod'] : '';

        $toolObject->LOAN_INSPECT_CHECK             = (isset($params['loanInspectCheck']) && strtolower($params['loanInspectCheck']) == 'true') ? 'T' : 'F';
        $toolObject->LOAN_LAST_INSPECT_CHECK_DATE   = isset($params['loanLastInspectCheckDate']) ? $params['loanLastInspectCheckDate'] : '';
        $toolObject->LOAN_INSPECT_PERIOD_NO         = isset($params['loanInspectPeriodNo']) ? $params['loanInspectPeriodNo'] : '';
        $toolObject->LOAN_INSPECT_PERIOD            = isset($params['loanInspectPeriod']) ? $params['loanInspectPeriod'] : '';

        $location                 = isset($params['location_id']) ? $params['location_id'] : '';
        $toolObject->WH_ID         = strval(substr($location, 0, 2));
        $toolObject->LOCN_ID       = strval(substr($location, 2));

        return $toolObject;
    }

    /**
     * @param Minder2_Model_Tool $tool
     * @param Minder_JSResponse $response
     * @return boolean
     */
    protected function _toolIsValid($tool, &$response) {
        $paramValidator = new Minder2_Validate_Param('BARCODE', true);

        if (!empty($tool->SSN_ID) && !$paramValidator->isValid($tool->SSN_ID))
            $response->errors[] = '"Tool ID" is incorrect.';

        if (empty($tool->SSN_TYPE))
            $response->errors[] = '"Tool Type 1" is empty.';

        if (empty($tool->location))
            $response->errors[] = '"Location" is empty.';

        return count($response->errors) < 1;
    }

    protected function _getToolMapper() {
        return new Minder2_Model_Mapper_Tool();
    }

    /**
     * @return Minder_Printer_Abstract
     */
    protected function _getPrinter() {
        return $this->minder->getPrinter(null, Minder2_Environment::getCurrentPrinter()->DEVICE_ID);
    }
   
    public function addToolAjaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();
        $response->ssnId = '';
        $response->ssnCopyAmount = $this->_getSsnCopyAmount();
        $response->toolCreatedAmount = 0;
        $response->labelPrintedAmount = 0;
        $tool = $this->_buildToolObject($this->getRequest()->getParams());
        $intNoCopyToPrint = $this->getRequest()->getParam('ssnCopyAmount', 1);

        if (!$this->_toolIsValid($tool, $response)) {
            echo json_encode($response);
            return;
        }

        $intToolCreateMode = 0;
        if (empty($tool->SSN_ID) && empty($tool->SERIAL_NUMBER)) {
            $intToolCreateMode = 1;
            while ((!$response->hasErrors()) && $response->ssnCopyAmount > 0) {
                $response = $this->_addTool($tool, $response);
            } 
        } else {
            $response = $this->_addTool($tool, $response);
        }

        if ($response->toolCreatedAmount > 0) {
            $response->addMessages($response->toolCreatedAmount . " Tool(s) created.");
        }

        if ($response->labelPrintedAmount) {
            if ($intToolCreateMode == 1) {
                $response->addMessages($intNoCopyToPrint." Label(s) printed.");
            }
            else{
                $response->addMessages("1 Label(s) printed.");
            }
        }

        echo json_encode($response);
        return;
    }

    protected function _getSsnCopyAmount() {
        $ssnCopyAmount = intval($this->getRequest()->getParam('ssnCopyAmount', 1));

        return ($ssnCopyAmount < 1) ? 1 : $ssnCopyAmount;
    }

    protected function _addTool($tool, Minder_JSResponse $response, $intNoCopyToPrint = "") {
        try {
            $tool = $this->_getToolMapper()->save($tool);
            $response->ssnId = $tool->SSN_ID;
            $response->ssnCopyAmount--;
            $response->toolCreatedAmount++;
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        if($this->getRequest()->getParam('print_label')=='true') {
            $result    = $this->_getPrinter()->printToolLabel($this->minder->getIssnForPrint($tool->SSN_ID), $intNoCopyToPrint);

            if($result['RES'] < 0) {
                $response->errors[] = 'Error while print label(s): ' . $result['ERROR_TEXT'];
            } else {
                $response->labelPrintedAmount++;
            }
        }

        return $response;
    }

    protected function _getProdProfileMapper() {
        return new Minder2_Model_Mapper_ProdProfile();
    }

    protected function _prodProfileIsValid(Minder2_Model_ProdProfile $prodProfile, Minder_JSResponse &$response) {
        if (empty($prodProfile->PROD_ID))
            $response->errors[] = 'Product ID is empty.';

        return empty($response->errors);
    }

    public function addProductAjaxAction()
    {
        $prodProfileMapper = $this->_getProdProfileMapper();

        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();
        $response->prodId = '';
        $request = $this->getRequest();
        $generateProdId = $request->getParam('generate_prod_id', false);
        $prodProfile = $this->_buildProdProfileObject($request->getParams());

        if ($generateProdId === "true") {
            try {
                $prodProfile->PROD_ID = $prodProfileMapper->getNextProdId($prodProfile->COMPANY_ID);
            } catch (Exception $e) {
                $response->errors[] = $e->getMessage();
                echo json_encode($response);
                return;
            }
        }

        $loadQty = intval($this->getRequest()->getParam('load_qty', 0));
        $shouldGenerateProdId = $this->getRequest()->getParam('generate_prod_id', "false");

        if (!$this->_prodProfileIsValid($prodProfile, $response)) {
            echo json_encode($response);
            return;
        }

    	try {
            $prodProfileMapper->createProdProfile($prodProfile, $request->getParam('prod_id_via', 'K'));
            $response->prodId = $prodProfile->PROD_ID;
            $response->messages[] = 'Product added.';

            if ($shouldGenerateProdId === "false" && $loadQty > 0) {
                $result = $prodProfileMapper->loadProduct($prodProfile, Minder_OtcProcess_Return::getDefaultReturnLocation(), $loadQty);

                $printResult = $this->_getPrinter()->printToolLabel($this->minder->getIssnForPrint($result->issn1));

                if($printResult['RES'] < 0)
                    $response->errors[] = 'Error while print label(s): ' . $printResult['ERROR_TEXT'];
                else
                    $response->messages[] = $printResult['ERROR_TEXT'];
            }

            if($request->getParam("print_label", 'false') == 'true') {
                $printData  = $this->minder->selectProdLabelData($prodProfile->PROD_ID, $prodProfile->COMPANY_ID);
                $printerObj = $this->minder->getPrinter();
                $result     = $printerObj->printProductLabel($printData);

                if($result['RES'] < 0){
                    $response->errors[] = 'Error while print label(s): ' . $result['ERROR_TEXT'];
                } else {
                    $response->messages[] = 'Print request send.';
                }
            }

        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            echo json_encode($response);
            return;
		}

        echo json_encode($response);
    }

    protected function _buildProdProfileObject(array $params)
    {
        $prodProfile                          = new Minder2_Model_ProdProfile();
        $prodProfile->PROD_ID                 = $params['prod_id'];
        $prodProfile->SHORT_DESC              = $params['description'];
        $prodProfile->PROD_TYPE               = $params['product_type'];
        $prodProfile->SSN_TYPE                = $params['inventory_type'];
        $prodProfile->ISSUE_UOM               = $params['unit_of_issue'];
        $prodProfile->DEFAULT_ISSUE_QTY       = $params['issue_qty'];
        $prodProfile->NET_WEIGHT              = $params['net_weight'];
        $prodProfile->PP_MATERIAL_SAFETY_DATA = $params['data_sheet'];
        $prodProfile->COMPANY_ID              = $params['owner_id'];
        return $prodProfile;
    }


    public function listLoansAjaxAction()
    {
    	$borrower = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState()->issueTo;
    	$id = $borrower->getLocnId();
    	if (empty($id)) {
    		die("Scan BORROWER_ID before.");
    	}

    	if (isset($this->session->conditions)) {
    		$conditions = $this->session->conditions;
    	} else {
    		$conditions = array();
    	}

    	if (isset($this->session->loans_perpage)) {
    		$loans_perpage = $this->session->loans_perpage;
    	} else {
    		$loans_perpage = 5;
    	}
    	$this->view->loans_show_by = $loans_perpage;

    	if (isset($this->session->loans_page)) {
    		$loans_page = $this->session->loans_page;
    	} else {
    		$loans_page = 1;
    	}
    	$this->view->loans_pageselected = $loans_page;

    	$this->view->headers = array(
    		'TOOL_OR_PRODUCT' 				=> 'ISSN',
            'ACTION'                        => 'Action',
    		'TRN_DATE' 					    => 'Date',
    		'DESCRIPTION' 				    => 'Description',
    		'LOAN_LAST_SAFETY_CHECK_DATE' 	=> 'Safety',
    		'LOAN_LAST_CALIBRATE_CHECK_DATE'=> 'Calib'
    	);

    	$locn = $this->minder->getLocationList($id);
    	$this->view->locn_name = $locn[$id];

    	$data = $this->minder->getIssuesAndReturnsByBorrower($id);

    	$loans_pages = array();
    	for($i=1; $i<=ceil(count($data)/$loans_perpage); $i++) {
			$loans_pages[$i] = $i;
    	}
    	$this->view->loans_pages =  $loans_pages;

    	$ids_all = array();
    	foreach ($data as $row) {
    		$id = $row['ROW_ID'];
    		$ids_all[$id] = $id;
    	}
    	$this->session->ids_all = $ids_all;

        $this->view->total = count($data);
        $data = array_slice($data, ($loans_page-1)*$loans_perpage, $loans_perpage);
    	$this->view->data = $data;
    	$this->view->shownFrom = ($loans_page-1)*$loans_perpage + min(1, count($data));
        $this->view->shownTill = ($loans_page-1)*$loans_perpage + count($data);
    	$this->view->conditions = $conditions;
    	$this->view->totalSelect = count($conditions);
    	$this->session->conditions = $conditions;
//    	var_dump($ids_all);
    	
    }

    protected function _getBorrowerName($borrowerId) {
        $borrower = $this->minder->getBorrower($borrowerId);
        return is_array($borrower) ? $borrower['LOCN_NAME'] : '';
    }

    public function listLoansReturnsAction() {
        $processState = $this->_getProcess(Minder_OtcProcess::RETURNS)->getState();
        $id = $processState->returnFrom->getLocnId();
        if (empty($id)) {
            $this->view->error = "Scan BORROWER_ID before.";
            return;
        }

        $this->_preProcessNavigation();
        /**
         * @var Minder_Controller_Action_Helper_Navigation $navigation;
         */
        $navigation = $this->_helper->Navigation();
        $navigation->setPage($navigation->getPage())->setShowBy($navigation->getShowBy());
        $loans = $this->minder->getIssuesAndReturnsByBorrower($id);

        $this->_postProcessNavigation($loans);

        $offset = $navigation->getPage() * $navigation->getShowBy();
        $this->session->issuesReturns = $loans;
        $this->view->data = array_slice($loans, $offset, $navigation->getShowBy());
        $this->view->headers = array(
            'ACTION'                        => 'I/R',
            'TOOL_OR_PRODUCT' 				=> 'ISSN',
            'TRN_DATE' 					    => 'Date',
            'DESCRIPTION' 				    => 'Description',
            'LOAN_LAST_SAFETY_CHECK_DATE' 	=> 'Safety',
            'LOAN_LAST_CALIBRATE_CHECK_DATE'=> 'Calib',
            'QTY'                           => 'Issued Qty'
        );
        $this->view->title = 'LOANED TO: [' . $id . '] ' . $this->_getBorrowerName($id);
        $this->view->conditions = $this->_getConditions();
    }

    public function markLoansReturnsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $jsonObject = new stdClass();

        $loans = isset($this->session->issuesReturns) ? $this->session->issuesReturns : array();
        $id = $this->getRequest()->getParam('rowId', null);
        $selected = $this->getRequest()->getParam('select', 'true');

        $conditions = $this->_markSelected($loans, $id, $id, $selected, 'list-loans-returns');

        $jsonObject->selected = $conditions;
        $jsonObject->totalRows = count($loans);
        $jsonObject->selectedRows = count($conditions);

        echo json_encode($jsonObject);
    }

    public function exportLoansReturnsAction()
    {
        $action = $this->getRequest()->getParam('report');
        $data = isset($this->session->issuesReturns) ? $this->session->issuesReturns : array();
        $conditions = $this->_getConditions('list-loans-returns');

        $this->view->data = array();
        foreach ($data as $row) {
            if (in_array($row['RECORD_ID'], $conditions)) {
                $this->view->data[] = $row;
            }
        }

        $this->view->headers = array(
            'Borrower Id'               => 'BORROWER_ID',
            'Borrower Name'             => 'LOCN_NAME',
            'Tool (ISSN) or Consumable' => 'TOOL_OR_PRODUCT',
            'Action'                    => 'ACTION',
            'Issued Date'               => 'TRN_DATE',
            'Description'               => 'DESCRIPTION',
            'Safety'                    => 'LOAN_LAST_SAFETY_CHECK_DATE',
            'Calib'                     => 'LOAN_LAST_CALIBRATE_CHECK_DATE'
        );

        $this->_processReportTo($action);
    }

    public function listReturnsAction() {
        $processState = $this->_getProcess(Minder_OtcProcess::RETURNS)->getState();
        $id = $processState->returnFrom->getLocnId();
        if (empty($id)) {
            $this->view->error = "Scan BORROWER_ID before.";
            return;
        }

        $this->_preProcessNavigation();
        /**
         * @var Minder_Controller_Action_Helper_Navigation $navigation;
         */
        $navigation = $this->_helper->Navigation();
        $navigation->setPage($navigation->getPage())->setShowBy($navigation->getShowBy());
        $loans = $this->minder->getReturnsByBorrower($id);

        $this->_postProcessNavigation($loans);

        $offset = $navigation->getPage() * $navigation->getShowBy();
        $this->session->returns = $loans;
        $this->view->data = array_slice($loans, $offset, $navigation->getShowBy());
        $this->view->headers = array(
            'TOOL_OR_PRODUCT' 				=> 'ISSN',
            'TRN_DATE' 					    => 'Date',
            'DESCRIPTION' 				    => 'Description',
            'LOAN_LAST_SAFETY_CHECK_DATE' 	=> 'Safety',
            'LOAN_LAST_CALIBRATE_CHECK_DATE'=> 'Calib'
        );
        $this->view->title = 'RETURNED BY:  [' . $id . '] ' . $this->_getBorrowerName($id);
        $this->view->conditions = $this->_getConditions();
    }

    public function markReturnsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $jsonObject = new stdClass();

        $loans = isset($this->session->returns) ? $this->session->returns : array();
        $id = $this->getRequest()->getParam('rowId', null);
        $selected = $this->getRequest()->getParam('select', 'true');

        $conditions = $this->_markSelected($loans, $id, $id, $selected, 'list-returns');

        $jsonObject->selected = $conditions;
        $jsonObject->totalRows = count($loans);
        $jsonObject->selectedRows = count($conditions);

        echo json_encode($jsonObject);
    }

     public function getLocationAction() {
	
	$locationid=$_GET['field1'];
	$wh_id=$_GET['field2'];
	$result = $this->minder->getLoc($wh_id, $locationid);
	print_r($result);
	exit;
    }

    public function exportReturnsAction()
    {
        $action = $this->getRequest()->getParam('report');
        $data = isset($this->session->returns) ? $this->session->returns : array();
        $conditions = $this->_getConditions('list-returns');

        $this->view->data = array();
        foreach ($data as $row) {
            if (in_array($row['RECORD_ID'], $conditions)) {
                $this->view->data[] = $row;
            }
        }

        $this->view->headers = array(
            'Borrower Id'                       => 'BORROWER_ID',
            'Borrower Name'                     => 'LOCN_NAME',
            'Tool (ISSN)'                       => 'TOOL_OR_PRODUCT',
            'Returned Date'                     => 'TRN_DATE',
            'SSN Description'                   => 'DESCRIPTION',
            'Safety'                            => 'LOAN_LAST_SAFETY_CHECK_DATE',
            'Calib'                             => 'LOAN_LAST_CALIBRATE_CHECK_DATE'
        );

        $this->_processReportTo($action);
    }

    public function listLoansAction() {
        $borrower = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState()->issueTo;
        $this->_listLoans($borrower->getLocnId());
    }

    public function returnsListLoansAction() {
        $processState = $this->_getProcess(Minder_OtcProcess::RETURNS)->getState();
        $this->_listLoans($processState->returnFrom->getLocnId());
        $this->render('list-loans');
    }

	public function markLoansAction()
	{
        $this->_helper->viewRenderer->setNoRender(true);
		$jsonObject = new stdClass();

        $loans = isset($this->session->loans) ? $this->session->loans : array();
        $id = $this->getRequest()->getParam('rowId', null);
        $selected = $this->getRequest()->getParam('select', 'true');

        $id = ($id == 'select_all') ? 'select_complete' : $id;
        $conditions = $this->_markSelected($loans, $id, $id, $selected, 'list-loans');

		$jsonObject->selected = $conditions;
        $jsonObject->totalRows = count($loans);
        $jsonObject->selectedRows = count($conditions);

		echo json_encode($jsonObject);
	}      
    
	public function exportLoansAction()
	{
        $action = $this->getRequest()->getParam('report');
        $data = isset($this->session->loans) ? $this->session->loans : array();
        $conditions = $this->_getConditions('list-loans');

        $this->view->data = array();
        foreach ($data as $row) {
            if (in_array($row['RECORD_ID'], $conditions)) {
                $this->view->data[] = $row;
            }
        }

		$this->view->headers = array(
            'Borrower Id' => 'BORROWER_ID',
            'Borrower Name' => 'LOCN_NAME',
            'Tool (ISSN)' => 'SSN_ID',
            'Issued Date' => 'INTO_DATE',
            'SSN Description' => 'SSN_DESCRIPTION',
            'Safety' => 'LOAN_LAST_SAFETY_CHECK_DATE',
            'Calib' => 'LOAN_LAST_CALIBRATE_CHECK_DATE'
        );

        $this->_processReportTo($action);
	}
	
    public function saveAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $process = $this->_getProcess($this->getRequest()->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Save Tab: ' . $this->getRequest()->getParam('tab', Minder_OtcProcess::ISSUES) . '. About to start ......');
        
        $result = $process->save();
        $this->_otcLog('...... served. Save Tab: ' . $this->getRequest()->getParam('tab', Minder_OtcProcess::ISSUES));
        echo json_encode($result);
    }

    public function recordHomeAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $process = $this->_getProcess($this->getRequest()->getParam('tab', Minder_OtcProcess::RETURNS));
        $this->_otcLog('Record Home: ' . $this->getRequest()->getParam('tab', Minder_OtcProcess::RETURNS) . '. About to start ......');
        $result = $process->recordHome();
        $this->_otcLog('...... served. Record Home: ' . $this->getRequest()->getParam('tab', Minder_OtcProcess::RETURNS));
        echo json_encode($result);
    }

    /**
     * @param $id
     */
    protected function _listLoans($id)
    {
        if (empty($id)) {
            $this->view->error = "Scan BORROWER_ID before.";
            return;
        }

        $this->_preProcessNavigation();

        $loans = $this->minder->getListLoanedByBorrower($id);

        $this->_postProcessNavigation($loans);

        $this->session->loans = $this->view->data = $loans;
        $this->view->data = array_slice(
            $loans,
            $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
            $this->view->navigation['show_by']
        );
        $this->view->headers = array(
            'SSN_ID' => 'ISSN',
            'INTO_DATE' => 'Date',
            'SSN_DESCRIPTION' => 'Description',
            'LOAN_LAST_SAFETY_CHECK_DATE' => 'Safety',
            'LOAN_LAST_CALIBRATE_CHECK_DATE' => 'Calib'
        );
        $this->view->title = 'LOANED TO: [' . $id . '] ' . $this->_getBorrowerName($id);
        $this->view->conditions = $this->_getConditions();
    }

    private function __getDatetime() {
        
    	return date('Y-m-d H:i:s');
    }
    
    public function cancelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $process = $this->_getProcess($this->getRequest()->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Cancel Tab: ' . $this->getRequest()->getParam('tab', Minder_OtcProcess::ISSUES));
        $process->resetProcess();

        if ($process->getState()->processId == Minder_OtcProcess::ISSUES) {
            $defaultCostCenter = $process->getDefaultCostCenter();
            if (!empty($defaultCostCenter))
                $process->setCostCenter($defaultCostCenter, 'S');
        }

        if ($process->getState()->processId == Minder_OtcProcess::RETURNS) {
            $issueProcessState = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState();
            $process->setCostCenter($issueProcessState->chargeTo->id, $issueProcessState->chargeTo->via);

            $defaultLocation = $process->getDefaultReturnLocation();
            if (!empty($defaultLocation))
                $process->setLocation($defaultLocation, 'S');
        }

        echo json_encode($process->getState());
    }

    
    /*protected function _setupShortcuts() {
        
        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
            $shortcuts = array(
                                'Assembly'                  => $this->view->url(array('controller' => 'trolley', 'action' => 'index'), null, true),
                                '<OTC-Issues/Returns>'      => $this->view->url(array('action' => 'index', 'controller' => 'otc'), null, true),
//                                'View Waiting Despatch'     => $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true),
                                'Consignment Exit'          => $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true),
                                'Scan Exit'                 => $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true),
                                'Austpost Manifest'         => $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true),
                                'View Despatched Orders'    => $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true),
                                'Despatch Activity Reports' => $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true),
                                'Person Details'            => array(
                                    'PERSON'                =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
                                )
                         );
        } else {
            $shortcuts = array(
                                'Awaiting Checking'         => $this->view->url(array('action' => 'index', 'controller' => 'awaiting-checking', 'module' => 'despatches'), null, true),
                                '<OTC-Issues/Returns>'      => $this->view->url(array('action' => 'index', 'controller' => 'otc'), null, true),
//                                'View Waiting Despatch'     => $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true),
                                'Consignment Exit'          => $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true),
                                'Scan Exit'                 => $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true),
                                'Austpost Manifest'         => $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true),
                                'View Despatched Orders'    => $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true),
                                'Despatch Activity Reports' => $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true),
                                'Person Details'            => array(
                                    'PERSON'                =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
                                )
                         );
        }
        
        
        $this->view->shortcuts = $shortcuts;
        
        return $this;
    }*/
    
    public function marklinesissuesAction() {
        
    	$jsonObject = new stdClass();
    	
    	die(json_encode($jsonObject));
    }
    
    public function marklinesreturnsAction() {
        
     	$jsonObject = new stdClass();
    	
    	die(json_encode($jsonObject));
    }

    /**
     * @param $processId
     * @return Minder_OtcProcess_Audit|Minder_OtcProcess_Issue|Minder_OtcProcess_Return
     * @throws Exception
     */
    protected function _getProcess($processId)
    {
        return Minder_OtcProcess::getOtcProcessObject($processId);
    }

    /**
     * @return Minder_Log_Otc
     */
    protected function _getOtcLogger() {
        if (is_null($this->_otcLogger))
            $this->_otcLogger = new Minder_Log_Otc();

        return $this->_otcLogger;
    }

    protected function _otcLog($message) {
        $this->_getOtcLogger()->info($message);
    }

    // action for list tool history dialog
    public function viewHistoryAction() {
        $processState = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState();

        $this->view->itemType = ($processState->item->isTool()) ? 'TOOL' : 'PRODUCT';
        $this->session->itemType = $this->view->itemType;

        if (empty($processState->item->id)) {
           $this->view->toolError = 'Scan TOOL ID at first';
           return;
        }

        $toolDesc   = $this->minder->getProductDescById($processState->item->id);

        if (empty($toolDesc)) {
            $toolDesc = trim($processState->item->description);
        }

        $this->view->toolInfo = $processState->item->id . ' ' . $toolDesc;

        $this->_preProcessNavigation();

        $this->view->headers = array(
            'LOCATION' 					    => 'Location',
            'DATE' 					        => 'Date',
            'LOCATION_NAME' 				=> 'Location Name',
            'QTY'                           => 'Qty',
            'CHARGED_TO' 	                => 'Charged To',
            'TRANSACTION_TYPE'              => 'Type'
        );

        $ssnId          = ($processState->item->isTool()) ? $processState->item->id :
                                                                $this->minder->getSsnIdByProdId($processState->item->id);
        $historyList    = $this->minder->getHistoryList($ssnId);
        $historyData    = $historyList['data'];

        $data           = array();
        $tool           = array();

        foreach($historyData as $history) {
            $tool['LOCATION']           = $history->items['WH_ID'] . ' ' . $history->items['LOCN_ID'];
            $tool['DATE']               = $history->items['TRN_DATE'];

            if (!empty($history->items['LOCN_ID'])) {
                $location                   = $this->minder->getLocn($history->items['LOCN_ID'], $history->items['WH_ID']);
                $tool['LOCATION_NAME']      = empty($location) ? '' : $location['LOCN_NAME'];
            } else {
                $tool['LOCATION_NAME']      = '';
            }
            $tool['QTY']                = $history->items['QTY'];
            $tool['CHARGED_TO']         = $history->items['CHARGED_TO'];
            $tool['TRANSACTION_TYPE']   = $history->items['TRN_TYPE'];
            $tool['ID']                 = $history->id;

            $data[] = $tool;
        }

        $this->_postProcessNavigation($data);

        $this->session->historyData = $historyData;

        $data = array_slice($data, $this->view->navigation['show_by'] * $this->view->navigation['pageselector'], $this->view->navigation['show_by']);

        $conditions = $this->_getConditions();
        $this->view->data           = $this->session->data = $data;
        $this->view->total          = count($historyData);
        $this->view->conditions     = $conditions;
        $this->view->totalSelect    = count($conditions);
    }

    public function historyExportAction()
    {
        $action = $this->getRequest()->getParam('report');
        $ids    = $this->_getConditions('view-history');

        // print only selected data rows
        $report_data = array();
        foreach($ids as $id) {
            foreach($this->session->data as $row) {
                if($row['ID'] == $id) {
                    array_push($report_data, $row);
                }
            }
        }
        $this->view->data = $report_data;

        // tools without QTY field
        $this->view->headers = ($this->session->itemType == "TOOL") ?
            array(
                'Location' 					    => 'LOCATION',
                'Date' 					        => 'DATE',
                'Location Name' 				=> 'LOCATION_NAME',
                'Charged to' 	                => 'CHARGED_TO',
                'Type'                          => 'TRANSACTION_TYPE'
            ) :
            array(
                'Location' 					    => 'LOCATION',
                'Date' 					        => 'DATE',
                'Location Name' 				=> 'LOCATION_NAME',
                'Qty'                           => 'QTY',
                'Charged to' 	                => 'CHARGED_TO',
                'Type'                          => 'TRANSACTION_TYPE'
            );

        $this->_processReportTo($action);
    }

    public function historyMarkAjaxAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $jsonObject = new stdClass();

        $historyIds = isset($this->session->historyData) ? $this->session->historyData : array();
        $id = $this->getRequest()->getParam('id', null);
        $selected = $this->getRequest()->getParam('selected', 'true');

        $id = ($id == 'select_all') ? 'select_complete' : $id;
        $conditions = $this->_markSelected($historyIds, $id, $id, $selected, 'view-history');

        $jsonObject->selected = $conditions;
        $jsonObject->totalRows = count($historyIds);
        $jsonObject->selectedRows = count($conditions);

        echo json_encode($jsonObject);
    }

    public function disposeToolAction()
    {
        $toolNo     = $this->getRequest()->getParam('toolNo');

        if (empty($toolNo)) {
            $this->view->toolError = 'Scan TOOL ID at first';
            return;
        }

        $toolDesc   = $this->minder->getProductDescById($toolNo);
        $this->view->toolInfo = $toolNo . ' ' . $toolDesc;

    }

    public function importAuditFileAction() {
        $this->_viewRenderer()->setNoRender(true);
        $result = array();

        $auditFile = new Zend_Form_Element_File('auditFile', array('required' => true));
        $process = $this->_getProcess(Minder_OtcProcess::AUDIT);
        $process->resetProcess();
        $processedItems = array();

        if ($auditFile->receive()) {

            $reader = new Minder_Reader_Csv($auditFile->getFileName(null, true));
            $barcodeDescriptors = $this->_barcodeParcer()->buildBarcodeDescriptors(array('LOCATION', 'BARCODE'));

            foreach ($reader as $dataRow) {
                $barcode = $this->_barcodeParcer()->doParseBarcode($dataRow[0], $barcodeDescriptors);

                if ($barcode['VALID']) {
                    switch (strtoupper($barcode['PARAM_NAME'])) {
                        case 'LOCATION':
                            $processState = $process->setLocation($barcode['PARSED_VALUE'], 'S');
                            $processedItems = array_merge($processedItems, $processState->save);

                            if (!$processState->auditLocation->existed) {
                                $result['error'] = $processState->auditLocation->description;
                                break 2;
                            } elseif (!$processState->auditLocation->opened) {
                                $result['error'] = $processState->transactionMessage;
                                break 2;
                            }
                            break;
                        case 'BARCODE':
                        case 'SSN_CODE':
                        case 'NON_UNIQUE_SSN_CODE':
                        case 'ALTBARCODE':
                            $processState = $process->setToolBarcode($barcode['PARSED_VALUE'], 'S');
                            $processedItems = array_merge($processedItems, $processState->save);

                            if (!$processState->item->existed) {
                                $result['error'] = $processState->item->description;
                                break 2;
                            } elseif (!$processState->committed) {
                                $result['error'] = $processState->transactionMessage;
                                break 2;
                            }

                            break;
                        case 'ALT_BARCODE':
                            $processState = $process->setToolAltBarcode($barcode['PARSED_VALUE'], 'S');
                            $processedItems = array_merge($processedItems, $processState->save);

                            if (!$processState->item->existed) {
                                $result['error'] = $processState->item->description;
                                break 2;
                            } elseif (!$processState->committed) {
                                $result['error'] = $processState->transactionMessage;
                                break 2;
                            }

                            break;
                    }

                } else {
                    $result['error'] = '"' . $dataRow[0] . '" is not valid barcode.';
                    break;
                }
            }
        } else {
            $result['error'] = 'Error uploading file';
        }

        $result['processState'] = $process->getState();
        $result['processState']->save = $processedItems;

        echo json_encode($result);
    }

    public function saveDisposalAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $processState = $this->_getProcess(Minder_OtcProcess::ISSUES)->getState();

        $issnId = $processState->item->id;
        $date   = $this->getRequest()->getParam('date');
        $cost   = $this->getRequest()->getParam('cost');
        $price  = $this->getRequest()->getParam('price');
        $notes  = $this->getRequest()->getParam('notes');

        $source   = 'SSKKSSKSS';
        $TRILwhId = 'XX';
        $TROLwhId = $this->minder->whId;

        $issnInfo = $this->minder->getIssn($issnId);

        $response = new Minder_JSResponse();

        // get all ISSN

        $sql = "SELECT * FROM ISSN WHERE SSN_ID = ?";
        $issnList = $this->minder->fetchAllAssoc($sql, $issnId);

        foreach($issnList as $node) {

            // try perform disposal transaction

            try {
                $transaction            =   new Transaction_TROLA();
                $transaction->objectId  =   $node['SSN_ID'];
                $transaction->whId      =   $TROLwhId;
                $transaction->locnId    =   $node['LOCN_ID'];
                $transaction->reference =   'ISSN Disposed';

                if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) {
                    throw new Exception('Error executing transaction TROL A: ' . $this->minder->lastError);
                } else {

                    $transaction            =   new Transaction_TRILA();
                    $transaction->objectId  =   $node['SSN_ID'];
                    $transaction->whId      =   $TRILwhId;
                    $transaction->locnId    =   $node['LOCN_ID'];
                    $transaction->reference =   'ISSN Disposed';
                    if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) {
                        throw new Exception('Error executing transaction TRIL A: ' . $this->minder->lastError);
                    }
                }
                $response->messages[] = 'Dispose operation : ' . $result;

                $this->minder->updateIssn($node['SSN_ID'], 'ISSN_STATUS', 'XX');
            } catch (Exception $e) {
                $this->addError($e->getMessage());
                $response->errors[] = $this->minder->lastError;

                echo json_encode($response);
                return;
            }
        }

        // save success messages

        // update ssn with data from form
        $updateList = array();
        $updateList['DISPOSAL_DATE']    = $date;
        $updateList['DISPOSAL_COST']    = $cost;
        $updateList['DISPOSAL_PRICE']   = $price;
        $updateList['DISPOSAL_NOTES']   = $notes;
        $updateList['DISPOSED']         = 'T';
        $updateList['STATUS_SSN']       = 'XX';

        $default_result = true;
        foreach($updateList as $key => $val) {
            $currentResult = $this->minder->updateSsn($issnId, $key, $val);
            $default_result        = $default_result && $currentResult;
            if (false == $currentResult) {
                $response->errors[] = $this->minder->lastError;
            }
        }

        echo json_encode($response);

    }

    public function saveImageAction() {
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();

        $response->success = true;

        $front = $this->getRequest()->getParam('front');
        list(,$data) = explode(',', $front);

        file_put_contents('/tmp/image.png', base64_decode($data));

        echo json_encode($response);
    }

    protected function _processReportTo($action = null)
    {
        switch ($action) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->view->filePointer = fopen("php://temp/", 'r+');
                $this->render('report-csv');
                rewind($this->view->filePointer);
                echo stream_get_contents($this->view->filePointer);
                fclose($this->view->filePointer);
                return true;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('report-xls-header');
                $this->render('report-xls');
                $this->render('report-xls-footer');
                return true;
        }
    }

    private function _getDefaultBorrowerLabelQty()
    {
        return $this->minder->getDefaultBorrowerLabelQty();
    }

    /**
     * @return Minder_Controller_Action_Helper_CostCentre
     */
    protected function _costCentreHelper() {
        return $this->getHelper('CostCentre');
    }

    /**
     * @return Minder_Controller_Action_Helper_BarcodeParser
     */
    protected function _barcodeParcer() {
        return $this->getHelper('BarcodeParser');
    }
}
