<?php

class Services_PostcodeDepotController extends Minder_Controller_Action {

    public function importAustpostAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        $postcodeDepotFile = new Zend_Form_Element_File('postcode_depot_file');
        if (!$postcodeDepotFile->receive()) {
            $result->errors[] = 'Error uploading file.';
            echo json_encode($result);
            return;
        }

        try {
            $postcodeReader = new PostcodeDepot_CsvReader($postcodeDepotFile->getFileName());
            $postcodeCollection = $postcodeReader->readFile(new PostcodeDepot_Austpost_Adapter());
            $austpostRoutings   = new PostcodeDepot_Routings();
            $austpostRoutings->saveCollectionOfAustpostPostcodes($postcodeCollection, new PostcodeDepot_Mapper());

            $result->messages[] = 'Proccessed ' . count($postcodeCollection) . ' records.';
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function deleteAllPostcodesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            $postcodeMapper = new PostcodeDepot_Mapper();
            $postcodeMapper->deleteAll();
            $result->messages[] = 'All records have been deleted.';
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function importCarriersCodesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            $carriersCodesFile = new Zend_Form_Element_File('carriers_codes_file');
            if (!$carriersCodesFile->receive())
                throw new Exception('Error uploading file.');

            $carrierId = $this->getRequest()->getParam('carrier_id');
            if (empty($carrierId))
                throw new Exception('No Carrier selected. Please, select one.');

            $mapper = new Carrier_Mapper();
            $carrier = $mapper->find($carrierId);
            if (!$carrier->existedRecord())
                throw new Exception('Carrier "' . $carrierId . '" does not exist.');

            $carrierDepotField = $carrier->postCodeDepotId;
            if (empty($carrierDepotField))
                throw new Exception('POST_CODE_DEPOT_ID for "' . $carrierId . '" Carrier is undefined. Please, check system setup.');

            $postcodeReader = new PostcodeDepot_CsvReader($carriersCodesFile->getFileName());
            $postcodeCollection = $postcodeReader->readFile(new PostcodeDepot_Carriers_Adapter(transformToObjectProp($carrierDepotField)));
            $postcodeRoutings   = new PostcodeDepot_Routings();
            $postcodeRoutings->updateCarriersDepots($postcodeCollection, new PostcodeDepot_Mapper(), $carrierDepotField);

            $result->messages[] = 'Proccessed ' . count($postcodeCollection) . ' records.';
        } catch (PostcodeDepot_CsvReader_Exception $e) {
            $result->errors[] = 'Error reading import file: ' . $e->getMessage();
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e));
        }

        echo json_encode($result);
    }
}