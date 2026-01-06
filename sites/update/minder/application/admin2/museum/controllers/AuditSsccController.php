<?php

class Admin2_AuditSsccController extends Minder_Controller_StandardPage {

    const AUDIT_SSCC_ITEM = 'AUDIT_SSCC_ITEM';
    const AUDIT_SSCC_ITEM_NAMESPACE = 'AUDIT_SSCC_ITEM_NAMESPACE';

    const AUDIT_SSCC_PACK = 'AUDIT_SSCC_PACK';
    const AUDIT_SSCC_PACK_NAMESPACE = 'AUDIT_SSCC_PACK_NAMESPACE';

    public function indexAction()
    {
        parent::indexAction();

        try {
            $this->view->sysScreens = $this->_buildDatatset(
                $this->_getNamespaceMap(),
                array(
                    static::AUDIT_SSCC_ITEM_NAMESPACE => array(),
                    static::AUDIT_SSCC_PACK_NAMESPACE => array(),
                )
            );
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    public function createSsccAction() {
        $this->_initViewMessagesContainers();

        $ssccPackModel = $this->_getSelectionsModel(static::AUDIT_SSCC_PACK_NAMESPACE);

        $originalConditions = $ssccPackModel->getConditions();
        $selectedAmount = $this->_rowSelector()->getSelectedCount(static::AUDIT_SSCC_PACK_NAMESPACE, static::$defaultSelectionAction, static::$defaultSelectionController);

        if ($selectedAmount < 1) {
            $this->view->warnings[] = 'No rows selected.';
        } elseif ($selectedAmount > 1) {
            $this->view->warnings[] = 'You should select one row.';
        } else {
            $ssccPackModel->addConditions($this->_rowSelector()->getSelectConditions(static::AUDIT_SSCC_PACK_NAMESPACE, static::$defaultSelectionAction, static::$defaultSelectionController));

            $items = $ssccPackModel->getItems(0, 1);

            $item = array_shift($items);
            $this->_copyMessagesToView($this->_awaitingCheckingEdiHelper()->createSscc($item['RECORD_ID']));
        }

        $ssccPackModel->setConditions($originalConditions);


        $this->_forward('get-dataset');
    }

    protected function _getNamespaceMap()
    {
        return array(
            static::AUDIT_SSCC_ITEM => static::AUDIT_SSCC_ITEM_NAMESPACE,
            static::AUDIT_SSCC_PACK => static::AUDIT_SSCC_PACK_NAMESPACE,
        );
    }

    protected function _getMenuId()
    {
        return 'ADMIN';
    }

    /**
     * @return Minder_Controller_Action_Helper_AwaitingCheckingEdi
     */
    protected function _awaitingCheckingEdiHelper() {
        return $this->getHelper('AwaitingCheckingEdi');
    }
}