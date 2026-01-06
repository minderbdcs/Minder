<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007-2008 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Warehouse_ReplaceController
 *
 * Action controller
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2008 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class Warehouse_ReplaceController extends Minder_Controller_Action
{
    protected $_showBy = 5;

    public function indexAction()
    {
        $this->_setupConditions();

        $this->view->masterDataTableList = minder_array_merge( array('' => ''), $this->minder->getOptionsList('UPDT_MDATA', 'DESCRIPTION', true));

    }

    public function searchAction() {
        
        
        $this->_preProcessNavigation();
        
        $selectedMasterTable=   $this->getRequest()->getParam('select_table');
        $searchedParams     =   $this->getRequest()->getParam('params');
        $selectedTableData  =   explode('|', $selectedMasterTable);
        $searchedParams     =   explode('&', $searchedParams);
        
        $serchedData        =   array();
        foreach($searchedParams as $value){
            
            list($rowName, $rowValue)   =   explode('=', $value);
            
            $serchedData[strtoupper($rowName)]      =    urldecode($rowValue);
        }
        
        $selectedTable      =   $selectedTableData[0];
        $transactionName    =   $selectedTableData[1];
        $tableDescription   =   $selectedTableData[2];
        
        $searchedFields     =   current($this->minder->getOptionsRecordByCode('UPDT_MDATA', $selectedMasterTable));
        
        $searchedFields     =   explode('|', $searchedFields['DESCRIPTION2']);
        
        $headers            =   array();
        foreach($searchedFields as $value){
            $headers[]    =   $value;    
        }
        
        $clause             =   array();
        if(!empty($serchedData)){
            foreach($serchedData as $key => $value){
                if(!empty($key) && !empty($value)){
                    $key            =   strtoupper($key) . ' LIKE ? AND ';
                    $clause[$key]   =   $value;   
                }
            }
        }
        
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];   
        
        $tableData  =   $this->minder->getTableDataList($selectedTable, $headers, $clause, $pageSelector, $showBy);
        
        $this->view->searchedFields =   $searchedFields;
        $this->view->headers        =   $headers;
        $this->view->tableName      =   $selectedTable;
        $this->view->tableData      =   $tableData['data'];
        $this->view->conditions     =   $serchedData;
        
        $this->_postProcessNavigation($tableData);
          
     }
    
    public function replaceAction(){
        
        $this->_preProcessNavigation();
        
        $selectedMasterTable=   $this->getRequest()->getParam('select_table');
        $searchedParams     =   $this->getRequest()->getParam('params');
        $selectedTableData  =   explode('|', $selectedMasterTable);
        $searchedParams     =   explode('&', $searchedParams);
        
        $serchedData        =   array();
        foreach($searchedParams as $value){
            
            list($rowName, $rowValue)   =   explode('=', $value);
            
            $serchedData[strtoupper($rowName)]      =   urldecode($rowValue);
        } 
        
        $selectedTable      =   $selectedTableData[0];
        $transactionName    =   $selectedTableData[1];
        $tableDescription   =   $selectedTableData[2];
        
        $searchedFields     =   current($this->minder->getOptionsRecordByCode('UPDT_MDATA', $selectedMasterTable));
        
        $searchedFields     =   explode('|', $searchedFields['DESCRIPTION2']);
        
        $headers            =   array();
        foreach($searchedFields as $value){
            $headers[]    =   $value;    
        }
        
        $clause             =   array();
        if(!empty($serchedData)){
            foreach($serchedData as $key => $value){
                if(!empty($key) && !empty($value)){
                    $key            =   strtoupper($key) . ' LIKE ? AND ';
                    $clause[$key]   =   $value;   
                }
            }
        }
        
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];   
        
        $tableData  =   $this->minder->getTableDataList($selectedTable, $headers, $clause, $pageSelector, $showBy);
        
        $this->view->searchedFields =   $searchedFields;
        $this->view->headers        =   $headers;
        $this->view->tableName      =   $selectedTable;
        $this->view->tableData      =   $tableData['data'];
        $this->view->conditions     =   $serchedData;
    
        
        $this->_postProcessNavigation($tableData);    
    }
    
    public function updateAction(){
        
        $beReplaced             =   $this->getRequest()->getParam('be_replaced');
        $beReplaced             =   json_decode($beReplaced);
        $replacedBy             =   $this->getRequest()->getParam('replaced_by');
        $selectedMasterTable    =   $this->getRequest()->getParam('select_table');
        $selectedTableData      =   explode('|', $selectedMasterTable);
        
        $selectedTable          =   $selectedTableData[0];
        $transactionName        =   "Transaction_{$selectedTableData[1]}A";
        $tableDescription       =   $selectedTableData[2];
        
        switch($selectedTable){
            case 'COST_CENTRE':
                foreach($beReplaced as $object){
                    $clause                               =   array();
                    $clause['SSN.COST_CENTER = ? AND ']   =   $object->code;   
                    
                    $ssnList    =   $this->minder->getSsns($clause, 0, 9999);
                    
                    if($ssnList['total'] > 0){
                        foreach($ssnList['data'] as $ssn){
                            
                            $transaction                    =   new $transactionName;
                            $transaction->ssnId          =   $ssn->id;
                            $transaction->costCenterValue   =   $replacedBy;
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            $clause =   array('CODE = ? AND, ' => $object->code);
                            $this->minder->deleteCostCentreLineByClause($clause);
                            
                        }
                    }
                }
                break;
                
            case 'BRAND':
                foreach($beReplaced as $object){
                    $clause                         =   array();
                    $clause['SSN.BRAND = ? AND ']   =   $object->code;   
                    
                    $ssnList    =   $this->minder->getSsns($clause, 0, 9999);
                    
                    if($ssnList['total'] > 0){
                        foreach($ssnList['data'] as $ssn){
                            
                            $transaction                    =   new $transactionName;
                            $transaction->ssnId          =   $ssn->id;
                            $transaction->brandCodeValue    =   $replacedBy;
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            $clause =   array('CODE = ? AND, ' => $object->code);
                            $this->minder->deleteBrandLineByClause($clause);
                            
                        }
                    }
                }
                break;
                
            case 'GENERIC':
                
                $replacedBy =   explode('|', $replacedBy);
                
                $replacedByCode       =   $replacedBy[0];
                $replacedBySsnType    =   $replacedBy[1];
                
                foreach($beReplaced as $object){
                    $clause                         =   array();
                    
                    $beReplacedData                 =   explode('|', $object->code);
                    $beReplacedCode                 =   $beReplacedData[0];
                    $beReplacedSsnType              =   $beReplacedData[1];
                    
                    $clause['SSN.SSN_TYPE = ? AND ']   =   $beReplacedSsnType;   
                    $clause['SSN.GENERIC = ? AND ']    =   $beReplacedCode;   
                    
                    $ssnList    =   $this->minder->getSsns($clause, 0, 9999);
                    
                    if($ssnList['total'] > 0){
                        foreach($ssnList['data'] as $ssn){
                            
                            $transaction                    =   new $transactionName;
                            $transaction->ssnId          =   $ssn->id;
                            $transaction->genericValue      =   $replacedByCode;
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            $clause =   array('CODE = ? AND '      => $beReplacedCode,
                                              'SSN_TYPE = ? AND '  => $beReplacedSsnType);
                                              
                            $this->minder->deleteGenericByClause($clause);
                            
                        }
                    }
                }
                
                break;
                
            case 'SSN_TYPE':
                foreach($beReplaced as $object){
                    $clause                         =   array();
                    $clause['SSN.SSN_TYPE = ? AND ']   =   $object->code;   
                    
                    $ssnList    =   $this->minder->getSsns($clause, 0, 9999);
                    
                    if($ssnList['total'] > 0){
                        foreach($ssnList['data'] as $ssn){
                            
                            $transaction                    =   new $transactionName;
                            $transaction->ssnId          =   $ssn->id;
                            $transaction->ssnTypeValue      =   $replacedBy;
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            $clause =   array('CODE = ? AND, ' => $object->code);
                            $this->minder->deleteSsnTypeLineByClause($clause);
                            
                        }
                    }
                }
                break;
                
            case 'SSN_SUB_TYPE':
            
                $replacedBy =   explode('|', $replacedBy);
                
                $replacedByCode       =   $replacedBy[0];
                $replacedBySsnType    =   $replacedBy[1];
                $replacedBySsnGeneric =   $replacedBy[2];
                
                foreach($beReplaced as $object){
                    $clause                         =   array();
                    
                    $beReplacedData                 =   explode('|', $object->code);
                    
                    $beReplacedCode                 =   $beReplacedData[0];
                    $beReplacedSsnType              =   $beReplacedData[1];
                    $beReplacedSsnGeneric           =   $beReplacedData[2];
                    
                    $clause['SSN.SSN_TYPE = ? AND ']        =   $beReplacedSsnType;   
                    $clause['SSN.GENERIC = ? AND ']         =   $beReplacedSsnGeneric;   
                    $clause['SSN.SSN_SUB_TYPE = ? AND ']    =   $beReplacedCode;   
                    
                    $ssnList    =   $this->minder->getSsns($clause, 0, 9999);
                    
                    if($ssnList['total'] > 0){
                        foreach($ssnList['data'] as $ssn){
                            
                            $transaction                    =   new $transactionName;
                            $transaction->ssnId          =   $ssn->id;
                            $transaction->ssnSubTypeValue   =   $replacedByCode;
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            $clause =   array('CODE = ? AND '      => $beReplacedCode,
                                              'SSN_TYPE = ? AND '  => $beReplacedSsnType,
                                              'GENERIC = ? AND '   => $beReplacedSsnGeneric);
                                              
                            $this->minder->deleteSsnSubTypeByClause($clause);
                            
                            // if GENERIC changed
                            if($ssn->items['GENERIC'] != $replacedBySsnGeneric){
                                
                                $transaction                    =   new Transaction_NIOBA() ;
                                $transaction->ssnId          =   $ssn->id;
                                $transaction->genericValue      =   $replacedBySsnGeneric;
                                
                                $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                                         
                            }
                            
                            // if SSN_TYPE changed
                            if($ssn->items['SSN_TYPE'] != $replacedBySsnType){
                                
                                $transaction                    =   new Transaction_NITPA();
                                $transaction->ssnId          =   $ssn->id;
                                $transaction->ssnTypeValue      =   $replacedBySsnType;
                                
                                $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            }
                            
                        }
                    }
                }
                
                break;
            
            case 'MODEL':
                foreach($beReplaced as $object){
                    $clause                         =   array();
                    $clause['SSN.MODEL = ? AND ']   =   $object->code;   
                    
                    $ssnList    =   $this->minder->getSsns($clause, 0, 9999);
                    
                    if($ssnList['total'] > 0){
                        foreach($ssnList['data'] as $ssn){
                            
                            $transaction                    =   new $transactionName;
                            $transaction->ssnId          =   $ssn->id;
                            $transaction->reference      =   $replacedBy;
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSKSSS', '', 'MASTER    ');
                            
                            $clause =   array('CODE = ? AND, ' => $object->code);
                            $this->minder->deleteModelLineByClause($clause);
                            
                        }
                    }
                }
                break;
        }
        
        $this->view->result =   true;        
    }
    
    protected function _setupShortcuts() {
    
        $shortcuts = array(
        
            'Audit'                    =>    array(
                                                'AUDIT_CODE'        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'audit_code'), '', true),
                                                'AUDIT PROCESSING'    =>  $this->view->url(array('controller' => 'audit-proccessing', 'action' => 'index', 'module'     => 'default'), '', true),
                                                'LEGACY_ADJUSTMENT'    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'legacy_adjustment'), '', true),
                                                'STOCKTAKE'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'stocktake'), '', true)
        
            ),
            'Import Data'            =>    array(
                                                'IMPORTING DATA'    =>    $this->view->url(array('controller' => 'admin', 'action' => 'import-clipboard'), '', true),
                                                'CREATE IMPORT MAP'    =>      $this->view->url(array('controller' => 'admin', 'action' => ''), '', true)
                                                
            
            ),
            'Master Data'            =>    array(
                                                'BRAND'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'brand'), '', true),
                                                'CARRIER'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'carrier'), '', true),
                                                'CARRIER_SERVICE'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'carrier_service'), '', true),
                                                'COST_CENTRE'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'cost_centre'), '', true),
                                                'COMPANY'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'company'), '', true),
                                                'DEPARTMENT'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'department'), '', true),
                                                'DIVISION'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'division'), '', true),
                                                'GROUP_COPY'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'group_copy'), '', true),
                                                'LABEL_LOCATION'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'label_location'), '', true),
                                                'LOAN_RATE'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'loan_rate'), '', true),
                                                'MODEL'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'model'), '', true),
                                                'PRODUCT_CONDITION'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_condition'), '', true),
                                                'PRODUCT_DESCRIPTION'        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_description'), '', true),
                                                'PROJECT'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'project'), '', true),
                                                'RETICULATION'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'reticulation'), '', true),
                                                'STATUS_DEFS'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'status_defs'), '', true),
                                                'TEST_QUESTIONS'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'test_questions'), '', true),
                                                'TURNOVER (TOG)'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'tog'), '', true),
                                                'TYPE I (SSN_TYPE)'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_type'), '', true),
                                                'TYPE II (GENERIC)'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'generic'), '', true),
                                                'TYPE III (SSN_SUB_TYPE)'    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_sub_type'), '', true),
                                                'UNIT OF MEASURE (UOM)'        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'uom'), '', true),
                                                'UOM_TYPE'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'uom_type'), '', true),
                                                'WARRANTY'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'warranty'), '', true),
                                                'ZONE'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'zone'), '', true),
                                                'REPLACE MASTER DATA'        =>    $this->view->url(array('controller' => 'replace', 'action' => 'index', 'module' => 'warehouse'), '', true)
                                                
            ),
            'Orders'                =>    array(
                                                'ORDER_TYPE'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'order_type'), '', true),
                                                'PAYMENT_METHOD'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'payment_methods'), '', true),
                                                'TERMS'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'terms'), '', true),
                                                'PICK_ORDER'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_order'), '', true),
                                                'PICK_ITEM'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_item'), '', true),
                                                'PICK_ITEM_CANCEL'          =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_item_cancel'), '', true),
                                                'PICK_ITEM_DETAIL'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_item_detail'), '', true),
                                                'PICK_MODE'                 =>  $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_mode'), '', true),
                                                'PICK_DESPATCH'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pick_despatch'), '', true),
                                                'PACK_ID'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pack_id'), '', true),
                                                'PURCHASE_ORDER'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'purchase_order'), '', true),
                                                'PURCHASE_ORDER_LINE'        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'purchase_order_line'), '', true),
                                                'GRN'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'grn'), '', true),
                                                
            ),
            'Product Profiles'        =>    array(
                                                'PRODUCTS (PROD_PROFILE)'    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'prod_profile'), '', true),
                                                'KIT'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'kit'), '', true),
                                                'PRODUCT_KIT'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_kit'), '', true),
                                                'PROD_EAN'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'prod_ean'), '', true),
                                                'PRODUCT TYPE'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'prod_type'), '', true),
                                                'PALLET CONFIGURATION'        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'pallet_cfg'), '', true),
                                                'SLOTTING PRODUCTS'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'slotting'), '', true),
                                                                
            ),
            'Person Profiles'        =>    array(
                                                'PERSON'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'person'), '', true),
                                                'PERSON_ADDRESS'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'person_address'), '', true),
                                                'PERSON_COMPANY'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'person_company'), '', true),
                                                'ACCESS_COMPANY'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'access_company'), '', true),
                                                'ACCESS_USER'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'access_user'), '', true),
                                                'USERS (SYS_USER)'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_user'), '', true),
                                                
            ),
            'Employee Profiles'        =>    array(
                                                'EMPLOYEE'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'employee'), '', true),
                                                'EMPLOYEE_IMAGE'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'employee_image'), '', true),
                                                'EMPLOYEE_ISSUE'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'employee_issue'), '', true),
                                                'OCCUPATION'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'occupation'), '', true),
                                                'SKILL'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'skill'), '', true),
                                            
            ),
            'System Tables'            =>    array(
                                                'ARCHIVING (ARCHIVE_LAYOUT)'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'archive_layout'), '', true),
                                                'CONTROL'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'control'), '', true),
                                                'DATA IDENTIFIERS (PARAM)'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'param'), '', true),
                                                'GLOBAL_CONDITIONS'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'global_conditions'), '', true),
                                                'OPTIONS'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'options'), '', true),
                                                'SSN OTHER TITLES (SSN_GROUP)'            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_group'), '', true),
                                                'SSN DESCRIPTION (DESCRIPTION_LAYOUT)'    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'description_layout'), '', true),
                                                'SYS_EQUIP'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_equip'), '', true),
                                                'SYS_HH'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_hh'), '', true),
                                                'SYS_MOVES'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_moves'), '', true),
                                                'SYS_LABEL'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'print-sys'), '', true),
                                                'SYS_LABEL_VAR'                            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_label_var'), '', true),
                                                'PRINT_REQUESTS'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'print_requests'), '', true),
                                                'PRINT_REQUESTS_ARCHIVE'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'print_requests_archive'), '', true),
                                                'LOCATION'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'location'), '', true),
                                                'LOCATION_RANGE'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'location-generate'), '', true),
                                                'WAREHOUSE'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'warehouse'), '', true),
            ),
            'System Transactions'    =>    array(
                                                'v3.9 TRANSACTIONS_ARCHIVE'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'transactions_archive'), '', true),
                                                'v4 TRANSACTIONS_ARCHIVE'                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'transactions4_archive'), '', true),
                                                'SOAP_CLI Status'                         =>    $this->view->url(array('controller' => 'admin', 'action' => 'check-soap-cli'), '', true),
                                                'USER_ENV'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'user_env'), '', true),
                                                'SSN_HIST'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_hist'), '', true),
                                                'SSN_TEST'                                =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_test'), '', true),
                                                'SSN_TEST_RESULTS'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'ssn_test_results'), '', true),
                                                'PRODUCT_COND_STATUS'                    =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'product_cond_status'), '', true),
                                                'TRANSACTION_TYPES'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'transaction_types'), '', true),
                                                'SESSION'                               =>  $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'session'), '', true)
            
            ),
            'System setup'          =>  array(
                                                'SYS_SCREEN'                            =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_screen'), '', true),
                                                'SYS_SCREEN_VAR'                        =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_screen_var'), '', true),
                                                'SYS_SCREEN_TABLE'                      =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_screen_table'), '', true),
                                                'SYS_SCREEN_ORDER'                      =>    $this->view->url(array('controller' => 'admin', 'action' => 'crud', 'table' => 'sys_screen_order'), '', true),
                                            
            
            )
        );
        
        $tooltip    =    array('Audit'                =>    'View/Edit Audit Results from Stocktaking',
                                'AUDIT_CODE'            =>    'Add/Edit Audit Codes and Colours used for displaying Stocktaking Results',
                                'AUDIT PROCESSING'        =>    'Displays Results from STOCKTAKE Table and allow Stock Adjustments', 
                                'LEGACY_ADJUSTMENT'        =>    'View LEGACY_ADJUSTMENT table using CRUD format',
                                'STOCKTAKE'                =>    'View STOCKTAKE table using CRUD format ', 
                              'Import Data'             =>    'Import with Copy and Paste, from .CSV files',
                                'IMPORTING DATA'     =>    'Import Data into Minder Tables - Import from Clipboard using Copy & Paste and import using .CSV files', 
                                'CREATE IMPORT MAP'  => '',
                              'Master Data'             =>    'Maintain SSN Descriptions and Conditions',
                                  'BRAND'                 =>    'Brand Names and Codes',
                                'CARRIER'             =>    'Carrier Names and Codes',
                                'CARRIER_SERVICE'     =>    'Carrier Service options',
                                 'COST_CENTRE'         =>    'Cost Centre Descriptions and Codes',
                                 'COMPANY'             =>    'Company and Inventory Owner Names and Codes',
                                'DEPARTMENT'         =>    'Department Names and Codes',
                                'DIVISION'             =>    'Division Names and Codes',
                                'GROUP_COPY'         =>    'Each GROUP_CODE Description and Code- updates up to 12xSSN Master Data Fields as one process',
                                'LABEL_LOCATION'     =>    'Codes used to indicate location of ISSN Labels',
                                'LOAN_RATE'             =>    'Loan Hire Costs by Loan Period by SSN_TYPE by Company',
                                'MODEL'                 =>    'Model Numbers and Codes - recommend not using',
                                'PRODUCT_CONDITION'  =>    'Conditions and Codes used for SSN.OTHER1 to 5 Drop down Lists',
                                'PRODUCT_DESCRIPTION'=>    'DESCRIPTION Codes used for SSN_TYPE.FIELD1 to 5 Drop down Lists',
                                'PROJECT'             => 'Project Names and Codes',
                                'RETICULATION'         => 'Reticulation Names and Codes',
                                'STATUS_DEFS'         => 'User defined SSN Status Descriptions and Codes',
                                'TEST_QUESTIONS'     => 'Lists Test Questions for each SSN_TYPE, used as part of ASSET*MINDER Refurbishment Module',
                                'TURNOVER (TOG)'     => 'Inventory Turnover Classifications',
                                'TYPE I (SSN_TYPE)'  => 'Type I (SSN_TYPE) Descriptions and Codes',
                                'TYPE II (GENERIC)'  =>    'Type II (GENERIC) Descriptions and Codes',
                                'TYPE III (SSN_SUB_TYPE)' => 'Type III (SSN_SUB_TYPE) Descriptions and Codes',
                                'UNIT OF MEASURE (UOM)' => 'Units of Measure Descriptions and Codes',
                                'UOM_TYPE'             => 'Types of Units of Measure Descriptions and Codes',
                                'WARRANTY'             => 'Sales Order Warranty Descriptions for drop down lists',
                                'ZONE'                 =>    'Picking Zone Descriptions and Codes',
                                'REPLACE MASTER DATA'=> 'Replaces SSN Master Data',
                              'Orders'                =>    '"View/Edit Order Tables',
                                'ORDER_TYPE'         =>    'Order Types includes - Sales, Transfer, Work, Replenish, Purchase, Returns',
                                'PAYMENT_METHOD'     => 'Sales Order Payment Methods for drop down list',
                                'TERMS'                 =>    'Sales Order Payment Terms for drop down list',
                                'PICK_ORDER'         =>    'Header Records for Orders',
                                'PICK_ITEM'             => 'Line Records for Orders',
                                'PICK_ITEM_CANCEL'   => 'Pick item cancel',
                                'PICK_ITEM_DETAIL'     =>    "Records for PICK_ITEM's that have been Picked to Despatch",
                                'PICK_MODE'          => 'PICK MODE',
                                'PICK_DESPATCH'         => 'Records for each Consignment Note',
                                'PACK_ID'             => 'Records for each Package Despatched on a PICK_DESPATCH',
                                'PURCHASE_ORDER'     => 'Header Records for Purchase Orders',
                                'PURCHASE_ORDER_LINE'=> 'Line Records for Purchase Orders',
                                'GRN'                  => 'Delivery Receipting Details - Carrier, Connote, Pallets etc.',
                              'Product Profiles'    =>    '"View/Edit Product Details',
                                'PRODUCTS (PROD_PROFILE)' =>    'Lists Product Descriptions and Properties',
                                'KIT'                      =>    'Kit Descriptions and Codes',
                                'PRODUCT_KIT'              =>    'Lists Compositions of each KIT',
                                'PROD_EAN'                  =>    'Lists GS1 EAN13/14 Barcode Numbers and Barcode Extensions for each Product',
                                'PRODUCT TYPE'              =>    'Lists Types of Products for Drop Down Lists',
                                'PALLET CONFIGURATION'      =>    'List Pallet stacking details for each product',
                                'SLOTTING PRODUCTS'          =>    'Used to place (Slot) each Product into Storage Location',
                              'Person Profiles'        =>    '"View/Edit Person Contact Address and Minder Access Details',
                                'PERSON'                 =>        'Add/Edit Contact Details for each Person (Corporations and Individuals) plus add Address Records into PERSON_ADDRESS',
                                'PERSON_ADDRESS'         =>        'Lists Address Details for PERSON - Office, Mail To, Deliver To',
                                'PERSON_COMPANY'         =>        'Add/Edit which Companies a PERSON is able to view in MINDER',
                                'ACCESS_COMPANY'         =>        'Add/Edit which Companies SYS_USER is able to view in MINDER',
                                'ACCESS_USER'             =>        'Add/Edit which Warehouses SYS_USER is able to view in MINDER',
                                'USERS (SYS_USER)'         =>        'Add/Edit Minder Users Details - User_ID, Password, SYS_ADMIN, INVENTORY_OPERATOR',
                              'Employee Profiles'    =>    '"View/Edit Employee Details - used by TIME*MINDER Module',
                                  'EMPLOYEE'                 =>        'EMPLOYEE details - Contact, Occupation, Union Membership, Induction etc.',
                                'EMPLOYEE_IMAGE'         =>        'Images of employees. Used for ID Card Printing',
                                'EMPLOYEE_ISSUE'         =>        'Issued KIT numbers to Employees for Personal Protective Equipment and Tools',
                                'OCCUPATION'             =>        'Employee OCCUPATION details',
                                'SKILL'                     =>        'Skill Descriptions and Codes plus Training Results for EMPLOYEEs',
                              'System Tables'        =>    '"View/Edit Minder Configuration details',
                                  'ARCHIVING (ARCHIVE_LAYOUT)'    =>    'Add/Edit which Tables and the period before Archiving records',
                                'CONTROL'                        =>    'Main MINDER Configuration Settings Table',
                                'DATA IDENTIFIERS (PARAM)'        =>    'Details used to identify scanned input - Symbology, Length, Expression types',
                                'GLOBAL_CONDITIONS'                =>    'Defines Drop down Descriptions and Codes for SSN.OTHERx fields',
                                'OPTIONS'                        =>    'Lists most Drop down list details plus other Configuration settings not in CONTROL table',
                                'SSN OTHER TITLES (SSN_GROUP)'    =>    'SSN.OTHERx Field Titles and if drop down lists (see GLOBAL_CONDITIONS) or single input',
                                'SSN DESCRIPTION (DESCRIPTION_LAYOUT)'    =>    'Define construction of SSN_DESCRIPTION using SSN and Master Data Tables',
                                'SYS_EQUIP'                        =>    'Lists details of each Minder Equipment - DEVICE_ID, IP_ADDRESS etc.',
                                'SYS_HH'                        =>    'Used to configure Remote Hand Held FTP details',
                                'SYS_MOVES'                        =>    'Used to configure SSN Allowed/Not Allowed Movements and Inventory Status updates',
                                'SYS_LABEL'                        =>    'Used to import and edit Native Label Printer Commands with Placeholders, Print Test Labels',
                                'SYS_LABEL_VAR'                    =>    'Lists all SYS_LABEL Placeholders, edit Data Expressions, Test Label Default values',
                                'PRINT_REQUESTS'                =>    'Lists each Print Label request. Use for Reprints',
                                'PRINT_REQUESTS_ARCHIVE'        =>    'Lists each archived Print Label request',
                                'LOCATION'                        =>    'Lists every Storage Location controlled by Minder System',
                                'LOCATION_RANGE'                =>    'Used to generate one or more LOCATION records each with the same Location Profile',
                                'WAREHOUSE'                        =>    'Lists every Warehouse or Repository controlled by Minder System',
                              'System Transactions'    =>    '"View/Edit Minder SOAP Interface, Transactions, SSN History',
                                'v3.9 TRANSACTIONS_ARCHIVE'        =>    'Input and Test v3.9 Transaction - uses TRANSACTIONS and TRANSACTIONS_ARCHIVE Tables',
                                'v4 TRANSACTIONS_ARCHIVE'        =>    'Input and Test v4 Transaction - uses TRANSACTIONS4 and TRANSACTIONS4_ARCHIVE Tables',
                                'SOAP_CLI Status'                =>    'Check Legacy Interface - SOAP-CLI',
                                'USER_ENV'                        =>    'User Session details -Use with care this maybe a very large table',
                                'SSN_HIST'                        =>    "Lists History of SSN's - Use with care this maybe a very large table",
                                'SSN_TEST'                        =>    'Lists details of SSN Test Start and Finish and Test Status',
                                'SSN_TEST_RESULTS'                =>    'Lists details of SSN Test Questions and Responses',
                                'PRODUCT_COND_STATUS'            =>    'Lists details of SSN Conditions - Original & Subsequent',
                                'TRANSACTION_TYPES'             =>  'Lists TRN_TYPE and Barcode Description Menu Data Identifier Details',
                                'SESSION'                        =>    'Session table',
                              'System setup'                    =>  'System setup',
                                'SYS_SCREEN'                    =>  'SYS_SCREEN',
                                'SYS_SCREEN_VAR'                =>  'SYS_SCREEN_VAR',
                                'SYS_SCREEN_TABLE'              =>  'SYS_SCREEN_TABLE',
                                'SYS_SCREEN_ORDER'              =>  'SYS_SCREEN_ORDER'
        );        
       
        if (!$this->minder->isStockAdjust) {
            unset($shortcuts['Audit']['STOCKTAKE']);
        }
        $this->view->shortcuts = $shortcuts;
        $this->view->tooltip   = $tooltip;    
    }
}
