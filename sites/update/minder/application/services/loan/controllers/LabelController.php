<?php

class Services_LabelController extends Minder_Controller_Action {
    public function printLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $response = new Minder_JSResponse();

        $namespace = $this->getRequest()->getParam('namespace', '');
        $labelName = $this->getRequest()->getParam('labelName', '');
        $paramsMap = $this->getRequest()->getParam('paramsMap', array());

        if (empty($namespace))
            $response->errors[] = 'Namespace is empty.';

        if (empty($labelName))
            $response->errors[] = 'Label Name is empty.';

        if (empty($paramsMap))
            $response->errors[] = 'Parameters Map cannot be empty.';

        if (count($response->errors) > 0) {
            echo(json_encode($response));
            return;
        }

        $selectionAction     = $this->getRequest()->getParam('selection_action', self::$defaultSelectionAction);
        $selectionController = $this->getRequest()->getParam('selection_controller', self::$defaultSelectionController);
        $companyId           = $this->getRequest()->getParam('companyId', '');
        $whId                = $this->getRequest()->getParam('whId', '');

        try {
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector = $this->_helper->getHelper('RowSelector');
            $data = $rowSelector->getSelectedRowsData($paramsMap, $companyId, $whId, $namespace, $selectionAction, $selectionController);

            if (empty($data))
                throw new Exception('No rows selected.');

            $labelPrinter = new Minder2_LabelPrinter();
            //$response->messages = $labelPrinter->printLabels($namespace, $labelName, $paramsMap, $data, Minder::getInstance()->getPrinter());
            $response->messages = $labelPrinter->printLabels($namespace, $labelName, $paramsMap, $data, $this->minder->getPrinter());
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        echo(json_encode($response));
    }

    protected function _setupShortcuts()
    {

    }
}