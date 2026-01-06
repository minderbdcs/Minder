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
 * Warehouse_ProductsController
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
class Warehouse_ProductsController extends Minder_Controller_Action
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
        $this->view->pageTitle = 'Search Product';
		$this->view->conditions = array();
        
        // get param from ORDER screen(ADD PRODUCT or ADD Non-Product)
        $productOptions = &$this->session->params['product_options'];
       
        if(isset($productOptions)){
            $this->session->from['pick_order']  =   $productOptions->pickOrder; 
            
            $this->getRequest()->setParam('from', $productOptions->from);   
            $this->getRequest()->setParam('company_id', $productOptions->company); 
            
            $this->session->params[$this->_controller][$this->_action]['required_qty1'] = $productOptions->qty; 
            
            if($productOptions->thisIs == 'product_code'){
                $this->getRequest()->setParam('prod_id', $productOptions->productName);
                $this->getRequest()->setParam('short_desc', '');    
            } elseif($productOptions->thisIs == 'product_description'){
                 $this->getRequest()->setParam('short_desc', stripslashes($productOptions->productName));
                 $this->getRequest()->setParam('prod_id', '');        
            }
        }
       
        $from = $this->_getParam('from');
		// Prevent direct access.
		if (($from == 'pick_order' || $from == 'transfer-order') && !isset($this->session->from['pick_order'])) {
			$this->_redirect();
		}
		$pickOrderNo = $this->session->from['pick_order'];
		switch ($from) {
			case 'pick-order':
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
                $this->session->returnOrder = 'pick-order';
           	break;

			default:
				unset($this->session->from);

				$from = null;
		}

		//-- preprocess input of navigation values
		if (isset($this->session->navigation['products']['index'])) {
			foreach ($this->session->navigation['products']['index'] as $key => $val) {
				if (null != $this->getRequest()->getParam($key)) {
					$this->session->navigation['products']['index'][$key] = (int)$this->getRequest()->getParam($key);
				}
			}
		} else {
			$this->session->navigation['products']['index']['show_by1']      = 15;
			$this->session->navigation['products']['index']['pageselector1'] = 0;
			$this->session->navigation['products']['index']['show_by2']      = 15;
			$this->session->navigation['products']['index']['pageselector2'] = 0;
			$this->session->navigation['products']['index']['show_by3']      = 15;
			$this->session->navigation['products']['index']['pageselector3'] = 0;
		}

		$this->view->navigation = $this->session->navigation['products']['index'];
		//-- end process input navigation values
		//-- setup conditions

		$isAllowed =    $this->_getAllowed();
        
	    if (!isset($this->session->activeTab)) {
			$this->session->activeTab = '1';
		}
		if (($activeTab = $this->_getParam('active_tab'))) {
			$this->session->activeTab = $activeTab;
		}
		unset($activeTab);
		

		$conditions = $this->_setupConditions(null, $isAllowed);
        $clause     = $this->_makeClause($conditions, $isAllowed);
        
        try{
            
            $parserObj =    new Parser($conditions['long_desc'], 'LONG_DESC', '', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';
            
            $parserObj =    new Parser($conditions['short_desc'], 'SHORT_DESC', '', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';    
      
        } catch(Exception $ex) {
            $this->addError($ex->getMessage());
        }
        
        
        
        if(!isset($this->session->CalcConditions)) {
            $this->session->CalcConditions = array();
        }
        
        $checkedId = array();
        foreach($this->session->CalcConditions as $key => $value) {
            if($key == $value) {
                $checkedId[$key]    =   $value;
            }
        }
        
        $this->session->productConditions  = array_merge($conditions, $checkedId);
        $this->view->conditions            = $this->session->productConditions;    
        $this->view->activeTab             = $this->session->activeTab;
     	//-- End setup conditions

		$this->_setupHeaders();
		
        // get param from ORDER screen(ADD PRODUCT or ADD Non-Product)
        $formAction = strtoupper($this->getRequest()->getPost('action'));
        if((!isset($productOptions) && !empty($formAction)) || (isset($productOptions) && $productOptions->makeSearch)){
            $this->view->tabShow    =   true;
            if(!empty($from)){
                $statuses               =   eregi_replace('([a-z]+)', "'\\1'", trim($this->minder->defaultControlValues['PICK_IMPORT_SSN_STATUS'], ','));
                $clause                 =   array_merge($clause, array(sprintf('ISSN_STATUS IN(%s) AND ', $statuses) => ''));
            }
            $this->view->lines1     =   $this->minder->getProductLine1s($clause);
        } else {
            $this->view->tabShow        =   false;
            $this->view->lines1         =   array();
            $productOptions->makeSearch =   true;
        }
        
        //$this->view->numRecords  = count($this->view->lines);
		$this->view->numRecords1 = count($this->view->lines1);
		$this->view->numRecords2 = count($this->view->lines2);
		$this->view->numRecords3 = count($this->view->lines3);
		switch ($this->session->activeTab) {
			case '3':
				$lines   = $this->view->lines3;
				break;
			case '2':
				$lines   = $this->view->lines2;
				break;
			case '1':
			default:
				$lines   = $this->view->lines1;
				break;
		}

		$data = array();
		foreach ($lines as $line) {
			if (array_key_exists($line->id, $this->session->productConditions) && 'off' != $this->session->productConditions[$line->id] && in_array($line->id, $this->session->productConditions)) {
				$data[$line->id] = $line->items;
			}
		}
		$this->view->data = $data;
		$property = 'headers' . $this->session->activeTab;
		$this->view->headers = $this->view->$property;
		switch ($formAction) {
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

			case 'PRODUCTS':
				$this->session->from['product'] = 1;
				$this->_redirect('warehouse/products/index' . (isset($from) ? '/from/' . $from : '') . ($this->_getParam('without') ? '/without/1' : ''));
				break;

			case 'NON-PRODUCTS':
				$this->session->from['product'] = 0;
				$this->_redirect('warehouse/ssn/index' . (isset($from) ? '/from/' . $from : '') . ($this->_getParam('without') ? '/without/1' : ''));
				break;

			case 'ADD':
				$requiredQty = $this->view->requiredQty = intval($this->_getParam('required_qty' . $this->session->activeTab));
                
                if ($requiredQty > 0) {
					if ($from != 'fso') {
						// ------------------------
						// --- ADD PRODUCT LINE ---
						// ------------------------
						$this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('required_qty' => $requiredQty, 'pick_order' => $pickOrderNo, 'case' => 1), $data);
						$this->session->params[$this->_controller][$this->_action] = array('required_qty1' => intval($this->_getParam('required_qty1')), 'required_qty2' => intval($this->_getParam('required_qty2')), 'required_qty3' => intval($this->_getParam('required_qty3')));
						unset($this->session->from);
						$this->_forward('add-issn-items', $this->returnOrder, 'default', array('redirect' => $this->returnOrder));
						return; // for _forward().
					} else {
						// ------------------------
						// --- Fast Sales Order ---
						// ------------------------
						$this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('required_qty' => $requiredQty, 'case' => $this->session->activeTab), $data);
						$this->session->params[$this->_controller][$this->_action] = array('required_qty1' => intval($this->_getParam('required_qty1')), 'required_qty2' => intval($this->_getParam('required_qty2')), 'required_qty3' => intval($this->_getParam('required_qty3')));
						unset($this->session->from);
						$this->_redirect('transfer-order/new/pick_order_type/TO');
				
					}
				} else {
					$this->_helper->flashMessenger->addMessage('Empty field: Required Qty for Order.');
				}
				$this->_redirect('warehouse/products/index'
					. (isset($from) ? '/from/' . $from : ''));
				break;

			case 'ADD & CONTINUE':
				$requiredQty = $this->view->requiredQty = intval($this->_getParam('required_qty' . $this->session->activeTab));
				if ($requiredQty > 0) {
					if ($from != 'fso') {
						// ------------------------
						// --- ADD PRODUCT LINE ---
						// ------------------------
						$this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('required_qty' => $requiredQty, 'pick_order' => $this->session->from['pick_order']), $data);
						$this->session->params[$this->_controller][$this->_action] = array('required_qty1' => intval($this->_getParam('required_qty1')), 'required_qty2' => intval($this->_getParam('required_qty2')), 'required_qty3' => intval($this->_getParam('required_qty3')));
						// Insert Pick Items and redirect on this page.
						$this->_forward('add-issn-items', $this->returnOrder , 'default', array('redirect' => 'warehouse/products/index/from/' . $this->returnOrder));
						return; // for _forward().
					} else {
						// ------------------------
						// --- Fast Sales Order ---
						// ------------------------
						if (isset($this->session->params[$this->returnOrder]['index']['pick_items'])) {
							$data = array_merge($this->session->params[$this->returnOrder]['index']['pick_items'], $data);
						}
						$this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('required_qty' => $requiredQty), $data);
						$this->session->params[$this->_controller][$this->_action] = array('required_qty1' => intval($this->_getParam('required_qty1')), 'required_qty2' => intval($this->_getParam('required_qty2')), 'required_qty3' => intval($this->_getParam('required_qty3')));
				   }
				} else {
					$this->_helper->flashMessenger->addMessage('Empty field: Required Qty for Order.');
				}
				$this->_redirect('warehouse/products/index'
					. (isset($from) ? '/from/' . $from : ''));
				break;

			case 'CANCEL ADD':
				  unset($this->session->from);
				  $this->_redirect($this->returnOrder . '/index');
			      break;
		}

		$statusInvolved = array();
		$tempArray = explode(',', $this->minder->getPickImportSsnStatus());
		foreach ($tempArray as $val) {
			if ($val != '') {
				$statusInvolved[] = $val;
			}
		}

		$this->view->stockList = minder_array_merge(array('' => ''), $this->minder->getStockList());
		$this->view->stock     = isset($_POST['stock']) ? $_POST['stock'] : ' ';

		$this->view->companyList = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
		$this->view->company     = isset($_POST['company_id']) ? $_POST['company_id'] : ' ';

		$this->view->personList =  minder_array_merge(array('' => '') , array_keys($this->minder->getPersonList(array('CO', 'CS'))));
		$this->view->return     =  isset($_POST['return_id']) ? $_POST['return_id'] : ' ';
		
        //- start precalc additional info
		$this->view->availableQty   = 0;
        $this->view->onAvailableQty = 0;
		$this->view->onHandQty      = 0;
        try{
            $productsStockStatus      = $this->minder->getProductStockStatus($clause);
            $this->view->onHandQty    = $productsStockStatus->onHandQty;
            $this->view->availableQty = $productsStockStatus->totalQty;
        } catch (Exception $e) {
            $this->addWarning($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

		if (isset($this->session->qtyProducts)) {
			if (array_key_exists('#tab1', $this->session->qtyProducts)) {
				$this->view->qtyProducts1 = $this->session->qtyProducts['#tab1'];
			} else {
				$this->view->qtyProducts1 = 0;
			}
		}

		$this->view->requiredQty1 = isset($this->session->params[$this->_controller][$this->_action]['required_qty1'])
			? $this->session->params[$this->_controller][$this->_action]['required_qty1']
			: 0;
        $requiredQty = $this->getRequest()->getParam('required_qty1');
        if (!empty($requiredQty)) {
			$this->view->requiredQty1 = $requiredQty;
		} elseif(!isset($this->session->params[$this->_controller][$this->_action]['required_qty1'])) {
            $this->view->requiredQty1 = 0;    
        }
		
		//- end precalc
		$this->view->searchFields = array('prod_id'      => 'Product ID',
										  'ssn_type'     => 'SSN Type I',
										  'options'      => 'Stock Flag'
										  );


		  //                            'ship_container_type' => 'Shipping Container'
										  //'alternate_id_srch' => 'Alternate ID'
		// added toooltips to dropdown
		$this->view->issnDescription = $this->minder->getIssnStatusList();

		$this->view->productIdList   = minder_array_merge(array('' => ''), $this->minder->getProductList());
		$this->view->alternateIdList = minder_array_merge(array('' => ''), $this->minder->getAlternateProductList());
		$this->view->companyIdList   = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
		$this->view->stockList       = minder_array_merge(array('' => ''), $this->minder->getStockList());
		$this->view->togList         = minder_array_merge(array('' => ''), $this->minder->getTurnoverGroupList());
		$this->view->statusList      = minder_array_merge(array('' => ''), $this->minder->getIssnStatusList());
		$this->view->tempZoneList    = minder_array_merge(array('' => ''), $this->minder->getTemperatureZoneList());

		$tempArray = $this->minder->getContainerTypeList();
		$tempArray = minder_array_merge(array('' => ''), $tempArray);
		$this->view->containerTypeList =  $tempArray;


		$tempArray = $this->minder->getPersonList(array('CO', 'CS', 'IS', 'RP'));
		if (count($tempArray) > 0) {
			$this->view->supplierIdList = minder_array_merge(array('' => ''), array_combine(array_keys($tempArray), array_keys($tempArray)));
		} else {
			$this->view->supplierIdList = array('' => '');
		}

		$this->view->warehouses = minder_array_merge(array('' => ''), $this->minder->getWarehouseList());

		//-- post process navigation
		$this->view->numRecords1 = count($this->view->lines1);
		if (($this->view->navigation['show_by1'] * ($this->view->navigation['pageselector1'] + 1)) > $this->view->numRecords1) {
			$this->view->navigation['pageselector1'] = $this->session->navigation['products']['index']['pageselector1']
													 = (int)floor($this->view->numRecords1 / $this->view->navigation['show_by1']);
			$this->view->maxno1 = $this->view->numRecords1 - ($this->view->navigation['show_by1'] * $this->view->navigation['pageselector1']);
		} else {
			$this->view->maxno1 = $this->view->navigation['show_by1'];
		}
		//-- end post process
		$this->view->pages1 = array();
		for ($i = 1; $i<=ceil($this->view->numRecords1/$this->view->navigation['show_by1']); $i++) {
			$this->view->pages1[] = $i;
		}
		$this->view->lines1 = array_slice($this->view->lines1,
										  $this->view->navigation['show_by1'] * $this->view->navigation['pageselector1'],
										  $this->view->maxno1);

		//-- post process navigation

		$this->view->numRecords2 = count($this->view->lines2);
		if (($this->view->navigation['show_by2'] * ($this->view->navigation['pageselector2'] + 1)) > $this->view->numRecords2) {
			$this->view->navigation['pageselector2'] = $this->session->navigation['products']['index']['pageselector2']
													 = (int)floor($this->view->numRecords2 / $this->view->navigation['show_by2']);
			$this->view->maxno2 = $this->view->numRecords2 - ($this->view->navigation['show_by2'] * $this->view->navigation['pageselector2']);
		} else {
			$this->view->maxno2 = $this->view->navigation['show_by2'];
		}
		//-- end post process
		$this->view->pages2 = array();
		for ($i = 1; $i<=ceil($this->view->numRecords2/$this->view->navigation['show_by2']); $i++) {
			$this->view->pages2[] = $i;
		}
		
		$this->view->lines2 = array();
		$this->view->lines3 = array();
	
		$this->view->lines2 = array_slice($this->view->lines2,
										  $this->view->navigation['show_by2'] * $this->view->navigation['pageselector2'],
										  $this->view->maxno2);

		//-- post process navigation
		$this->view->numRecords3 = count($this->view->lines3);

		if (($this->view->navigation['show_by3'] * ($this->view->navigation['pageselector3'] + 1)) > $this->view->numRecords3) {
			$this->view->navigation['pageselector3'] = $this->session->navigation['products']['index']['pageselector3']
													 = (int)floor($this->view->numRecords3 / $this->view->navigation['show_by3']);
			$this->view->maxno3 = $this->view->numRecords3 - ($this->view->navigation['show_by3'] * $this->view->navigation['pageselector3']);
		} else {
			$this->view->maxno3 = $this->view->navigation['show_by3'];
		}
		//-- end post process

		$this->view->pages3 = array();
		for ($i = 1; $i<=ceil($this->view->numRecords3/$this->view->navigation['show_by3']); $i++) {
			$this->view->pages3[] = $i;
		}
		$this->view->lines3 = array_slice($this->view->lines3,
										  $this->view->navigation['show_by3'] * $this->view->navigation['pageselector3'],
										  $this->view->maxno3);
    
        $productOptions = null;
        
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
			case 'prod_id':
				$tdata = $this->minder->getProductList($param);
				break;
			case 'company_id':
				$tdata = $this->minder->getCompanyList($param);
				break;
			case 'alternate_id':
				$tdata = $this->minder->getAlternateProductList($param);
				break;
				case 'short_desc':
				$tdata = $this->minder->getProductShortDescriptionList($param);
				break;
			case 'long_desc':
				$tdata = $this->minder->getProductLongDescriptionList($param);
				break;
			case 'variety':
				$tdata = $this->minder->getAutocompleteVarietyList($param);
				break;
			case 'brand':
				$tdata = $this->minder->getBrandList(array('CODE LIKE ? ' => $param));
				break;
			case 'grn':
				$tdata = $this->minder->getGrnList($param);
				break;
			case 'ship_container_type':
				break;

			case 'ssn_type':
				$tdata = $this->minder->getSsnTypeListFromSsnType($param);
				break;
			case 'company_id':
				if($this->minder->limitCompany == 'all') {
				   $tdata = $this->minder->getCompanyList($param);
				   break;
				}
				$tdata = $this->minder->getCompanyList($this->minder->limitCompany);
				break;
			case 'return_id':
				$tdata = $this->minder->getPersonList(array('CO', 'CS'), $param);
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
		//$param = $this->getRequest()->getParam('q');

		$src = $this->getRequest()->getParam('field');
		switch ($src) {
			case 'ssn_type':
				$tdata = $this->minder->getSsnTypeListFromSsnType($param);
				break;
			case 'generic':
				$tdata = $this->minder->getVarietyList($this->getRequest()->getParam('ssn_type'));
				break;
			case 'ssn_sub_type':
				$tdata = $this->minder->getSsnSubTypeList(array('GENERIC ='  => $this->getRequest()->getParam('generic'),
																'SSN_TYPE =' => $this->getRequest()->getParam('ssn_type')
																));
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
		$calculatedValue  = 0; // calculated value
		$count            = 0; // number of elements used for calculation
        
		$field     = strtoupper($this->getRequest()->getParam('field'));
		$method    = $this->getRequest()->getParam('method');
		$activeTab = $this->getRequest()->getParam('activeTab');
		$id        = $this->getRequest()->getParam('id');
        $value     = $this->getRequest()->getParam('value');
		$from      = $this->getRequest()->getParam('from');
		//-- setup conditions

		$isAllowed = $this->_getAllowed();

		if(!empty($this->session->productConditions)) {
            $conditions =   $this->session->productConditions;
        } else {
            $conditions =   array();
        }
   
        $clause = array();
		
        $clause = $this->_makeClause($conditions, $isAllowed);
		//-- end conversion

		// parse fields LONG_DESC & SHORT_DESC
		try{
            
            $parserObj =    new Parser($conditions['long_desc'], 'LONG_DESC', '', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';
            
            $parserObj =    new Parser($conditions['short_desc'], 'SHORT_DESC', '', 'AND');
            $outStr    =    $parserObj->parse();  

            $clause[$outStr] = '';    
        
        } catch(Exception $ex) {}

        if(!empty($from)){
            $statuses               =   eregi_replace('([a-z]+)', "'\\1'", trim($this->minder->defaultControlValues['PICK_IMPORT_SSN_STATUS'], ','));
            $clause                 =   array_merge($clause, array(sprintf('ISSN_STATUS IN(%s) AND ', $statuses) => ''));
        }
        
        //-- get appropriate lines
		switch ($activeTab) {
			case '#tab1':
				$lines = $this->minder->getProductLine1s($clause);
				$tabid = '1';
				break;
	    }
        
        $numRecords = count($lines);

		//-- mark all rows in whole table as selected
		if ('select_complete' == $id) {
			if ('true' == $method) {
				foreach ($lines as $line) {
					$conditions[$line->id] = $line->id;
				}
			} elseif ('false' == $method) {
				foreach ($lines as $line) {
					$conditions[$line->id] = '';
				}
			}
		} elseif ('select_all' == $id) {
			if (($this->session->navigation['products']['index']['show_by' . $tabid] * $this->session->navigation['products']['index']['pageselector' . $tabid]) > $numRecords) {
				$maxno = $numRecords - ($this->session->navigation['products']['index']['show_by' . $tabid] * $this->session->navigation['products']['index']['pageselector' . $tabid]);
			} else {
				$maxno = $this->session->navigation['products']['index']['show_by' . $tabid];
			}
			if ('true' == $method) {
				foreach(array_slice($lines,
									$this->session->navigation['products']['index']['show_by' . $tabid] * $this->session->navigation['products']['index']['pageselector' . $tabid] ,
									$maxno) as $line) {
					$conditions[$line->id] = $line->id;
				}
			} elseif ('false' == $method) {
				foreach(array_slice($lines,
									$this->session->navigation['products']['index']['show_by' . $tabid] * $this->session->navigation['products']['index']['pageselector' . $tabid] ,
									$maxno) as $line) {
					$conditions[$line->id] = '';
				}
			}
		} else {
            if ('true' == $method) {
				$conditions[$id] = $value;
                
			} elseif ('false' == $method) {
				$conditions[$id] = '';
			}
		}
            
        
        $this->session->productConditions = $conditions;
        
        switch ($field) {
			case 'QTY':
				$recordNum = count($lines);
				for ($i = 0; $i < $recordNum; $i++) {
					if (false !== array_search($lines[$i]->id, $conditions, true )) {
						$calculatedValue += $lines[$i]->items[$field];
						$count++;
					}
				}
				if (isset($this->session->qtyProducts)) {
					$temp = $this->session->qtyProducts;
				$temp[$activeTab] = $calculatedValue;
					$this->session->qtyProducts = $temp;
				} else {
					$this->session->qtyProducts = array($activeTab => $calculatedValue);
				}

				break;
			default:
			break;
		}

		$data                           = array();
		$data['selected_num']           = $count;
		$data['total_qty']              = $calculatedValue;
		$this->view->data               = $data;
        $this->session->CalcConditions  = $conditions;
        
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
		if (!isset($this->session->headers[$this->_controller][$this->_action]['table1'])) {
			$this->session->headers[$this->_controller][$this->_action]['table1'] =
			$this->view->headers1 = array('PROD_ID'     => 'Product ID',
										  'ALTERNATE_ID'=> 'Alternate ID',
										  'DESCRIPTION' => 'Description',
										  'SALE_PRICE'  => 'Price $',
										  'QTY'         => 'Qty',
										  'ISSN_STATUS' => 'Status',
										  'WH_ID'       => 'WH ID'
										  );
		

		} else {
			$this->view->headers1 = $this->session->headers[$this->_controller][$this->_action]['table1'];
		}

		if (!isset($this->session->headers[$this->_controller][$this->_action]['table2'])) {
			$this->session->headers[$this->_controller][$this->_action]['table2'] =
				$this->view->headers2 = array('CREATE_DATE' => 'Received',
											  'PROD_ID'     => 'Product ID',
											  'DESCRIPTION' => 'SSN Description',
											  'QTY'         => 'Qty',
											  'WH_ID'       => 'WH',
                                              'ISSN_STATUS' => 'Status',
											  'COMPANY_ID'  => 'Owner ID',
											  'RETURN_ID'   => 'Supplier ID');
		} else {
			$this->view->headers2 = $this->session->headers[$this->_controller][$this->_action]['table2'];
		}

		if (!isset($this->session->headers[$this->_controller][$this->_action]['table3'])) {
			$this->session->headers[$this->_controller][$this->_action]['table3'] =
				$this->view->headers3 = array('CREATE_DATE' => 'Received',
											  'RETURN_ID'   => 'Supplier ID',
                                              'ISSN_STATUS' => 'Status',
											  'QTY'         => 'Qty',
											  'PROD_ID'     => 'Product ID',
											  'DESCRIPTION' => 'SSN Description',
											  'WH_ID'       => 'WH',
											  'SSN_ID'      => 'ISSN',
											  'COMPANY_ID'  => 'Company ID');
		} else {
			$this->view->headers3 = $this->session->headers[$this->_controller][$this->_action]['table3'];
		}
		return true;
	}

	/**
	 * Return list of allowed fields
	 *
	 * @return array
	 */
	protected function _getAllowed()
	{
		$isAllowed = array(
                           'prod_id'        => "PROD_ID LIKE ? AND ",
						   'alternate_id'   => "ALTERNATE_ID LIKE ? AND ",
						   'ssn_type'       => "SSN_TYPE LIKE ? AND ",
						   'stock'          => "STOCK = ? AND ",
						   'company_id'     => "COMPANY_ID = ? AND ",
						   'return_id'      => "RETURN_ID = ? AND ",
						   'short_desc'     => "",
						   'long_desc'      => ""

						  );

		return $isAllowed;
	}
}
