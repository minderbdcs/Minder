<?php

/**
 * desined for speed, so no extra initialisations from Minder_Controller_Action
 */
class OtcServiceController extends Zend_Controller_Action {
    protected $_otcLogger;
    protected $_toolManager;

    public function init()
    {
    }

    public function setCostCenterAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Cost Center: ' . $request->getParam('costCenterId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setCostCenter($request->getParam('costCenterId'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Cost Center: ' . $request->getParam('costCenterId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    /**
     * @param $processId
     * @return Minder_OtcProcess_Audit|Minder_OtcProcess_Issue|Minder_OtcProcess_Return
     * @throws Exception
     */
    protected function _getProcess($processId)
    {
        return Minder_OtcProcess::getOtcProcessObject($processId);
    }

    public function setBorrowerAction() {
        $this->session->conditions = array();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Borrower: ' . $request->getParam('borrowerId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setBorrower($request->getParam('borrowerId'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Borrower: ' . $request->getParam('borrowerId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    public function setToolAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Tool: ' . $request->getParam('itemId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setToolBarcode($request->getParam('itemId'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Tool: ' . $request->getParam('itemId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    public function setToolAltBarcodeAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Tool Alt Barcode: ' . $request->getParam('itemId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setToolAltBarcode($request->getParam('itemId'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Tool Alt Barcode: ' . $request->getParam('itemId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    public function setConsumableAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Consumable: ' . $request->getParam('itemId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setConsumable($request->getParam('itemId'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Consumable: ' . $request->getParam('itemId') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    public function setIssueQtyAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Issue Qty: ' . $request->getParam('qty') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setIssueQty($request->getParam('qty'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Issue Qty: ' . $request->getParam('qty') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    public function setLocationAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Set Location: ' . $request->getParam('location') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '. About to start ...');
        $result = $process->setLocation($request->getParam('location'), $request->getParam('via', 'K'));
        $this->_otcLog('..... served. Set Location: ' . $request->getParam('location') . '. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. Via: ' . $request->getParam('via', 'K') . '.');
        echo json_encode($result);
    }

    public function confirmExpirationsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Confirm Expirations. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. About to start ...');
        $result = $process->confirmExpiration();
        $this->_otcLog('..... served. Confirm Expirations. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '.');
        echo json_encode($result);
    }

    public function confirmToolTransferAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Confirm Tool Transfer. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. About to start ...');
        $result = $process->confirmToolTransfer();
        $this->_otcLog('..... served. Confirm Tool Transfer. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '.');
        echo json_encode($result);
    }

    public function executeToolTransactionAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Execute Tool Transaction. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. About to start ...');
        $result = $process->executeToolTransaction($request->getParam('descriptionLabel'));
        $this->_otcLog('..... served. Execute Tool Transaction. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '.');
        echo json_encode($result);
    }

    public function endAuditAction() {
        $this->_viewRenderer()->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::AUDIT));
        $this->_otcLog('End Audit. Tab: ' . $request->getParam('tab', Minder_OtcProcess::AUDIT) . '. About to start ...');
        $result = $process->endAudit();
        $this->_otcLog('..... served. End Audit. Tab: ' . $request->getParam('tab', Minder_OtcProcess::AUDIT) . '.');
        echo json_encode($result);
    }

    public function reloadToolAction() {
        $this->_viewRenderer()->setNoRender(true);
        $request = $this->getRequest();

        $process = $this->_getProcess($request->getParam('tab', Minder_OtcProcess::ISSUES));
        $this->_otcLog('Reload tool. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '. About to start ...');
        $result = $process->reloadTool();
        $this->_otcLog('..... served. Reload tool. Tab: ' . $request->getParam('tab', Minder_OtcProcess::ISSUES) . '.');
        echo json_encode($result);
    }

    public function saveImageAction() {
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();

        $tool = $this->getRequest()->getParam('tool');
        $images = $this->getRequest()->getParam('images');

        $this->_otcLog('Save tool image. About to start ...');
        try {
            $this->_getToolManager()->saveToolImages($tool['id'], $images);
            $response->addMessages('Images saved.');
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        $this->_otcLog('..... served. Save tool image.');
        echo json_encode($response);
    }

    protected function _getToolManager() {
        if (empty($this->_toolManager)) {
            $this->_toolManager = new Minder_OtcProcess_Manager_Tool();
        }

        return $this->_toolManager;
    }

    /**
     * @return Minder_Log_Otc
     */
    protected function _getOtcLogger() {
        if (is_null($this->_otcLogger))
            $this->_otcLogger = new Minder_Log_Otc();

        return $this->_otcLogger;
    }

    protected function _otcLog($message) {
        $this->_getOtcLogger()->info($message);
    }

    /**
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    protected function _viewRenderer()
    {
        return $this->_helper->viewRenderer;
    }


}