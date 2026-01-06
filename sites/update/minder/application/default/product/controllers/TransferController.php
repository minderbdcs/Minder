<?php
/**
 * TransferController
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class TransferController extends Minder_Controller_Action
{
	/**
	* @desc 5 records per page
	*/
	protected $_showBy = 5;
	
	public function init()
	{
		parent::init();
	   
		$this->_setupHeaders();
        $this->_setupAllowed();
        
		
		if(isset($this->session->navigation)) {
			$this->view->navigation = $this->session->navigation;
		} else {
			 $navigation[$this->_controller][$this->_action]['pageselector']    =   0;
			 $navigation[$this->_controller][$this->_action]['show_by']         =   $this->_showBy;
			 
			 $this->session->navigation = $this->view->navigation = $navigation;
		}
		// Do not load ISSN list into div 
		if(!isset($this->session->displayList)) {
			$this->session->displayList = array('displayInto'   => false,
												'displayWhole'  => false,
												'displayMove'   => false);
		}
       
        if(!isset($this->session->transactionStatusList)) {
            $this->view->transactionStatusList = $this->session->transactionStatusList = array('into'    =>  array('CODE'       => 'TRIL',
                                                                                                                   'RESULT'     => true,
                                                                                                                   'LOCN_ID'    => '',
                                                                                                                   'SSN_ID'     => '',
                                                                                                                   'WH_ID'      => ''),
                                                                                               
                                                                                               'whole'   =>  array('CODE'       => 'TRLI',
                                                                                                                   'RESULT'     => true,
                                                                                                                   'LOCN_ID'    => '',
                                                                                                                   'WH_ID'      => '',
                                                                                                                   'LOCN_ID2'   => '',
                                                                                                                   'WH_ID2'     => ''));
        } else {
            $this->view->transactionStatusList = $this->session->transactionStatusList; 
        }
        $this->view->action = $this->_action;
	}
	
	public function indexAction()
	{
		   
	}
	
	public function intoAction() {
	
		$this->view->title      = "Place ISSN Into";
		$this->view->locn_id    =  isset($_POST['hid_locn_id']) ? $_POST['hid_locn_id'] : '';
		$this->view->ssnFilter  = $this->minder->getValidationParams('BARCODE', true);
		$this->view->locFilter  = $this->minder->getValidationParams('LOCATION', true);

        try {
            $this->view->ssnBarcodeDescriptor      = $this->view->SymbologyPrefixDescriptor('BARCODE');
            $this->view->locationBarcodeDescriptor = $this->view->SymbologyPrefixDescriptor('LOCATION');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

    	$action = strtoupper($this->getRequest()->getPost('action'));
		
        switch($action) {
			case 'UPDATE':
							$params['LOCATION'] = trim($this->getRequest()->getPost('hid_locn_id'));  
							$params['BARCODE']  = trim($this->getRequest()->getPost('hid_ssn_id'));
                        
                            $params = $this->_explodParams($params);
							
                            if(!empty($params['BARCODE']['FIELD']) && !empty($params['LOCATION']['FIELD'])) {
								$warehouseList          = $this->minder->getWarehouseList();
								$whId                   = substr($params['LOCATION']['FIELD'], 0, 2);
								$locnId                 = substr($params['LOCATION']['FIELD'], 2, strlen($params['LOCATION']['FIELD']));
                                $ssnId                  = $params['BARCODE']['FIELD'];
                                
                                if(!in_array($whId, array_keys($warehouseList))) {
									$this->addError('Warehouse not found, please enter.');
									$this->view->locn_id     = '';
                                    $this->view->displayInto = true;
									return;    
								}
                                
                                $clause         =  array('SSN_ID = ? ' => $ssnId);
                                $data           =  $this->minder->getClearIssns($clause);
								if(!$data) {
                                    $this->addError('SSN: ' . $ssnId . ' not found.');
                                    $this->view->displayInto = true;
                                    return;    
                                }
                                $foundIssn      =  $data[0];
                                
                                
                                if(!empty($params['BARCODE']['PREFIX']) && !empty($params['LOCATION']['PREFIX'])) {
                                    $source = 'SSBBSSKSS';
                                } elseif(empty($params['BARCODE']['PREFIX']) && !empty($params['LOCATION']['PREFIX'])) {
                                    $source = 'SSKBSSKSS';
                                } elseif(!empty($params['BARCODE']['PREFIX']) && empty($params['LOCATION']['PREFIX'])) {
                                    $source = 'SSBKSSKSS';    
                                } else {
                                    $source = 'SSKKSSKSS';
                                }
                                
                                // execute AUOB transaction
				if($foundIssn['CURRENT_QTY'] == 1) {
					try { 
						$transaction           = new Transaction_AUOBA();
						$transaction->ssnId = $ssnId;
						$transaction->whId     = $whId;
						$transaction->locnId   = $locnId;
                        $transaction->reason = 'On-Screen Transfer to new Location';
						if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) { 
							throw new Exception($result);
						} 
						$this->addMessage($result);
					} catch (Exception $e) {
						$this->addError('Error occured while AUOB transaction ' . $e->getMessage() . ' for SSN ' . $ssnId);
					}
					// execute TRIL + TROL transaction      
				} elseif($foundIssn['CURRENT_QTY'] > 1) {
					try {
						$transaction            =   new Transaction_TROLA();
						$transaction->objectId  =   $ssnId;
						$transaction->whId      =   $foundIssn['WH_ID'];
						$transaction->locnId    =   $foundIssn['LOCN_ID'];
                                        	$transaction->reference =   'On-Screen Transfer to new Location';
						if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) { 
							throw new Exception('TROL');
						} else {
							$transaction            =   new Transaction_TRILA();
							$transaction->objectId  =   $ssnId;
							$transaction->whId      =   $whId;
							$transaction->locnId    =   $locnId;
                                            		$transaction->reference =   'On-Screen Transfer to new Location';
							if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) { 
								throw new Exception('TRIL');
                                                		$this->session->transactionStatusList['into']['RESULT']     = false;
                                                		$this->session->transactionStatusList['into']['LOCN_ID']    = $locnId;
                                                		$this->session->transactionStatusList['into']['SSN_ID']     = $ssnId;
                                                		$this->session->transactionStatusList['into']['WH_ID']      = $whId;
							}
							$this->addMessage($result);
                                            		if($this->session->transactionStatusList['into']['LOCN_ID'] === $locnId &&
                                               		   $this->session->transactionStatusList['into']['SSN_ID']  === $ssnId &&
                                                           $this->session->transactionStatusList['into']['WH_ID']   === $whId) {
                                                   		$this->session->transactionStatusList['into']['RESULT'] = true;
                                            		}
						}
					} catch (Exception $e) {
						$this->addError('Error occured while ' . $e->getMessage() . ' transaction for SSN ' . $ssnId);
					}        
				}
			    } else {
				//$this->addError('Location or ISSN field is empty.');
				$this->addMessage('Enter Empty Location or ISSN field.');
			    }
			    $this->view->displayInto = true;
                            $this->session->transactionStatusList['into']['TRIL'] = true;
				            
							break;
			case 'REPORT: TXT':
			case 'REPORT: XLS':
			case 'REPORT: XML':
			case 'REPORT: CSV':
							$conditions     = $this->_getConditions('lines', 'transfer');
							$clause         = $this->_makeClause($conditions, $this->_allowed);
							$warehouseList  = $this->minder->getWarehouseList();
							
                            $params['LOCATION'] = $clause['LOCN_ID = ? '];
                            $result             = $this->_explodParams($params);
                            $whId               = substr($result['LOCATION']['FIELD'], 0, 2);
                            $locnId             = substr($result['LOCATION']['FIELD'], 2, strlen($result['LOCATION']['FIELD']));
        
							if(in_array($whId, array_keys($warehouseList))) {
								$clause['LOCN_ID = ? '] = $locnId;
							}
							$data         = $this->minder->getIssns($clause);
							$numRecords   = count($data);
							
							$this->view->data   = array();
							$this->_setupHeaders();  
                            unset($this->view->headers['INTO_DATE']);
							for ($i = 0, $count = 0; $i < $numRecords; $i++) {
								if (array_key_exists($data[$i]->id, $conditions) && false !== array_search($data[$i]->id, $conditions, true )) {
									$this->view->data[] = $data[$i];
								}
							}
							$this->_processReportTo($action);
							break;
			default:        
		
		}
	}
	public function wholeAction() {
	
		$this->view->title          =   "Transfer Whole Location";
		$warehouseList              =   array_keys($this->minder->getWarehouseList());
        $this->view->locFilter      =   $this->minder->getValidationParams('LOCATION', true);
        $this->view->locn_id        =   isset($_POST['hid_locn_id']) ? $_POST['hid_locn_id'] : '';
	    
        try {
            $this->view->locationBarcodeDescriptor = $this->view->SymbologyPrefixDescriptor('LOCATION');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        $action = strtoupper($this->getRequest()->getPost('action'));
		switch($action) {
			case 'UPDATE':
                            
                            $params['LOCATION']  = trim($this->getRequest()->getPost('hid_locn_id'));  
                            $params              = $this->_explodParams($params);
                            
                            $params2['LOCATION'] = trim($this->getRequest()->getPost('hid_locn_id_2'));
                            $params2             = $this->_explodParams($params2);
                            
                            $params['LOCATION2'] = $params2['LOCATION'];
                            if(!empty($params['LOCATION']) && !empty($params['LOCATION2'])) {
                                
                                $warehouseList          = $this->minder->getWarehouseList();
                                $whId                   = substr($params['LOCATION']['FIELD'], 0, 2);
                                $locnId                 = substr($params['LOCATION']['FIELD'], 2, strlen($params['LOCATION']['FIELD']));
                                
                                $whId2                   = substr($params['LOCATION2']['FIELD'], 0, 2);
                                $locnId2                 = substr($params['LOCATION2']['FIELD'], 2, strlen($params['LOCATION2']['FIELD'])); 
                                
                                if(!in_array($whId, array_keys($warehouseList))) {
                                    $this->addError('Warehouse not found, please enter.');
                                    $this->view->displayWhole = true;
                                    return;    
                                }
                                
                                if(!in_array($whId2, array_keys($warehouseList))) {
                                    $this->addError('Warehouse not found, please enter.');
                                    $this->view->displayWhole = true;
                                    return;    
                                }
                            } else {
                                return;
                            }
                            
                            try {
                                    if(!empty($params['LOCATION']['PREFIX'])) {
                                        $source = 'SSSBSSKSS';
                                    } elseif(empty($params['LOCATION']['PREFIX'])) {
                                        $source = 'SSSKSSKSS';
                                    } 
                                    
                                    $transaction = new Transaction_TRLOA();
                                    $transaction->whId      =   $whId;
                                    $transaction->locnId    =   $locnId;
                                    $transaction->reference =   'On-Screen Whole Location Transfer';
                                    if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) { 
                                        throw new Exception('TROL');
                                    } else {
                                        
                                        if(!empty($params['LOCATION2']['PREFIX'])) {
                                            $source = 'SSSBSSKSS';
                                        } elseif(empty($params['LOCATION2']['PREFIX'])) {
                                            $source = 'SSSKSSKSS';
                                        }
                                        
                                        $transaction            =   new Transaction_TRLIA();
                                        $transaction->whId      =   $whId2;
                                        $transaction->locnId    =   $locnId2;
                                        $transaction->reference =   'On-Screen Whole Location Transfer';
                                        
                                        if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) { 
                                            $this->session->transactionStatusList['whole']['RESULT']    = false;
                                            $this->session->transactionStatusList['into']['LOCN_ID']    = $locnId;
                                            $this->session->transactionStatusList['into']['WH_ID']      = $whId;
                                            
                                            $this->session->transactionStatusList['into']['LOCN_ID2']   = $locnId2;
                                            $this->session->transactionStatusList['into']['WH_ID2']     = $whId2;
                                            throw new Exception('TRLI');
                                        }
                                        
                                        $this->addMessage($result);
                                        if($this->session->transactionStatusList['into']['LOCN_ID'] === $locnId  &&
                                           $this->session->transactionStatusList['into']['WH_ID']   === $whId    &&
                                           $this->session->transactionStatusList['into']['LOCN_ID'] === $locnId2 &&
                                           $this->session->transactionStatusList['into']['WH_ID']   === $whId2) {
                                               
                                              $this->session->transactionStatusList['whole']['RESULT'] = true;
                                        }
                                    }
                            } catch(Exception $ex) {
                                $this->addError('Error occured while ' . $ex->getMessage() . ' transaction ');
                            }    
                            
							$this->session->displayList['displayWhole'] = $this->view->displayWhole = true;
							break;
                            
            case 'REPORT: TXT':
            case 'REPORT: XLS':
            case 'REPORT: XML':
            case 'REPORT: CSV':
                            $conditions     = $this->_getConditions('lines', 'transfer');
                            $clause         = $this->_makeClause($conditions, $this->_allowed);
                            $warehouseList  = $this->minder->getWarehouseList();
                            $whId           = substr($clause['LOCN_ID = ? '], 0, 2);
                            $locnId         = substr($clause['LOCN_ID = ? '], 2, strlen($clause['LOCN_ID = ? ']));
                          
                            if(in_array($whId, array_keys($warehouseList))) {
                                $clause['LOCN_ID = ? '] = $locnId;
                            }
                            $data         = $this->minder->getIssns($clause);
                            $numRecords   = count($data);
                            
                            $this->view->data   = array();
                            $this->_setupHeaders();  
                            unset($this->view->headers['INTO_DATE']);
                            for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                                if (array_key_exists($data[$i]->id, $conditions) && false !== array_search($data[$i]->id, $conditions, true )) {
                                    $this->view->data[] = $data[$i];
                                }
                            }
                            $this->_processReportTo($action);
                           
                            break;
			default:        
		
		}
	}
	
	public function moveableAction() {
	
		$this->view->title     = "Transfer Moveable";
		$this->view->locn_id   =  isset($_POST['locn_id']) ? $_POST['locn_id'] : '';
        $this->view->locFilter = $this->minder->getValidationParams('LOCATION', true);
    
        
        
		$action = strtoupper($this->getRequest()->getPost('action'));
		switch($action) {
			case 'UPDATE':
                            
                            $params['LOCATION']  = trim($this->getRequest()->getPost('locn_id'));  
                            $params              = $this->_explodParams($params);
                            
                            $params2['LOCATION'] = trim($this->getRequest()->getPost('hid_locn_id'));
                            $params2             = $this->_explodParams($params2);
                            
                            $params['LOCATION2'] = $params2['LOCATION'];
                            
							
                            if(!empty($params['LOCATION']['FIELD']) && !empty($params['LOCATION2']['FIELD'])) {
                            
                                $warehouseList  = $this->minder->getWarehouseList();
                                
                                
                                $whId                   = substr($params['LOCATION']['FIELD'], 0, 2);
                                $locnId                 = substr($params['LOCATION']['FIELD'], 2, strlen($params['LOCATION']['FIELD']));
                                
                                $whId2                   = substr($params['LOCATION2']['FIELD'], 0, 2);
                                $locnId2                 = substr($params['LOCATION2']['FIELD'], 2, strlen($params['LOCATION2']['FIELD'])); 
                                
                                if(!in_array($whId, array_keys($warehouseList))) {
                                    $this->addError('Warehouse not found, please enter.');
                                    $this->view->displayWhole = true;
                                    return;    
                                }
                                
                                if(!in_array($whId2, array_keys($warehouseList))) {
                                    $this->addError('Warehouse not found, please enter.');
                                    $this->view->displayWhole = true;
                                    return;    
                                }
                                
                                
                                try {
                                        if(!empty($params['LOCATION2']['PREFIX'])) {
                                            $source = 'SSSBSSKSS';
                                        } elseif(empty($params['LOCATION2']['PREFIX'])) {
                                            $source = 'SSSKSSKSS';
                                        }
                                        
                                        $transaction = new Transaction_TRMIA();
                                        $transaction->whId          =   $whId;
                                        $transaction->locnId        =   $locnId;
                                        $transaction->subLocation   =   $whId2 . $locnId2;
                                        $transaction->reference     =   'On-Screen Moveable Location Transfer';
                                        
                                        if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', $source, '', 'MASTER    '))) { 
                                            throw new Exception('TRMIA');
                                        }
                                        
                                        $this->addMessage($result); 
                                } catch(Exception $ex) {
                                    $this->addError('Error occured while ' . $e->getMessage() . ' transaction ');
                                }
                            } else {
                                return;
                            }
                            
                            $this->session->displayList['displayMove'] = $this->view->displayMove = true; 
							break;
            case 'REPORT: TXT':
            case 'REPORT: XLS':
            case 'REPORT: XML':
            case 'REPORT: CSV':
                            $conditions     = $this->_getConditions('lines', 'transfer');
                            $clause         = $this->_makeClause($conditions, $this->_allowed);
                            $warehouseList  = $this->minder->getWarehouseList();
                            $whId           = substr($clause['LOCN_ID = ? '], 0, 2);
                            $locnId         = substr($clause['LOCN_ID = ? '], 2, strlen($clause['LOCN_ID = ? ']));
                          
                            if(in_array($whId, array_keys($warehouseList))) {
                                $clause['LOCN_ID = ? '] = $locnId;
                            }
                            $data         = $this->minder->getIssns($clause);
                            $numRecords   = count($data);
                            
                            $this->view->data   = array();
                            $this->_setupHeaders();
                            for ($i = 0, $count = 0; $i < $numRecords; $i++) {
                                if (array_key_exists($data[$i]->id, $conditions) && false !== array_search($data[$i]->id, $conditions, true )) {
                                    $this->view->data[] = $data[$i];
                                }
                            }
                            $this->_processReportTo($action);
                            break;                
			
			default:        
		
		}
	}
	public function linesAction() {
	   
		$conditions         = $this->_setupConditions(null, $this->_allowed);
		$clause             = $this->_makeClause($conditions, $this->_allowed);
	    $warehouseList      = $this->minder->getWarehouseList();
		
        $params['LOCATION'] = $clause['LOCN_ID = ? '];
        $result             = $this->_explodParams($params);
        $whId               = substr($result['LOCATION']['FIELD'], 0, 2);
		$locnId             = substr($result['LOCATION']['FIELD'], 2, strlen($result['LOCATION']['FIELD']));
	    
    	if(in_array($whId, array_keys($warehouseList))) {
			$clause['LOCN_ID = ? '] = $locnId;
            $clause['WH_ID = ? ']   = $whId;
		}
		
		$this->_preProcessNavigation();
		$pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
		$showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
	    $data         = $this->minder->getClearIssns($clause, $pageSelector, $showBy);
	 
		$numRecords =   count($data['data']);
		// Calculate the number of selected items.
		for ($i = 0, $count = 0; $i < $numRecords; $i++) {
			if (array_key_exists($data['data'][$i]->id, $conditions) && false !== array_search($data['data'][$i]->id, $conditions, true )) {
				$count++;
			}
		}
		// check upper chakbox
		$this->view->upperChekbox = '';
		if($count == count($data['data'])) {
			$this->view->upperChekbox = 'checked';
		}
		
		$this->view->upperChekbox;
		$this->view->totalSelect= $count;
		$this->view->issnList   = $data['data'];
		$this->view->issnStatus = $this->minder->getIssnStatusList();
	
		$this->_postProcessNavigation($data);
		
	}
	
	public function lookupAction() {
		try{
				$field  =   $this->_getParam('field');
				$value  =   $this->_getParam('q');
				switch($field) {
					case 'locn_id':
                                    $params['LOCATION'] =   $value;
									$result             =   $this->_explodParams($params);
                                    $whId               =   strtoupper(substr($result['LOCATION']['FIELD'], 0, 2));
									$locnId             =   substr($result['LOCATION']['FIELD'], 2, strlen($result['LOCATION']['FIELD']));
                                    $locationList       =   $this->minder->getLocationList($locnId);
									
                                    $data               =   array();
									foreach($locationList as $key => $value) {
										$data[$result['LOCATION']['PREFIX'] . $whId . $key] =  $value;
									}
										$this->view->data   =   $data;
									break;
					case 'clear_locn_id':
									$this->view->data    =   $this->minder->getClearLocationList($value, $this->_getParam('q2'));
									break;
					default:
									break;
				
				}
		} catch(Exception $e) {
		
		}
	}
	
	public function marklinesAction() {
	
		$conditions = $this->_getConditions('lines', 'transfer');
		$clause     = $this->_makeClause($conditions, $this->_allowed);
		
		
        $warehouseList      = $this->minder->getWarehouseList();
        $params['LOCATION'] = $clause['LOCN_ID = ? '];
        $result             = $this->_explodParams($params);
        $whId               = substr($result['LOCATION']['FIELD'], 0, 2);
        $locnId             = substr($result['LOCATION']['FIELD'], 2, strlen($result['LOCATION']['FIELD']));
        
      
        if(in_array($whId, array_keys($warehouseList))) {
            $clause['LOCN_ID = ? '] = $locnId;
        }
        				 
		$data         = $this->minder->getIssns($clause);
		$method       = $this->getRequest()->getParam('method');
		$id           = $this->getRequest()->getParam('id');
		$value        = $this->getRequest()->getParam('value');

		
		$conditions = $this->_markSelected($data, $id, $value, $method, 'lines');
		$numRecords = count($data);
	   
		// Calculate the number of selected items.
		for ($i = 0, $count = 0; $i < $numRecords; $i++) {
			if (array_key_exists($data[$i]->id, $conditions) && false !== array_search($data[$i]->id, $conditions, true )) {
				$count++;
			}
		}
		
		$data = array();
		$data['selected_num']   = $count;
		$this->view->data       = $data;        
	}
	
	public function searchAction() {
		try{
				$field = $this->_getParam('field');
				$value  =   $this->_getParam('q');
				if(!empty($value)) {
					switch($field) {
						case 'locn_id':
										$params['LOCATION'] = $value;
                                        
                                        $result = $this->_explodParams($params);
                                        $whId   =   strtoupper(substr($result['LOCATION']['FIELD'], 0, 2));
										$locnId =   substr($result['LOCATION']['FIELD'], 2, strlen($result['LOCATION']['FIELD']));
										
                                        $data   =   $this->minder->getClearLocationList($locnId, $whId);
							            if($data) {
											$this->view->data = array('locn_name' => $data[$locnId]);
										}
										break;
						case 'clear_locn_id':
										$data    =   $this->minder->getLocationList($value);
										if($data) {
											$this->view->data   = array('locn_name' => $data[$value]);   
										}
										break;
						case 'description':
										$warehouseList      =   $this->minder->getWarehouseList();
										if(isset($warehouseList[$value])) {
											$this->view->data = array('description' => $warehouseList[$value]);
										} 
										break;
						default:
										break;
					
					}
				}
				
		} catch(Exception $e) {
		
		}
	}
	
	protected function _setupHeaders() {
		$this->view->headers = array(
									   'INTO_DATE'          =>  'Date Into',
									   'SSN_ID'             =>  'ISSN',
									   'SSN_DESCRIPTION'    =>  'SSN Description',
									   'ISSN_STATUS'        =>  'Status',
									   'CURRENT_QTY'        =>  'Qty'
							   );
	
	}

	protected function _setupAllowed() {
		switch($this->_action) {
			case 'into':
			            $this->_allowed = array('ssn_id'  => ' SSN_ID = ? ',
                                                'locn_id' => 'LOCN_ID = ? ');
						break;
			case 'whole':
                        $this->_allowed = array('locn_id' => 'LOCN_ID = ? ');
						break;
			
			case 'moveable':
						$this->_allowed = array('locn_id' => 'LOCN_ID = ? ');
						break;
			
			case 'marklines':
                        $this->_allowed = array('locn_id' => 'LOCN_ID = ? ');
        				break;
            case 'lines':
                        $this->_allowed = array('locn_id' => 'LOCN_ID = ? ');
                        break;
			
			default:
						break;
		
		
		}
        
	
	}
    
    protected function _explodParams($params) {
        
        $data         = array();  
        
        foreach($params as $type => $value) {
            
            $value        = strtoupper($value);  
            $validateExpr = $this->minder->getValidationParams($type, true);
            $prefix       = $validateExpr['SYMBOLOGY_PREFIX'];
            $regExp       = $validateExpr['DATA_EXPRESSION'];      
                            
            switch(strtoupper($type)){
                case 'BARCODE' :
                            $formPrefix = substr($value, 0, strlen($prefix));
                            $formSnn    = substr($value, strlen($prefix), strlen($value));
                            
                            if($formPrefix === $prefix) {
                                $data[$type] = array('FIELD' => $formSnn,
                                                     'PREFIX'=> $formPrefix);
                            } else {
                                $data[$type] = array('FIELD' => $value,
                                                     'PREFIX'=> '');
                            }    
                            break;
                case 'LOCATION2':
                case 'LOCATION':
                            
                            $formPrefix = substr($value, 0, strlen($prefix));
                            $formWhId   = substr($value, 3, 2);
                            $formLocnId = substr($value, 5, strlen($value));
                            
                            if($prefix === $formPrefix) {
                                $data[$type] = array('FIELD' => $formWhId . $formLocnId,
                                                     'PREFIX'=> $formPrefix); 
                            } else {
                                $data[$type] = array('FIELD' => $value,
                                                     'PREFIX'=> '');
                            }
                            break;
                            
                default:
                        
            
            }
        }
        
        return $data;
    }
}
