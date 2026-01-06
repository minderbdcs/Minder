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
 * @todo      refactoring code  
 *
 */

/**
 * Function ( minder_array_merge() );
 */
include "functions.php";


class AdminController extends Minder_Controller_Action
{
	public function init() {
		
		parent::init();
	 
		$this->_initSession('admin');

		if (false == $this->minder->isAdmin) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setCode(303)->goto('index', 'index', '', array());
			return;
		}

		$this->initView();
		$this->view->addHelperPath(ROOT_DIR . '/includes/helpers/', 'Minder_View_Helper');
		$this->view->minder         = $this->minder;
		$this->view->flashMessenger = $this->_helper->getHelper('flashMessenger');
		$this->view->licensee       = $this->minder->getCompanyName();
	}


	/**
	 * Enter description here...
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->pageTitle = 'Administration';
	}



	/**
	 * Prepares list of contacts.
	 * Allows admin to search, add, delete contacts
	 *
	 * @return void
	 */
	public function contactsAction() {
		
		$this->view->pageTitle  = 'Contacts';
		$this->view->conditions = array();

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

		//-- setup conditions
		$conditions = array();
		switch ($this->getRequest()->getPost('action')) {
		  case 'SEARCH':
			  foreach ($this->getRequest()->getParams() as $key => $val) {
				  switch ($key) {
					  case 'person_id_srch':
							$conditions[$key] = $val;
							break;
					  case 'first_name_srch':
						  $conditions[$key] = $val;
						  break;
					  case 'last_name_srch':
						  $conditions[$key] = $val;
						  break;
					  case 'contact_first_name_srch':
						  $conditions[$key] = $val;
						  break;
					  case 'contact_last_name_srch':
						  $conditions[$key] = $val;
						  break;
					  case 'company_id_srch':
						  $conditions[$key] = $val;
						  break;
					  case 'person_type_srch':
						  $conditions[$key] = $val;
						  break;
					  default:
						  break;
				  }
			  }
			  $this->session->conditionsContact = $conditions;
			  break;
		  
		  case 'ADD CONTACT':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-add', 'admin', 'default');
			  break;
		 
		  case 'DELETE CONTACT':
				$personToDelete = $this->getRequest()->getPost('person_id');
				if (count($personToDelete) > 0) {
					foreach($personToDelete as $val) {
						if ($val != 'on') {
							$currentResult = $this->minder->personDelete($val);
						 
							if (false == $currentResult) {
								$this->addMessage($val . ' delete failed - ' . $this->minder->lastError);
							} else {
								$this->addMessage($val . ' - successfully deleted ');
							}
						}
					}
				}
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contacts', 'admin', 'default');
			  break;
		  
		  default:
			  break;
		}
		if (isset($this->session->conditionsContact)) {
			$conditions = $this->session->conditionsContact;
		}
		$this->view->conditions = $conditions;
		//-- End setup conditions

		//-- convert screen filter conditions to Minder acceptable format for preparing query
		$clause = array();
		if (array_key_exists('person_id_srch', $conditions)) {
			$clause['PERSON.PERSON_ID']= $conditions['person_id_srch'];
		}
		if (array_key_exists('first_name_srch', $conditions)) {
			$clause['PERSON.FIRST_NAME']= $conditions['first_name_srch'];
		}
		if (array_key_exists('last_name_srch', $conditions)) {
			$clause['PERSON.LAST_NAME']= $conditions['last_name_srch'];
		}
		if (array_key_exists('contact_first_name_srch', $conditions)) {
			$clause['PERSON.CONTACT_FIRST_NAME']= $conditions['contact_first_name_srch'];
		}
		if (array_key_exists('contact_last_name_srch', $conditions)) {
			$clause['PERSON.CONTACT_LAST_NAME']= $conditions['contact_last_name_srch'];
		}
		if (array_key_exists('company_id_srch', $conditions)) {
			$clause['PERSON.COMPANY_ID']= $conditions['company_id_srch'];
		}
		if (array_key_exists('person_type_srch', $conditions)) {
			$clause['PERSON.PERSON_TYPE']= $conditions['person_type_srch'];
		}
		//-- end conversion

		$this->view->headers    = array('PERSON_ID'          => 'Person ID',
										'FIRST_NAME'         => 'Name/First',
										'LAST_NAME'          => 'Name/Last',
										'COMPANY_ID'         => 'Company ID',
										'CONTACT_FIRST_NAME' => 'Contact/First',
										'CONTACT_LAST_NAME'  => 'Contact/Last',
										'PERSON_TYPE'        => 'Type'
									   );

		$this->view->contactFirstNameList = minder_array_merge(array('' => ''), $this->minder->getFieldListFromPerson('PERSON.CONTACT_FIRST_NAME'));
		$this->view->contactLastNameList  = minder_array_merge(array('' => ''), $this->minder->getFieldListFromPerson('PERSON.CONTACT_LAST_NAME'));
		$this->view->contactTypeList      = minder_array_merge(array('' => ''), $this->minder->getPersonTypeList());
		$this->view->companyIdList        = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

		$this->view->contacts             = $this->minder->getContacts($clause);

		$this->view->numRecords  = count($this->view->contacts);

		//-- @todo: code need a tunning for logic
		//-- post process navigation
		if ($this->view->startItem > count($this->view->contacts)) {
			$this->view->startItem = count($this->view->contacts) - $this->view->showBy;
		}
		if ($this->view->startItem < 0) {
			$this->view->startItem = 0;
		}
		if (($this->view->startItem + $this->view->showBy) > count($this->view->contacts)) {
			$this->view->maxno = count($this->view->contacts) - $this->view->startItem;
		} else {
			$this->view->maxno = $this->view->showBy;
		}
		//-- end post process

		$this->view->numRecords = count($this->view->contacts);
		$this->view->pages      = array();
		for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->showBy); $i++) {
			$this->view->pages[] = $i;
		}
		$this->view->contacts = array_slice($this->view->contacts, $this->view->startItem, $this->view->maxno);
	}

	/**
	 * Show addressed owned by selected contact.
	 * Allows admin to add, delete addresses.
	 *
	 * @return void
	 */
	public function contactAddressesAction() {
		
		$this->view->pageTitle  = 'Contacts';
		$this->view->conditions = array();
		$this->view->personObj  = $this->minder->getContacts(array('PERSON_ID' => $this->getRequest()->getParam('edit_person_id')));
		$this->view->personObj  = $this->view->personObj[0];

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
		$params = array('edit_person_id' => $this->view->personObj->items['PERSON_ID']);
		
		switch ($this->getRequest()->getPost('action')) {
			case 'EDIT CONTACT':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-edit', 'admin', 'default',$params);
				return;
				break;
			
			case 'ADD ADDRESS':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-add-address', 'admin', 'default', $params);
				break;
			
			case 'RETURN':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contacts', 'admin', 'default');
				return;
				break;
			
			case 'DELETE ADDRESS':
				$addressToDelete = $this->getRequest()->getPost('record_id');
				if (count($addressToDelete) > 0) {
					foreach($addressToDelete as $val) {
						if ($val != 'on') {
							$currentResult = $this->minder->personAddressDelete($val);
							if (false == $currentResult) {
								$this->addMessage($val . ' delete failed - ' . $this->minder->lastError);
							} else {
								$this->addMessage($val . ' - successfully deleted ');
							}
							$this->_redirector = $this->_helper->getHelper('Redirector');
							$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);

						}
					}
				}
				break;
			
			default:
			  break;
		}

		$this->view->headers = array('RECORD_ID'    => 'Addr#',
									 'ADDR_TYPE'    => 'Address Type',
									 'ADDR_LINE1'   => 'Address Line 1',
									 'ADDR_LINE2'   => 'Address Line 2',
									 'ADDR_SUBURB'  => 'Suburb',
									 'ADDR_CITY'    => 'City',
									 'ADDR_STATE'   => 'State',
									 'ADDR_COUNTRY' => 'Country'
									);

		$this->view->addresses      = $this->minder->getAddressLines(array('PERSON_ID' =>$this->view->personObj->items['PERSON_ID']));
		$this->view->numRecords     = count($this->view->addresses);

		//-- @todo: code need a tunning for logic
		if ($this->view->startItem > count($this->view->addresses)) {
			$this->view->startItem = count($this->view->addresses) - $this->view->showBy;
		}
		if ($this->view->startItem < 0) {
			$this->view->startItem = 0;
		}
		if (($this->view->startItem + $this->view->showBy) > count($this->view->addresses)) {
			$this->view->maxno = count($this->view->addresses) - $this->view->startItem;
		} else {
			$this->view->maxno = $this->view->showBy;
		}

		$this->view->numRecords = count($this->view->addresses);
		$this->view->pages      = array();
		for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->showBy); $i++) {
			$this->view->pages[] = $i;
		}
		$this->view->addresses = array_slice($this->view->addresses, $this->view->startItem, $this->view->maxno);
	}

	/**
	 * Allow edit contact
	 *
	 * @return void
	 */
	public function contactEditAction() {
		
		$this->view->pageTitle = 'Edit';
		$this->view->personObj = $this->minder->getContacts(array('PERSON_ID' => $this->getRequest()->getParam('edit_person_id')));
		$this->view->personObj = $this->view->personObj[0];
		
		$params = array('edit_person_id' => $this->view->personObj->items['PERSON_ID']);
		
		switch ($this->getRequest()->getPost('action')) {
			case 'CANCEL CHANGES':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);
			 break;
			
			case 'SAVE & RETURN':
				$this->view->personObj->save($this->getRequest()->getPost());
				if (false === $this->minder->personUpdate($this->view->personObj)) {

				}
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);
				break;
			
			case 'ADD ADDRESS':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-add-address', 'admin', 'default', $params);
				break;
			
			default:
			 break;
		}

		$this->view->companyIdList      = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
		$this->view->contactStatusList  = $this->minder->getPersonStatusList();
		$this->view->contactTypeList    = minder_array_merge(array('' => ''), $this->minder->getPersonTypeList());
	}

	/**
	 * Allow add new address to contact
	 *
	 * @return void
	 */
	public function contactAddAddressAction(){
		
		$this->view->pageTitle  = 'Add:';
		$this->view->pageTitle  = 'ADDRESS:';
		$this->view->addressObj = new AddressLine();
		$params = array('edit_record_id' => $this->view->addressObj->items['RECORD_ID'],
						'edit_person_id' => $this->getRequest()->getParam('edit_person_id')
					   );
		
		switch ($this->getRequest()->getPost('action')) {
			case 'CANCEL CHANGES':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);
			   break;
			
			case 'SAVE & RETURN':
				$this->view->addressObj->save($this->getRequest()->getPost());
				if (false === $this->minder->personAddressAdd($this->view->addressObj)) {
				}
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);
				break;
			
			default:
			break;
		}
		$this->view->addressObj->items['PERSON_ID']     = $this->getRequest()->getParam('edit_person_id');
		$this->view->addressTypeList                    = $this->minder->getAddressTypeList();
		$this->view->addressStatusList                  = $this->minder->getAddressStatusList();
		$this->view->companyIdList                      = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
	}

	/**
	 * Allow add new contact
	 *
	 * @return void
	 */
	public function contactAddAction() {
		
		$this->view->pageTitle = 'Add:';
		if (null != $this->session->personObj) {
			$this->view->personObj      = $this->session->personObj;
			$this->session->personObj   = null;
		} else {
			$this->view->personObj = new ContactLine();
		}
		$this->view->companyIdList      = $this->minder->getCompanyList();
		$this->view->contactStatusList  = $this->minder->getPersonStatusList();
		$this->view->contactTypeList    = minder_array_merge(array('' => ''), $this->minder->getPersonTypeList());
		
		switch ($this->getRequest()->getPost('action')) {
			case 'CANCEL CHANGES':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contacts', 'admin', 'default');
			   break;
			
			case 'SAVE & RETURN':
				$checkPersonObj = $this->minder->getContacts(array('PERSON_ID' => $this->getRequest()->getParam('person_id')));
				$this->view->personObj->save($this->getRequest()->getPost());
				
				if (count($checkPersonObj) > 0 ) {
					$this->addMessage('Contact with PERSON ID = ' . $this->view->personObj->items['PERSON_ID'] . ' already exists.');
					$this->session->personObj = $this->view->personObj;

					$this->_redirector = $this->_helper->getHelper('Redirector');
					$this->_redirector->setCode(303)->goto('contact-add', 'admin', 'default');

				} else {
					if (false === $this->minder->personAdd($this->view->personObj)) {
							$this->addMessage($this->view->personObj->itemds['PERSON_ID'] . ' - not added' . "\n" . $this->minder->lastError);
					}
					$this->_redirector = $this->_helper->getHelper('Redirector');
					$this->_redirector->setCode(303)->goto('contacts', 'admin', 'default');
				}
				break;
			
			default:
			break;
		}

	}

	/**
	 * Allow edit address
	 *
	 * @return void
	 */
	public function contactEditAddressAction() {
		
		$this->view->pageTitle  = 'ADDRESS:';
		$this->view->addressObj = $this->minder->getAddressLines(array('PERSON_ADDRESS.RECORD_ID' => $this->getRequest()->getParam('edit_record_id')));
		$this->view->addressObj         = $this->view->addressObj[0];
		$this->view->addressTypeList    = $this->minder->getAddressTypeList();
		$this->view->addressStatusList  = $this->minder->getAddressStatusList();
		$this->view->companyIdList      = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
		
		$params = array('edit_person_id' => $this->getRequest()->getParam('person_id'));
		
		switch ($this->getRequest()->getPost('action')) {
			case 'CANCEL CHANGES':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);
			   break;
			
			case 'SAVE & RETURN':
				$this->view->addressObj->save($this->getRequest()->getPost());
				if (false === $this->minder->personAddressUpdate($this->view->addressObj)) {

				}
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('contact-addresses', 'admin', 'default', $params);
				break;
			
			default:
				break;
		}
	}

	/**
	 * Allow edit CONTROL table
	 *
	 * @return void
	 */
	public function controlEditAction() {}

	/**
	 * Allow user to manually run ADD_TRAN_RESPONSE()
	 *
	 * @return void
	 */
	public function testTransactionAction(){
		
		$this->view->pageTitle = 'TEST TRANSACTION';

		$form       = $this->_getTestTransactionForm();
		$error_text = '';
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$trans = new Transaction_Unified($form->getValue('trn_type'),
												 $form->getValue('trn_code'),
												 $form->getValue('object_id'),
												 $form->getValue('locn_id'),
												 $form->getValue('wh_id'),
												 $form->getValue('reference'),
												 $form->getValue('qty'),
												 $form->getValue('sub_location'));

				if (false != $this->minder->doTransactionResponse($trans,
													 'Y',
													 $form->getValue('input_source'),
													 $form->getValue('trn_date'),
													 $form->getValue('instance'))) {
				}
				$error_text = $this->minder->lastError;
				$form->populate(array('error_text' => $error_text,
									  'record_id' => $trans->getObjectId()
									 )
								);

			}
		} else {
			$date = new Zend_Date();
			$defaultValues = array('instance_id'  => 'MASTER  ',
								   'person_id'    => $this->minder->userId,
								   'device_id'    => $this->minder->deviceId,
								   'qty'          => '1',
								   'input_source' => 'SSBSSKSSS',
								   'error_text'   => $error_text,
								   'trn_date'     => $date->toString('YYYY-MM-DD HH:mm:ss')
								  );
			$form->populate($defaultValues);

		}

		$this->view->form = $form;
	}

	/**
	 * Allow import data from Clipboard into selected table
	 * \n and \t used as delimiter for row and fields accordingly
	 *
	 * @return void
	 */
	public function importClipboardAction() {
		
		if ($this->getRequest()->getParam('refresh') != '') {
			$data   = $this->minder->getFieldList($this->getRequest()->getParam('table'));
			$unique = $this->minder->getUniqueConstraint($this->getRequest()->getParam('table'));
			if (is_array($unique)) {
				foreach ($unique as $val) {
					$data[$val] = '*' . $val;
				}
			}
			$this->view->data = $data;
			$this->render('import-fieldlist');
		
		} elseif ($this->getRequest()->getParam('table') != '') {
			$table      = $this->getRequest()->getParam('table');
			$value      = $this->getRequest()->getParam('value');
			$list       = $this->getRequest()->getParam('list');

			$headers = array();
			foreach (explode('&', $list) as $pair) {
				list($key, $val) = explode('=', $pair);
				$headers[$key] = $key;
			}
		   
			if (isset($this->session->fileUploaded) && $this->session->fileUploaded) {
				$fh = fopen($this->session->fileUploaded, 'r');
				if ($fh) {
					while (($tmpdata = fgetcsv($fh, 10240, ','))) {
						$data[] = $tmpdata;
					}
					fclose($fh);
					@unlink($this->session->fileUploaded);
					$this->session->fileUploaded = false;
				} else {
					$data = array();
				}
			} else {
				
				$value = str_replace("\r", "\n", $value);
				$value = str_replace("\n\n", "\n", $value);
			 
				$data = array();
				$lines = explode("\n", $value);
				foreach ($lines as $row) {
					if (trim($row) != '') {
						$data[] = explode(chr(9), $row);
					}
				}
			}
			
			if ($data != null) {
				$countDataSegments  = count($data[0]);
				$countHeaders       = count($headers);
				if ($countDataSegments == $countHeaders) {
					$pKeys = $this->minder->getUniqueConstraint($table);
					if (false === $pKeys) {
						$pKeys = array();
					}
					if (!count(array_intersect_key($pKeys, $headers))) {
						$response['status']     = false;
						$response['message']    = 'Primary key ' . (count($pKeys) ?  '(' . implode(', ', $pKeys) . ') ' : '') . 'is not found in the fields list.';
					} else {
						$this->session->import['headers'] = $headers;
						$this->session->import['data']    = $data;
						$this->session->import['table']   = $table;
						$response['status']  = true;
						$response['message'] = 'Submit successful';
					}
				} else {
					$response['status']  = false;
					$response['message'] = 'Number of headers ' . $countHeaders . ' not equal to calculated number of fields = ' . $countDataSegments . ' in imported data';
				}
			} else {
				$response['status']  = false;
				$response['message'] = 'Nothing to import';
			}
			$this->_helper->viewRenderer->setNoRender();
			echo json_encode($response);
			return;
		} elseif ($this->getRequest()->getParam('clear') == 'true') {
			$this->session->import = null;
			$this->_helper->json(array('status' => true, 'message' => 'The data were cleaned.'));
		} elseif ($this->getRequest()->getParam('import') == 'true') {
			if (isset($this->session->import['data']) && isset($this->session->import['headers']) && isset($this->session->import['table'])) {

				$this->_clearCache($this->session->import['table']);

				$response['status']  = $this->minder->importFromClipboard($this->session->import);
				$response['message'] = $this->minder->lastError;
			 } else {
				$response['status']  = false;
				$response['message'] = 'Nothing for import';
			}
			$this->_helper->viewRenderer->setNoRender();
			echo json_encode($response);
			return;
		} elseif ($this->getRequest()->getParam('sample') == 'true') {
			$this->view->data    = $this->session->import['data'];
			$this->view->headers = $this->session->import['headers'];
			$this->view->table   = $this->session->import['table'];
			$this->render('import-sample');
		} else {
   
		}
		$this->view->tableList = $this->minder->getTableList();
	}

	public function importClipboardFileUploadAction() {
		
		$this->fileUpload();
	}

	private function fileUpload() {

		$fileElementName = 'csvfile';
		$msg             = '';

		if(!empty($_FILES[$fileElementName]['error'])) {
			switch($_FILES[$fileElementName]['error']) {
					case '1':
						$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
					break;
					
					case '2':
						$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
					
					case '3':
						$error = 'The uploaded file was only partially uploaded';
					break;
					
					case '4':
						$error = 'No file was uploaded.';
					break;
					
					case '6':
						$error = 'Missing a temporary folder';
					break;
					
					case '7':
						$error = 'Failed to write file to disk';
					break;
					
					case '8':
						$error = 'File upload stopped by extension';
					break;
					
					case '999':
					default:
						$error = 'No error code avaiable';
					}
		} elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
			$error = 'No file was uploaded.';
		} else {
			$error = '';
			$msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
			$msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);
	
			$tempDir = $this->minder->isWinOs()?'C:/':'/tmp/';
			$this->session->fileUploaded = $tempDir . uniqid();
			move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $this->session->fileUploaded);
		}
		$this->_helper->viewRenderer->setNoRender();
		$data['error'] = $error;
		$data['msg']   = $msg;
		$log = Zend_Registry::get('logger');
		$log->info(json_encode($data));
		echo json_encode($data);
		return;
	 }
	
	/**
	 * Provide PHPINFO for Admin
	 *
	 */
	public function phpinfoAction() {
		
		phpinfo();
	}

	public function crudAction() {
		
	
		$mode = 'NONE';

		$this->view->tableName = $this->view->pageTitle = $table = strtoupper($this->getRequest()->getParam('table'));
		if ('' == $table) {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setCode(303)->goto('index', 'admin', 'default');
		}

		if (!isset($this->session->table[$table]['rowNumber'])) {
			$this->view->rowNumber = $this->session->table[$table]['rowNumber'] = 1;
		} else {
			$this->view->rowNumber = $this->session->table[$table]['rowNumber'];
		}

		if (null != $this->getRequest()->getParam('row_number')) {
			$this->view->rowNumber = (int)$this->getRequest()->getParam('row_number');
		}





		// set mode for screen (ADD, EDIT and LOAD_EDIT is allowed)
		$mode    = strtoupper($this->getRequest()->getParam('mode'));
		$allowed = $this->_getAllowed($table);
		
		switch (strtoupper($this->getRequest()->getPost('action'))) {
			case 'PREV.':
			case 'NEXT':
			case 'LAST':
			case 'FIRST':
				$mode = 'LOAD_EDIT';
				break;

			case 'SEARCH':
			
			 //   ST_AUDIT_DATE ST_EXPORTED_DATE
				$search_array=$this->getRequest()->getParams();
	 
				
				if($this->minder->isNewDateCalculation() == false){
				  $session = new Zend_Session_Namespace();
				  $tz_from=$session->BrowserTimeZone;

				   foreach($search_array as $key=>$value){

				      /*if(DateTime::createFromFormat('Y-m-d H:i:s', $value)!== FALSE  || DateTime::createFromFormat('Y-m-d',$value)!==FALSE) {*/
				      if($this->minder->isValidDate($value)) {

				        /*$dt = new DateTime($value, new DateTimeZone($tz_from));
				        $dt->setTimeZone(new DateTimeZone('UTC'));
				        $utc=$dt->format('Y-m-d h:i:s') ;
				        $search_array[$key] = $utc;*/
				                             
				        $search_array[$key] = $this->minder->getFormatedDateToDb($value ,"d.m.Y,")." 00:00:00.000";
				      }
				    }
				}

				$conditions = $this->_getConditions($table, $search_array, $allowed);
				$this->view->conditions=$conditions;
				 
				break;



			case 'SAVE':
			case 'CANCEL':
				if ($mode != 'SAVENEW' && $mode != 'ADD') {}
				break;
		}
		$this->view->mode       = $mode;
		$this->_preProcessNavigation($table);
		$conditions = $this->_getConditions($table, array(), $allowed);
		
		$clause = $this->_makeClause($conditions[$table], $allowed);

		if($this->minder->isNewDateCalculation() == false){
		  if(isset($clause["UPPER(ST_AUDIT_DATE) LIKE ?"])){
		    $strKey = "cast( extract(day from ST_AUDIT_DATE)||'.'|| extract(month from ST_AUDIT_DATE)||'.'|| extract(year from ST_AUDIT_DATE) as date) = ?";
		    $clause[$strKey] = $clause["UPPER(ST_AUDIT_DATE) LIKE ?"];
		    unset($clause["UPPER(ST_AUDIT_DATE) LIKE ?"]);
		  }
		  if(isset($clause["UPPER(ST_APPLIED_DATE) LIKE ?"])){
		    $strKey = "cast( extract(day from ST_APPLIED_DATE)||'.'|| extract(month from ST_APPLIED_DATE)||'.'|| extract(year from ST_APPLIED_DATE) as date) = ?";
		    $clause[$strKey] = $clause["UPPER(ST_APPLIED_DATE) LIKE ?"];
		    unset($clause["UPPER(ST_APPLIED_DATE) LIKE ?"]);
		  }
		  if(isset($clause["UPPER(ST_EXPORTED_DATE) LIKE ?"])){
		    $strKey = "cast( extract(day from ST_EXPORTED_DATE)||'.'|| extract(month from ST_EXPORTED_DATE)||'.'|| extract(year from ST_EXPORTED_DATE) as date) = ?";
		    $clause[$strKey] = $clause["UPPER(ST_EXPORTED_DATE) LIKE ?"];
		    unset($clause["UPPER(ST_EXPORTED_DATE) LIKE ?"]);
		  }
		}

		$clause = $clause + array('_limit_'  => $this->view->navigation[$table]['show_by'] ,
								  '_offset_' => $this->view->navigation[$table]['show_by'] * $this->view->navigation[$table]['pageselector']);

		
		$clause  = $this->addedAdditionalConditions($clause);


		$order   = $this->getOrdering($table);
		$dataset = $this->minder->getMasterTableDataSet($table, array('*'), $clause, $order);

		if (!$dataset) {
			$this->addError('Error while execute SQL');
			$this->view->noTable = true;
			
			$clause = array('_limit_'  => $this->view->navigation[$table]['show_by'] ,
							'_offset_' => $this->view->navigation[$table]['show_by'] * $this->view->navigation[$table]['pageselector']);
			$clause = $this->addedAdditionalConditions($clause);
			$dataset= $this->minder->getMasterTableDataSet($table, array('*'), $clause, $order);
		}

		$arrParmToReLoadDataAfterSave = array($table, array('*'), $clause, $order);

            $session = new Zend_Session_Namespace();
 			$tz_to=$session->BrowserTimeZone;

		foreach($conditions as $key=>$value){

			if($key==$table){
			 foreach($value as $key1=>$value1){

						     if(strtotime($value1)===FALSE){ }else{


		  								$dt = new DateTime($value1, new DateTimeZone('UTC'));
		                                $tz = new DateTimeZone($tz_to); 
		                                $dt->setTimezone($tz);
		                                $val=$dt->format('Y-m-d H:i:s');						                     
	                    				//$conditions[$key][$key1]=$val;


						    }

			}			    
                    
		}

	}



		$this->view->conditions = $conditions;
		
		$this->view->noTable      = false;
		$this->view->numRecords   = $this->session->table[$table]['total'] = $dataset->total();
		$this->view->skipped      = $dataset->skipped();
		$this->view->formId       = 'frm_' . $this->view->tableId = $table;
		$this->view->selectables  = $this->_setupSelectables($table);
		$this->view->radiobuttons = $this->_setupSelectables($table);

		$this->_postProcessNavigation($table);



		$this->view->fieldList = $dataset->getFields();
		
		$headers = $this->_setupHeaders($dataset);

		if(!empty($headers)){
			$this->view->headers = $headers;
			$this->view->dataset = $dataset;
			$this->view->row     = $dataset->current();    
		} else {
			$this->addError('No headers for ' . $dataset->table . ' table');
			
			$this->view->dataset = null;
			$this->view->row     = null;
		}
		

		switch ($mode) {          
			case 'EDIT':
				$recordId                                  = $this->getRequest()->getParam('record_id');
				$this->view->row                           = $dataset->getRecord($recordId);
				$this->session->table[$table]['rowNumber'] = $dataset->getRowNumberByRecordId($recordId);
				$this->view->rowNumber                     = $this->session->table[$table]['rowNumber'];
				$this->view->loadEdit = false;
				$this->render('ajaxform-crud-edit');
				return;
				break;
			
			case 'LOAD_EDIT':
				$recordId = $dataset->getRecordIdByRowNumber($this->view->rowNumber);
				$this->view->row = $dataset->getRecord($recordId);
				$this->view->loadEdit = true;
				break;
			
			case 'ADD':
				$this->view->row = $dataset->getNewRecord();
				$this->render('ajaxform-crud-edit');
				return;
				break;
			
			case 'SAVENEW':
				$this->view->row = $dataset->getNewRecord();
				break;
			
			default:


				if (null != $this->getRequest()->getParam('record_id')) {
					$recordId                                  = $this->getRequest()->getParam('record_id');
				  
					$this->view->row                           = $dataset->getRecord($recordId);
					$this->session->table[$table]['rowNumber'] = $dataset->getRowNumberByRecordId($recordId);
					$this->view->rowNumber                     = $this->session->table[$table]['rowNumber'];
				}
				$this->view->loadEdit = false;
				break;
		}

		switch (strtoupper($this->getRequest()->getPost('action'))) {
			case 'FIRST':
				$this->_saveData($table, $dataset);

				$dataset= $this->minder->getMasterTableDataSet(
					$arrParmToReLoadDataAfterSave[0],
					$arrParmToReLoadDataAfterSave[1],
					$arrParmToReLoadDataAfterSave[2],
					$arrParmToReLoadDataAfterSave[3]
				);
				$this->view->dataset = $dataset;

				$params = array();
				$this->view->rowNumber = 1;
				$this->session->table[$table]['rowNumber'] = $this->view->rowNumber;

				$this->view->navigation[$table]['pageselector'] = 0;

				$this->_refreshData($table, $allowed);

				break;

			case 'PREV.':
				$this->_saveData($table, $dataset);

				$dataset= $this->minder->getMasterTableDataSet(
					$arrParmToReLoadDataAfterSave[0],
					$arrParmToReLoadDataAfterSave[1],
					$arrParmToReLoadDataAfterSave[2],
					$arrParmToReLoadDataAfterSave[3]
				);
				$this->view->dataset = $dataset;

				$params = array();

				$this->view->rowNumber--;

				if ($this->view->rowNumber < 1 ) {
					$this->view->rowNumber = 1;
				}

				$this->session->table[$table]['rowNumber'] = $this->view->rowNumber;

				if (ceil($this->view->rowNumber / $this->view->navigation[$table]['show_by']) < $this->view->navigation[$table]['pageselector'] + 1) {

					$this->view->navigation[$table]['pageselector']--;
					$this->_refreshData($table, $allowed);

				}

				else {
					$recordId = $dataset->getRecordIdByRowNumber($this->view->rowNumber);
					$this->view->row = $dataset->getRecord($recordId);
				}

				break;

			case 'NEXT':
				$this->_saveData($table, $dataset);

				$dataset= $this->minder->getMasterTableDataSet(
					$arrParmToReLoadDataAfterSave[0],
					$arrParmToReLoadDataAfterSave[1],
					$arrParmToReLoadDataAfterSave[2],
					$arrParmToReLoadDataAfterSave[3]
				);
				$this->view->dataset = $dataset;

				$params = array();
				$this->view->rowNumber++;

				if ($this->view->rowNumber > $this->session->table[$table]['total'] ) {
					$this->view->rowNumber = $this->session->table[$table]['total'];
				}

				if ($this->view->rowNumber > $this->view->navigation[$table]['show_by'] * ($this->view->navigation[$table]['pageselector'] + 1)) {
					$this->view->navigation[$table]['pageselector']++;

					$this->_refreshData($table, $allowed);
				}

				else {
					$recordId = $dataset->getRecordIdByRowNumber($this->view->rowNumber);
					$this->view->row = $dataset->getRecord($recordId);
				}
				
				break;

			case 'LAST':
				$this->_saveData($table, $dataset);

				$dataset= $this->minder->getMasterTableDataSet(
					$arrParmToReLoadDataAfterSave[0],
					$arrParmToReLoadDataAfterSave[1],
					$arrParmToReLoadDataAfterSave[2],
					$arrParmToReLoadDataAfterSave[3]
				);
				$this->view->dataset = $dataset;

				$params = array();
				$sb = $this->view->navigation[$table]['show_by'];

				$this->view->rowNumber = $this->session->table[$table]['total'];
				$this->view->navigation[$table]['pageselector'] = ceil($this->session->table[$table]['total'] / $sb);

				$this->session->table[$table]['rowNumber'] = $this->view->rowNumber;

				// selecting new data to dataset
				$this->_refreshData($table, $allowed, 1);

				// round to the lower value
				$this->view->navigation[$table]['pageselector'] = floor($this->session->table[$table]['total'] / $sb);


				break;

			case 'SAVE':
				$this->_saveData($table, $dataset);

				$dataset= $this->minder->getMasterTableDataSet(
					$arrParmToReLoadDataAfterSave[0],
					$arrParmToReLoadDataAfterSave[1],
					$arrParmToReLoadDataAfterSave[2],
					$arrParmToReLoadDataAfterSave[3]
				);
				$this->view->dataset = $dataset;

				break;
			
			case 'CANCEL':
				$this->_redirector = $this->_helper->getHelper('Redirector');
				$this->_redirector->setCode(303)->goto('index', 'admin', 'default');
				break;
			
			case 'ADD':
				break;
			
			case 'REPORT: XLS':
			case 'REPORT: CSV':
				$data = $dataset->getRawData();
				if (is_array($records = $this->getRequest()->getPost('records'))) {
					foreach ($records as &$val) {
						$val = html_entity_decode($val);
					}
					$records = array_flip($records);
					foreach ($data as $key => $val) {
						if (!array_key_exists($key, $records)) {
							unset($data[$key]);
						}
					}
				} else {
					$full = $clause;
					unset($full['_limit_']);
					unset($full['_offset_']);
					$dataset = $this->minder->getMasterTableDataSet($table, array('*'), $full);
					$data    = $dataset->getRawData();
				}
				$this->_viewRenderer()->setNoRender(true);
				$this->_exportHelper()->exportTo(strtoupper($this->getRequest()->getPost('action')), array_flip(array_keys($dataset->getFields())), $data);
				return;

			case 'DELETE':
				if (($records = $this->_request->getPost('records')) && is_array($records)) {

					$this->_clearCache($table);

					foreach ($records as $id) {
						$id = stripslashes($id);
						if (isset($dataset->$id)) {
							$dataset->getRecord($id)->isDeleted(true);
						}
					}
					if ($this->minder->updateMasterTableDataSet($dataset)) {
						$this->addMessage('Record(s) deleted successfully.');
					} else {
						$this->addMessage($this->minder->lastError);
					}
				}
				$this->_redirect($this->view->url(), array('prependBase' => false));
				break;
				
			case 'CLEAR SLOTTING':
				
				$records = $this->getRequest()->getPost('records');
				if(is_array($records)) {
					foreach($records as $prodId) {
						$locnId = $this->minder->clearProductSlotting($prodId);
						if(!is_null($locnId)) {
							$this->minder->clearLocationSlotting($locnId);
						}
					}
				}
				
				$this->addMessage('Clear Slotting is complete');
				break;
			
			case 'PRINT LOCATION':
			case 'PRINT BORROWER':
			case 'PRINT LOGON':
			case 'PRINT PRODUCT':
			case 'PRINT LABEL':
			case 'PRINT SSCC':
			case 'PRINT PACK LABEL':
			case 'PRINT COST CENTRE':
				$action     = strtoupper($this->getRequest()->getPost('action'));
				$records  	= $this->getRequest()->getPost('records');	

	 		$data     	= $dataset->getRawData();
	
		$result   	= false;
			
				if(!empty($records)) {
					$printLabelHelper = $this->_printLabelHelper();
					$count    = 0;
$check_arr=array();
$i=0;
$message_disp='';
					foreach($data as $id => $values){
						if(in_array($id, $records)) {



							try{
								
								switch ($action) {
/*********************************************/

			case 'PRINT BORROWER':

				$result = $printLabelHelper->printLabel($values, $action);
				if($result->hasErrors())
					throw new Minder_Exception(implode('. ', $result->errors));
				break;
    	 
/*********************************************/

									case 'PRINT PRODUCT':
										$tmpValues                    = $this->minder->selectProdLabelData($values['PROD_ID'], $values['COMPANY_ID']);
										$request                      = $this->getRequest();
										$productLabelType             = $request->getParam('product_label_type', 'PRODUCT_LABEL');
										$tmpValues['PACK_QTY']        = $request->getParam('first_label_qty', 1);
										$tmpValues['TOTAL_ON_LABEL']  = $request->getParam('first_label_total', 1);
										$tmpValues['labelqty']        = $tmpValues['PACK_QTY'];
										$result                       = $printLabelHelper->printLabel($tmpValues, $action, $productLabelType);
				
										if($result->hasErrors())
											throw new Minder_Exception(implode('. ', $result->errors));

										$secondLabelQty               = $request->getParam('second_label_qty', 0);
										$secondLabelTotal             = $request->getParam('second_label_total', 0);
										
										if (is_numeric($secondLabelQty) && $secondLabelQty > 0) {
											$tmpValues['PACK_QTY']       = $secondLabelQty;
											$tmpValues['TOTAL_ON_LABEL'] = $secondLabelTotal;
											$tmpValues['labelqty']       = $tmpValues['PACK_QTY'];
											$result                      = $printLabelHelper->printLabel($tmpValues, $action, $productLabelType);

											if($result->hasErrors())
												throw new Minder_Exception(implode('. ', $result->errors));
							}
							break;
									


							default:
					array_push($check_arr,$values);
					if(!empty($check_arr[$i]['CODE'])) {
					$message_disp.=$check_arr[$i]['CODE']." - ".$check_arr[$i]['DESCRIPTION'].",\r\n";
					}
							$result = $printLabelHelper->printLabel($values, $action);


								if($result->hasErrors())
											throw new Minder_Exception(implode('. ', $result->errors));
								}
							} catch(Exception $ex){
								$this->addError($ex->getMessage());
								return;
							}
							$count++;
							$i++;
						}
					}
					if($result && !$result->hasErrors()) {

				//$this->view->flash =$count . ' label(s) printed successfully';
		$this->addMessage($count . ' label(s) printed successfully. '. $message_disp.$printLabelHelper->getMessage($action));
					}
				} else {
					 $this->addError('Missing data for printing');    
				}
				break;
		 
			case 'REPRINT':
				$records  = $this->getRequest()->getPost('records'); 
				$data     = $dataset->getRawData(); 
				$count    = 0;
				$result   = false;
				  
				if(!empty($records)) {
				   foreach($data as $record) {
					if(in_array($record['MESSAGE_ID'], $records)) {
						$record['REQUEST_STATUS']   =   'NP';
						
						$recordSet = $dataset->getRecord($record['MESSAGE_ID']);
						$recordSet->save($record);
						$result    = $this->minder->updateMasterTableDataSet($dataset);
						if($result['RES'] < 0){
							$this->addError('Error while print label(s): ' . $result['ERROR_TEXT']); 
							break;
						}
						$count++;    
					}
					if($result['RES'] >= 0){
						$this->addMessage($count . ' label(s) reprinted successfully');
					}
				   }
				} else {
					$this->addError('Missing data for reprinting');    
				}
				break;    
		}

		$this->view->searchElementList = $this->_getCrudSearchElements($dataset);

	}

	private function _getCrudSearchElements(MasterTable_DataSet &$dataset) {
		return 'SEARCH';
	}

	/**
	 * Performs lookup for Autocomplete fields built in CRUD action
	 *
	 * @return void
	 */
	public function crudLookupAction() {
		
		$table = $this->getRequest()->getParam('table');
		$field = $this->getRequest()->getParam('field');
		$value = $this->getRequest()->getParam('q');
		
		$this->view->data = $this->minder->getAutocompleteList($table, $field, $value, '');
		$this->render('lookup');
	}

	/**
	 * Performs lookup for Autocomplete fields
	 *
	 * @return void
	 */
	public function lookupAction() {
		
		$tdata = array();
		switch ($this->getRequest()->getParam('field')) {
			case 'person_id_srch':
				$tdata = $this->minder->getFieldListFromPerson('PERSON.PERSON_ID', $this->getRequest()->getParam('q'));
				break;
	
			case 'first_name_srch':
				$tdata = $this->minder->getFieldListFromPerson('PERSON.FIRST_NAME', $this->getRequest()->getParam('q'));
				break;
	
			case 'last_name_srch':
				$tdata = $this->minder->getFieldListFromPerson('PERSON.LAST_NAME', $this->getRequest()->getParam('q'));
				break;
	
			case 'company_id_srch':
				$tdata = $this->minder->getCompanyList($this->getRequest()->getParam('q'));
				break;
	
			case 'contact_first_name_srch':
				$tdata = $this->minder->getFieldListFromPerson('PERSON.CONTACT_FIRST_NAME', $this->getRequest()->getParam('q'));
				if (count($tdata) > 10) {
					$tdata = array_slice($tdata, 0, 10, true);
				}
				break;
	
			case 'contact_last_name_srch':
				$tdata = $this->minder->getFieldListFromPerson('PERSON.CONTACT_LAST_NAME', $this->getRequest()->getParam('q'));
				if (count($tdata) > 10) {
					$tdata = array_slice($tdata, 0, 10, true);
				}
				break;
	
			case 'person_type_srch':
				$tdata = $this->minder->getPersonTypeList($this->getRequest()->getParam('q'));
				if (count($tdata) > 10) {
					$tdata = array_slice($tdata, 0, 10, true);
				}
				break;
	
			case 'label_name':
				$tdata = $this->minder->getFormatList($this->getRequest()->getParam('q'));
				break;
	
			case 'label_brand':
				$tdata = $this->minder->getSysEquipBrandList($this->getRequest()->getParam('q'));
				break;
	
			case 'label_model':
				$tdata = $this->minder->getSysEquipModelList($this->getRequest()->getParam('q'));
				break;
	
			default:
				$tdata = array();
				break;
		}
		$this->view->data = $tdata;
	}
	
	public function seekAction() {
	
		$fieldId = $this->getRequest()->getParam('field');
		switch($fieldId) {
			case 'record_id':
								$recordId = $this->getRequest()->getParam('q');
								$data     = $this->minder->getOnePrintLabelData($recordId);
								$data     = $data->items;
				break;
			
			case 'var_record':
			
								$recordId = $this->getRequest()->getParam('q');
								$data     = $this->minder->getSysLabelVarById($recordId);
				break;  
			
			default:        $data = '';
							
		}
	
		$this->view->data = $data;
	}

	public function checkSoapCliAction() {

		$cfg = Zend_Registry::get('config');

		$fc = $cfg->soapcli->logcommit;
		$fu = $cfg->soapcli->logupdate;

		$this->view->periodUpdate = $cfg->soapcli->period_update;
		$this->view->periodCommit = $cfg->soapcli->period_commit;

		$cmt = file_get_contents($fc);
		$upd = file_get_contents($fu);
		$c = array();
		$u = array();

		preg_match_all('~(Commit started.)(.*)~', $cmt, $c);
		preg_match_all('~(Update started.)(.*)~', $upd, $u);
		$this->view->lastCommit     = end($c[2]);
		$this->view->previousCommit = prev($c[2]);
		$this->view->lastUpdate     = end($u[2]);
		$this->view->previousUpdate = prev($u[2]);

			$flag = false;
			if (time() - strtotime($this->view->lastCommit) > $this->view->periodCommit * 60) {
				$this->addError('Last commit log is too old. It\'s possible that CLI is not running');
				$flag = true;
			}

			if (time() - strtotime($this->view->lastUpdate) > $this->view->periodUpdate * 60) {
				$this->addError('Last update log is too old. It\'s possible that CLI is not running');
				$flag = true;
			}

			if (!$flag) {
				$this->addMessage('CLI is working now...');
			}
			$this->view->check       = 'HERE';
			$this->view->currentTime = date($cfg->date->dateformat, time());
		  
			$this->session->check    = false;

			$dbUpdateDate            = $this->minder->getSoapCache('last_update_date');

			$this->view->dbLastUpdate= date($cfg->date->dateformat, $dbUpdateDate);

		$s = new NetSuite_SoapWrapper();
		if ($this->getRequest()->isPost()) {
			$action = strtoupper($this->getRequest()->getPost('action'));
			if ($action == 'LOCK') {
				$s->lockSoapTransaction();
			} elseif ($action == 'UNLOCK') {
				$s->unlockSoapTransaction();
			} elseif ($action == 'RUN CLI SERVICE') {
				$this->minder->allowCli();
			} elseif ($action == 'STOP CLI SERVICE') {
				$this->minder->denyCli();
			}
		} else {
			$c1 = array();
			$u1 = array();
			$rj = array();

			$result = array();
			preg_match_all('~(Commit completed.)(.*)(-)~', $cmt, $c1);
			preg_match_all('~(Update completed.)(.*)(-) record to update (.*) record updated (.*)~', $upd, $u1);
			preg_match_all('~(.*)rejected(.*)Order with status(.*)~', $upd, $rj['Order with status']);
			preg_match_all('~(.*)rejected(.*)Order doesn\'t have(.*)~', $upd, $rj['Order doesn\'t have']);
			preg_match_all('~(.*)rejected(.*)LastModifiedDate(.*)~', $upd, $rj['LastModifiedDate']);

			$toImport = 0;
			$imported = 0;
			foreach ($u[2] as $key => $val) {
				$result['start'][]      = $u[2][$key];
				$result['duration'][]   = strtotime($u1[2][$key]) - strtotime($u[2][$key]);
				$result['search'][]     = $u1[4][$key];
				$result['success'][]    = $u1[5][$key];
				$toImport              += $u1[4][$key];
				$imported              += $u1[5][$key];
			}
		  
			$this->view->toImport = $toImport;
			$this->view->imported = $imported;
			$this->view->rejected = (count($rj['LastModifiedDate'][0]) + count($rj['Order doesn\'t have'][0]) + count($rj['Order with status'][0]));
			
			$reason = array();
			$reason['lastModifiedDate']     = count($rj['LastModifiedDate'][0]);
			$reason['no required field']    = count($rj['Order doesn\'t have'][0]);
			$reason['not allowed status']   = count($rj['Order with status'][0]);
			
			$this->view->reason             = $reason;
		}
		if (($this->view->isAllowedCli = $this->minder->isAllowedCli())) {
			$this->view->serviceStatus = 'STARTED';
			$this->addMessage('CLI allowed');
		} else {
			$this->view->serviceStatus = 'STOPPED';
			$this->addWarning('CLI not allowed');
		}

		$this->view->lockStatus     = $s->getLockSoapTransactionStatus();
		$this->view->lockDate       = $s->getLockSoapTransactionDate();
		$this->view->toggleStatus   = ($this->view->lockStatus == 'lock' ? 'UNLOCK' : 'LOCK');
	}
	/**
	* @desc show replase Maser Data in SSN'S page
	*/
	public function replaceinssnAction() { }

	/**
	 * make form for TestTransaction
	 *
	 * @return Zend_Form
	 */
	protected function _getTestTransactionForm() {
		
		$form = new Zend_Form();
		$form->setMethod('post');

		$decorate = array(array('ViewScript', array('viewScript' => 'admin/_decor.phtml','class' => 'form_element')));

		/**
		 * Leftside Group
		 */
		$elements = array();

		$el = new Zend_Form_Element_Text('trn_type');
		$el->setLabel('TRN_TYPE : ')->addValidators(array(new Zend_Validate_StringLength(4,4), new Zend_Validate_Alnum()))->setRequired(true)->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('trn_code');
		$el->setLabel('TRN_CODE : ')->addValidators(array(new Zend_Validate_StringLength(1,1), new Zend_Validate_Alnum()))->setRequired(true)->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('trn_date');
		$el->setLabel('TRN_DATE : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('object_id');
		$el->setLabel('OBJECT ID: ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('reference');
		$el->setLabel('REFERENCE : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('qty');
		$el->setLabel('QTY : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('instance_id');
		$el->setLabel('INSTANCE : ')->setDecorators($decorate);
		$elements[] = $el;

		$form->addElements($elements);
		$form->addDisplayGroup(array('trn_type', 'trn_code', 'trn_date', 'object_id', 'reference', 'qty', 'instance_id'), 'leftside');


		/**
		 * Rightside Group
		 */
		$elements = array();

		$el = new Zend_Form_Element_Text('wh_id');
		$el->setLabel('WH_ID : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('locn_id');
		$el->setLabel('LOCN_ID : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('sub_locn_id');
		$el->setLabel('SUB_LOCN_ID : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('person_id');
		$el->setLabel('PERSON_ID : ')->setRequired(true)->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('device_id');
		$el->setLabel('DEVICE_ID : ')->setRequired(true)->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('input_source');
		$el->setLabel('INPUT_SOURCE : ')->setDecorators($decorate);
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('complete');
		$el->setLabel('COMPLETE : ')->setDecorators($decorate);
		$elements[] = $el;
		
		$form->addElements($elements);
		$form->addDisplayGroup(array('wh_id', 'locn_id', 'sub_locn_id', 'person_id', 'device_id', 'input_source', 'complete'), 'rightside');


		/**
		 * Toolbar Group
		 */
		$elements = array();
		$el = new Zend_Form_Element_Submit('action');
		$el->setLabel('SUBMIT')->setDecorators(array(array('ViewHelper', array('helper' => 'formSubmit')), array('HtmlTag' , array('tag' => 'li'))));
		$elements[] = $el;

		$el = new Zend_Form_Element_Reset('clear');
		$el->setLabel('CLEAR')->setDecorators(array(array('ViewHelper', array('helper' => 'formReset')), array('HtmlTag' , array('tag' => 'li'))));
		$elements[] = $el;

		$form->addElements($elements);
		$form->addDisplayGroup(array('action', 'clear'), 'bottom_toolbar');

		/**
		 * Notes Group
		 */
		$elements = array();
		$el = new Zend_Form_Element_Text('error_text');
		$el->setLabel('ERROR_TEXT : ')->setDecorators($decorate)->setAttrib('disabled', 'disabled')->setAttrib('style', 'width:800px;')->setAttrib('class', 'disabled');
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('record_id');
		$el->setLabel('RECORD_ID : ')->setDecorators($decorate)->setAttrib('disabled', 'disabled')->setAttrib('style', 'width:800px;')->setAttrib('class', 'disabled');
		$elements[] = $el;

		$el = new Zend_Form_Element_Text('other');
		$el->setLabel('OTHER : ')->setDecorators($decorate)->setAttrib('disabled', 'disabled')->setAttrib('style', 'width:800px;')->setAttrib('class', 'disabled');
		$elements[]= $el;

		$form->addElements($elements);
		$form->addDisplayGroup(array('error_text', 'record_id', 'other'), 'notes');

		try {
			$dg = $form->getDisplayGroup('leftside');
			$dg->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'table', 'class' => 'leftside'))));

			$dg = $form->getDisplayGroup('rightside');
			$dg->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'table', 'class' => 'rightside'))));

			$dg = $form->getDisplayGroup('bottom_toolbar');
			$dg->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'ul', 'class' => 'toolbar'))));

			$dg = $form->getDisplayGroup('notes');
			$dg->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'table', 'class' => 'notes', 'style' => 'width:99%;'))));

		} catch (Exception $e) {
		}
		return $form;
	}

	/**
	* @desc action realize logic for Label Printing System
	* 
	* 
	* @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>, <dmitriy_suhinin@mail.ru>
	* @copyright 2007 Barcoding & Data Collection Systems
	* @license   http://www.barcoding.com.au/licence.html B&DCS Licence
	* @link      http://www.barcoding.com.au/
	*/
	public function printSysAction() {
		$limitPrinter = $this->getRequest()->getParam('limitPrinter', Minder2_Environment::getCurrentPrinter());
		Minder2_Environment::setCurrentPrinter($limitPrinter);
		
		if(count($_POST) > 0){
			$this->session->formParams	            =	$_POST;
			$this->session->formParams['action']    =   null;
		} else {
			$_POST	=	$this->session->formParams;	
		}
		
		$this->view->headers = array('SL_NAME'      => 'Label Name', 
									 'SL_SEQUENCE'  => 'Seq #', 
									 'SL_LINE'      => 'Command', 
									 'SL_BRAND'     => 'Brand',
									 'SL_MODEL'     => 'Model',
									 'SL_FIRMWARE'  => 'Firmware',
									 'RECORD_ID'    => '#Rec');
		
		$allowed = array('sys_label_brand'   => 'SL_BRAND = ? AND ',
						 'sys_label_model'   => 'SL_MODEL = ? AND ',
						 'sys_label_firmware'=> 'SL_FIRMWARE = ? AND ',
						 'sys_label_name'	 =>	'SL_NAME LIKE ? AND '
						);
		
		parent::_preProcessNavigation();
		
		$pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
		$resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
		
		$conditions = $this->_setupConditions(null, $allowed);
		$clause     = $this->_makeClause($conditions, $allowed);
		
		if(isset($clause['SL_NAME LIKE ? AND '])){
			$clause['SL_NAME LIKE ? AND ']	=	str_replace('\'', '', $clause['SL_NAME LIKE ? AND ']);
		}
		
		if(!empty($_POST['action'])) {
			$action = strtoupper($_POST['action']);
		} else {
			$action = '';
		}

		switch($action) {
			case 'ADD':
						$allowed    =   array('sl_name'     =>  'SL_NAME',
											  'sl_sequence' =>  'SL_SEQUENCE',
											  'sl_line'     =>  'SL_LINE',
											  'sl_brand'    =>  'SL_BRAND',
											  'sl_model'    =>  'SL_MODEL',
											  'sl_firmware' =>  'SL_FIRMWARE',
											  'sl_notes'    =>  'SL_NOTES');
						
						
						$conditions   = $this->_setupConditions(null, $allowed);
						$actionClause = $this->_makeClause($conditions, $allowed);
						if(!empty($_FILES) && $_FILES['sl_image']['size'] > 0) {
							   $actionClause['SL_IMAGE']    =   file_get_contents($_FILES['sl_image']['tmp_name']);
						}
					   $actionClause['LAST_UPDATE_BY'] = $this->minder->userId;
					   $result = $this->minder->insertDataLine($actionClause);
					   if($result){
							$this->addMessage('Row succesfuly inserted');
					   } else {
							$this->addError('Error while insert new row');
					   }
						
						break;
			
			case 'COPY':
						
						$allowed    =   array('sl_name_copy'         =>  'SL_NAME',
											  'new_sl_sequence_copy' =>  'SL_SEQUENCE',
											  'sl_line_copy'         =>  'SL_LINE',
											  'sl_brand_copy'        =>  'SL_BRAND',
											  'sl_model_copy'        =>  'SL_MODEL',
											  'sl_firmware_copy'     =>  'SL_FIRMWARE',
											  'sl_notes_copy'        =>  'SL_NOTES');
						
						$conditions   = $this->_setupConditions(null, $allowed);
						$actionClause = $this->_makeClause($conditions, $allowed);
						
						$slNmae         = $this->getRequest()->getParam('sl_name_copy');
						$slLine         = $this->getRequest()->getParam('sl_line_copy');
						$slBrand        = $this->getRequest()->getParam('sl_brand_copy');
						$slModel        = $this->getRequest()->getParam('sl_model_copy');
						$slFirmware     = $this->getRequest()->getParam('sl_firmware_copy');
						
						$recordId       = $this->getRequest()->getParam('record_id');
						$newSequence    = $this->getRequest()->getParam('new_sl_sequence_copy');
						$sequenceStep   = $this->getRequest()->getParam('sequence_step_copy');
						$copyNumbers    = $this->getRequest()->getParam('number_copies');
						
						$oldLineData    = $this->minder->getOnePrintLabelData($recordId);
						
						// add old fileds
						$actionClause['SL_IMAGE']           = $oldLineData['SL_IMAGE'];
						$actionClause['SL_NOTES']           = $oldLineData['SL_NOTES'];
						$actionClause['LAST_UPDATE_DATE']   = $oldLineData['LAST_UPDATE_DATE'];
						$actionClause['LAST_UPDATE_BY']     = $oldLineData['LAST_UPDATE_BY'];
						
						if(!empty($copyNumbers)) {
							for($i=0; $i<$copyNumbers; $i++) {
								$result = $this->minder->insertDataLine($actionClause);
								if(!$result){
									$this->addError('Error while copy rows.');
									return;
								}
								$newSequence += $sequenceStep;
								$actionClause['SL_SEQUENCE'] = $newSequence;
									  
							}
						} else {
							$result = $this->minder->insertDataLine($actionClause);
							if(!$result){
								$this->addError('Error while copy rows.');
								return;
							}    
						}
						
						if($result) {
							$this->addMessage('Copy rows succesfuly');
						}
						
						break;
			
			case 'DELETE':
						parent::_preProcessNavigation();
						
						$pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
						$showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
						
						$result             = $this->minder->getPrintLabelData($clause, $pageNo, $resultsPerPage); 
						$data               = $result['all'];  
						$numRecords         = count($result['all']);
						$conditions         = parent::_getConditions('print-sys');
						$this->view->data   = array();
						$toDelete = array();
						for ($i = 0, $count = 0; $i < $numRecords; $i++) {
							if (array_key_exists($data[$i]->id, $conditions)) {
								$toDelete[] = $data[$i]->id;
							}
						}
						if(count($toDelete) == 0) {
							$this->addError('Please select rows to delete');
							return;
						}
						$result = $this->minder->deleteDataLine($toDelete);
						if($result){
							$this->addMessage(count($toDelete) . ' row(s) successfully deleted');
						} else {
							$this->addError('Error while delete rows');
						}
						
						break;
			
			case 'REFRESH':
						break;
			
			case 'IMPORT':


						 $allowed    =   array('sl_name_import'     =>  'SL_NAME',
											   'sl_brand_import'    =>  'SL_BRAND',
											   'sl_model_import'    =>  'SL_MODEL',
											   'sl_firmware_import' =>  'SL_FIRMWARE',
											   'sl_notes_import'    =>  'Sl_NOTES'
											  );
						
						$sequenceStep = $this->getRequest()->getParam('sl_sequnce_step_import');
						$sequence     = $this->getRequest()->getParam('sl_sequnce_import');   
						$conditions   = $this->_setupConditions(null, $allowed);
						$actionClause = $this->_makeClause($conditions, $allowed);
						$actionClause['LAST_UPDATE_BY'] = $this->minder->userId;
						
						if(!empty($_FILES) && $_FILES['sl_file_import']['size'] > 0) {
							   $data   =   file($_FILES['sl_file_import']['tmp_name']);
						}
						
						foreach($data as $line) {
							// if not comment then add to SYS_LABEL table
							$actionClause['SL_LINE']    = $line;
							$actionClause['SL_SEQUENCE'] = $sequence;
							$result = $this->minder->insertDataLine($actionClause);
							if(!$result){
								$this->addError('Error while import rows. Error in: ' . $line . $this->minder->lastError);
								return;
							}
							
							$sequence +=  $sequenceStep;
						}
						if($result) {
							$this->addMessage('Import file successfully');
						}
						break;
			
			case 'TEST PRINT':
					   
						$tableName		=	'';
						$prnType		=	$this->getRequest()->getParam('labelFormt');
						$data 			=	array('DEFAULT'	=>	''); 
							
						$printerObj		=	$this->minder->getPrinter($tableName);
						
						try{
							
							$result 		= 	$printerObj->printTestLabel($data, $prnType);
							
							if($result){
								$this->addMessage(count($data) . ' label(s) printed successfully');
							} else {
								$this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
							} 
						
						} catch(Exception $ex){
							$this->addError($ex->getMessage());		
						}
						
					   break;
			
			case 'REPORT: CSV':
						 parent::_preProcessNavigation();
						 
						 $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
						 $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
	
						 $result             = $this->minder->getPrintLabelData($clause, $pageNo, $resultsPerPage); 
						 $data               = $result['all'];  
						 $numRecords         = count($result['all']);
						 $conditions         = parent::_getConditions('print-sys');
						 $this->view->data   = array();
						 for ($i = 0, $count = 0; $i < $numRecords; $i++) {
							if (array_key_exists($data[$i]->id, $conditions)) {
								$this->view->data[] = $data[$i];
							}
						 }
						 $this->_viewRenderer()->setNoRender(true);
						 $this->_exportHelper()->exportTo($action, $this->view->headers, $this->view->data);
						return;
			
			case 'EXIT':
						$redirector = $this->_helper->getHelper('Redirector');
						$redirector->setCode(303)
								   ->goto('index', 'admin', null, array());
						break;
			
			default:
					
		}
		
		
		$dataCommandList    = $this->minder->getPrintLabelData($clause, $pageNo, $resultsPerPage);
		$data               = $this->view->data   = $dataCommandList['data'];
		$numRecords         = count($data);
		$count = 0;
		for ($i = 0, $count = 0; $i < $numRecords; $i++) {
			if (array_key_exists($data[$i]->id, $conditions)) {
				$count++;
			}
		}
		
		$this->view->totalSelected = $count;
		$this->view->upperChekbox = false;
		if($count == $numRecords) {
			$this->view->upperChekbox = true;
		}
		
		$data       = $dataCommandList['all'];
		$numRecords = count($data);
		for ($i = 0, $count = 0; $i < $numRecords; $i++) {
			if (array_key_exists($data[$i]->id, $conditions)) {
				$count++;
			}
		}
		$this->view->totalSelected = $count;
		
		
		$this->view->checkAll = false;
		if($count == $numRecords) {
			$this->view->checkAll = true;
		}
		
		$printerFormat             = $this->minder->limitPrinter . '_FORMAT';
		$keys                      = array_keys($this->minder->getPrnFile($printerFormat)); 
		
		$this->view->labelName     = !empty($_POST['sys_label_name'])? $_POST['sys_label_name']: ''; 
		$this->view->labelBrand    = !empty($_POST['sys_label_brand'])? $_POST['sys_label_brand']: ''; 
		$this->view->labelModel    = !empty($_POST['sys_label_model'])? $_POST['sys_label_model']: ''; 
		$this->view->labelFirmware = !empty($_POST['sys_label_firmware'])? $_POST['sys_label_firmware']: '';
		$this->view->labelFormat   = !empty($_POST['labelFormt']) ? $_POST['labelFormt'] : ''; 
		$this->view->labelFormats  = array_merge(array(' ' => ' '), array_combine($keys, $keys));
	   
		parent::_postProcessNavigation($dataCommandList);
		
	}
	
	
	public function syslabelvarAction() {
	
		parent::_preProcessNavigation();
		
		
		$this->view->headers    =   array('SL_NAME'         =>  'SL_NAME',
										  'SLV_NAME'        =>  'SLV_NAME',
										  'SLV_SEQUENCE'    =>  'SLV_SEQUENCE',
										  'SLV_EXPRESSION'  =>  'SLV_EXPRESSION',
										  'SLV_DEFAULT'     =>  'SLV_DEFAULT',
										  'LAST_UPDATE_DATE'=>  'LAST_UPDATE_DATE',
										  'LAST_UPDATE_BY'  =>  'LAST_UPDATE_BY',
										  'RECORD_ID'       =>  'RECORD_ID');
		
		$action =   $this->getRequest()->getParam('method');
		
		if(!isset($this->session->marlines)) {
			$this->session->marlines    =   array();
		}
	  
		switch(strtoupper($action)) {
		
			case 'REPORT: CSV':
			case 'REPORT: TXT':
			case 'REPORT: XML':
			case 'REPORT: XLS':
			
				 $slName            =  $this->session->slName;
				 $markLines         =  $this->session->marlines;
				 $sysLabelVarList   =  $this->minder->getSysLabelVarList($slName);
				 $this->view->data  =   array();
			  
				 foreach($sysLabelVarList['data'] as $value){
					if(in_array($value['RECORD_ID'], $markLines)) {
						$this->view->data[] =   $value;     
					}
				 }
				 
				 $this->_exportHelper()->exportTo($action, $this->view->headers, $this->view->data);
				 $this->_viewRenderer()->setNoRender(true);
				 return;
			
			case 'MARK':
				
				$value                  = $this->getRequest()->getParam('value');
				$markLines              = &$this->session->marlines;
				
				if(!in_array($value, $markLines)) {
					$markLines[$value]  =   $value;
				} else {
					unset($markLines[$value]);
				}
				
				echo json_encode(array('selected_num'   =>  count($markLines),
									   'selected_total' =>  $this->session->countLines));
				$this->_helper->viewRenderer->setNoRender();
				return;
			
			case 'MARK_ALL':
				
				$slName             = $this->session->slName;
				$sysLabelVarList    = $this->minder->getSysLabelVarList($slName);
				$markLines          = &$this->session->marlines;
				
				foreach($sysLabelVarList['data'] as $value){
					
					if(!in_array($value['RECORD_ID'], $markLines)) {
						$markLines[$value['RECORD_ID']]  =   $value['RECORD_ID'];
					} else {
						unset($markLines[$value['RECORD_ID']]);
					}
				}
				
				echo json_encode(array('selected_num'   =>  count($markLines)));
				$this->_helper->viewRenderer->setNoRender();
				return;
			
			case 'SAVE':
			
				$args   =   array();
				$args[] =   $this->getRequest()->getParam('slv_name');
				$args[] =   $this->getRequest()->getParam('slv_default');
				$args[] =   $this->getRequest()->getParam('slv_expression');
				$args[] =   $this->getRequest()->getParam('last_update_date');
				$args[] =   $this->getRequest()->getParam('slv_sl_name');
				$args[] =   $this->getRequest()->getParam('last_update_by');
				$args[] =   $this->getRequest()->getParam('slv_sequence');
				$args[] =   $this->getRequest()->getParam('record_id');
				
				$this->minder->updateSysLabelVar($args);
			
				$this->_helper->viewRenderer->setNoRender();
				return;
			
			case 'ADD':
				
				$args   =   array();
				$args[] =   $this->getRequest()->getParam('slv_name');
				$args[] =   $this->getRequest()->getParam('slv_expression');
				$args[] =   $this->getRequest()->getParam('slv_default');
				$args[] =   $this->getRequest()->getParam('slv_sequence');
				$args[] =   $this->getRequest()->getParam('slv_sl_name');
				$args[] =   $this->getRequest()->getParam('last_update_by');
				$args[] =   $this->getRequest()->getParam('last_update_date');
				
				$this->minder->addSysLabelVar($args);
				
				$this->_helper->viewRenderer->setNoRender();
				return;
		}
		
		
		$slName                     =   $this->getRequest()->getParam('slName');
		
		
		$sysLabelVarList            = $this->minder->getSysLabelVarList($slName);
		
		$this->session->slName      =   $slName;
		$this->session->countLines  =   $sysLabelVarList['total'];
								
		$this->view->data           = $sysLabelVarList['data'];
		$this->view->totalSelected  = count($this->session->marlines) > 0 ? count($this->session->marlines) : 0;
		$this->view->upperCheckbox  = count($this->session->marlines) == count($this->view->data) ? 'checked' : '';    
		$this->view->conditions     = $this->session->marlines;
		
		parent::_postProcessNavigation($sysLabelVarList);
		
	}
	
	public function editprintAction() {
	
		$recordId   = $this->getRequest()->getParam('rec_id');
		$printData  = $this->minder->getOnePrintLabelData($recordId);
		
		if(!empty($_POST['action'])) {
			$action = strtoupper($_POST['action']);
		} else {
			$action = '';
		}
		
		switch($action) {
			case 'SAVE':
					   
					   $allowed    =   array('sl_name'     =>  'SL_NAME',
											 'sl_sequence' =>  'SL_SEQUENCE',
											 'sl_line'     =>  'SL_LINE',
											 'sl_brand'    =>  'SL_BRAND',
											 'sl_model'    =>  'SL_MODEL',
											 'sl_firmware' =>  'SL_FIRMWARE',
											 'sl_notes'    =>  'SL_NOTES');
									
						
					   $conditions   = $this->_setupConditions(null, $allowed);
					   $actionClause = $this->_makeClause($conditions, $allowed);
					   if(!empty($_FILES) && $_FILES['sl_image']['size'] > 0) {
							   $actionClause['SL_IMAGE']    =   file_get_contents($_FILES['sl_image']['tmp_name']);
					   }
					   $actionClause['LAST_UPDATE_BY'] = $this->minder->userId;
					   $result   = $this->minder->updateDataLine($recordId, $actionClause);
					  
					   if($result){
							$this->addMessage('Row was successfully updated');
					   } else {
							$this->addError('Error while update row');
					   }
					   
			case 'DISCARD':
						$redirector = $this->_helper->getHelper('Redirector');
						$redirector->setCode(303)
								   ->goto('print-sys', 'admin', null, array());
						break;
						
		}
		
		$bid        = md5($printData['SL_IMAGE']);
		$session    = new Zend_Session_Namespace('blobs');
		$session->blob[$bid] = $printData['SL_IMAGE'];
		
		$this->view->bid        = $bid;
		$this->view->recordId   = $recordId;
		$this->view->printData  = $printData;
	}
	
	public function marklinesAction() {
		
		parent::_preProcessNavigation();
		
		$pageSelector = $this->session->navigation[$this->_controller]['print-sys']['pageselector'];
		$showBy       = $this->session->navigation[$this->_controller]['print-sys']['show_by'];
		
		$allowed = array('sys_label_brand'   => 'SL_BRAND = ? AND ',
						 'sys_label_model'   => 'SL_MODEL = ? AND ',
						 'sys_label_firmware'=> 'SL_FIRMWARE = ? AND ',
						 'sys_label_name'	 =>	'SL_NAME LIKE ? AND '
						);
		
		$conditions = $this->_setupConditions(null, $allowed);
		$clause     = $this->_makeClause($conditions, $allowed);
		
		if(isset($clause['SL_NAME LIKE ? AND '])){
			$clause['SL_NAME LIKE ? AND ']	=	str_replace('\'', '', $clause['SL_NAME LIKE ? AND ']);
		}
		$result       = $this->minder->getPrintLabelData($clause, $pageSelector, $showBy); 
	   
		$data         = $result['all'];
		$method       = $this->getRequest()->getParam('method');
		$id           = $this->getRequest()->getParam('id');
		$value        = $this->getRequest()->getParam('value');

		$conditions = $this->_markSelected($data, $id, $value, $method, 'print-sys');
		$numRecords = count($data);
		// Calculate the number of selected items.
		$count = 0;
		for ($i = 0, $count = 0; $i < $numRecords; $i++) {
			if (array_key_exists($data[$i]['RECORD_ID'], $conditions)) {
				$count++;
			}
		}
		
		$data = array();
		$data['selected_num']   = $count;
		$this->view->data       = $data;  
	}
	/*protected function _setupShortcuts() {
		$this->view->shortcuts = Minder_Navigation_Array::getShortcuts('admin');
		$this->view->tooltip   = Minder_Navigation_Array::getTooltips('admin');

		return $this;
	}*/

	protected function _preProcessNavigation($tableid) {
		
		$controller = $this->_controller;
		$action     = $this->_action;
		
		if (isset($this->session->navigation[$controller][$action][$tableid])) {
			foreach ($this->session->navigation[$controller][$action][$tableid] as $key => $val) {
				if (!is_null($this->getRequest()->getParam($key))) {
					$this->session->navigation[$controller][$action][$tableid][$key] = (int)$this->getRequest()->getParam($key);
					$this->view->rowNumber = $this->session->navigation[$controller][$action][$tableid]['show_by'] *
											 $this->session->navigation[$controller][$action][$tableid]['pageselector'] + 1;
				}
			}
		} else {
			$this->session->navigation[$controller][$action][$tableid]['show_by']      = $this->_showBy;
			$this->session->navigation[$controller][$action][$tableid]['pageselector'] = $this->_pageSelector;
		}
		if ($this->view->rowNumber > $this->session->navigation[$controller][$action][$tableid]['show_by'] *
									($this->session->navigation[$controller][$action][$tableid]['pageselector'] + 1)) {
			$this->session->navigation[$controller][$action][$tableid]['pageselector'] =
				floor($this->view->rowNumber / $this->session->navigation[$controller][$action][$tableid]['show_by']);
		} elseif (($this->view->rowNumber-1) >=0 &&
				floor(($this->view->rowNumber-1) / $this->session->navigation[$controller][$action][$tableid]['show_by']) <=
											($this->session->navigation[$controller][$action][$tableid]['pageselector'])) {
			$this->session->navigation[$controller][$action][$tableid]['pageselector'] =
				floor(($this->view->rowNumber-1) / $this->session->navigation[$controller][$action][$tableid]['show_by']);
		}

		$nav            = $this->view->navigation;
		$nav[$tableid]  = $this->session->navigation[$controller][$action][$tableid];
		
		$this->view->navigation = $nav;
		
		return $this;
	}

	/**
	 * post process navigation
	 *
	 * @param string $tableid
	 * @return self
	 */
	protected function _postProcessNavigation($tableid) {
		
		$controller = $this->_controller;
		$action     = $this->_action;

		$nav            = $this->view->navigation;
		$nav[$tableid]  = $this->session->navigation[$controller][$action][$tableid];
		$this->view->navigation = $nav;

		if (($this->view->navigation[$tableid]['show_by'] * ($this->view->navigation[$tableid]['pageselector'] + 1)) > $this->view->numRecords) {
			// Recount number of pages.
			$this->view->navigation[$tableid]['pageselector'] = $this->session->navigation[$controller][$action][$tableid]['pageselector']
													= (int) floor($this->view->numRecords / $this->view->navigation[$tableid]['show_by']);
			if (!($this->view->maxno = $this->view->numRecords % $this->view->navigation[$tableid]['show_by']) &&
				$this->view->numRecords > 0) {
				$this->view->navigation[$tableid]['pageselector'] = $this->session->navigation[$controller][$action][$tableid]['pageselector'] -= 1;
				$this->view->maxno = $this->view->navigation[$tableid]['show_by'];
			}
		} else {
			$this->view->maxno = $this->view->navigation[$tableid]['show_by'];
		}

		$this->view->pages = array();
		for ($i = 1; $i <= ceil($this->view->numRecords / $this->view->navigation[$tableid]['show_by']); $i++) {
			$this->view->pages[] = $i;
		}
		return $this;
	}

	protected function _setupSelectables($table) {
		
		$selectables = array();

		switch ($table) {
			case 'SSN_TYPE':
				break;
			default:
				break;
		}
		return $selectables;
	}

	protected function _setupHeaders(MasterTable_DataSet $dataset) {
		
		$headers    = array();
		$headers    = $dataset->getUniqueConstraint();
		$i          = count($headers);
		$fieldList  = $dataset->getFields();
		
		foreach ($fieldList as $key => $val) {
			$headers[$key] = $val->name;
			if (++$i >= 6) {
				break;
			}
		}
		if (strtolower($dataset->table) == 'stocktake') {
			$headers['ST_VARIANCE'] = 'ST_VARIANCE';
			$headers['ST_ACTION']   = 'ST_ACTION';
			$headers['ST_STATUS']   = 'ST_STATUS';
		}
		
		$headers = $this->getAdditionalHeaders($dataset->table, $headers);
		
		return $headers;
	}

	protected function _getAllowed($table) {
		
		$allowed = array();

		if($this->minder->isNewDateCalculation() == false){
		  $temp = $this->minder->getFieldList($table);
		  if (false != $temp) {
		      foreach ($temp as $key => $val) {
		          $allowed[$key] = 'UPPER(' . $val . ') LIKE ?';
		      }
		  }
		}
		else{
		  $temp = $this->minder->getFieldList($table, "new");
		  if (false != $temp) {
		      foreach ($temp as $key => $val) {
		        if($val == "TIMESTAMP"){
		          $allowed[$key] = 'zerotime('.$key.') >= zerotime(?)';
		        }
		        else{
		          $allowed[$key] = 'UPPER('.$key.') LIKE ?';
		        }
		      }
		  }
		}		

		return $allowed;
	}

	protected function _getConditions($table, $data, $allowed)
	{
		$conditions = array();
		if (isset($this->session->table)) {
			$conditions = $this->session->table;
		}
		foreach ($data as $key => $val) {
			$key = strtoupper($key);
			if (array_key_exists($key, $allowed)) {
				$conditions[$table][$key] = $val;
			}
		}
		$this->session->table = $conditions;

		return $conditions;
	}

	public function logStatusAction() { }
	
	public function locationGenerateAction() {
		
		$this->view->pageTitle = 'GENERATE LOCATION SCREEN';
		
		$aisleFrom         =   $this->getRequest()->getParam('aisle_from');
		$aisleTo           =   $this->getRequest()->getParam('aisle_to');
		$aisleIncrement    =   (int)$this->getRequest()->getParam('aisle_increment');
		
		$bayFrom           =   $this->getRequest()->getParam('bay_from');
		$bayTo             =   $this->getRequest()->getParam('bay_to');
		$bayIncrement      =   (int)$this->getRequest()->getParam('bay_increment');
		
		$shelfFrom         =   $this->getRequest()->getParam('shelf_from');
		$shelfTo           =   $this->getRequest()->getParam('shelf_to');
		$shelfIncrement    =   (int)$this->getRequest()->getParam('shelf_increment');
		
		$positionFrom      =   $this->getRequest()->getParam('position_from');
		$positionTo        =   $this->getRequest()->getParam('position_to');
		$positionIncrement =   (int)$this->getRequest()->getParam('position_increment');

		$sequence = $this->getRequest()->getParam('seq', array(
			'aisle' => array('from' => '', 'to' => '', 'step' => '', 'type' => 'N2'),
			'bay' => array('from' => '', 'to' => '', 'step' => '', 'type' => 'N2'),
			'sub' => array('from' => '', 'to' => '', 'step' => '', 'type' => 'N2'),
			'sh' => array('from' => '', 'to' => '', 'step' => '', 'type' => 'N2'),
			'pos' => array('from' => '', 'to' => '', 'step' => '', 'type' => 'N2'),
		));

		$altFormat                  = $this->getRequest()->getParam('sequenceFormat', '%AISLE%-%BAY%.%SUB%-%SH%-%POS%');
		$altLocnNameFormat          = $this->getRequest()->getParam('altLocnNameFormat', '[%AISLE%-%BAY%.%SUB%-%SH%-%POS%]');
		$generatorForAltLocnName    = $this->getRequest()->getParam('generatorForAltLocnName', 'SEQ');
		
		if($aisleIncrement == 0) {
			$aisleIncrement = 1;
		}
		if($bayIncrement == 0) {
			$bayIncrement = 1;
		}
		if($shelfIncrement == 0) {
			$shelfIncrement = 1;
		}
		if($positionIncrement == 0) {
			$positionIncrement = 1;
		}
		
		$aisleDefaultInc    = 1;  
		$bayDefaultInc      = 1;  
		$shelfDefaultInc    = 1;  
		$positionDefaultInc = 1;
		
		$zoneSequnce        = 'N2';
		if(!isset($_POST['aisle_sequence'])) {
			$aisleSequnce      = 'N2';
		} else {
			$aisleSequnce      = $this->getRequest()->getParam('aisle_sequence'); 
		}
		
		if(!isset($_POST['bay_sequence'])) {
			$baySequnce      = 'N2';
		} else {
			$baySequnce      = $this->getRequest()->getParam('bay_sequence'); 
		}
		
		if(!isset($_POST['shelf_sequence'])) {
			$shelfSequnce       = 'N2';
		} else {
			$shelfSequnce       = $this->getRequest()->getParam('shelf_sequence'); 
		}
		
		if(!isset($_POST['position_sequence'])) {
			$positionSequnce       = 'N2';
		} else {
			$positionSequnce       = $this->getRequest()->getParam('position_sequence'); 
		}
		
		if(empty($aisleIncrement)) {
			$aisleIncrement = $aisleDefaultInc;
		}
		if(empty($bayIncrement)) {
			$bayIncrement = $bayDefaultInc; 
		}
		if(empty($shelfIncrement )) {
			$shelfIncrement = $shelfDefaultInc; 
		}
		if(empty($positionIncrement)) {
			$positionIncrement = $positionDefaultInc; 
		}

		$checkDigitLength = $this->getRequest()->getParam('checkDigitLength', 2);

		$sequenceOrder = $this->getRequest()->getParam('sequenceOrder', 'asc');
		$startSequence = $this->getRequest()->getParam('startSequence', 0);
		$applyTo = $this->getRequest()->getParam('applyTo', 'alt');

		$locnIdStartsFrom   = $this->getRequest()->getParam('locnIdStartsFrom');
		$locnIdEndsWith     = $this->getRequest()->getParam('locnIdEndsWith');

		$this->view->headers = array('LOCN_ID'       => 'Location Id',
									 'WH_ID'         => 'Warehouse Id', 
									 'LOCN_NAME'     => 'Location Name', 
									 'LOCN_STAT'     => 'Location Stat',
									 'MOVE_STAT'     => 'Move Stat',
									 'STORE_AREA'    => 'Store Area',
									 'MOVEABLE_LOCN' => 'Moveable Locn',
									 'LOCN_REPRINT'  => 'Reprint Locn',
									);
		
		$allowedOterInputs  = array('LOCN_NAME',
									'LOCN_TYPE',
									'LOCN_METRIC',
									'LOCN_HGHT',
									'PARENT_LOCN_ID',
									'ZONE_C',
									'CC_C',
									'TOG_C',
									'LOCN_STAT',
									'MOVE_STAT',
									'REPLENISH',
									'PACK_T',
									'STORE_TYPE',
									'STORE_AREA',
									'STORE_METH',
									'PERM_LEVEL',
									'LABEL_DATE',
									'LAST_AUDITED_DATE',
									'PROD_ID',
									'MAX_QTY',
									'MIN_QTY',
									'REORDER_QTY',
									'AISLE_SEQ',
									'BAY_SEQ',
									'SHELF_SEQ',
									'COMPARTMENT_SEQ',
									'LAST_UPDATE_DATE',
									'LAST_UPDATE_BY',
									'PUTAWAY_QTY',
									'LOCN_OWNER',
									'CURRENT_WH_ID',
									'MOVEABLE_LOCN',
									'SSN_TRACK',
									'LOCN_SEQ',
									'INSTANCE_ID',
									'TEMPERATURE_ZONE',
									'LOCN_TARE_WEIGHT',
									'LOCN_TARE_WEIGHT_UOM',
									'LOCN_INT_DIMENSION_X',
									'LOCN_INT_DIMENSION_Y',
									'LOCN_INT_DIMENSION_Z',
									'LOCN_DIMENSION_UOM',
									'LOCN_OUT_DIMENSION_X',
									'LOCN_OUT_DIMENSION_Y',
									'LOCN_OUT_DIMENSION_Z',
									'LOCN_REPRINT',
			);
		
		 $validator         = array('LOCN_TYPE'             =>  new Zend_Validate_StringLength(0, 40),
									'LOCN_METRIC'           =>  new Zend_Validate_StringLength(0, 2),
									'LOCN_HGHT'             =>  new Zend_Validate_Float(),
									'PARENT_LOCN_ID'        =>  new Zend_Validate_StringLength(0, 10),
									'ZONE_C'                =>  new Zend_Validate_StringLength(0, 2),
									'CC_C'                  =>  new Zend_Validate_StringLength(0, 10),
									'TOG_C'                 =>  new Zend_Validate_StringLength(0, 2),
									'LOCN_STAT'             =>  new Zend_Validate_StringLength(0, 2),
									'MOVE_STAT'             =>  new Zend_Validate_StringLength(0, 2),
									'REPLENISH'             =>  new Zend_Validate_StringLength(0, 1),
									'PACK_T'                =>  new Zend_Validate_StringLength(0, 1),
									'STORE_TYPE'            =>  new Zend_Validate_StringLength(0, 2),
									'STORE_AREA'            =>  new Zend_Validate_StringLength(0, 2),
									'STORE_METH'            =>  new Zend_Validate_StringLength(0, 2),
									'PERM_LEVEL'            =>  new Zend_Validate_StringLength(0, 1),
									'LABEL_DATE'            =>  new Zend_Validate_StringLength(0, 255),
									'LAST_AUDITED_DATE'     =>  new Zend_Validate_StringLength(0, 255),
									'PROD_ID'               =>  new Zend_Validate_StringLength(0, 30),
									'INSTANCE_ID'           =>  new Zend_Validate_StringLength(0, 20),
									'MAX_QTY'               =>  new Zend_Validate_Float(),
									'MIN_QTY'               =>  new Zend_Validate_Float(),
									'REORDER_QTY'           =>  new Zend_Validate_Float(),
									'AISLE_SEQ'             =>  new Zend_Validate_StringLength(0, 2),
									'BAY_SEQ'               =>  new Zend_Validate_StringLength(0, 2),
									'SHELF_SEQ'             =>  new Zend_Validate_StringLength(0, 2),
									'COMPARTMENT_SEQ'       =>  new Zend_Validate_StringLength(0, 2),
									'LAST_UPDATE_DATE'      =>  new Zend_Validate_StringLength(0, 255),
									'LAST_UPDATE_BY'        =>  new Zend_Validate_StringLength(0, 255),
									'PUTAWAY_QTY'           =>  new Zend_Validate_Float(),
									'LOCN_OWNER'            =>  new Zend_Validate_StringLength(0, 10),
									'CURRENT_WH_ID'         =>  new Zend_Validate_StringLength(0, 2),
									'MOVEABLE_LOCN'         =>  new Zend_Validate_StringLength(0, 1),
									'SSN_TRACK'             =>  new Zend_Validate_StringLength(0, 1),
									'LOCN_SEQ'              =>  new Zend_Validate_StringLength(0, 2),
									'TEMPERATURE_ZONE'      =>  new Zend_Validate_StringLength(0, 2),
									'LOCN_TARE_WEIGHT'      =>  new Zend_Validate_Float(),
									'LOCN_TARE_WEIGHT_UOM'  =>  new Zend_Validate_StringLength(0, 2),
									'LOCN_INT_DIMENSION_X'  =>  new Zend_Validate_Float(),
									'LOCN_INT_DIMENSION_Y'  =>  new Zend_Validate_Float(),
									'LOCN_INT_DIMENSION_Z'  =>  new Zend_Validate_Float(),
									'LOCN_DIMENSION_UOM'    =>  new Zend_Validate_StringLength(0, 2),
									'LOCN_OUT_DIMENSION_X'  =>  new Zend_Validate_Float(),
									'LOCN_OUT_DIMENSION_Y'  =>  new Zend_Validate_Float(),
									'LOCN_OUT_DIMENSION_Z'  =>  new Zend_Validate_Float(),
									'LOCN_REPRINT'          =>  new Zend_Validate_StringLength(0, 1),
			 );
		
		$allParams          = $this->getRequest()->getParams();
		$items              = array();
		$validationError    = false;  
		foreach($allParams as $key => $value) {
			if(in_array($key, $allowedOterInputs) && !empty($value)) {
				$items[$key] = $value;
				if(isset($validator[$key]) && !$validator[$key]->isValid($value)){
					$this->addError($key . ' value is too long or have invalid type.');
					$validationError    =   true;
				}
			}
		}
		
		
		if(!array_key_exists('LAST_UPDATE_BY', $items)) {
			$items['LAST_UPDATE_BY'] = $this->minder->userId;
		}
		
		parent::_preProcessNavigation();
		
		$pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
		$resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
		
		if(isset($_POST['action'])){
			$action = $_POST['action'];
		} else {
			$action = '';
		}
		
		
		switch(strtoupper($action)) {
			case 'GENERATE':
					$this->session->activeTab = 'other_inputs';

					if(!$validationError){
						$this->session->locationArray['data']  = array();
						$this->session->locationArray['total'] = 0;
						
						if($this->minder->limitWarehouse === 'all') {
							$this->addError('Please select one Warehouse.');
							break;    
						}
						
						try {

							$locationGenerator = new Minder_LocationGenerator($this->minder->limitWarehouse, $items);
						
							$locationGenerator->setSequence(Minder_LocationGenerator::AISLE, $aisleFrom, $aisleTo, $aisleSequnce, $aisleIncrement);
							$locationGenerator->setSequence(Minder_LocationGenerator::BAY, $bayFrom, $bayTo, $baySequnce, $bayIncrement);
							$locationGenerator->setSequence(Minder_LocationGenerator::SHELF, $shelfFrom, $shelfTo, $shelfSequnce, $shelfIncrement);
							$locationGenerator->setSequence(Minder_LocationGenerator::POSITION, $positionFrom, $positionTo, $positionSequnce, $positionIncrement);

							$locationArray['data']        = $locationGenerator->doGenerate();
							$locationArray['total']       = count($locationArray['data']);
							$this->session->locationArray = $locationArray;
							$this->addMessage('New Locations successfully generated. Generated ' . $locationArray['total'] . ' record(s)');
						} catch (Exception $e) {
							$this->addError('Error generating Locations: ' . $e->getMessage());
							break;    
						}
				}    
				break;
			
			case 'GENERATE-CHECK-DIGITS':

					if(!$validationError){
						$this->session->locationArray['data']  = array();
						$this->session->locationArray['total'] = 0;

						if($this->minder->limitWarehouse === 'all') {
							$this->addError('Please select one Warehouse.');
							break;
						}

						try {
							$this->session->activeTab = 'check_digits';
							$this->getRequest()->setParam('show_by', 100);
							parent::_preProcessNavigation();
							$locationGenerator = new Minder_LocationGenerator_CheckDigit($this->minder->limitWarehouse);

							$locationGenerator->setSequence(Minder_LocationGenerator::AISLE, $aisleFrom, $aisleTo, $aisleSequnce, $aisleIncrement);
							$locationGenerator->setSequence(Minder_LocationGenerator::BAY, $bayFrom, $bayTo, $baySequnce, $bayIncrement);
							$locationGenerator->setSequence(Minder_LocationGenerator::SHELF, $shelfFrom, $shelfTo, $shelfSequnce, $shelfIncrement);
							$locationGenerator->setSequence(Minder_LocationGenerator::POSITION, $positionFrom, $positionTo, $positionSequnce, $positionIncrement);

							$locationArray['data']        = $locationGenerator->doGenerate($checkDigitLength);
							$locationArray['total']       = count($locationArray['data']);
							$this->session->locationArray = $locationArray;
						} catch (Exception $e) {
							$this->addError('Error generating Locations: ' . $e->getMessage());
							break;
						}
				}
				break;

			case 'GENERATE-SEQUENCE':
				if(!$validationError){
					$this->session->locationArray['data']  = array();
					$this->session->locationArray['total'] = 0;

					if($this->minder->limitWarehouse === 'all') {
						$this->addError('Please select one Warehouse.');
						break;
					}

					try {
						$this->session->activeTab = 'sequences';
						$items['LOCN_NAME'] = $altLocnNameFormat;
						$locationGenerator = new Minder_LocationGenerator_Sequence($this->minder->limitWarehouse, $items);

						$locationGenerator->setSequence(Minder_LocationGenerator::AISLE, $aisleFrom, $aisleTo, $aisleSequnce, $aisleIncrement);
						$locationGenerator->setSequence(Minder_LocationGenerator::BAY, $bayFrom, $bayTo, $baySequnce, $bayIncrement);
						$locationGenerator->setSequence(Minder_LocationGenerator::SHELF, $shelfFrom, $shelfTo, $shelfSequnce, $shelfIncrement);
						$locationGenerator->setSequence(Minder_LocationGenerator::POSITION, $positionFrom, $positionTo, $positionSequnce, $positionIncrement);

						$locationArray['data']        = $locationGenerator->doGenerate($sequence, $altFormat, $applyTo, $locnIdStartsFrom, $locnIdEndsWith, isset($_POST['updateOtherInputs']), $generatorForAltLocnName);
						$locationArray['total']       = count($locationArray['data']);
						$locnIdEndsWith               = '';
						$this->session->locationArray = $locationArray;
					} catch (Exception $e) {
						$this->addError('Error generating Locations: ' . $e->getMessage());
						break;
					}
				}
				break;

			case 'APPLY-SEQUENCE':
				if(!$validationError) $this->_updateLocationList(Minder_Routine_LocationGenerate::UPDATE);
				break;

			case 'APPLY-CHECK-DIGITS':
				if(!$validationError) $this->_updateLocationList(Minder_Routine_LocationGenerate::ADD_CHECK_DIGIT);
				break;

			case 'REPORT: CSV':
				if(!$validationError){
					parent::_preProcessNavigation();
					$data = $this->session->locationArray['data'];
					
					if(count($data) > 0) {
						$this->_viewRenderer()->setNoRender();
						$this->_exportHelper()->exportTo($action, $this->view->headers, $data);
						return;
					} else {
						$this->addError('Generate data first.');
					}
				}
				
				break;
			
			case 'ADD/UPDATE':
				if(!$validationError) $this->_updateLocationList(Minder_Routine_LocationGenerate::UPDATE_AND_INSERT);
				break;

			case 'REFRESH':
				if(!$validationError) $this->_updateLocationList(Minder_Routine_LocationGenerate::REFRESH);
				break;
		   
			case 'CLEAR RESULTS':
			case 'CLEAR':
				$this->session->locationArray['data']   =   array();
				$this->session->locationArray['total']  =   0;
				$this->addMessage('Results cleared');
				break;
			
			case 'PRINT':
				
				$printerObj		=	$this->minder->getPrinter();
				$printerObj->setTableName('LOCATION');
				
				$data     = $this->session->locationArray['data'];
				$result   = false;
				$count	  =	0;	
				if(count($data) > 0){
				  foreach($data as $location){
					   try{
							$result	=	$printerObj->printLocationLabel($location->items);
							$result&=	$result; 			
					   } catch(Exception $ex){
							$this->addMessage($ex->getMessage());
							break;	
					   }
					   $count++;
				  }
				  if($result){
					  $this->addMessage($count . ' label(s) printed successfully');
				  } else {
					  $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
				  }
			   } else {
					$this->addError('Missing data for printing');
			   }
			   break;
			
			case 'EXIT':
				$redirector = $this->_helper->getHelper('Redirector');
				$redirector->setCode(303)
						   ->goto('index', 'admin', null, array());
				break;
			
			default:
			
				if(!isset($this->session->locationArray)) {
					$this->session->locationArray['data']  = array();
					$this->session->locationArray['total'] = 0;
				}
		
		}
		
		
		$this->view->aisleFrom          =   $aisleFrom;
		$this->view->aisleTo            =   $aisleTo;
		$this->view->aisleIncrement     =   !empty($_POST['aisle_increment'])? $_POST['aisle_increment']: '';
		
		$this->view->bayFrom            =   $bayFrom;
		$this->view->bayTo              =   $bayTo;
		$this->view->bayIncrement       =   !empty($_POST['bay_increment'])? $_POST['bay_increment']: '';
		
		$this->view->shelfFrom          =   $shelfFrom;
		$this->view->shelfTo            =   $shelfTo;
		$this->view->shelfIncrement     =   !empty($_POST['shelf_increment'])? $_POST['shelf_increment']: '';
		
		$this->view->positionFrom       =   $positionFrom;
		$this->view->positionTo         =   $positionTo;
		$this->view->positionIncrement  =   !empty($_POST['position_increment'])? $_POST['position_increment']: '';
		
		$this->view->aisleDefaultInc    = $aisleDefaultInc;  
		$this->view->bayDefaultInc      = $bayDefaultInc;  
		$this->view->shelfDefaultInc    = $shelfDefaultInc;  
		$this->view->positionDefaultInc = $positionDefaultInc;
		
		$this->view->zoneSequnce        = $zoneSequnce;
		$this->view->aisleSequnce       = $aisleSequnce;
		$this->view->baySequnce         = $baySequnce;
		$this->view->shelfSequnce       = $shelfSequnce;
		$this->view->positionSequnce    = $positionSequnce; 
		
		
		parent::_postProcessNavigation($this->session->locationArray);
		
		$this->view->data  =  array_slice($this->session->locationArray['data'], $pageNo*$resultsPerPage, $this->view->maxno);

		$this->view->locnName           =   !empty($_POST['LOCN_NAME'])? $_POST['LOCN_NAME']: '[%WH_ID%-%AISLE%-%BAY%-%SH%-%POS%]';
		$this->view->locnMetric         =   !empty($_POST['LOCN_METRIC'])? $_POST['LOCN_METRIC']: '';
		$this->view->locnHght           =   !empty($_POST['LOCN_HGHT'])? $_POST['LOCN_HGHT']: ''; 
		$this->view->parentLocnId       =   !empty($_POST['PARENT_LOCN_ID'])? $_POST['PARENT_LOCN_ID']: ''; 
		$this->view->locnType           =   !empty($_POST['LOCN_TYPE'])? $_POST['LOCN_TYPE']: ''; 
		$this->view->ccC                =   !empty($_POST['CC_C'])? $_POST['CC_C']: ''; 
		$this->view->togC               =   !empty($_POST['TOG_C'])? $_POST['TOG_C']: ''; 
		
		$this->view->replenish          =   !empty($_POST['REPLENISH'])? $_POST['REPLENISH']: ''; 
		$this->view->packT              =   !empty($_POST['PACK_T'])? $_POST['PACK_T']: ''; 
		$this->view->storeType          =   !empty($_POST['STORE_TYPE'])? $_POST['STORE_TYPE']: ''; 
		$this->view->storeMeth          =   !empty($_POST['STORE_METH'])? $_POST['STORE_METH']: ''; 
		$this->view->permLevel          =   !empty($_POST['PERM_LEVEL'])? $_POST['PERM_LEVEL']: ''; 
		$this->view->labelDate          =   !empty($_POST['LABEL_DATE'])? $_POST['LABEL_DATE']: ''; 
		
		$this->view->lastAuditedDate    =   !empty($_POST['LAST_AUDITED_DATE'])? $_POST['LAST_AUDITED_DATE']: ''; 
		$this->view->prodId             =   !empty($_POST['PROD_ID'])? $_POST['PROD_ID']: ''; 
		$this->view->maxQty             =   !empty($_POST['MAX_QTY'])? $_POST['MAX_QTY']: ''; 
		$this->view->minQty             =   !empty($_POST['MIN_QTY'])? $_POST['MIN_QTY']: ''; 
		$this->view->recorderQty        =   !empty($_POST['REORDER_QTY'])? $_POST['REORDER_QTY']: ''; 
		$this->view->aisleSeq           =   !empty($_POST['AISLE_SEQ'])? $_POST['AISLE_SEQ']: ''; 
		
		$this->view->baySeq             =   !empty($_POST['BAY_SEQ'])? $_POST['BAY_SEQ']: ''; 
		$this->view->shelfSeq           =   !empty($_POST['SHELF_SEQ'])? $_POST['SHELF_SEQ']: ''; 
		$this->view->compartmentSeq     =   !empty($_POST['COMPARTMENT_SEQ'])? $_POST['COMPARTMENT_SEQ']: ''; 
		$this->view->lastUpdateDate     =   !empty($_POST['LAST_UPDATE_DATE'])? $_POST['LAST_UPDATE_DATE']: ''; 
		$this->view->lastUpdateBy       =   !empty($_POST['LAST_UPDATE_BY'])? $_POST['LAST_UPDATE_BY']: ''; 
		$this->view->putawayQty         =   !empty($_POST['PUTAWAY_QTY'])? $_POST['PUTAWAY_QTY']: ''; 
		$this->view->locnOwner          =   !empty($_POST['LOCN_OWNER'])? $_POST['LOCN_OWNER']: ''; 
		$this->view->currentWhId        =   !empty($_POST['CURRENT_WH_ID'])? $_POST['CURRENT_WH_ID']: ''; 
		
		$this->view->ssnTrack           =   !empty($_POST['SSN_TRACK'])? $_POST['SSN_TRACK']: ''; 
		$this->view->locnSeq            =   !empty($_POST['LOCN_SEQ'])? $_POST['LOCN_SEQ']: ''; 
		$this->view->temperatureZone    =   !empty($_POST['TEMPERATURE_ZONE'])? $_POST['TEMPERATURE_ZONE']: ''; 
		$this->view->locnTareWeight     =   !empty($_POST['LOCN_TARE_WEIGHT'])? $_POST['LOCN_TARE_WEIGHT']: ''; 
		$this->view->locnTareWeightUom  =   !empty($_POST['LOCN_TARE_WEIGHT_UOM'])? $_POST['LOCN_TARE_WEIGHT_UOM']: ''; 
		$this->view->locnIntDimensionX  =   !empty($_POST['LOCN_INT_DIMENSION_X'])? $_POST['LOCN_INT_DIMENSION_X']: ''; 
		$this->view->locnIntDimensionY  =   !empty($_POST['LOCN_INT_DIMENSION_Y'])? $_POST['LOCN_INT_DIMENSION_Y']: ''; 
		$this->view->locnIntDimensionZ  =   !empty($_POST['LOCN_INT_DIMENSION_Z'])? $_POST['LOCN_INT_DIMENSION_Z']: ''; 
		$this->view->locnIntDimensionUom=   !empty($_POST['LOCN_DIMENSION_UOM'])? $_POST['LOCN_DIMENSION_UOM']: ''; 
		$this->view->locnOutDimensionX  =   !empty($_POST['LOCN_OUT_DIMENSION_X'])? $_POST['LOCN_OUT_DIMENSION_X']: ''; 
		$this->view->locnOutDimensionY  =   !empty($_POST['LOCN_OUT_DIMENSION_Y'])? $_POST['LOCN_OUT_DIMENSION_Y']: ''; 
		$this->view->locnOutDimensionZ  =   !empty($_POST['LOCN_OUT_DIMENSION_Z'])? $_POST['LOCN_OUT_DIMENSION_Z']: '';
		$this->view->zoneC              =   !empty($_POST['ZONE_C'])? $_POST['ZONE_C']: ''; 

		/**
		 * @var Zend_Controller_Request_Http $request
		 */
		$request = $this->getRequest();
		$this->view->locnStat           =   !empty($_POST['LOCN_STAT'])? $_POST['LOCN_STAT']: ($request->isPost() ? '' : 'OK');
		$this->view->moveStat           =   !empty($_POST['MOVE_STAT'])? $_POST['MOVE_STAT']: ($request->isPost() ? '' : 'ST');
		$this->view->storeArea          =   !empty($_POST['STORE_AREA'])? $_POST['STORE_AREA']: ($request->isPost() ? '' : 'ST');
		$this->view->instanceId         =   !empty($_POST['INSTANCE_ID'])? $_POST['INSTANCE_ID']: ($request->isPost() ? '' : 'MASTER    ');
		$this->view->moveableLocn       =   !empty($_POST['MOVEABLE_LOCN'])? $_POST['MOVEABLE_LOCN']: ($request->isPost() ? '' : 'F');
		$this->view->locnReprint        =   !empty($_POST['LOCN_REPRINT'])? $_POST['LOCN_REPRINT']: ($request->isPost() ? '' : 'F');

		$this->view->activeTab = isset($this->session->activeTab) ? $this->session->activeTab : 'GENERATE';
		$this->view->checkDigitLength = $checkDigitLength;

		$this->view->sequenceOrder  = $sequenceOrder;
		$this->view->startSequence  = $startSequence;
		$this->view->applyTo        = $applyTo;

		$this->view->seq                        = $sequence;
		$this->view->altFormat                  = $altFormat;
		$this->view->altLocnNameFormat          =  $altLocnNameFormat;
		$this->view->generatorForAltLocnName    =  $generatorForAltLocnName;
		$disableSubBay = $this->minder->getOptionsRecordByCode('LOC_GEN', 'DISABLE_SEQ_SUB_BAY');
		$disableSubBay = count($disableSubBay) ? $disableSubBay[0]['DESCRIPTION'] : 'F';
		$this->view->disableSubBay = (strtoupper($disableSubBay) == 'T');

		$this->view->locnIdStartsFrom   = $locnIdStartsFrom;
		$this->view->locnIdEndsWith     = $locnIdEndsWith;
		$this->view->updateOtherInputs  = $this->getRequest()->isPost() ? isset($_POST['updateOtherInputs']) : true;

		switch (strtoupper($this->view->activeTab)) {
			case 'OTHER_INPUTS':
				$this->view->headers = array('LOCN_ID'       => 'Location Id',
					'WH_ID'         => 'Warehouse Id',
					'LOCN_NAME'     => 'Location Name',
					'LOCN_STAT'     => 'Location Stat',
					'MOVE_STAT'     => 'Move Stat',
					'STORE_AREA'    => 'Store Area',
					'MOVEABLE_LOCN' => 'Moveable Locn',
					'LOCN_REPRINT'  => 'Reprint Locn',
				);
				break;
			case 'SEQUENCES':
				$this->view->headers = array('LOCN_ID'       => 'Location Id',
					'WH_ID'         => 'Warehouse Id',
					'LOCN_NAME'     => 'Location Name',
					'LOCN_ID2'      => 'LOCN_ID2',
					'LOCN_SEQ'      => 'LOCN_SEQ',
				);
				break;
		}

	}

	protected function _updateLocationList($method)
	{
		$locationList = $this->session->locationArray['data'];
		$total = $this->session->locationArray['total'];
		if ($total > 0) {
			$routine = new Minder_Routine_LocationGenerate();

			try {
				$result  = $routine->updateLocation($locationList, $method);

				$this->session->locationArray['data'] = array();
				$this->session->locationArray['total'] = 0;

				if ($result->inserted > 0)
					$this->addMessage('Locations successfully added. Add ' . $result->inserted . ' record(s)');

				if ($result->updated > 0)
					$this->addMessage('Locations successfully updated. Update ' . $result->updated . ' record(s)');

				if ($result->inserted < 1 && $result->updated < 1) {
					$this->addWarning('No records out of ' . $total . ' were inserted or updated.');
				}
			} catch (Exception $e) {
				$this->addError($e->getMessage());
			}

		} else {
			$this->addWarning('Generate data first.');
		}
	}

	/**
	* @desc transfore intreger value to string value with leeding zero
	* 
	* @param integer $int - input integer value
	* @param 
	* @return string values 
	*/
	protected function intToStr($int, $byteLength) {
		if($int != 0) {
			if($byteLength == 'N2') {
				if($int <= 9) {
					return '0'. (string)$int;
				}
			}             
				return (string)$int;
		} else {
			return '';
		}
	}
	
	public function slottingAction() {
	
		$this->view->pageTitle = 'SLOTTING PRODUCTS';
		
		if(!isset($this->session->selectedSlottingProducts)) {
			$this->session->selectedSlottingProducts = array();
		}       
		
		$this->view->headers   = array('PROD_ID'              => 'PROD_ID',
									   'SHORT_DESC'           => 'SHORT_DESC',
									   'HITS'                 => 'Hits/Mth',
									   'SALES'                => 'Sales/Mth',
									   'DIMENSION_X'          => 'DIM_X',
									   'DIMENSION_Y'          => 'DIM_Y',
									   'DIMENSION_Z'          => 'DIM_Z',
									   'STORE_TYPE'           => 'Store Type',
									   'ALT_PROD_TYPE'        => 'Alt Type',
									   'LOCN_ID'              => 'LOCN_ID',
									   'LOCN_INT_DIMENSION_X' => 'DIM_X',
									   'LOCN_INT_DIMENSION_Y' => 'DIM_Y',
									   'LOCN_INT_DIMENSION_Z' => 'DIM_Z'
									  );
		
		 $allowedProdProfile =  array('prod_id'                 => 'PROD_PROFILE.PROD_ID = ? AND ',
									  'short_desc'              => 'PROD_PROFILE.SHORT_DESC = ?  AND ',
									  'prod_type'               => 'PROD_PROFILE.PROD_TYPE = ? AND ',
									  'alt_prod_type'           => 'PROD_PROFILE.ALT_PROD_TYPE = ? AND ',
									  'store_type'              => 'PROD_PROFILE.STORE_TYPE = ? AND ',
									  'tog_c'                   => 'PROD_PROFILE.TOG_C =  ? AND ',
									  'locn_visit_per_mth_from' => 'PROD_PROFILE.LOCN_VISIT_PER_MTH >= ? AND ',
									  'locn_visit_per_mth_to'   => 'PROD_PROFILE.LOCN_VISIT_PER_MTH <= ? AND ',
									  'sale_per_mth_from'       => 'PROD_PROFILE.SALE_VOL_PER_MTH >= ? AND ',
									  'sale_per_mth_to'         => 'PROD_PROFILE.SALE_VOL_PER_MTH <= ? AND ',
									  'net_weight_from'         => 'PROD_PROFILE.NET_WEIGHT >= ? AND ',
									  'net_weight_to'           => 'PROD_PROFILE.NET_WEIGHT <= ? AND ',
									  'company_id'              => 'PROD_PROFILE.COMPANY_ID = ? AND ',
									  'orientation'             => 'PROD_PROFILE.ORIENTATION = ? AND '
									 );
		 
		 $allowedLocation = array('wh_id'                   => 'LOCATION.WH_ID = ? AND ',
								  'locn_id'                 => 'LOCATION.LOCN_ID = ? AND ',
								  'zone_c'                  => 'LOCATION.ZONE_C = ? AND ',
								  'store_type'              => 'LOCATION.STORE_TYPE = ? AND ',
								  'tog_c'                   => 'LOCATION.TOG_C = ? AND ',
								  'store_meth'              => 'LOCATION.STORE_METH = ? AND ',
								  'store_area'              => 'LOCATION.STORE_AREA = ? AND ',
								  'locn_type'               => 'LOCATION.LOCN_TYPE = ? AND ',
								  'locn_metric'             => 'LOCATION.LOCN_METRIC = ? AND ',
								  'store_area'              => 'LOCATION.STORE_AREA = ? AND '
								 );
		
		$conditions          = $this->_setupConditions(null, $allowedProdProfile);
		$clauseProdProfile   = $this->_makeClause($conditions, $allowedProdProfile);
		
		$conditions       = $this->_setupConditions(null, $allowedLocation);
		$clauseLocation   = $this->_makeClause($conditions, $allowedLocation);
		
		
		if($this->getRequest()->getParam('dimensions_x') == 'checked') {
			$clauseLocation['LOCN_INT_DIMENSION_X >= ? AND '] = '';       
		}
		if($this->getRequest()->getParam('dimensions_y') == 'checked') {
			$clauseLocation['LOCN_INT_DIMENSION_Y >= ? AND '] = '';       
		}
		if($this->getRequest()->getParam('dimensions_z') == 'checked') {
			$clauseLocation['LOCN_INT_DIMENSION_Z >= ? AND '] = '';       
		}
		
		parent::_preProcessNavigation();
		
		$pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
		$resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
		
		if(isset($_POST['action'])){
			$action = $_POST['action'];
		} elseif(isset($_GET['action'])) {
			$action = $_GET['action'];
		} else {
			$action = '';
		}
		
		$action = strtoupper($action);
		switch($action) {
			case 'UPDATE+CSV':
				
				$data = $this->session->slottingProducts['data'];
				
				$selectedSlottingProducts = $this->session->selectedSlottingProducts;
				foreach($selectedSlottingProducts as $prodId => $locnId) {
					if($locnId !== '***') {
						$result = $this->minder->updateSlottedProduct($prodId , $locnId);
						$result = $result && $this->minder->updateSlottedLocation($prodId , $locnId); 
					 
						if(!$result) {
							$this->addError('Prod Id: ' . $prodId .' Location: ' . $locnId . ' Error while update location or prod_id.');
						}
					}
				}
				if($result) {
				   $this->addMessage('Updated Data successfull');
				}
				
				$data = $this->session->slottingProducts['data'];
				
				$data = array();
				foreach($this->session->slottingProducts['data'] as $product) {
					if(array_key_exists($product['PROD_ID'], $this->session->selectedSlottingProducts)) {
						$data[] = $product;
					}
				}
				
				// delete all session variables
				$this->session->selectedSlottingProducts = array();

				if(count($data) > 0) {
					$this->_viewRenderer()->setNoRender();
					$this->_exportHelper()->exportTo('REPORT: CSV', $this->view->headers, $data);
				}
				break;      
			
			case 'REPORT: CSV':
			case 'REPORT: XLS':
				$data = array();
				foreach($this->session->slottingProducts['data'] as $product) {
					if(array_key_exists($product['PROD_ID'], $this->session->selectedSlottingProducts)) {
						$data[] = $product;
					}
				}
			   
				if(count($data) > 0) {
					$this->_viewRenderer()->setNoRender();
					$this->_exportHelper()->exportTo($action, $this->view->headers, $data);
				}
				 
				break;
			
			case 'SELECT':
				if($_GET['condition'] === 'true') {
					$data     = explode('|', $_GET['value']);
					$this->session->selectedSlottingProducts[$data[0]] = $data[1]; 
		
				} else {
					$data     = explode('|', $_GET['value']);
					unset($this->session->selectedSlottingProducts[$data[0]]);
				}
				
				echo json_encode(array('selected_num' => count($this->session->selectedSlottingProducts)));
				exit();
			case 'REFRESH':
			
					break;
			
			default:
			
			
		}
									  
		$this->session->slottingProducts = $this->view->slottingProducts = $this->minder->getSlottedProducts($clauseProdProfile, $clauseLocation, $pageNo, $resultsPerPage);        
	
		parent::_postProcessNavigation($this->view->slottingProducts);
		
		$this->view->data               =   $this->view->slottingProducts['data'];
		
		$this->view->prodId             =   !empty($_POST['prod_id'])? $_POST['prod_id']: '';   
		$this->view->shortDesc          =   !empty($_POST['short_desc'])? $_POST['short_desc']: '';   
		$this->view->prodType           =   !empty($_POST['prod_type'])? $_POST['prod_type']: '';   
		$this->view->altProdType        =   !empty($_POST['alt_prod_type'])? $_POST['alt_prod_type']: '';   
		$this->view->storeType          =   !empty($_POST['store_type'])? $_POST['store_type']: '';   
		$this->view->togC               =   !empty($_POST['tog_c'])? $_POST['tog_c']: '';   
		$this->view->locnVisitPerMthFrom=   !empty($_POST['locn_visit_per_mth_from'])? $_POST['locn_visit_per_mth_from']: '';   
		$this->view->locnVisitPerMthTo  =   !empty($_POST['locn_visit_per_mth_to'])? $_POST['locn_visit_per_mth_to']: '';   
		$this->view->salePerMthFrom     =   !empty($_POST['sale_per_mth_from'])? $_POST['sale_per_mth_from']: '';   
		$this->view->salePerMthTo       =   !empty($_POST['sale_per_mth_to'])? $_POST['sale_per_mth_to']: '';   
		$this->view->newWeightFrom      =   !empty($_POST['net_weight_from'])? $_POST['net_weight_from']: '';   
		$this->view->newWeightTo        =   !empty($_POST['net_weight_to'])? $_POST['net_weight_to']: '';   
		$this->view->companyId          =   !empty($_POST['company_id'])? $_POST['company_id']: '';   
		$this->view->orientation        =   !empty($_POST['orientation'])? $_POST['orientation']: '';   
		
		$this->view->whId               =   !empty($_POST['wh_id'])? $_POST['wh_id']: '';
		$this->view->locnId             =   !empty($_POST['locn_id'])? $_POST['locn_id']: '';
		$this->view->zoneC              =   !empty($_POST['zone_c'])? $_POST['zone_c']: '';
		$this->view->storeType          =   !empty($_POST['store_type'])? $_POST['store_type']: '';
		$this->view->togC               =   !empty($_POST['tog_c'])? $_POST['tog_c']: '';
		$this->view->storeMeth          =   !empty($_POST['store_meth'])? $_POST['store_meth']: '';
		$this->view->storeArea          =   !empty($_POST['store_area'])? $_POST['store_area']: '';
		$this->view->locnType           =   !empty($_POST['locn_type'])? $_POST['locn_type']: '';
		$this->view->locnMetric         =   !empty($_POST['locn_metric'])? $_POST['locn_metric']: '';
		
		$this->view->selectedSlottingProducts = $this->session->selectedSlottingProducts;
		$this->view->selectedNum              = count($this->session->selectedSlottingProducts);
		
		
	}
	
	public function sysBackupAction() {
		if (!$this->minder->isSysAdmin()) {
			$redirector = $this->_helper->getHelper('Redirector');
			$redirector->setCode(303)->goto('index', 'admin', '', array());
		}
		
		$this->view->pageTitle = 'Backup / Restore Tool';
	}
	
	public function doSysBackupAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		$this->getResponse()->setHeader('Content-Type', 'text/csv')
							->setHeader('Content-Disposition', 'attachment; filename="system_defaults.csv"');
							
		$sysBackup = new Minder_SysBackup();
		$sysBackup->doBackup(new Minder_SysBackup_Writer_Csv());
	}
	
	public function checkRestoreFileAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		
		$result = new stdClass();
		$result->errors            = array();
		$result->warnings          = array();
		$result->messages          = array();
		$result->fileCheckMessages = array();
		$result->fileCheckErrors   = array();
		
		if (!$this->minder->isSysAdmin()) {
			$result->errors[] = 'Only Sys Admin can do this.';
			echo json_encode($result);
			return;
		}

		$restoreFile = new Zend_Form_Element_File('backup_file');
		if (!$restoreFile->receive()) {
			$result->errors[] = 'Error uploading file.';
			echo json_encode($result);
			return;
		}
		
		try {
			$backupReader              = new Minder_SysBackup_Reader_Csv($restoreFile->getFileName());
			$backupReader->open();
			$sysBackup                 = new Minder_SysBackup();
			$this->session->backupData = $sysBackup->checkBackup($backupReader);
			$result->fileCheckMessages = array_merge($result->fileCheckMessages, $sysBackup->getCheckMessages());

			if ($sysBackup->wasCheckErrors()) {
				$result->fileCheckErrors = array_merge($result->fileCheckErrors, $sysBackup->getCheckErrors());

				if (isset($this->session->backupData))
					unset($this->session->backupData);
			}

			$backupReader->close();
		} catch (Exception $e) {
			$backupReader->close();
			$result->errors[] = $e->getMessage();
		}

		echo json_encode($result);
	}
	
	public function doRestoreAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		
		$result = new stdClass();
		$result->errors            = array();
		$result->warnings          = array();
		$result->messages          = array();
		
		if (!$this->minder->isSysAdmin()) {
			$result->errors[] = 'Only Sys Admin can do this.';
			echo json_encode($result);
			return;
		}

		if (!isset($this->session->backupData) || !is_array($this->session->backupData)) {
			$result->errors[] = 'No backup data given.';
			echo json_encode($result);
			return;
		}

		$this->cache()->dropScreenDescriptionCache();
		$sysBackup = new Minder_SysBackup();
		if ($sysBackup->doRestore($this->session->backupData)) {
			$result->messages   = array_merge($result->messages, $sysBackup->getRestoreMessages());
		} else {
			$result->warnings[] = 'There were errors during data restoration. Check backup file.';
			$result->errors     = array_merge($result->errors, $sysBackup->getRestoreErrors());
		}
		
		unset($this->session->backupData);
		
		echo json_encode($result);
	}
	
	/**
	* @desc ged additional headers and add to CRUD headers
	* @param $tableName - string table name
	* 
	* @return $headers - additional heders
	*/
	private function getAdditionalHeaders($tableName, $headers) {
	
		$tableName  =   strtoupper($tableName);
		switch($tableName) {
			case 'PURCHASE_ORDER':
				break;
			
			case 'PICK_ORDER':
				break;
			
			case 'LOCATION':
				$headersToDelete = array('LOCN_METRIC');
				foreach($headersToDelete as $value) {
					unset($headers[$value]);
				}
			   
				$headerToAdd    =   array('LOCN_INT_DIMENSION_X' =>  'INT_X',
										  'LOCN_INT_DIMENSION_Y' =>  'INT_Y',
										  'LOCN_INT_DIMENSION_Z' =>  'INT_Z');
				$headers        =   array_merge($headers, $headerToAdd);       
				break;
		   
		   case 'SYS_LABEL_VAR':
				$headersToDelete = array('RECORD_ID');
				foreach($headersToDelete as $value) {
					unset($headers[$value]);
				}
				
				$headers        =   array_merge($headers, array('SL_NAME'   => 'SL_NAME'));
									krsort($headers);
				$headers        =   array_merge($headers, array('RECORD_ID'   => 'RECORD_ID'));
				   
				
				break;
		   
		   case 'PRINT_REQUESTS':
				
				$data    =   $this->minder->getUserHeaders('SCN_PRINTR');
				$headers =   $data['headers'];
			
				break;
		   case 'SSN_HIST':
				$headers        =   array_merge($headers, array('TRN_TYPE'      => 'TRN_TYPE', 
																'TRN_CODE'      => 'TRN_CODE', 
																'REFERENCE'     => 'REFERENCE ',
																'ERROR_TEXT'    => 'ERROR_TEXT',
																'QTY'           => 'QTY',
																'SUB_LOCN_ID'   => 'SUB_LOCN_ID',
																'DEVICE_ID'     => 'DEVICE_ID',
																'PERSON_ID'      => 'PERSON_ID'));
				unset($headers['RECORD_ID']);
				break;
		   case 'PICK_INVOICE':
				$headers        =   array_merge($headers, array('PICK_ORDER'    => 'PICK_ORDER'));
				break;
		}
		
			return $headers;
	}
	
	/**
	* @desc added additional conditions to the clause
	* @param $clause - array of clause
	* 
	* @return $clause  
	*/
	private function addedAdditionalConditions($clause) {
		
		foreach($clause as $key => $value) {
			$value = strtoupper($value);
			if(stripos($value, 'NULL')) {
				$newKey = str_replace('LIKE ?', $value, $key);
				unset($clause[$key]);
				$clause[$newKey]    =   '';
			}
			if(stristr($value, '=')) {
				$newKey = str_replace('LIKE ?', $value, $key);
				unset($clause[$key]);
				$clause[$newKey]    =   '';
		   
			}
			if(stristr($value, '<')) {
				$newKey = str_replace('LIKE ?', $value, $key);
				unset($clause[$key]);
				$clause[$newKey]    =   '';
		   
			}
			if(stristr($value, '>')) {
				$newKey = str_replace('LIKE ?', $value, $key);
				unset($clause[$key]);
				$clause[$newKey]    =   '';
		   
			}
			if(stristr($value, 'LIKE')) {
				$newKey = str_replace('LIKE ?', $value, $key);
				unset($clause[$key]);
				$clause[$newKey]    =   '';
		   
			}
	  
		}
		return $clause;
	}
	/**
	* @desc get order instruction for current table
	* @param string $tableName - table name
	* 
	* @return string order instructions
	*/
	private function getOrdering($tableName){
	
		 $orderArray =   array();   
		 $tableName  =   strtoupper($tableName);
		 
		 switch($tableName){
		 
			 case 'PRINT_REQUESTS':
				$orderArray[]   =   'PRN_DATE DESC';
				$orderArray[]   =   'MESSAGE_ID DESC';
				break;
			 case 'TRANSACTIONS_ARCHIVE':
				$orderArray[]   =   'TRN_DATE DESC';
				break;
			 case 'TRANSACTIONS4_ARCHIVE':
				$orderArray[]   =   'TRN_DATE DESC';
				break;
			 case 'SSN_HIST':
				$orderArray[]   =   'RECORD_ID DESC';
				break;
			 case 'SSN_TEST':
				$orderArray[]   =   'TEST_ID DESC';
				break;
			 case 'SSN_TEST_RESULTS':
				$orderArray[]   =   'SSN_TEST_ID DESC';
				break;
			 case 'SESSION':
				$orderArray[]   =   'DEVICE_ID, CODE';
				break;
			  case 'PICK_ITEM_CANCEL':
				$orderArray[]   =   'LAST_UPDATE_DATE DESC';
				break;
			 default:
				$orderArray[]   =   '1';
		 }
		 
		 return $orderArray;
	}
	
	private function addDefaultValues($table, $update){
		
		$tableName  =   strtoupper($table);
		
		switch($tableName){
			case  'SYS_USER':
				if(empty($update['status_adjust_issn'])){
					$update['status_adjust_issn'] = 'F';    
				}
				if(empty($update['status_adjust_pick_item'])){
					$update['status_adjust_pick_item']  =   'F';    
				}
				if(empty($update['status_adjust_pick_order'])){
					$update['status_adjust_pick_order']  =   'F';    
				}
				if(empty($update['status_adjust_purchase_order'])){
					$update['status_adjust_purchase_order']  =   'F';    
				}
				if(empty($update['status_adjust_po_line'])){
					$update['status_adjust_po_line']  =   'F';    
				}
				break;
			case 'IMPORT_MAP':
				if (empty($update['map_import_sheet']))
					$update['map_import_sheet'] = 'NOT_USED';
				break;
			default:
				$update = $this->_crudHelper()->fillDefaults($tableName, $update);
		}
		
		return $update;    
	}

	protected function _clearCache($tableName) {
		$this->cache()->clear($tableName);
	}

	protected function _saveData($table, $dataset)
	{
		$this->_clearCache($table);
		$validateResult = new Minder_JSResponse();

 		$add_save_array=$this->getRequest()->getPost();
        $session = new Zend_Session_Namespace();

        // NOT REQUIRED
		/*foreach($add_save_array as $key=>$value){
			if (DateTime::createFromFormat('Y/m/d H:i:s', $value) !== FALSE  || DateTime::createFromFormat('Y/m/d', $value) !== FALSE) { 		

                $tz_from=$session->BrowserTimeZone;
                $to_date     = $add_save_array[$key];
                $datetimet = $to_date;
                $tz_tot = 'UTC';
                $format = 'Y/m/d h:i:s';
                $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                $dtt->setTimeZone(new DateTimeZone($tz_tot));
                $add_save_array[$key]=$dtt->format($format);

                $add_save_array[$key]= $this->minder->getFormatedDateToDb($value);
        	}        
		}*/
		
		$update = $add_save_array;
		if (isset($_FILES) && count($_FILES) > 0) {
			foreach ($_FILES as $key => $item) {
				if ($item['name']) {
					$file = new Zend_Form_Element_File($key);

					if ($file->receive()) {
						$fileData     = file_get_contents($file->getFileName());
						$update[$key] = $fileData;
						unlink($file->getFileName());
					} else {
						$validateResult->addErrors($file->getMessages());
					}
				}
			}
		}

		$update = $this->addDefaultValues($table, $update);
		$validateResult = $this->_crudHelper()->validateData($table, $update, $validateResult);

		$this->view->row->save($update);

		$validateResult->addErrors($this->view->row->getValidationErrorList());

		if (!$validateResult->hasErrors()) {
			if ($dataset->getRecord($this->view->row->id)) {
				$flagReread = false;
			} else {
				$flagReread = true;
			}


			$dataset->setRecord($this->view->row);
			if ($this->minder->updateMasterTableDataSet($dataset)) {
				switch ($flagReread) {
					case true:
						$params = array('table' => $table);
						$this->addMessage('Record ' . $this->view->row->id . ' updated successfully');
						$this->_redirector = $this->_helper->getHelper('Redirector');
						$this->_redirector->setCode(303)->goto('crud', 'admin', 'default', $params);
						return;
						break;

					default:
						$this->addMessage('Record ' . $this->view->row->id . ' updated successfully');
						$this->view->row->regenerateId();
						break;
				}

			} else {
				$this->addError($this->minder->lastError);
			}
		} else {
			$this->addError($validateResult->errors);
		}
	}

	protected function _refreshData($table, $allowed, $correction = 0)
	{
		$this->view->conditions = $conditions = $this->_getConditions($table, array(), $allowed);

		$clause = $this->_makeClause($conditions[$table], $allowed);
		$clause += array('_limit_'  => $this->view->navigation[$table]['show_by'] ,
						 '_offset_' => $this->view->navigation[$table]['show_by'] *
									   ($this->view->navigation[$table]['pageselector'] - $correction));

		$clause     = $this->addedAdditionalConditions($clause);
		$order      = $this->getOrdering($table);
		$dataset    = $this->minder->getMasterTableDataSet($table, array('*'), $clause, $order);

		$recordId   = $dataset->getRecordIdByRowNumber($this->view->rowNumber);

		$this->view->row = $dataset->getRecord($recordId);
		$this->view->row = $dataset->getRecord($recordId);

		$this->view->dataset = $dataset;
	}

	protected function _getMenuId()
	{
		return 'ADMIN';
	}

	/**
	 * @return Minder_Controller_Action_Helper_Crud
	 */
	protected function _crudHelper() {
		return $this->getHelper('Crud');
	}
}
