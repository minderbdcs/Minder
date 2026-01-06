<?php

class Receipts_ReceiveController extends Minder2_Controller_Action {

    const RECEIVE_MENU_ID  = 'RECEIVE_PURCHASE_ORDER';
    const PO_SS_NAME       = 'RECEIVE_PURCHASE_ORDER';
    const PO_LINES_SS_NAME = 'RECEIVE_PO_LINE';

    public function indexAction() {
        $page = null;
        try {
            $this->view->recieveLocations = $this->_getReceiveLocations();
            $this->view->defaultUnitsPerIssn = Minder2_Environment::getInstance()->getSystemControls()->RECEIVE_ISSN_ORIGINAL_QTY;
            $this->view->receiveDirectDeliveryEnabled = (Minder2_Environment::getInstance()->getSystemControls()->RECEIVE_DIRECT_DELIVERY == 'T');
            $this->view->carriers= $this->_getMinder()->getCarriersList();

            $pageBuilder      = Minder2_Page_Builder::getPageBuilder(self::RECEIVE_MENU_ID);
            $page             = $pageBuilder->build(self::RECEIVE_MENU_ID);

            /**
             * @var Minder2_Model_SysScreen $sysScreen
             */
            foreach ($page->getScreens() as $sysScreen) {
                $sysScreen->serviceUrl = $this->_helper->url->url(array('menuId' => $page->menuId, 'screenId' => $sysScreen->SS_NAME), 'screenService');
                $sysScreen->saveState();
            }

            $page->serviceUrl = $this->_helper->url->url(array('menuId' => $page->menuId), 'pageService');
        } catch (Exception $e) {
            $this->view->messages()->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e));
        }

        $this->view->page = $page;
    }

    protected function _getLocationMapper() {
        return new Minder2_Model_Mapper_Location();
    }

    protected function _getReceiveLocations() {
        return $this->_getLocationMapper()->fetchReceiveLocations();
    }

}