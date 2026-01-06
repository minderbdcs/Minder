<?php

class GoogleGearsController extends Zend_Controller_Action
{
    protected $minder = null;
    
    public function init()
    {
         // redirect to warehouse/grn/index
        $this->minder = Minder::getInstance(); 
    }

    public function indexAction()
    {
    }

    public function getjsonAction()
    {
        
    }
    /**
    * @desc if system is online
    */
    public function isOnlineAction() {
        
        $this->view->cbFunction = $this->getRequest()->getParam('callback');
        $this->view->isOnline = array('isConnected' => true);
        
    }
    
    /**
    * @desc receive data from local database
    */
    public function receiveDataAction() {
    
        
        $table      = $this->getRequest()->getParam('TABLE_NAME');
        $recordId   = $this->getRequest()->getParam('RECORD_ID');
        
        switch(strtoupper($table)) {
            case 'SYS_LABEL':
                    $allowed = array('SL_NAME', 
                                     'SL_SEQUENCE', 
                                     'SL_LINE',
                                     'SL_IMAGE', 
                                     'SL_BRAND',
                                     'SL_MODEL', 
                                     'SL_FIRMWARE',
                                     'SL_NOTES', 
                                     'LAST_UPDATE_DATE',
                                     'LAST_UPDATE_BY'
                                    );
                    $receiveParams = $this->getRequest()->getParams();
                    $clause        = array(); 
                    foreach($receiveParams as $key => $value) {
                        if(in_array(strtoupper($key), $allowed)) {
                            $clause[$key] = $value;    
                        }
                    }
                    
                    $result = $this->minder->insertDataLine($clause);
                    if($result) {
                        $this->view->returnResponse = array('error'     => false,
                                                            'message'   => 'was successfuly uploaded',
                                                            'data'      =>  $recordId);
                    } else {
                        $this->view->returnResponse = array('error'     => true,
                                                            'message'   => 'Error while upload data',
                                                            'data'      => $recordId);
                    }
         
                    break;
         
         case 'BARCODE_CODES':
                    
                    $allowed = array('RECORD_ID', 
                                     'BARCODE_CODE', 
                                     'ITEM_COUNT',
                                     'DATE_ADD' 
                                    );
                    $receiveParams = $this->getRequest()->getParams();
                    $clause        = array(); 
                    foreach($receiveParams as $key => $value) {
                        if(in_array(strtoupper($key), $allowed)) {
                            $clause[$key] = $value;    
                        }
                    }
                    
                    $clause['ITEM_COUNT'] = $receiveParams['ITEM_COUNTS'];
                     
                    
                    $result = $this->minder->insertBarcodeCodes($clause);
                    if($result) {
                        $this->view->returnResponse = array('error'     => false,
                                                            'message'   => 'was successguly uploaded',
                                                            'data'      =>  $recordId);
                    } else {
                        $this->view->returnResponse = array('error'     => true,
                                                            'message'   => 'Error while upload data',
                                                            'data'      => $recordId);
                    }
         
                    break;
            default:
                    
                    $this->view->returnResponse =   array('error'   => true,
                                                          'message' => 'Table for upload data not found',
                                                          'data'    => '');    
        
        }
        
            $this->view->cbFunction = $this->getRequest()->getParam('callback');
         
    }
    
}
