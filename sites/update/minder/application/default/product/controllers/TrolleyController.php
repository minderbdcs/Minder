<?php

class TrolleyController extends Minder_Controller_Action 
{
    protected $linesSysScreenName = 'CONNOTELINE';
    protected $orderSysScreenName = 'PICK_ORDER';

    const CANCELED_LINES_MODEL_NAME = 'UNPICK_ASSEMBLY';
    const CANCELED_LINES_NAMESPACE  = 'ASSEMBLY-UNPICK_ASSEMBLY';
	
    public function init()
    {
        parent::init();	
        $this->view->linesSelectionNamespace = 'trolley_line_selection';
        $this->view->orderSelectionNamespace = 'trolley_order_selection';
        
        $this->view->linesSelectionActionName = 'select-row';
        $this->view->orderSelectionActionName = 'select-row';
    }
        
	public function indexAction()
	{
        
		// required for SOAP in future		
		$this->session->sl_flag = 'false';
		$this->session->cl_flag = 'true';
		
		// removing redirects data from session ( for connote page )
		unset($this->session->assembly_orders);
		unset($this->session->assembly_returnurl);
		
		// catch connote return message
		
		if (isset($this->session->connote_result)) {
			$this->view->connote_result = $this->session->connote_result; 
			unset($this->session->connote_result);
		} else {
			$this->view->connote_result = '';
		} 
		
		// get trolleys for drop down
        $locns      = $this->minder->getMoveableLocations();
        $frontLocns = array();
        $sideLocns  = array();
		$trolleys   = array();
		foreach ($locns['data'] as $slot) {
            switch ($slot['LOCN_TYPE']) {
                case 'TF':
                    $trolleys[substr($slot['LOCN_ID'], 0, 2)] = $slot['LOCN_NAME'];
                    $frontLocns[$slot['LOCN_ID']] = $slot;
                    break;
                case 'TV':
                    $sideLocns[$slot['LOCN_ID']]  = $slot;
                    break;
            }
		}
		$trolleys = array_unique($trolleys);
			
		$this->view->trolleys = array_merge(array(''=>''), $trolleys);		
		
		$selected_trolley = $this->getRequest()->getParam('selected_trolley', 'T1');
		$this->view->selected_trolley = $selected_trolley;
		
        //setup view for page
        $default_view = 'trolley';
        $view_toogle = '';
        $restoreView = $this->getRequest()->getParam('restore_view', 'false');
        
        if (strtolower(trim($restoreView)) == 'true') {
            $view_toogle = $this->session->view_toogle;
        }
        
        if (empty($view_toogle)) {
            //if no view was saved, use default
            $view_toogle = $default_view;
        }
        
		$this->session->view_toogle = $view_toogle;
		$this->view->view_toogle    = $view_toogle;
		
        $raw_products = $this->minder->getProdInLocations($selected_trolley, "i1.locn_id STARTING '$selected_trolley' AND po.pick_order IS NOT NULL AND po.PICK_STATUS <> 'CN' AND pid.pick_detail_status IN ('PL', 'DS')");
	
		$this->view->raw_products = $raw_products;
		
		$raw_order_id = $this->getRequest()->getParam('order_num', $this->session->order_id);
		$this->view->selected_order = $raw_order_id;		
		
		
		$orders_list = array();
		foreach ($raw_products['data'] as $prod) {
			$orders_list[$prod['PICK_ORDER']] = $prod['PICK_ORDER'];   
		}
                                       
		$this->view->orders_list = array('' => '') + $orders_list;

        $tmpParam = $this->_helper->BarcodeParser($raw_order_id, array('SALESORDER'));
        if ($tmpParam['VALID'] === true) {
            $order_id = $tmpParam['PARSED_VALUE']; 
        } else {
            //maybe this is order without prefix
            $order_id = $raw_order_id;
        }
            
        $this->view->selected_order = $order_id;
        if (!isset($this->view->orders_list[$order_id])) {
            $this->view->selected_order = '';
        }
            
        
		if ($view_toogle=='standard') {
            // toogle to standart view        
			$rows = array();
			$rows_selected = array();
			$allRows = array();
			
			
			if ($raw_products['total']!=0) {
				foreach ($raw_products['data'] as $raw_product) {
					$row['order_id'] 		= $raw_product['PICK_ORDER'];
				    $row['location'] 		= $raw_product['LOCN_ID'];
				    $row['prod_id']			= $raw_product['PROD_ID'];
				    $row['ssn']				= $raw_product['SSN_ID'];
				    $row['description'] 	= $raw_product['SHORT_DESC'];
				    $row['qty'] 			= $raw_product['ISSN_QTY'];
				    $row['from_wh']			= $raw_product['PREV_PREV_WH_ID'];
					$row['from_location']	= $raw_product['PREV_PREV_LOCN_ID'];
                    $row['pid_status']      = $raw_product['PID_STATUS'];
                    $rowKey                 = uniqid();
                    $row['_key']            = $rowKey;

                    if (empty($this->view->selected_order) || $this->view->selected_order == $raw_product['PICK_ORDER']) {
					    $rows[$row['_key']] = $row; 
                    }
					$allRows[$row['_key']] = $row;
				}
				
			}
			
			
			$this->view->rows = $rows;
            $this->session->allRows = $allRows;
			
            $totalProducts = array();
            $selectedProducts = array();
            if (!empty($this->view->selected_order)) {
                foreach ($allRows as $key => $row) {
                    if ($row['order_id'] != $this->view->selected_order) { 
                        unset($allRows[$key]);
                    } else {
                        $totalProducts[$row['prod_id']] = $row['prod_id'];
                    }
                }
                foreach ($rows_selected as $key => $row) {
                    if ($row['order_id'] != $this->view->selected_order) {
                        unset($rows_selected[$key]);
                    } else {
                        $selectedProducts[$row['prod_id']] = $row['prod_id'];
                    }
                }
            } else {
                $tmpFunction = create_function('$elem', 'return $elem["prod_id"];');
                $totalProducts = array_map($tmpFunction, $allRows);
                $selectedProducts = array_map($tmpFunction, $rows_selected);
                array_unique($totalProducts);
                array_unique($selectedProducts);
                $totalProducts = array_combine($totalProducts, $totalProducts);
                $selectedProducts = array_combine($selectedProducts, $selectedProducts);

                if (!is_array($totalProducts)) 
                    $totalProducts = array();

                if (!is_array($selectedProducts)) 
                    $selectedProducts = array();
            }
            $this->view->total_orders = count($rows);
			$this->view->rows_selected = $this->session->rows_selected = $rows_selected;	
            $this->view->total_prod    = count($totalProducts);
            $this->view->selected_prod = count($selectedProducts);
			
			
			$this->session->assembly_trolley = $selected_trolley;
		} elseif ($view_toogle=='trolley') {
            // toogle to trolley view

			$products = array(); 
			$slot_orders = array();
            
            $assembly = array('LOCATIONS' => array(), 'PRODUCTS' => array(), 'SELECTED_ORDER' => array()); //contains all slots and sides whith orders in it whis products for orders in it
	
			foreach ($raw_products['data'] as $prod) {
				$f = true;
				$locn_id      = $prod['LOCN_ID'];
				$prod_id      = $prod['PICK_ORDER'];
                $pick_order   = $prod['PICK_ORDER'];
                $trolley_name = substr($locn_id, 0, 2);
                
                if ($trolley_name == $selected_trolley) {
                    if (!isset($assembly['LOCATIONS'][$locn_id])) {
                        $assembly['LOCATIONS'][$locn_id] = array(
                            'LOCN_ID'   => $locn_id,
                            'ORDERS'    => array(),
                            'TOTAL_QTY' => 0
                        );
                    }
                
                    if (!isset($assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order])) {
                        $assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order] = array(
                            'LOCN_ID'    => $locn_id,
                            'PICK_ORDER' => $pick_order,
                            'PRODUCTS'   => array(),
                            'TOTAL_QTY' => 0
                        );
                    }
                
                    if (!isset($assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['PRODUCTS'][$prod['PROD_ID']])) {
                        $assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['PRODUCTS'][$prod['PROD_ID']] =  array(
                            'LOCN_ID'    => $locn_id,
                            'PICK_ORDER' => $pick_order,
                            'PROD_ID'    => $prod['PROD_ID'],
                            'NEEDED_QTY' => 0,
                            'PICKED_QTY' => 0
                        );
                    }
                
                    $assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['PRODUCTS'][$prod['PROD_ID']]['NEEDED_QTY'] +=  $prod['ISSN_QTY'];
                    $assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['TOTAL_QTY']                                +=  $prod['ISSN_QTY'];
                    $assembly['LOCATIONS'][$locn_id]['TOTAL_QTY']                                                       +=  $prod['ISSN_QTY'];
                    $assembly['PRODUCTS'][$prod['PROD_ID']]['ORDERS'][$pick_order]['TROLLEYS'][$trolley_name]            =  &$assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['PRODUCTS'][$prod['PROD_ID']];
                    
                    if ($pick_order == $order_id) {
                        $assembly['SELECTED_ORDER'][$prod['PROD_ID']] = &$assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['PRODUCTS'][$prod['PROD_ID']];
                    }
                }
				
				if (!isset($products[$locn_id][$prod['PICK_ORDER']])) {
                    $products[$locn_id][$prod['PICK_ORDER']] = $prod;
                } else {
                    $products[$locn_id][$prod['PICK_ORDER']]['ISSN_QTY']   += $prod['ISSN_QTY'];
                    $products[$locn_id][$prod['PICK_ORDER']]['QTY_PICKED'] += $prod['QTY_PICKED'];
                }
				
				if (isset($slot_orders[$locn_id])) {
					foreach ($slot_orders[$locn_id] as $val) {
						if($val['PICK_ORDER']==$prod_id) {
							$f=false;	 				
						}
					}
				}
				
                if (!isset($slot_orders[$locn_id][$prod_id]))
					$slot_orders[$locn_id][$prod_id] = array('PICK_ORDER'=>$prod_id,'ISSN_QTY'=>0);
                $slot_orders[$locn_id][$prod_id]['ISSN_QTY'] += $prod['ISSN_QTY'];
			}
			$this->view->slot_orders = $slot_orders;
			
			$slots = array();
//			$assembly = array();
				
            foreach ($frontLocns as $slot) {
				$slot_name = $slot['LOCN_ID'];  
				$trolley_name = substr($slot_name, 0, 2);
				$x = (int) substr($slot_name, 2, 2);
				$y = (int) substr($slot_name, 4, 2);
				
				if ($trolley_name==$selected_trolley ) {
					$slots[$x][$y]['status'] = $slot['LOCN_STAT'];
					$slots[$x][$y]['slot_name'] = $slot_name;
					if(isset($products[$slot_name])) {						
						if (isset($products[$slot_name][$order_id])) {
                            
							$slots[$x][$y]['qty'] = $products[$slot_name][$order_id]['ISSN_QTY']; 
							$slots[$x][$y]['no'] = $products[$slot_name][$order_id]['PROD_ID'];
							
								$prod_desc = array_flip($this->minder->getProductShortDescriptionList($slots[$x][$y]['no']));
								$prod_desc = $prod_desc[$slots[$x][$y]['no']];
							$slots[$x][$y]['prod_desc'] = $prod_desc;
                            
//                            if (!isset($assembly[$slots[$x][$y]['no']])) {
//                                $assembly[$slots[$x][$y]['no']] = array(
//                                    'NEEDED_QTY' => 0,
//                                    'PICKED_QTY' => 0,
//                                    'ORDER_ID'   => $order_id,
//                                    'LOCN_ID'    => $slot_name
//                                );
//                            }
//							$assembly[$slots[$x][$y]['no']]['NEEDED_QTY'] = $slots[$x][$y]['qty'];
						}
                        
                        if (!isset($slots[$x][$y]['no'])) {
                            if (false !== ($firstOrder = reset($products[$slot_name])))
                                $slots[$x][$y]['no'] = $firstOrder['PROD_ID'];
                        }
                        
					}
	
//					if (!isset($slots[$x][$y]['no'])) {
//						$slots[$x][$y]['no'] = $slot['PROD_ID'];
//					}
				}
			}
			$this->view->slots = $slots;
			
// lefthand side and righthand side handler
			
			$sides = null;
			
			$left_side = array();
			$right_side = array();
			
            foreach ($sideLocns as $slot) {
					$slot_name = $slot['LOCN_ID'];  
					$trolley_name = substr($slot_name, 0, 2);
					$y = substr($slot_name, 6, 2);
					$side = substr($slot_name, 2, 2);
					
					if ($trolley_name==$selected_trolley ) {
							$sides[$side][$y]['name'] = $slot_name;
//							$sides[$side][$y]['barcode'] = $slot['PROD_ID'];
							$sides[$side][$y]['qty'] = '';
							$sides[$side][$y]['desc'] = '';
						
						if (isset($products[$slot_name])) {
							if (isset($products[$slot_name][$order_id])) {
											$sides[$side][$y]['qty'] = $products[$slot_name][$order_id]['ISSN_QTY'];
											$sides[$side][$y]['desc'] = $products[$slot_name][$order_id]['SHORT_DESC'];	

//                                            if (!isset($assembly[$sides[$side][$y]['barcode']])) {
//                                                $assembly[$sides[$side][$y]['barcode']] = array(
//                                                    'NEEDED_QTY' => 0,
//                                                    'PICKED_QTY' => 0,
//                                                    'ORDER_ID'   => $order_id,
//                                                    'LOCN_ID'    => $slot['LOCN_ID']
//                                                );
//                                            }
//											$assembly[$sides[$side][$y]['barcode']]['NEEDED_QTY'] = $products[$slot_name][$order_id]['ISSN_QTY'];
							}						
                            if (false !== ($firstOrder = reset($products[$slot_name])))
                                $sides[$side][$y]['barcode'] = $firstOrder['PROD_ID'];
						}
					}
			}
				
			$this->view->sides = $sides;
	
			if($order_id!='') {
	
				$this->view->supplier_list = ($this->minder->getSupplierListProperty($order_id)=='T')?true:false;
				$instrs = $this->minder->getSpecialInstructionsProperty($order_id);
				$this->view->external_instructions = $instrs['SPECIAL_INSTRUCTIONS1']; 
				$this->view->internal_instructions = $instrs['SPECIAL_INSTRUCTIONS2'];
			} else {
				$this->view->supplier_list = false;
				$this->view->external_instructions = false;
				$this->view->internal_instructions = false;
			}
            
			$this->session->assembly_trolley = $selected_trolley;
			$this->session->assembly_order = $order_id;
			$this->session->assembly = $assembly;
            $this->session->rows_selected = array();
		}
        
        //find if there is products ready for despatch for selected order
        $orderStatus = $this->session->orderStatus;
        if (!is_array($orderStatus))
            $orderStatus = array();
        
        $completedLines = array();
        if (!empty($this->view->selected_order)) {
            $tmpProducts = $this->minder->getProdInLocations(null, "po.pick_order = '" . $order_id . "' AND i1.locn_id STARTING '" . $selected_trolley . "' AND pid.pick_detail_status = 'DS'");
            $tmpProducts = $tmpProducts['data'];
            foreach ($tmpProducts as $prodDesc) {
                if (!isset($completedLines[$prodDesc['PROD_ID']])) {
                    $completedLines[$prodDesc['PROD_ID']] = $prodDesc;
                } else {
                    $completedLines[$prodDesc['PROD_ID']]['QTY_PICKED'] += $prodDesc['QTY_PICKED'];
                }
            }
            $orderStatus[$this->view->selected_order] = array('LINES_OPENED' => count($completedLines), 'LINES_COMPLETED' => count($completedLines));
        } else {
            $orderStatus[$this->view->selected_order] = array('LINES_OPENED' => 0, 'LINES_COMPLETED' => 0);
        }

        $this->view->orderStatus    = $orderStatus[$this->view->selected_order];
        $this->view->completedLines = $completedLines;
		
        $this->session->orderStatus = $orderStatus;
        $this->session->order_id    = $order_id;
		$this->_setupShortcuts();
        $this->_setupHeaders();
        
        $this->view->leftPannelState = $this->session->leftPannelState = 'hide';
        $this->view->cancelOrderReasons = array_merge(array('' => ''), $this->minder->getOptionsList('CAN_ORDER'));

        if (isset($this->session->linesCanceled) && $this->session->linesCanceled) {
            $this->buildCanceledLinesSysScreen();
            unset($this->session->linesCanceled);
        }
	}

    protected function buildCanceledLinesSysScreen() {
        $this->view->sysScreenErrors = array();
        $this->view->linesSsName     = self::CANCELED_LINES_MODEL_NAME;
        $this->view->linesNamespace  = self::CANCELED_LINES_NAMESPACE;
        $this->view->renderCanceledLines = true;

        try {
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                $this->view->sysScreenErrors = $this->view->errors;
                $this->view->hasErrors = true;
            }

            $this->view->linesJsSearchResults = $this->view->jsSearchResult(
                                                    self::CANCELED_LINES_MODEL_NAME,
                                                    self::CANCELED_LINES_NAMESPACE,
                                                    array('sysScreenCaption' => 'CANCELED LINES', 'usePagination'    => true)
            );
            $this->view->linesJsSearchResultsDataset = $this->view->sysScreens[self::CANCELED_LINES_NAMESPACE];
        } catch (Exception $e) {
            $this->view->sysScreenErrors[] = $e->getMessage();
        }
    }
    
    public function getDatasetAction() {
        $datasets = array(
            self::CANCELED_LINES_NAMESPACE   => self::CANCELED_LINES_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);
            if (isset($sysScreens[$namespace]) && isset($sysScreens[$namespace]['paginator'])) {
                $pagination['selectedPage'] = (isset($sysScreens[$namespace]['paginator']['selectedPage'])) ? $sysScreens[$namespace]['paginator']['selectedPage'] : $pagination['selectedPage'];
                $pagination['showBy']       = (isset($sysScreens[$namespace]['paginator']['showBy']))       ? $sysScreens[$namespace]['paginator']['showBy']       : $pagination['showBy'];
            }

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy']);
            $pagination = array_merge($pagination, $this->view->paginator);
            $this->savePagination($namespace, $pagination);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelname, $this->view->dataset, $this->view->selectedRows, $pagination);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function toggleViewAction() {
        if(!isset($this->session->view_toogle)) {
            $this->session->view_toogle = 'trolley';
        }
        
        if ($this->session->view_toogle == 'trolley') {
            $this->session->view_toogle = 'standard';
        } else {
            $this->session->view_toogle = 'trolley';
        }
        
        $params = $this->getRequest()->getParams();
        if (!isset($params['restore_view']))
            $params['restore_view'] = 'true';
        
        $this->_helper->getHelper('Redirector')->setCode(303)->goto('index', 'trolley', null, $params);
    }
	

	
	public function testAction()
	{
		
		var_dump($this->session->rows_selected );
		unset($this->session->rows_selected);
		die();	 
		
		unset ($this->session->rows_selected);		
		
//		var_dump($assembly); 
		
//		$product = $this->minder->getProduct('9320075041083');
//		var_dump($product);

//		$gen_id = $this->minder->runDbGenerator(TRANSACTION_ID);
                $messageId = $this->minder->getNextMessageId() ;
//		var_dump($gen_id);
//		var_dump($messageId);
		$trn_type = 'PRCL';
		
		$this->minder->populateWebRequests($messageId, $trn_type);
		
//		$gen_id = $this->minder->runDbGenerator(TRANSACTION_ID);
		$transaction = new Transaction_PRCLS();
		
		$transaction->transCode = $trn_type;
		$transaction->reference = "<ContactInstanceID>2102040";

		$this->minder->doTransactionV4Response($transaction, $messageId);
                $this->minder->updateWebRequests($messageId, "WS" );
		
		die();
	}
	
	public function markActionAjaxAction()
	{
		if (isset($this->session->rows_selected)) {
			$rows_selected = $this->session->rows_selected;
		} else {
			$rows_selected = array();				
		}
		
		$rowKey = $this->getRequest()->getParam('row_key', null);
        $status = $this->getRequest()->getParam('status', 'true');

		$jsonObject = new stdClass();
        
        $orderId = $this->session->order_id;
        $allRows = $this->session->allRows;
        
        
        if (!empty($orderId)) {
            foreach ($allRows as $key => $row) {
                if ($row['order_id'] != $orderId)
                    unset($allRows[$key]);
            }
        }
		
		if ($rowKey=='all') {
			if (strtolower($status) == 'true') {
                $rows_selected = $allRows;
            } elseif (strtolower($status) == 'false') {
                $rows_selected = array();                    
			}
		} elseif (isset($allRows[$rowKey])) {
			if ($status == 'true') {
                $rows_selected[$rowKey] = $allRows[$rowKey];
			} else {
                unset($rows_selected[$rowKey]);
			}
		}
		
        $this->session->rows_selected = $rows_selected;
        $selectedProds = array();
        if (!empty($orderId)) {
            foreach ($rows_selected as $key => $row) {
                if ($row['order_id'] != $orderId) {
                    unset($rows_selected[$key]);
                } else {
                    $selectedProds[$row['prod_id']] = $row['prod_id'];
                }
            }
        } else {
            $tmpFunction = create_function('$elem', 'return $elem["prod_id"];');
            $selectedProds = array_map($tmpFunction, $rows_selected);
            array_unique($selectedProds);
            $selectedProds = array_combine($selectedProds, $selectedProds);

            if (!is_array($selectedProds)) 
                $selectedProds = array();
        }

		$jsonObject->selected      = count ($rows_selected); 
        $jsonObject->selected_prod = count ($selectedProds); 
		
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($jsonObject);
	}
	
	
	
	public function inputAction()
	{
		$assembly = $this->session->assembly;
		$order = $this->session->assembly_order;
		$trolley = $this->session->assembly_trolley;
		$order_id = $this->session->order_id;
        $orderStatus = $this->session->orderStatus;
        if (!is_array($orderStatus))
            $orderStatus = array();
		
		$barcode_input = trim($this->getRequest()->getParam('barcode_input'));
		$qty_input = (int) trim($this->getRequest()->getParam('qty'));
		
		$jsonObject = new stdClass();
		
		$jsonObject->time = date("H:i:s");
		$jsonObject->locn = "-";
		$jsonObject->order_id = $order_id;
		$jsonObject->id = "-";
		$jsonObject->desc = "-";
		$jsonObject->qty = "-";
        $jsonObject->qty_picked = "-";
        $jsonObject->qty_left = "-";
        $jsonObject->qty_left_total = "-";
        $jsonObject->container_id = "-";
        $jsonObject->connote_allowed = false;
		$jsonObject->wh = $this->minder->limitWarehouse;
		$jsonObject->old_location = "-";
        $jsonObject->order_lines_opened = "0";
        $jsonObject->order_lines_completed = "0";
        
		
		try {
            $tmpParam = $this->_helper->BarcodeParser($barcode_input, array('SALESORDER', 'PRODUCT_CODE'));
            if ($tmpParam['VALID'] !== true) {
                $jsonObject->error = 'Unrecognized barcode.';
                $this->_helper->viewRenderer->setNoRender();
                echo json_encode($jsonObject);
                return;
            }
		} catch ( Exception $e ) {
			$jsonObject->error = 'Unrecognized barcode.';
            $this->_helper->viewRenderer->setNoRender();
            echo json_encode($jsonObject);
            return;
		}
		
// may be order_id entered ?
		if ($tmpParam['PARAM_NAME'] == 'SALESORDER') {
			$jsonObject->redirect = $this->view->url(array('action' => 'index', 'controller' => 'trolley', 'restore_view' => 'true', 'selected_trolley' => $trolley, 'order_num' => $barcode_input) , null, true);
            $this->_helper->viewRenderer->setNoRender();
            echo json_encode($jsonObject);
            return;
		}
		
		$id = $tmpParam['PARSED_VALUE'];
		
		if (!isset($assembly['PRODUCTS'][$id]['ORDERS'][$order_id]['TROLLEYS'][$trolley])) {
			$jsonObject->error = 'There are no such products at this trolley!';
            $this->_helper->viewRenderer->setNoRender();
            echo json_encode($jsonObject);
            return;
		} else {
            
            $productDescription = &$assembly['PRODUCTS'][$id]['ORDERS'][$order_id]['TROLLEYS'][$trolley];
            if ($productDescription['PICKED_QTY'] + $qty_input > $productDescription['NEEDED_QTY']) {
                $jsonObject->error = 'Scanning completed for this Product ' . $id . '.';
                $this->_helper->viewRenderer->setNoRender();
                echo json_encode($jsonObject);
                return;
            }
            
            if (!isset($orderStatus[$order_id]))
                $orderStatus[$order_id] = array('LINES_OPENED' => 0, 'LINES_COMPLETED' => 0);
            
            if (($productDescription['PICKED_QTY'] < 1) && $qty_input >0) {
                $orderStatus[$order_id]['LINES_OPENED']++;
            }
            
			$productDescription['PICKED_QTY'] += $qty_input;
            $locn_id = $productDescription['LOCN_ID'];
            $pick_order = $productDescription['PICK_ORDER'];
            
            $assembly['LOCATIONS'][$locn_id]['ORDERS'][$pick_order]['TOTAL_QTY'] -= $qty_input;
            $assembly['LOCATIONS'][$locn_id]['TOTAL_QTY']                        -= $qty_input;

            $jsonObject->qty_picked     = $productDescription['PICKED_QTY'];
            $jsonObject->qty_left       = $productDescription['NEEDED_QTY'] - $productDescription['PICKED_QTY'];
            $jsonObject->qty_left_total = $assembly['LOCATIONS'][$locn_id]['TOTAL_QTY'];
            $jsonObject->container_id   = $productDescription['LOCN_ID'];
            
            if ($productDescription['PICKED_QTY']==$productDescription['NEEDED_QTY']) {
                
                $reference = $productDescription['LOCN_ID'];

				$transaction = new Transaction_PKILG();
				$transaction->objectId  = $id;   
				$wkDespatch  = array_keys($this->minder->getDespatchLocationList4LocationGroup($this->minder->limitWarehouse, $trolley)) ;
				$transaction->locnId    = $wkDespatch[0];
				$transaction->subLocnId = $order;
				$transaction->reference = $reference;
				if (strlen($order) > 10) {
 				       	$transaction->subLocnId = 'ORDER2BIG';
			        	$transaction->reference = $reference .  "|" . $order . "|";
				}
	
				$result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBBBKSSS', '', 'MASTER    ');

				if (!$result) { 
					throw new Exception($result);
				}
	
				$jsonObject->locn = $order;
				$jsonObject->old_location = $reference;
                $orderStatus[$order_id]['LINES_COMPLETED']++;
            }
            
            $jsonObject->connote_allowed = true;
            $flagAllPicked               = true;
            foreach ($assembly['SELECTED_ORDER'] as $prodId => $prodDesc) {
                if ($prodDesc['PICKED_QTY'] > 0 && $prodDesc['PICKED_QTY'] != $prodDesc['NEEDED_QTY']) {
                    $jsonObject->connote_allowed = false;
                }
                
                if ($prodDesc['PICKED_QTY'] != $prodDesc['NEEDED_QTY']) {
                    $flagAllPicked = false;
                }
            }
            
            if ($flagAllPicked) {
                $jsonObject->redirect = $this->view->url(array('controller' => 'trolley', 'action' => 'connote'), null, true);
            }
            $jsonObject->order_lines_opened = $orderStatus[$order_id]['LINES_OPENED'];
            $jsonObject->order_lines_completed = $orderStatus[$order_id]['LINES_COMPLETED'];
		}
		
		$jsonObject->id = $id;
		$jsonObject->desc = $this->minder->getProductDescById($id);
		
		$this->session->assembly = $assembly;	
        $this->session->orderStatus = $orderStatus;
	
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($jsonObject);
	}
	
	
	public function markImportsAction()
	{
		if (isset($this->session->imports_rows_selected)) {
			$rows_selected = $this->session->imports_rows_selected;
		} else {
			$rows_selected = array();				
		}
		
		$id = $this->getRequest()->getParam('id', null);

		$jsonObject = new stdClass();
		
		if ($id=='all') {
			if (count($this->session->rows_all_imports_ids)==count($rows_selected)) {
				$rows_selected = array();					
			} else {
				$rows_selected = $this->session->rows_all_imports_ids;
			}
		} else if ($id!=null) {
			if (($key = array_search($id, $rows_selected))!==false) {
				unset($rows_selected[$key]);
			} else {
				$rows_selected[] = $id;
			}
		}
		
		$jsonObject->selected = count ($rows_selected); 
		$this->session->imports_rows_selected = $rows_selected;
		
		die(json_encode($jsonObject));
		
	}
	
	public function reprintAction()
	{
		$ids = $this->session->rows_selected;
		$raw_order_id = $this->session->order_id;
		
		$jsonObject = new stdClass();
		$i=0;
		
		if ($raw_order_id != '') { 
                        $messageId = $this->minder->getNextMessageId() ;
			$trn_type = 'PRCL';
                        $this->minder->populateWebRequests($messageId, $trn_type);
			$transaction = new Transaction_PRCLS();
			$transaction->transCode = $trn_type;
			$transaction->reference = "<ContactInstanceID>".$raw_order_id;
					
			if($this->session->cl_flag == 'true') {
				$transaction->reference = $transaction->reference.'|<CoverLetterFlag>true';
			}
			
			if($this->session->sl_flag == 'true') {
				$transaction->reference = $transaction->reference.'|<SupplierListFlag>true';
			}
			// need the printer name to use
                        $printerInfo = $this->minder->getDefaultPickPrinter();
			$transaction->reference = $transaction->reference .  "|<PrinterName>".$printerInfo;
					
			$transactionResult = $this->minder->doTransactionV4Response($transaction, $messageId);
			$this->minder->updateWebRequests($messageId, "WS" );
			$i++;
		}
		if ($i>0) {
			$jsonObject->msg               = $i. " requests added.";
		} else {
			$jsonObject->error = 'No Order is selected!';
		}

/*
	huh order related not product related !!!!!!!!
		if (count ($ids)>0) {
			foreach ($ids as $id) {		
				if ($id!='') { 
					//$gen_id = $this->minder->runDbGenerator(TRANSACTION_ID);
                                        $messageId = $this->minder->getNextMessageId() ;
					$trn_type = 'PRCL';
		                        $this->minder->populateWebRequests($messageId, $trn_type);
					//$gen_id = $this->minder->runDbGenerator(TRANSACTION_ID);
					$transaction = new Transaction_PRCLS();
					$transaction->transCode = $trn_type;
					$transaction->reference = "<ContactInstanceID>".$id;
					
					if($this->session->cl_flag = 'true') {
						$transaction->reference = $transaction->reference.'|<CoverLetterFlag>true';
					}
					
					if($this->session->sl_flag = 'true') {
						$transaction->reference = $transaction->reference.'|<SupplierListFlag>true';
					}
					
		                        $this->minder->doTransactionV4Response($transaction, $messageId);
                                        $this->minder->updateWebRequests($messageId, "WS" );
					$i++;
				}
			}
			
			$jsonObject->msg = $i. " requests added.";
		} else {
			$jsonObject->error = 'No products is selected!';
		}
*/
		
		die(json_encode($jsonObject));
		
	}
	
	public function reportAction()
	{
		
		$action = $this->getRequest()->getParam('do');
		$ids = $this->session->rows_selected;
		if (count($ids) < 1) {
            $this->_helper->getHelper('Redirector')->setCode(303)->goto('index', 'trolley', null, array('restore_view' => 'true'));
		} else {
            $prodIds  = array();
            $orderIds = array();
            $ssns     = array();
            
            foreach ($ids as $row) {
                $prodIds[$row['prod_id']]   = $row['prod_id'];
                $orderIds[$row['order_id']] = $row['order_id'];
                $ssns[$row['ssn']]          = $row['ssn'];
            }
            
            $clause = "po.pick_order IN ('" . implode("', '", $orderIds) . "') AND i1.prod_id IN ('" . implode("', '", $prodIds) . "') AND i1.ssn_id IN ('" . implode("', '", $ssns) . "') ";
		}
		
		$rows = $this->minder->getProdInLocations(null, $clause);
		$rows = $rows['data'];
		$this->view->data = $rows;
		
		$this->view->headers = array(	"PICK_ORDER"=>'',
								"PROD_ID"=>'',
								"SHORT_DESC"=>'',
								"ISSN_QTY"=>'',
								"QTY_PICKED"=>'',
								"LOCN_ID"=>'',
								"PID_STATUS"=>'',
								"SSN_ID"=>'',
								"PREV_PREV_WH_ID"=>'',
								"PREV_PREV_LOCN_ID"=>''
						);
		
		   switch ($action) {
			case 'REPORT: CSV':
				$this->getResponse()->setHeader('Content-Type', 'text/csv')
									->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
				$this->render('report-csv');
				return true;

			case 'REPORT: XML':
				$response = $this->getResponse();
				$response->setHeader('Content-type', 'application/octet-stream');
				$response->setHeader('Content-type', 'application/force-download');
				$response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
				$this->render('report-xml');
				return true;

			case 'REPORT: XLS':
				$xls = new Spreadsheet_Excel_Writer();
				$xls->send('report.xls');
				$this->view->xls = $xls;
				$this->render('report-xls');
				return true;

			case 'REPORT: TXT':
				$this->getResponse()->setHeader('Content-Type', 'text/plain')
									->setHeader('Content-Disposition', 'attachment; filename="report.txt"');
				$this->render('report-txt');
				return true;
		}		
	}
		
	
	public function checkImportsAction()
	{
		$data = $this->minder->getImports();
		$this->view->rows = $data;
		
		$rows_all_imports_ids = array();
		
		foreach ($data as $id) {
			$rows_all_imports_ids[] = $id[0];
		}
		
		$this->session->rows_all_imports_ids = $rows_all_imports_ids;
		
		if (isset($this->session->imports_rows_selected)) {
			$rows_selected = $this->session->imports_rows_selected;
		} else {
			$rows_selected = array();
		}
		$this->view->imports_rows_selected = $this->session->imports_rows_selected = $rows_selected;

	}	
	
	public function getAddressAjaxAction()
	{
		
		$raw_order_id = $this->session->order_id;
		$jsonObject = new stdClass();
		
		$i=0;
		
		if ($raw_order_id!='') { 
                        $messageId = $this->minder->getNextMessageId() ;
			$trn_type = 'GCNA';
                        $this->minder->populateWebRequests($messageId, $trn_type);
			$transaction = new Transaction_GCNAS();
			$transaction->transCode = $trn_type;
			$transaction->reference = "<ContactInstanceID>".$raw_order_id;
					
                        $this->minder->doTransactionV4Response($transaction, $messageId);
                        $this->minder->updateWebRequests($messageId, "WS" );
			$i++;
		}
		if ($i>0) {
			$jsonObject->msg = $i. " requests added.";
		} else {
			$jsonObject->error = 'No Order is selected!';
		}

/*
	huh order related not product related !!!!!!!!
		if (count ($ids)>0) {
			foreach ($ids as $id) {		
				//$gen_id = $this->minder->runDbGenerator(TRANSACTION_ID);
                                $messageId = $this->minder->getNextMessageId() ;
				$trn_type = 'GCNA';
		                $this->minder->populateWebRequests($messageId, $trn_type);
				//$gen_id = $this->minder->runDbGenerator(TRANSACTION_ID);
				$transaction = new Transaction_GCNAS();
				$transaction->transCode = $trn_type;
				$transaction->reference = "<ContactInstanceID>".$id;
				
		                $this->minder->doTransactionV4Response($transaction, $messageId);
                                $this->minder->updateWebRequests($messageId, "WS" );
			}
			
			$jsonObject->msg = count ($ids). " requests added.";
		} else {
			$jsonObject->error = 'No products is selected!';
		}
*/
		
		die(json_encode($jsonObject));
	}
	
	public function coverLetterAjaxAction()
	{
		
		$jsonObject = new stdClass();
		
		$flag = $this->getRequest()->getParam('flag', 'false');

		if ($flag == 'true') {
			$this->session->cl_flag = 'true';
		} else {
			$this->session->cl_flag = 'false';
		}
		
		die(json_encode($jsonObject));
	}
	
	public function supplierListAjaxAction()
	{
		$jsonObject = new stdClass();
		
		$flag = $this->getRequest()->getParam('flag', 'false');

		if ($flag == 'true') {
			$this->session->sl_flag = 'true';
		} else {
			$this->session->sl_flag = 'false';
		}
		
		die(json_encode($jsonObject));		 
	}	
	
	public function viewRevisionsAction()
	{
		$ids = $this->session->rows_selected;
		if (count($ids)>0) {
			$id = $ids[0];
		} else {
			$id = null;
		}
		
		$data = $this->minder->getRevisions($id);
		$this->view->rows = $data;
		
		$rows_all_revisions_ids = array();
		
		foreach ($data as $id) {
			$rows_all_revisions_ids[] = $id[0];
		}
		
		$this->session->rows_all_revisions_ids = $rows_all_revisions_ids;
		
		if (isset($this->session->revisions_rows_selected)) {
			$rows_selected = $this->session->revisions_rows_selected;
		} else {
			$rows_selected = array();
		}
		$this->view->revisions_rows_selected = $this->session->revisions_rows_selected = $rows_selected;
		
	}

	public function gotoConnoteAction()
	{
		$ids = $this->session->rows_selected;
		
		$this->session->assembly_orders = $ids;
		$this->session->assembly_returnurl = '/trolley';
		$this->_redirect('/despatch/connote');
			
		
//		if (count($ids)>0) {
//			$this->session->assembly_orders = $ids;
//			$this->session->assembly_returnurl = '/minder/trolley';
//			$this->_redirect('/despatch/connote');
//		} else {
//			$this->_redirect('/trolley/index/toogle_view/false');
//		}
	}	
	
	public function markRevisionsAjaxAction()
	{
		if (isset($this->session->revisions_rows_selected)) {
			$rows_selected = $this->session->revisions_rows_selected;
		} else {
			$rows_selected = array();				
		}
		
		$id = $this->getRequest()->getParam('id', null);

		$jsonObject = new stdClass();
		
		if ($id=='all') {
			if (count($this->session->rows_all_revisions_ids)==count($rows_selected)) {
				$rows_selected = array();					
			} else {
				$rows_selected = $this->session->rows_all_revisions_ids;
			}
		} else if ($id!=null) {
			if (($key = array_search($id, $rows_selected))!==false) {
				unset($rows_selected[$key]);
			} else {
				$rows_selected[] = $id;
			}
		}
		
		$jsonObject->selected = count ($rows_selected); 
		$this->session->revisions_rows_selected = $rows_selected;
		
		die(json_encode($jsonObject));
	}
    
    public function connoteAction() {
        $this->_preProcessNavigation();
        $messenger = $this->_helper->getHelper('FlashMessenger');
        
        $request     = $this->getRequest();
        $rowSelector = $this->_helper->rowSelector;

        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $this->view->linesDataSet = array();
        $this->view->selectedLinesCount = 0;
        $this->view->selectedLines      = array();
        
        $this->view->selectMode         = $rowSelector->getSelectionMode('', $this->view->orderSelectionNamespace, $this->view->orderSelectionActionName);
        
        $this->view->selected_order = $this->session->order_id;

        try {
            $screenBuilder  = new Minder_SysScreen_Builder();
            
            //first setup some order selection for connote screen
            $orderModel = new Minder_SysScreen_Model_OrdersIds();
            $orderModel->setConditions(array('PICK_ORDER.PICK_ORDER = ?' => array($this->session->order_id)));
            $rowSelector->setRowSelection('select_complete', 'true', null, null, $orderModel, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionActionName);
            
            //now get lines for screen            
            $pickItemsModel = new Minder_SysScreen_Model_ConnoteLine();
            $pickItemsModel = $screenBuilder->buildSysScreenModel($this->linesSysScreenName, $pickItemsModel);
            $pickItemsModel->setConditions(array('PICK_ITEM.PICK_ORDER = ? ' => array($this->session->order_id)));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $pickItemsModel, true, $this->view->linesSelectionNamespace, $this->view->linesSelectionActionName);
            $totalRows        = count($pickItemsModel);

            $pickItemsModel->addConditions(array('PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?' => array('DS')));
            $readyToDespTotal = count($pickItemsModel);
            $readyToDespRows  = $pickItemsModel->getItems(0, $readyToDespTotal, true);
            $rowSelector->setRowSelection('select_complete', 'false', null, null, null, false, $this->view->linesSelectionNamespace, $this->view->linesSelectionActionName);
            foreach ($readyToDespRows as $rowId => $row) {
                $rowSelector->setRowSelection($rowId, 'true', null, null, null, false, $this->view->linesSelectionNamespace, $this->view->linesSelectionActionName);
            }
            $pickItemsModel->removeConditions(array('PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?' => array('DS')));

            $this->_postProcessNavigation(array('total' => $totalRows));
            $pageSelector        = $this->view->navigation['pageselector'];
            $showBy              = $this->view->navigation['show_by'];
            if ($totalRows > 0) {
                $this->view->selectedLinesCount  = $rowSelector->getSelectedCount($this->view->linesSelectionNamespace, $this->view->linesSelectionActionName);
                $this->view->selectedLines       = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->linesSelectionNamespace, $this->view->linesSelectionActionName);
                $this->view->readyToDespTotal    = $readyToDespTotal;
                $this->view->readyToDespSelected = count(array_intersect_key($this->view->selectedLines, $readyToDespRows));
                $this->view->linesDataSet        = $pickItemsModel->getItems($pageSelector * $showBy, $showBy, false);
            }
            
            list($this->view->fieldList, $this->view->tabList) = $screenBuilder->buildSysScreenSearchResult($this->linesSysScreenName);
        } catch (Exception $e) {
            $messenger->setNamespace('errors')->addMessage($e->getMessage());
            $this->view->errors[] = $e->getMessage();
        }
        
        $this->view->errors = $messenger->setNamespace('errors')->getMessages();
        $messenger->setNamespace('errors')->clearMessages();
    }

    public function selectRowAction() {
        $this->_preProcessNavigation();
        
        $response = new stdClass();
        $response->selected           = 0;
        $response->errors             = array();
        $response->warnings           = array();
        $response->selectedRows       = array();
        $response->selectionNamespace = 'default';

        $request                      = $this->getRequest();
        $response->selectionNamespace = $selectionNamespace                      = $request->getParam('selection_namespace', 'default');
        $rowId                        = $request->getParam('row_id', 'none');
        $state                        = $request->getParam('state',  'none');
        $pageselector                 = $request->getParam('pageselector', '0');
        $showBy                       = $request->getParam('show_by', 15);
        $selectionMode                = $request->getParam('selection_mode');
        
        $rowSelector  = $this->_helper->rowSelector;
        $count = 0;

        try {
            $actionName = '';
            switch ($selectionNamespace) {
                case $this->view->linesSelectionNamespace: 
                    $actionName = $this->view->linesSelectionActionName;
                    break;
            }
            
            $rowSelector->setSelectionMode($selectionMode, $selectionNamespace, $actionName);
            $rowSelector->setRowSelection($rowId, $state, $pageselector, $showBy, null, false, $selectionNamespace, $actionName);
            
            if ($selectionNamespace == $this->view->linesSelectionNamespace) {
                $totalRows    = $rowSelector->getTotalCount($selectionNamespace, $actionName);
                $selectedRows = $rowSelector->getSelected(0, $totalRows, false, $selectionNamespace, $actionName);
                $badStatusArr = array();
                foreach ($selectedRows as $tmpRrowId => $row) {
                    if ($row['PICK_DETAIL_STATUS'] != 'DS') {
                        $badStatusArr[$row['PICK_DETAIL_STATUS']] = $row['PICK_DETAIL_STATUS'];
                    }
                }
                if (count($badStatusArr) > 0) {
                    $response->warnings[] = "Cannot Despatch Lines with Status = ('" . implode("', '", $badStatusArr) . "')";
                }
                
                $rowsModel = $rowSelector->getModel($selectionNamespace, $actionName);
                $rowsModel->addConditions(array('PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?' => array('DS')));
                $readyForDespTotal              = count($rowsModel);
                $readyForDespRows               = $rowsModel->getItems(0, $readyForDespTotal, true);
                $response->selectedRows         = $selectedRows;
                $response->readyForDespSelected = count(array_intersect_key($readyForDespRows, $selectedRows));
            }
            $response->selected = $rowSelector->getSelectedCount($selectionNamespace);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function printAction() {
        $request            = $this->getRequest();
        $selectionNamespace = $request->getParam('selection_namespace', 'default');
        
        $rowSelector  = $this->_helper->rowSelector;
        $this->view->headers = array();
        $this->view->data    = array();
        
        $actionName = '';
        switch ($selectionNamespace) {
            case $this->view->linesSelectionNamespace: 
                $actionName = $this->view->linesSelectionActionName;
                break;
        }
        $totalRows          = $rowSelector->getTotalCount($selectionNamespace, $actionName);
        $this->view->data   = $rowSelector->getSelected(0, $totalRows, false, $selectionNamespace, $actionName);
        
        if (reset($this->view->data)) {
            $this->view->headers = array_keys(current($this->view->data));
            $this->view->headers = array_combine($this->view->headers, $this->view->headers);
        }
        
        $rowsModel = $rowSelector->getModel($selectionNamespace, $actionName);

        if (isset($this->view->headers[$rowsModel->getPkeyAlias()])) {
            //remove synthetic primary key collumn from report
            unset($this->view->headers[$rowsModel->getPkeyAlias()]);
        }
            
        switch (strtoupper($this->getRequest()->getPost('report_format'))) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('/reports/report-csv');
                return;
                break;

            case 'REPORT: XML':
                $response = $this->getResponse();
                $response->setHeader('Content-type', 'application/octet-stream');
                $response->setHeader('Content-type', 'application/force-download');
                $response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
                $this->render('/reports/report-xml');
                return;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('/reports/report-xls');
                return;

            case 'REPORT: TXT':
                $this->getResponse()->setHeader('Content-Type', 'text/plain')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.txt"');
                $this->render('/reports/report-txt'); 
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
                    $this->render('/reports/report-pdf');
                }
                return;

            default:
                break;
        }
    }


    protected function cancelOrder($orderId, $reason, &$canceledOrders, &$notCanceledOrders) {
//        if (true) {
        if ($this->minder->pickOrderCancel($orderId, $reason)) {
            $canceledOrders[$orderId] = $orderId;
            return true;
        }

        $notCanceledOrders[$orderId] = $orderId;
        return false;
    }

    protected function initUnpickedAssemblyModel($canceledOrders) {
        $builder = new Minder_SysScreen_Builder();
        /**
         * @var Minder_SysScreen_Model_UnpickAssembly $linesModel
         */
        $linesModel = $builder->buildSysScreenModel(self::CANCELED_LINES_MODEL_NAME, new Minder_SysScreen_Model_UnpickAssembly());
        $linesModel->addOrdersLimit($canceledOrders);

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->rowSelector;
        $rowSelector->setRowSelection('select_complete', 'true', null, null, $linesModel, true, self::CANCELED_LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    public function selectUnpickAssemblyRowAction() {
        $result = new Minder_JSResponse();
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
            $pagination = $this->restorePagination(self::CANCELED_LINES_NAMESPACE);

            if (isset($sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator'])) {
                $pagination['selectedPage']  = (isset($sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator']['selectedPage']))  ? $sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator']['selectedPage']  : $pagination['selectedPage'];
                $pagination['showBy']        = (isset($sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator']['showBy']))        ? $sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator']['showBy']        : $pagination['showBy'];
                $pagination['selectionMode'] = (isset($sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator']['selectionMode'])) ? $sysScreens[self::CANCELED_LINES_NAMESPACE]['paginator']['selectionMode'] : $pagination['selectionMode'];
            }

            if (isset($sysScreens[self::CANCELED_LINES_NAMESPACE]['rowId']) && isset($sysScreens[self::CANCELED_LINES_NAMESPACE]['state'])) {
                $rowSelector->setSelectionMode($pagination['selectionMode'], self::CANCELED_LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                $rowSelector->setRowSelection($sysScreens[self::CANCELED_LINES_NAMESPACE]['rowId'], $sysScreens[self::CANCELED_LINES_NAMESPACE]['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, self::CANCELED_LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

            $result->selectedRowsTotal = $rowSelector->getSelectedCount(self::CANCELED_LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            if ($result->selectedRowsTotal > 0) {
                $result->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, self::CANCELED_LINES_NAMESPACE);
                $result->selectedRowsOnPage = count($result->selectedRows);
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function unpickAssemblyReportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');

    }

    public function cancelOrderAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->session->order_id));
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->session->rows_selected));


        if (empty($this->session->order_id) && empty($this->session->rows_selected)) {
            $result->errors[] = 'No Order selected.';
            echo json_encode($result);
            return;
        }

        $reason = $this->getRequest()->getParam('reason');
        if (empty($reason)) {
            $result->errors[] = 'Please, select cancel reason.';
            echo json_encode($result);
            return;
        }

        try {
            $canceledOrders = array();
            $notCanceledOrders = array();
            if (!empty($this->session->order_id))
                if ($this->cancelOrder($this->session->order_id, $reason, $canceledOrders, $notCanceledOrders))
                    unset($this->session->order_id);

            foreach ($this->session->rows_selected as $line) {
                $lineOrderId = $line['order_id'];
                if (isset($canceledOrders[$lineOrderId]) || isset($canceledOrders[$lineOrderId]))
                    continue;

                $this->cancelOrder($lineOrderId, $reason, $canceledOrders, $notCanceledOrders);
            }

            if (count($notCanceledOrders) > 0)
                $result->errors[] = 'Orders: ' . implode(', ', $notCanceledOrders) . ' were not canceled.';

            if (count($canceledOrders) > 0) {
                $result->messages[] = 'Orders: ' . implode(', ', $canceledOrders) . ' were canceled.';
                $result->location = $this->view->url(array('action' => 'index', 'controller' => 'trolley', 'restore_view' => 'true'), null, true);
                $this->session->linesCanceled = true;
                $this->initUnpickedAssemblyModel($canceledOrders);
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }
	
//	protected function _setupShortcuts()
//    {
//        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
//            $shortcuts = array(
//                                '<Assembly>'                => $this->view->url(array('controller' => 'trolley', 'action' => 'index'), null, true),
////                                'View Waiting Despatch'     => $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true),
//                                'Consignment Exit'          => $this->view->url(array('action' => 'awaiting-exit', 'controller' => 'despatch'), null, true),
//                                'Austpost Manifest'         => $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true),
//                                'View Despatched Orders'    => $this->view->url(array('action' => 'despatched-orders', 'controller' => 'despatch'), null, true),
//                                'Despatch Activity Reports' => $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true)
//                         );
//        } else {
//            $shortcuts = array(
//                                'Awaiting Checking  '       =>   $this->view->url(array('action' => 'index', 'controller' => 'despatch-awaiting-checking'), null, true),
////                                'View Waiting Despatch'     => $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true),
//                                'Consignment Exit'          => $this->view->url(array('action' => 'awaiting-exit', 'controller' => 'despatch'), null, true),
//                                'Austpost Manifest'         => $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true),
//                                'View Despatched Orders'    => $this->view->url(array('action' => 'despatched-orders', 'controller' => 'despatch'), null, true),
//                                'Despatch Activity Reports' => $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true)
//                         );
//        }
//
//        $shortcuts['Person Details']                        =   array(
//            'PERSON'                                        =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
//        );
//
//        $this->view->shortcuts = $shortcuts;
//
//        return $this;
//    }
    
    protected function _setupHeaders() {
        
        switch($this->_action) {
            case 'index':
                            $this->view->headers = array(
                                'TIMESTAMP' => 'TIMESTAMP', 
                                'PICK_ORDER' => 'Order #', 
                                'LOCN_ID' => 'Location', 
                                'PROD_ID' => 'PROD_ID', 
                                'SHORT_DESC' => 'SHORT_DESC', 
                                'QTY_PICKED' => 'QTY', 
                                'PREV_WH_ID' => 'From WH', 
                                'PREV_LOCN_ID' => 'From Location'
                            );
                            break;
            default:
        }
    }	
}


?>
