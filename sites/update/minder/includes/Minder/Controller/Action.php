<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 */

/** Function minder_array_merge(). */
require_once 'functions.php';

/**
 * @category  Minder
 * @package   Minder
 * @author    Strelnikov Evgeniy <strelnikov.evgeniy@binary-studio.com@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
abstract class Minder_Controller_Action extends Zend_Controller_Action
{
    /**
    * Amount of pages paginator should show. 
    * 0 - no page limit
    */
    const MAX_PAGINATOR_PAGES = 100;

    const DATA              = 'data';
    CONST TOTAL             = 'total';
    const SHOW_BY           = 'show_by';
    CONST PAGE_SELECTOR     = 'pageselector';
    const SORT_FIELDS       = 'sortFields';

    //actually we don't need Action and Controller names for row selector, namespace is enough, 
    //but to keep back compatibility I will use default Action and Controller names in new code
    //untill this can be safely removed
    public static $defaultSelectionAction     = 'select-row';

    public static $defaultSelectionController = 'service';
    
	protected $_controller;

	protected $_action;

	protected $_showBy = 5;

	protected $_pageSelector = 0;
    
        /**
        * @var Zend_Controller_Action_Helper_Redirector $_redirector
        */
    protected $_redirector = null;

    /**
     * @var Minder
     */
    protected $minder = null;

	public $_licensee;

    /**
     * @var Zend_Session_Namespace
     */
    public $session;

    /**
     * @var Minder_SysScreen_View_Builder
     */
    protected $_modelBuilder;

    /**
     * @var Minder_SysScreen_Builder
     */
    protected $_screenBuilder;

    public function init()
	{
        $this->_helper->addPrefix('Minder_Controller_Action_Helper');
		$this->minder = Minder::getInstance();
        
        $this->minder->setDefaultCompanyId();
        $this->minder->setDefaultWhId();
        
        $this->_redirector = $this->_helper->getHelper('Redirector');
		if ($this->minder->userId == null) {
			$this->_redirector->setCode(303)
							  ->goto('login', 'user', '', array());
			return;
		}

		$this->initView();
		$this->view->addHelperPath(ROOT_DIR . '/includes/helpers/', 'Minder_View_Helper');
		$this->view->minder         = $this->minder;
		$this->view->flashMessenger = $this->_helper->getHelper('flashMessenger');
		
		$this->_initSession('warehouse');
		$this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index'), '', true);

        //$this->view->topMenu = $this->buildTopMenu();
        $this->_setupShortcuts();

        $this->_controller = $this->getRequest()->getControllerName();
		$this->_action     = $this->getRequest()->getActionName();
        
        if(isset($this->session->transactionStatusList)) {
            $this->view->transactionStatusList = $this->session->transactionStatusList;
        }
        
        $session = new Zend_Session_Namespace();
       
        $this->minder->whId              =   $session->whId;
        $this->minder->isAdjustIssn      =   $session->isAdjustIssn;   
        $this->minder->isAdjustPickOrder =   $session->isAdjustPickOrder;
        
       
        $this->view->reportModule     = 'default';
        $this->view->reportController = 'service';
        $this->view->reportAction     = 'report';

        $menuManager = new Minder2_SysMenu_Manager();
        $this->view->renderLimits = $menuManager->getRenderLimitsFlag($this->_getMenuId());

        //set leftPannel state
        $this->view->leftPannelState = 'show';
        if (!$this->view->renderLimits && empty($this->view->shortcuts))
            $this->view->leftPannelState = 'hide';

        if (isset($session->leftPannelState)) {
            $this->view->leftPannelState = $session->leftPannelState;
        }

//        $this->minder->limitCompany = $this->minder->getCompanyListLimited();
//        $this->minder->limitWarehouse = $this->minder->getWarehouseListLimited();

   }

	public function addMessage($message, $area = null)
	{
		return $this->_addMessage($message, 'messages', $area);
	}

	public function addWarning($message, $area = null)
	{
		return $this->_addMessage($message, 'warnings', $area);
	}

	public function addError($message, $area = null)
	{
		return $this->_addMessage($message, 'errors', $area);
	}

	protected function _addMessage($message, $type, $area = null)
	{
		if (!empty($area)) {
			$type = $area . '_' . $type;
		}

        if (is_array($message)) {
            foreach ($message as $singleMessage)
                $this->_helper->flashMessenger->setNamespace($type)->addMessage($singleMessage);
        } else {
            $this->_helper->flashMessenger->setNamespace($type)->addMessage($message);
        }
        
		return $this;
	}

	protected function _preProcessNavigation()
	{
		$controller = $this->_controller;
		$action     = $this->_action;

		if (isset($this->session->navigation[$controller][$action])) {
			foreach ($this->session->navigation[$controller][$action] as $key => $val) {
				if (!is_null($this->getRequest()->getParam($key))) {
					$this->session->navigation[$controller][$action][$key] = (int)$this->getRequest()->getParam($key);
				}
			}
			if (isset($this->session->navigation[$controller][$action]['total']) && $this->session->navigation[$controller][$action]['total'] != 0) {
				$total = $this->session->navigation[$controller][$action]['total'];
				if ($this->session->navigation[$controller][$action]['show_by'] * $this->session->navigation[$controller][$action]['pageselector'] > $total) {
					$this->session->navigation[$controller][$action]['pageselector'] = ceil($total / $this->session->navigation[$controller][$action]['show_by']);
				}
			}
		} else {
			$this->session->navigation[$controller][$action]['show_by']      = $this->_showBy;
			$this->session->navigation[$controller][$action]['pageselector'] = $this->_pageSelector;
			$this->session->navigation[$controller][$action]['total'] = 0;
		}
		return $this;
	}

    /**
     * @param null $action
     * @param null $controller
     *
     * @return array
     * @deprecated
     */
    protected function _getNavigationState($action = null, $controller = null) {
        $action = is_null($action) ? $this->_action : $action;
        $controller = is_null($controller) ? $this->_controller : $controller;

        $navigation = isset($this->session->navigation[$controller][$action]) ?
            $this->session->navigation[$controller][$action] :
            array(
                static::SHOW_BY         => $this->_showBy,
                static::PAGE_SELECTOR   => $this->_pageSelector,
            );

        return array(
            static::SHOW_BY         => isset($navigation[static::SHOW_BY]) ? $navigation[static::SHOW_BY] : $this->_showBy,
            static::PAGE_SELECTOR   => isset($navigation[static::PAGE_SELECTOR]) ? $navigation[static::PAGE_SELECTOR] : $this->_showBy,
        );

    }

	protected function _postProcessNavigation($data)
	{
		$controller = $this->_controller;
		$action = $this->_action;

		if (isset($data[static::TOTAL])) {
			$this->session->navigation[$controller][$action][static::TOTAL] = $this->view->numRecords = $data[static::TOTAL];
		} else {
            $this->session->navigation[$controller][$action][static::TOTAL] = $this->view->numRecords = count($data);
		}

		$this->view->navigation = $this->session->navigation[$controller][$action];

		if (($this->view->navigation['show_by'] * ($this->view->navigation['pageselector'] + 1)) > $this->view->numRecords) {
			// Recount number of pages.
			$this->view->navigation['pageselector'] = $this->session->navigation[$controller][$action]['pageselector']
													= (int) floor($this->view->numRecords / $this->view->navigation['show_by']);
			if (!($this->view->maxno = $this->view->numRecords % $this->view->navigation['show_by']) &&
				$this->view->numRecords > 0) {
				$this->view->navigation['pageselector'] = $this->session->navigation[$controller][$action]['pageselector'] -= 1;
				$this->view->maxno = $this->view->navigation['show_by'];
			}
		} else {
			$this->view->maxno = $this->view->navigation['show_by'];
		}
		
		
        $tmpPagesAmount    = ceil($this->view->numRecords / $this->view->navigation['show_by']);
		$this->view->pages = array(0 => 1);
        if ($tmpPagesAmount > 0) {
            $this->view->pages = range(1, min($tmpPagesAmount, self::MAX_PAGINATOR_PAGES));
        }
        $this->view->navigation['pageselector'] = (int)$this->view->navigation['pageselector'];
        return $this;
	}

	protected function _makeConditions($allowed, $action = null, $controller = null)
	{
        if (!is_string($action)) {
            $action = $this->_action;
        }
        if (!is_string($controller)) {
            $controller = $this->_controller;
        }
        if (!isset($this->session->conditions[$controller][$action])) {
            $this->session->conditions[$controller][$action] = array();
        }

        $allowed = array_change_key_case($allowed, CASE_UPPER);
        foreach ($this->_getAllParams() as $key => $value) {
            $key = strtoupper($key);
            $value = is_array($value) ? array_map('trim', $value) : trim($value);
            if (array_key_exists($key, $allowed)) {
                if (empty($value) && !is_numeric($value)) {
                    unset($this->session->conditions[$controller][$action][$key]);
                } else {
                    $this->session->conditions[$controller][$action][$key] = $value;
                }
            }
        }
        return array_intersect_key($this->session->conditions[$controller][$action], $allowed);
	}

	protected function _getConditions($action = null, $controller = null)
	{
		if (!is_string($action)) {
			$action = $this->_action;
		}
		if (!is_string($controller)) {
			$controller = $this->_controller;
		}
		if (!isset($this->session->conditions[$controller][$action])) {
			$this->session->conditions[$controller][$action] = array();
		}
		return $this->session->conditions[$controller][$action];
	}

	protected function _setConditions($conditions, $action = null, $controller = null)
	{
		if (!is_string($action)) {
			$action = $this->_action;
		}
		if (!is_string($controller)) {
			$controller = $this->_controller;
		}
		$this->session->conditions[$controller][$action] = $conditions;
		return $this;
	}

	/**
	 * Make clause for query.
	 * @param array $conditions Conditions that will be used in the search.
	 * @param array $allowed Array of allowed fields.
	 * @return array
	 */
	protected function _makeClause($conditions, $allowed, $searchInputs = null)
	{
        $clause = array();

        if (empty($searchInputs)) {
            $searchInputs = array();
        }

		foreach ($conditions as $key => $val) {
			if ($key != 1 && array_key_exists($key, $allowed)) {
                foreach($searchInputs as $input) {
                    if ($key == $input['SSV_NAME']) {
                        $inputParser = new Minder_Page_FormBuilder_InputMethodParcer();
                        $parseResult = $inputParser->parse($input['TYPE']);
                        if ($parseResult->inputMethod == Minder_Page_FormBuilder_InputMethod::INPUT) {
                            if (!empty($parseResult->wildcardType)) {
                                $tmpval = (empty($val)) ? '' : $this->addWildcardsToLikeSearchParams($val, $parseResult->wildcardType);
                                $val = $tmpval;
                                break;
                            }
                        }
                    }
                }

				if (null != $val && !empty($allowed[$key])) {
					if($this->minder->isNewDateCalculation() == true){
						if($this->minder->isValidDate($val)){
							if(strlen($val) == 19){
								$allowed[$key] = str_ireplace("ZEROTIME", "", $allowed[$key]);
								$allowed[$key] = str_ireplace("MAXTIME", "", $allowed[$key]);
								$val = $this->minder->getFormatedDateToDb($val);
							}
							else{
								$val = $this->minder->getFormatedDateToDb($val, "", false);
							}
				        }
        			}
               	    $clause[strtoupper($allowed[$key])] = $val;
				}
			}
		}
		return $clause;
	}

	/**
	 * Saves user search conditions in session and return them.
	 *
	 * @param string $action Action name
	 * @param array $allowed Array of allowed fields.
	 * @return array
	 */
	protected function _setupConditions($action = null, array $allowed = null)
	{
		$controller = $this->_controller;
		$action = $action == null ? $this->_action : $action;

		if (isset($this->session->conditions[$controller][$action])) {
			$conditions = $this->session->conditions[$controller][$action];
		} else {
			$conditions = array();
		}
		if (null != $allowed) {
			foreach ($this->getRequest()->getParams() as $key => $val) {
				if (array_key_exists($key, $allowed)) {
					/*if(DateTime::createFromFormat('Y-m-d H:i:s', $val)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val)!==FALSE) {
		                $val = $this->minder->getFormatedDateToDb($val, "", false);
		            }*/ 
					$conditions[$key] = $val;
				}
			}
		} else {
			foreach ($this->getRequest()->getParams() as $key => $val) {
				/*if(DateTime::createFromFormat('Y-m-d H:i:s', $val)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val)!==FALSE) {
		            $val = $this->minder->getFormatedDateToDb($val, "", false);
		        }*/
				$conditions[$key] = $val;
			}
		}
		$this->session->conditions[$controller][$action] = $this->view->conditions = $conditions;
		return $conditions;
	}

	/**
	 * Process REPORT: TO actions.
	 * @return boolean
	 */
	protected function _processReportTo($action = null)
	{
		
	   if (null === $action) {
			$action = strtoupper($this->getRequest()->getPost('action'));
	   }
	  
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
			
			case 'REPORT: GD':
				$this->getResponse()->setHeader('Content-Type', 'text/csv')
									->setHeader('Content-Disposition', 'attachment; filename="report-gd.csv"');
				$this->render('report-gd');
				
				return true;
			default:
				return false;
		}
	}

	protected function _setupHeaders()
	{
		$this->view->tableId = 'table';
		if (!isset($this->session->headers[$this->_controller][$this->_action][$this->view->tableId])) {
			return false;
		} else {
			$this->view->headers = $this->session->headers[$this->_controller][$this->_action][$this->view->tableId];
			return true;
		}
	}

	protected function _initSession($namespace)
	{
		if (!Zend_Session::namespaceIsset($namespace)) {
			$this->session = new Zend_Session_Namespace($namespace);
			$session = $this->_getEvnSession($namespace);
			foreach ($session as $key => $val) {
				$this->session->$key = $val;
			}
		} else {
			$this->session = new Zend_Session_Namespace($namespace);
		}
		return $this;
	}

	protected function _getEvnSession($name)
	{
		try {
			$result = $this->minder->getEnvSession($this->minder->userId, $name);
			if (empty($result)) {
				throw new Exception('Invalid user environment session in database');
			} else {
				//$result = array_values($result);
				$result = unserialize($result['PARAM_VALUE']);
			}
		} catch (Exception $e) {
			$this->minder->createEnvSession($this->minder->userId,
											$name,
											serialize($this->session->getIterator()));
			$result = array();
		}
		return $result;
	}

	protected function _saveEnvSession($name)
	{
		if (!$this->session instanceof Zend_Session_Namespace) {
			throw new Exception('Session is not instance of class Zend_Session_Namespace');
		}
		$iterator = $this->session->getIterator();
		return $this->minder->saveEnvSession($this->minder->userId,
											 $name,
											 serialize($this->session->getIterator()));
	}

	/**
	 * Provide functionality to mark/unmark lines.
	 *
	 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
	 * @copyright 2007 Barcoding & Data Collection Systems
	 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
	 * @link      http://www.barcoding.com.au/
	 *
	 * @param array  $lines  array of lines
	 * @param string $id     id to be changed state (allowed 'select_all' | 'select_complete' | value_of_item).
	 * @param string $method method used to process (allowed states 'true' | 'false' | 'init').
     * 
     * @deprecated use _markSelected2(). All data is filtered alredy or should be filtered by viewing page, 
     *             so using of array_slice leads to incorrect behaviour
	 */
	protected function _markSelected($lines, $id, $value, $method = 'init', $action = null)
	{
		if (null == $action) {
			$action = $this->_action;
		}

		$numRecords = count($lines);
		$conditions = isset($this->session->conditions[$this->_controller][$action]) ? $this->session->conditions[$this->_controller][$action] : array() ;
		//-- mark all rows in whole table as selected
		if ('select_complete' == $id) {
			if ('true' == $method) {
				foreach ($lines as $line) {
                    if(isset($line->id)){
                        $conditions[$line->id] = $line->id;
                    } else {
                        $conditions[$line['RECORD_ID']] =   $line['RECORD_ID'];
                    }
				}
			} elseif ('false' == $method) {
				foreach ($lines as $line) {
                    if(isset($line->id)){
                        unset($conditions[$line->id]);
                    } else {
                        unset($conditions[$line['RECORD_ID']]);
                    }
				}
			}
		} elseif ('select_all' == $id) {
			if (($this->session->navigation[$this->_controller][$action]['show_by'] * $this->session->navigation[$this->_controller][$action]['pageselector']) > $numRecords) {
				$maxno = $numRecords - ($this->session->navigation[$this->_controller][$action]['show_by'] * $this->session->navigation[$this->_controller][$action]['pageselector']);
			} else {
				$maxno = $this->session->navigation[$this->_controller][$action]['show_by'];
			}
			if ('true' == $method) {
				foreach(array_slice($lines,
									$this->session->navigation[$this->_controller][$action]['show_by'] *
									$this->session->navigation[$this->_controller][$action]['pageselector'], $maxno) as $line) {
					if(isset($line->id)){
                        $conditions[$line->id] = $line->id;     
                    } else {
                        $conditions[$line['RECORD_ID']] =   $line['RECORD_ID'];   
                    }
                    
				}
			} elseif ('false' == $method) {
				foreach(array_slice($lines,
									$this->session->navigation[$this->_controller][$action]['show_by'] *
									$this->session->navigation[$this->_controller][$action]['pageselector'], $maxno) as $line) {
                    if(isset($line->id)){
                        unset($conditions[$line->id]);    
                    } else {
                        unset($conditions[$line['RECORD_ID']]);
                    }
				}
			}
		} else {
			if ('true' == $method) {
				$conditions[$id] = $value == null ? $id: $value;
			} elseif ('false' == $method) {
				unset($conditions[$id]);
			}
		}
		$this->session->conditions[$this->_controller][$action] = $conditions;
		return $conditions;
	}

    /**
     * Provide functionality to mark/unmark lines.
     *
     * @param array  $lines  - array of lines
     * @param string $id     - id to be changed state (allowed 'select_all' | 'select_complete' | value_of_item).
     * @param mixed  $value  - value to save in array instead of $id
     * @param string $method - method used to process (allowed states 'true' | 'false' | 'init').
     * @param string $action - action name to use for saving marked data
     */
    protected function _markSelected2($lines, $id, $value, $method = 'init', $action = null)
    {
        if (null == $action) {
            $action = $this->_action;
        }

        $numRecords = count($lines);
        $conditions = isset($this->session->conditions[$this->_controller][$action]) ? $this->session->conditions[$this->_controller][$action] : array() ;
        //-- mark all rows in whole table as selected
        if ('select_complete' == $id) {
            //todo: doesn't work for now, as all data filtered by page, think how to bypass this
            if ('true' == $method) {
                foreach ($lines as $line) {
                    $conditions[$line->id] = $line->id;
                }
            } elseif ('false' == $method) {
                foreach ($lines as $line) {
                    unset($conditions[$line->id]);
                }
            }
        } elseif ('select_all' == $id) {
            if ('true' == $method) {
                foreach($lines as $line) {
                    if(isset($line->id)){
                        $conditions[$line->id] = $line->id;     
                    } else {
                        $conditions[$line['RECORD_ID']] =   $line['RECORD_ID'];   
                    }
                    
                }
            } elseif ('false' == $method) {
                foreach($lines as $line) {
                    if(isset($line->id)){
                        unset($conditions[$line->id]);    
                    } else {
                        unset($conditions[$line['RECORD_ID']]);
                    }
                }
            }
        } else {
            if ('true' == $method) {
                $conditions[$id] = $value == null ? $id: $value;
            } elseif ('false' == $method) {
                unset($conditions[$id]);
            }
        }
        $this->session->conditions[$this->_controller][$action] = $conditions;
        return $conditions;
    }

	/**
	 * Setup menu shortcuts.
	 * @return Minder_Controller_Action Provides a fluent interface.
	 */
	protected function _setupShortcuts()
    {
        $manager = new Minder_SysMenuManager();

        // save old ADMIN module shortcuts building method
        if ($this->_getMenuId() == 'ADMIN') {
            $this->view->shortcuts = Minder_Navigation_Array::getShortcuts('admin');
            $this->view->tooltip   = Minder_Navigation_Array::getTooltips('admin');
        } else {
            $this->view->shortcuts = $manager->getNavigation(Minder_SysMenuManager::MENU_TYPE_LEFT, $this->_getMenuId());
        }

        $this->view->topMenu = $manager->getNavigation(Minder_SysMenuManager::MENU_TYPE_TOP,  $this->_getMenuId());
    }

    protected function _getMenuId() {
        $this->session->menuId = isset($this->session->menuId) ? $this->session->menuId : '';
        return $this->session->menuId = $this->getRequest()->getParam('menuId', $this->session->menuId);
    }

    /**
     * Provide functionality to fill form fields after submit data.
     *
     * @author    Dmitriy Suhinin <dmitriy.suhinin@binary-studio.com>
     * @copyright 2007 Barcoding & Data Collection Systems
     * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
     * @link      http://www.barcoding.com.au/
     *
     * @param array  $formFields  array fields name with data
     */
    protected function _fillFormFields($fillAll = true, array $fieldNames = null) {
    
        $params = $this->getRequest()->getParams();
        foreach($params as $fieldName => $fieldValue) {
            $explodeName= explode('_', $fieldName);
            
            if(count($explodeName) > 1) {
                $newFieldName = '';
                $newFieldName.= $explodeName[0];
                for($i=1; $i<count($explodeName); $i++) {
                    $newFieldName .= ucwords($explodeName[$i]);
                }
            } else {
                $newFieldName = $fieldName;
            }
           
            if(!$fillAll && in_array($fieldName, $fieldNames)) {
                $this->view->$newFieldName = $fieldValue;
            } elseif($fillAll) {
              $this->view->$newFieldName = $fieldValue;      
            }
        }
    }
    
    protected function _saveSearchedValue($inputs, $conditions){
        
        
        foreach($conditions as $key => $value){
            foreach($inputs as &$input){
                if($input['SSV_NAME'] == $key){
                    $input['SEARCH_PARAM']  =   $value;    
                }
            }
        }
        
        return  $inputs;      
    }

    /**
    * Save and restore values for DL fields in search form
    * 
    * @param array   $inputs       - array of search input fields
    * @param array   $allowed      - array of allowed conditions
    * @param string  $controller   - controller name
    * @param string  $action       - action name 
    * @param boolean $thisIsSearch - flag either this is search action or not
    */
    protected function _saveSearchedDLValue($inputs, $allowed, $controller = NULL, $action = NULL, $thisIsSearch = TRUE) {
        if (NULL === $action)
            $action = $this->_action;
            
        if (NULL === $controller)
            $controller = $this->_controller;

        if (!isset($this->session->conditions[$controller][$action])) {
            $this->session->conditions[$controller][$action] = array();
        }

        $request = $this->getRequest();
        
        foreach ($inputs as &$input) {
            $tmpInputMethod = explode('|', $input['TYPE']);
            if ($tmpInputMethod[0] == 'DL') {
                $tmpName = $tmpInputMethod[0].'_'.$tmpInputMethod[1];
                
                if ($thisIsSearch) {
                    $input['SEARCH_PARAM']  = $tmpValue = $request->getParam($tmpName);
                    $input['SEARCH_PARAM1']             = $request->getParam($tmpValue);
                } else {
                    $input['SEARCH_PARAM']  = $tmpValue = '';
                    $input['SEARCH_PARAM1']             = '';
                    if (isset($this->session->conditions[$controller][$action][$tmpName])) {
                        $input['SEARCH_PARAM']  = $tmpValue = $this->session->conditions[$controller][$action][$tmpName];
                        
                        if (isset($this->session->conditions[$controller][$action][$tmpValue])) {
                            $input['SEARCH_PARAM1']         = $this->session->conditions[$controller][$action][$tmpValue];
                        }
                    }
                }
                
                $this->session->conditions[$controller][$action][$tmpName]  = $tmpValue;
                $this->session->conditions[$controller][$action][$tmpValue] = $input['SEARCH_PARAM1'];
                
                foreach ($input['VALUES'] as $key => $value) {
                    if (isset($allowed[$key]) && ($key != $tmpValue))
                        unset($allowed[$key]);
                        
                    if (isset($this->session->conditions[$controller][$action][$key]) && ($key != $tmpValue))
                        unset($this->session->conditions[$controller][$action][$key]);
                }
            }
        }
        
        return array($inputs, $allowed);
    }
    
    /**
    * @deprecated
    */
    public function ajaxBuildDataset($namespace, $pageselector, $showBy, $paginator = array(), $fetchRows = true, $fetchSelectedRows = true) {
        $this->view->dataset      = array();
        $this->view->selectedRows = array();
        $this->view->paginator    = array(
            'totalRows'     => 0,
            'selectedRows'  => 0,
            'pages'         => 0,
            'maxPages'      => self::MAX_PAGINATOR_PAGES,
            'selectedPage'  => 0,
            'showBy'        => 5,
            'selectionMode' => 'all',
            'shownFrom'     => 0,
            'shownTill'     => 0,
            'totalOnPage'   => 0,
            'selectedOnPage' => 0,
            'sortTabId'     => '',
            'sortFields'    => array(),
            'sortList'      => array()
        );
        $this->view->errors    = isset($this->view->errors)?   $this->view->errors   : array();
        $this->view->warnings  = isset($this->view->warnings)? $this->view->warnings : array();
        $this->view->messages  = isset($this->view->messages)? $this->view->messages : array();
        $this->view->paginator = array_merge($this->view->paginator, $paginator);
        
        try {
            if (empty($namespace)) {
                $this->view->errors[] = __CLASS__ . ':' . __METHOD__ .  ' error: "namespace" is not defined.';
                return;
            }
            
            $this->view->paginator['selectedPage'] = $pageselector;
            $this->view->paginator['showBy']       = $showBy;
            
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->getHelper('RowSelector');

            $dataModel                              = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (empty($dataModel)) {
                throw new Minder_Exception("Model " . self::$defaultSelectionController . ':' . self::$defaultSelectionAction . ':' . $namespace . ' is not found.');
            }

            $dataModel->setCustomOrderFields($this->view->paginator['sortFields']);
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $dataModel, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->view->paginator['selectionMode'] = $rowSelector->getSelectionMode($this->view->paginator['selectionMode'], $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->paginator['totalRows']     = $rowSelector->getTotalCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $this->view->paginator['pages']             = ceil($this->view->paginator['totalRows'] / $this->view->paginator['showBy']);
            $this->view->paginator['selectedPage']      = min($this->view->paginator['selectedPage'], max(0, $this->view->paginator['pages'] - 1));
            $this->view->paginator['shownFrom']         = 0;
            $this->view->paginator['shownTill']         = 0;
            $this->view->paginator['totalOnPage']       = 0;
            $this->view->paginator['selectedRows']      = 0;
            $this->view->paginator['selectedOnPage']    = 0;

            if ($this->view->paginator['totalRows'] > 0) {

                $rowOffset = $this->view->paginator['selectedPage'] * $this->view->paginator['showBy'];

                $this->view->paginator['shownFrom'] = $rowOffset + 1;
                $this->view->paginator['shownTill'] = $rowOffset + min($this->view->paginator['showBy'], $this->view->paginator['totalRows'] - $rowOffset);

                if ($fetchRows)
                    $this->view->dataset = $dataModel->getItems($rowOffset, $this->view->paginator['showBy']);
                $this->view->paginator['totalOnPage'] = count($this->view->dataset);
                
                $this->view->paginator['selectedRows'] = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                
                if ($this->view->paginator['selectedRows'] > 0) {
                    if ($fetchSelectedRows) {
                        if ($fetchRows) {
                            $this->view->selectedRows = $rowSelector->filterSelectedRows($this->view->dataset, $namespace);
                        } else {
                            $this->view->selectedRows = $rowSelector->getSelectedOnPage($this->view->paginator['selectedPage'], $showBy, false, $namespace);
                        }
                    }
                    $this->view->paginator['selectedOnPage'] = count($this->view->selectedRows);
                }
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this;
    }

    protected function _buildDatatset($namespaceMap, $screensToBuild = null) {
        $modelMap = array_flip($namespaceMap);
        $sysScreens = is_null($screensToBuild) ? $this->getRequest()->getParam('sysScreens', array()) : $screensToBuild;
        $result = array();

        $disabledRelations = $this->getRequest()->getParam('disabledRelations', $this->_loadDisabledRelations());
        $this->_storeDisabledRelations($disabledRelations);
        $this->_masterSlaveHelper()->initSubDatasets($modelMap, null, $disabledRelations, self::$defaultSelectionAction, self::$defaultSelectionController);

        foreach ($sysScreens as $namespace => $data) {
            if (in_array($namespace, $namespaceMap)) {
                $screenDefaults = $this->_getScreenBuilder()->getSysScreenDescription($modelMap[$namespace]);

                $pagination = $this->restorePagination($namespace, $screenDefaults);
                if (isset($sysScreens[$namespace])) {
                    $pagination = $this->fillPagination($pagination, $sysScreens[$namespace]);
                }

                $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
                $pagination = array_merge($pagination, $this->view->paginator);
                $this->savePagination($namespace, $pagination);
                $result[$namespace] = $this->view->jsSearchResultDataset($modelMap[$namespace], $this->view->dataset, $this->view->selectedRows, $pagination);

                unset($this->view->paginator);
                unset($this->view->dataset);
                unset($this->view->selectedRows);
            }
        }

        return $result;
    }

    public function restorePagination($namespace, $sysScreenDefaults = array()) {
        $tmpPagination = $this->session->pagination;

        if (isset($tmpPagination[$namespace])) {
            return  $tmpPagination[$namespace];
        } else {
            return array(
                'selectedPage' => 0,
                'showBy' => (isset($sysScreenDefaults['SS_VIEW_BY_VALUE']) ? $sysScreenDefaults['SS_VIEW_BY_VALUE'] : 5),
            );
        }
    }

    public function fillPagination($pagination, $sysScreen) {
        $result = $pagination;
        if (isset($sysScreen['paginator'])) {
            $result['selectedPage'] = (isset($sysScreen['paginator']['selectedPage'])) ? $sysScreen['paginator']['selectedPage'] : $pagination['selectedPage'];
            $result['showBy']       = (isset($sysScreen['paginator']['showBy']))       ? $sysScreen['paginator']['showBy']       : $pagination['showBy'];
            $result['sortTabId']    = (isset($sysScreen['paginator']['sortTabId']))    ? $sysScreen['paginator']['sortTabId']    : $pagination['sortTabId'];
            $result['sortFields']   = (isset($sysScreen['paginator']['sortFields']))   ? $sysScreen['paginator']['sortFields']   : $pagination['sortFields'];
            $result['sortList']     = (isset($sysScreen['paginator']['sortList']))     ? $sysScreen['paginator']['sortList']     : $pagination['sortList'];
        }

        return $result;
    }
    
    public function savePagination($namespace, $pagination) {
        $tmpPagination             = $this->session->pagination;
        $tmpPagination[$namespace] = $pagination;
        $this->session->pagination = $tmpPagination;
        return $this;
    }

    protected function addWildcardsToLikeSearchParams($originalValue, $wildcardType = null) {
        if (!isset($wildcardType)) {
            return '%' . trim($originalValue, '%') . '%';
        }
        else {
            switch($wildcardType) {
                case Minder_Page_FormBuilder_InputMethod::WILDCARD_OFF:
                    return $originalValue;
                    break;

                case Minder_Page_FormBuilder_InputMethod::WILDCARD_RIGHT:
                    return rtrim($originalValue, '%') . '%';
                    break;

                case Minder_Page_FormBuilder_InputMethod::WILDCARD_LEFT:
                    return '%' . ltrim($originalValue, '%');
                    break;

                case Minder_Page_FormBuilder_InputMethod::WILDCARD_BOTH:
                    return '%' . trim($originalValue, '%'). '%';
                    break;

                default:
                    return '%' . trim($originalValue, '%'). '%';
            }
        }
    }

    /**
     *
     * @return Minder_Controller_Action_Helper_Cache
     */
    protected function cache() {
        return $this->_helper->getHelper('Cache');
    }

    /**
     * @return Minder_Controller_Action_Helper_RowSelector
     */
    protected function _rowSelector() {
        return $this->getHelper('RowSelector');
    }

    /**
     * @return Minder_Controller_Action_Helper_MasterSlave
     */
    protected function _masterSlaveHelper() {
        return $this->getHelper('MasterSlave');
    }

    /**
     * @return Minder_Controller_Action_Helper_JsSearchResultBuilder
     */
    protected function _getJsSearchResultBuilder() {
        return $this->getHelper('JsSearchResultBuilder');
    }

    protected function _getScreenBuilder() {
        if (empty($this->_screenBuilder)) {
            $this->_screenBuilder = new Minder_SysScreen_Builder();
        }
        return $this->_screenBuilder;
    }

    protected function _getModelBuilder() {
        if (empty($this->_modelBuilder)) {
            $this->_modelBuilder = new Minder_SysScreen_View_Builder();
        }
        return $this->_modelBuilder;
    }

    protected function _storeDisabledRelations($disableRelations) {
        $this->session->disabledRelations = $disableRelations;
    }

    protected function _loadDisabledRelations() {
        return isset($this->session->disabledRelations) ? $this->session->disabledRelations : array();
    }

    /**
     * @param \Zend_View_Interface $view
     * @return string|Minder_Controller_Action_Helper_DatasetToJson
     */
    protected function _datasetToJson(Zend_View_Interface $view = null) {
        /**
         * @var Minder_Controller_Action_Helper_DatasetToJson $helper
         */
        $helper = $this->getHelper('DatasetToJson');
        return $helper->datasetToJson($view);
    }

    /**
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    protected function _viewRenderer() {
        return $this->getHelper('viewRenderer');
    }

    /**
     * @return Minder_Controller_Action_Helper_SearchKeeper
     */
    protected function _searchHelper() {
        return $this->getHelper('SearchKeeper');
    }

    /**
     * @return Minder_Controller_Action_Helper_CarrierPack
     */
    protected function _carrierPack() {
        return $this->getHelper('CarrierPack');
    }

    protected function _initViewMessagesContainers() {
        $this->view->errors     = isset($this->view->errors)   ? $this->view->errors   : array();
        $this->view->warnings   = isset($this->view->warnings) ? $this->view->warnings : array();
        $this->view->messages   = isset($this->view->messages) ? $this->view->messages : array();

    }

    protected function _copyMessagesToView(Minder_JSResponse $response) {
        $this->view->errors     = array_merge($this->view->errors, $response->errors);
        $this->view->warnings   = array_merge($this->view->warnings, $response->warnings);
        $this->view->messages   = array_merge($this->view->messages, $response->messages);
    }

    /**
     * @return Minder_Controller_Action_Helper_ExportTo
     */
    protected function _exportHelper() {
        return $this->getHelper('ExportTo');
    }

    /**
     * @return Minder_Controller_Action_Helper_MinderOptions
     */
    protected function _minderOptions() {
        return $this->getHelper('MinderOptions');
    }

    /**
     * @return Minder_Controller_Action_Helper_PrintLabel
     */
    protected function _printLabelHelper() {
        return $this->_helper->getHelper('PrintLabel');
    }

    /**
     * @return Minder_Controller_Action_Helper_Company
     */
    protected function _companyHelper() {
        return $this->_helper->getHelper('Company');
    }

    /**
     * @return Minder_Controller_Action_Helper_ParamManager
     */
    protected function _paramMangerHelper() {
        return $this->getHelper('ParamManager');
    }
}
