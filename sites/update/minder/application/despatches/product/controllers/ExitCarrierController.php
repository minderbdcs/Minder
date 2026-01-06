<?php

class Despatches_ExitCarrierController extends Minder_Controller_StandardPage
{
    const EXIT_CARRIER_MODEL = 'AWAITING_EXIT_CARRIER';
    const EXIT_CARRIER_NAMESPACE = 'ECC-AWAITING_EXIT_CARRIER';
    public function indexAction()
    {

 
        parent::indexAction();

        try {
            $this->view->sysScreens = $this->_buildDatatset($this->_getNamespaceMap(), array(static::EXIT_CARRIER_NAMESPACE => array()));


//date update
	   $session = new Zend_Session_Namespace();
           $tz_to=$session->BrowserTimeZone;


        $key_array=array_keys($this->view->sysScreens);


 foreach($this->view->sysScreens as $key=>$val){

                    foreach($val as $key1=>$val1){



 if(DateTime::createFromFormat('Y-m-d H:i:s', $val1)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val1)!==FALSE) {


                                $dt = new DateTime($val1, new DateTimeZone('UTC'));
                                $tz = new DateTimeZone($tz_to); 
                                $dt->setTimezone($tz);
                                $val1=$dt->format('Y-m-d H:i:s');
                                $this->view->sysScreens[$key][$key1]=$val1;

                    } 


            }

}
//date update


        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
        $this->view->carrierIdParam     = array();
        try {
            $this->view->carrierIdParam     = $this->view->SymbologyPrefixDescriptor('CARRIER_ID');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->view->knownCarriers  = array();

        try {
            $this->view->knownCarriers = array_values($this->minder->getShipViaList());
        } catch (Exception $e) {
            $this->addWarning($e->getMessage());
        }

        $this->view->connoteBarcodeDescriptions = array();

        try {
            $connoteBarcodes = $this->minder->getConnoteBarcodeDataIds();
            foreach ($connoteBarcodes as $dataId) {
                try {
                    $this->view->connoteBarcodeDescriptions[] = $this->view->SymbologyPrefixDescriptor($dataId['DATA_ID']);
                } catch (Exception $e) {
                    $this->addWarning('CONNOTE_PARAM_ID = "' . $dataId['DATA_ID'] .'" is defined in CARRIER table. But was not found in PARAM table. Check system setup.');
                }
            }
        } catch (Exception $e) {
            $this->addWarning('Cannot get CONNOTE barcode label descriptions for CARRIERS: ' . $e->getMessage() . ' Check system setup.');
        }

    }

    public function despatchPackAction() {
        $response = $this->_carrierPack()->doDespatch(
            $this->getRequest()->getParam('despatchLabelNo'),
            $this->getRequest()->getParam('carrierId')
        );

        $response = $this->_carrierPack()->fillCarrierPackStatistics($this->getRequest()->getParam('scannedCarriers', array()), $response);

        try {
            $response->sysScreens = $this->_buildDatatset($this->_getNamespaceMap());
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->_viewRenderer()->setNoRender();
        echo json_encode($response);
    }


    protected function _getNamespaceMap()
    {
        return array(
            static::EXIT_CARRIER_MODEL => static::EXIT_CARRIER_NAMESPACE,
        );
    }
}
