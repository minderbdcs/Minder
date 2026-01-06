<?php
/**
 * UserController
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Minder
 *
 * Action controller
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class UserController extends Minder_Controller_Action
{
    /**
     * Initialize class. Get minder instance.
     *
     * @return void
     */
    public function init()
    {
        $this->_helper->addPrefix('Minder_Controller_Action_Helper');
        $this->minder = Minder::getInstance();
        $this->initView();
        $this->view->minder = $this->minder;
        $manager               = new Minder_SysMenuManager();
        $this->view->topMenu   = $manager->getNavigation(Minder_SysMenuManager::MENU_TYPE_TOP,  $this->_getMenuId());
        $this->view->shortcuts = array(
            'Logout' => $this->view->url(array('controller' => 'user',
                                               'action' => 'logout'), '', true),
            'Logout All' => $this->view->url(array('controller' => 'user',
                                                   'action' => 'logout-all'), '', true));
        $this->view->baseUrl = $this->view->url(array('action' => 'index',
                                                      'controller' => 'index'), '', true);
    }

    /**
     * User/Login action
     * if success write userId & deviceId in cookie
     *
     * @return void
     */
    public function loginAction()
    {
    	
/*        $config = Zend_Registry::get('config');
        $dsns = $config->database->dsn->toArray();
        $this->view->dsnList = array_combine(array_keys($dsns), array_keys($dsns));*/
        $this->view->userId = '';
        $this->view->password = '';
        $this->view->pageTitle = 'User Login';
        if ($this->_request->isPost()) {
        	
        	$userId = $this->_request->getPost('userId', null);
        	$password = $this->_request->getPost('password', null);
        	$objSession = new Zend_Session_Namespace();
            $objSession->BrowserTimeZone = $this->_request->getPost('time_zone');

			if ($this->_login($userId, $password)==true) {
                $this->cache()->clearAll();
				$redirector = $this->_helper->getHelper('Redirector');
                $redirector->setCode(303)
                           ->goto('index', 'index', null, array());
			}
        }
        
    }

	public function loginajaxAction()
	{
		$jsonObject = new stdClass();
		
		$raw_code = $this->getRequest()->getParam('barcode');
		$barcode = new Barcode($raw_code, $this->minder);
		
		if ($barcode->getData_id()=='LOGON') {
			$creds = explode('|', $barcode->getRest());
			if ($this->_login($creds[0],$creds[1]) == true) {
                $this->cache()->clearAll();
				if ($this->minder->isAdmin) {
					$jsonObject->url = '/minder/index/index';	
					$session->isAdmin = false;
				} else {
					$jsonObject->url = '/minder/otc';					 
				}
			} else {
				$jsonObject->error = 'Wrong login/password';
			}
		}
		
		die(json_encode($jsonObject));
	}

	    public function setutctimeAction(){

		   $utc = $this->_request->getPost('utc', null);

		                $session                        = new Zend_Session_Namespace();
		                $session->BrowserTimeZone               = $utc;

		                echo $session->BrowserTimeZone;
	    }

	private function _login($userId, $password)
    {
            $this->view->userId = $userId;

            if (null !== $userId && null !== $password) {
                try {
                    
                    if (true === $this->minder->login($userId, $password, $this->_request->getServer('REMOTE_ADDR'))) {
                        $session                        = new Zend_Session_Namespace();
                        $session->isAdmin               = $this->minder->isAdmin;
                        $session->isInventoryOperator   = $this->minder->isInventoryOperator;
                        $session->isStockAdjust         = $this->minder->isStockAdjust;
                        $session->isAdjustIssn          = $this->minder->isAdjustIssn;
                        $session->isAdjustPickOrder     = $this->minder->isAdjustPickOrder;
                        $session->isEditable            = $this->minder->isEditable;
                        $session->limitCompany          = 'all';
                        $session->limitWarehouse        = 'all';
                        $session->userId                = $this->minder->userId;
                        $session->deviceId              = $this->minder->deviceId;
                        $session->whId                  = $this->minder->whId;
                        
                     
                        return true;
                    }
                        
                } catch(Exception $ex){
                    $this->addError($ex->getMessage());    
                } 
                
            }        
        
    }
    
    
    /**
     * Display currently logged in user
     *
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * User/Logout action
     * get
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->cache()->clearAll();
        $session = new Zend_Session_Namespace();
        if (isset($session->userId)) {
            $this->minder->logout();
            Zend_Session::destroy();
        }

        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->setCode(303)
                   ->goto('index', 'index', null, array());
    }

    /**
     * User/Login action
     *
     * @return void
     */
    public function logoutAllAction()
    {
        $this->cache()->clearAll();
        $this->view->loggedOut = false;

        $isAdmin = false;
        if ($this->minder->userId !== null) {
            $isAdmin = $this->minder->isAdmin;
        }
       
        $userId   = $this->_request->getPost('userId', null);
        $password = $this->_request->getPost('password', null);
       
        if ($userId !== null && $password !== null) {
            $isAdmin = $this->minder->validateAdministrator($userId, $password);
        }

        if ($isAdmin) {
            $session = new Zend_Session_Namespace();
            if (isset($session->userId)) {
                Zend_Session::destroy();
            }
            
            try {
                $this->minder->logoutAll();
            } catch(Exception $ex){
                $this->addError($ex->getMessage());    
            }

            $redirector = $this->_helper->getHelper('Redirector');
            $redirector->setCode(303)
                       ->goto('index', 'index', null, array());
        }
    }

    /**
     * Set
     *
     * @return void
     */
    public function limitAction()
    {
        $companyId  = $this->_request->getParam('company', null);
        $whId       = $this->_request->getParam('warehouse', null);
        $printerId  = $this->_request->getParam('printer', null);

        if ($companyId !== null) {
            Minder2_Environment::setCompanyLimit($companyId);
        }
        if ($whId !== null) {
            Minder2_Environment::setWarehouseLimit($whId);
        }
        if ($printerId !== null) {
            Minder2_Environment::setCurrentPrinter($printerId);
        }
    }

        
    public function logoAction()
    {
    	$this->getResponse()->setHeader('Content-type', 'image-x-jpg');
    	$logo = $this->minder->getLogo();
    	echo $logo;
    	
    }  
    
    /**
    * Get or set left pannel state for current session
    * 
    */
    public function ajaxLeftPannelStateAction()
    {
        $request = $this->getRequest();
        $action  = strtolower($request->getParam('pannelAction'));
        $state   = strtolower($request->getParam('state'));
        
        if (is_null($action)) {
            $action = 'get';
        }
        
        if (is_null($state)) {
            $state = 'show';
        }
        
        $session = new Zend_Session_Namespace();
//        var_dump(array('file' => __FILE__, 'line' => __LINE__, 'left_pannel_state' =>$session->leftPannelState));
        if (isset($session->left_pannel_state)) {
            $sessionState = $session->leftPannelState;
        } else {
            $sessionState = $session->leftPannelState = $state;
        }
        
//        var_dump(array('file' => __FILE__, 'line' => __LINE__, '$sessionState' =>$sessionState, 'left_pannel_state' =>$this->session->navigation[$this->_controller][$this->_action]['left_pannel_state']));
        
        switch ($action) {
            case 'get':
                $state = $sessionState;
                break;
            case 'switch':
                    $state = ($state == 'show')?'hide':'show';
                break;
            default:
                $this->addError("Bad parametr 'action' = '$action' in ".$this->_controller."::".$this->_action);
        }
        
        $session->leftPannelState = $state;
//        var_dump(array('file' => __FILE__, 'line' => __LINE__, '$sessionState' =>$state, 'left_pannel_state' =>$session->leftPannelState));
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode(array('state' => $state, 'errors' => $this->_helper->flashMessenger->setNamespace('errors')->getCurrentMessages()));
        $this->_helper->flashMessenger->setNamespace('errors')->clearCurrentMessages();
    }
    
    public function ajaxCallFastFillAction()
    {
        $request = $this->getRequest();
        
        $recordId  = $request->getParam('record_id', 'undefined');
        $personId  = $request->getParam('PERSON_ID', null);
        $method    = $request->getParam('status', 'list');
        $value     = $request->getParam('selected_value', 'undefined');
        
        $conditions = array();
        if ($personId !== null) {
            $conditions['PERSON_ID'] = $personId;
        }
        
        $wasErrors = false;
        
        if ($this->minder->userId == null) {
            $this->addError('Error. Not logged in.');
            $wasErrors = true;
        }

        if ($recordId == 'undefined') {
            $this->addError('Error. RECORD_ID is undefined');
            $wasErrors = true;
        }

        $data = array();
            
        if (!$wasErrors) {
            $input = $this->minder->getSingleEditInput($recordId, $method, $conditions, false);
            
            if (empty($input)) {
                $this->addError("Error. SYS_SCREEN_VAR with RECORD_ID #'$recordId' was not found.");
            } else {
                $input = $this->minder->getDropDownExInfo($input, $conditions, false, $value);
                $data  = $input['MAPPED_FIELDS'];
            }
        }
        
        $this->_helper->viewRenderer->setNoRender();

        echo json_encode(array('result' => $data, 'errors' => $this->_helper->flashMessenger->setNamespace('errors')->getCurrentMessages()));
        $this->_helper->flashMessenger->setNamespace('errors')->clearCurrentMessages();
    }

    public function pingAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        echo 'OK';
    }
}
