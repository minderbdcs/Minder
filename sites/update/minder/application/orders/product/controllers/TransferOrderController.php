<?php

class Orders_TransferOrderController extends Minder_Controller_StandardPage {
    const ORDER_MODEL = 'TRANSFER_ORDER';
    const ORDER_NAMESPACE = 'TRANSFER-TRANSFER_ORDER';
    const LINES_MODEL = 'TRANSFER_LINES';
    const LINES_NAMESPACE = 'TRANSFER-TRANSFER_LINES';

    public function indexAction() {
        $this->view->pageTitle     = 'SEARCH TRANSFER ORDER:';
        $this->getRequest()->setParam('sysScreens', array(
            self::ORDER_NAMESPACE => array(),
            self::LINES_NAMESPACE => array()
        ));

        parent::indexAction();
    }

    protected function _getNamespaceMap() {
        return array(
            static::ORDER_MODEL => static::ORDER_NAMESPACE,
            static::LINES_MODEL => static::LINES_NAMESPACE
        );
    }
}