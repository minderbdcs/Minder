<?php

class Orders_TransferRequestController extends Minder_Controller_StandardPage {
    const SCREEN_MODEL = 'TRANSFER_REQUEST';
    const SCREEN_NAMESPACE = 'ORDERS-TRANSFER_REQUEST';

    public function indexAction()
    {
        $this->view->pageTitle = 'Transfer Requests';
        $this->getRequest()->setParam('sysScreens', array(
            static::SCREEN_NAMESPACE => array()
        ));
        parent::indexAction();
    }

    public function changeStatusAction() {
        $this->_initViewMessagesContainers();
        $defaultRequest = array(
            'screenName' => '',
            'lineNoField' => '',
            'newStatus' => ''
        );
        $namespaceMap = $this->_getNamespaceMap();

        $changeRequest = array_merge($defaultRequest, $this->getRequest()->getParam('changeRequest', $defaultRequest));
        $screen = $changeRequest['screenName'];
        $field = $changeRequest['lineNoField'];
        $newStatus = $changeRequest['newStatus'];

        if (empty($screen)) {
            $this->view->errors[] = 'No Screen Name given.';
        } else {
            if (!isset($namespaceMap[$screen])) {
                $this->view->errors[] = 'Unknown screen "' . $screen . '".';
            }
        }

        if (empty($field)) {
            $this->view->errors[] = 'No LineNo Field given.';
        }

        if (empty($newStatus)) {
            $this->view->errors[] = 'No New Status value given.';
        }

        $namespace = $namespaceMap[$screen];

        $selectedRows = $this->_rowSelector()->getSelectedCount($namespace, static::$defaultSelectionAction, static::$defaultSelectionController);

        if ($selectedRows < 1) {
            $this->view->errors[] = 'No rows selected.';
        }

        if (count($this->view->errors) > 0) {
            $this->getDatasetAction();
            return;
        }

        try {
            $model = $this->_rowSelector()->getModel($namespace, static::$defaultSelectionAction, static::$defaultSelectionController);
            $oldConditions = $model->getConditions();
            $model->addConditions($this->_rowSelector()->getSelectConditions($namespace, static::$defaultSelectionAction, static::$defaultSelectionController));
            $items = array_unique(array_values(Minder_ArrayUtils::mapField($model->getItems(0, $selectedRows), $field)));
            $model->setConditions($oldConditions);

            $sql = 'UPDATE TRANSFER_REQUEST SET TRN_STATUS = ? WHERE TRN_LINE_NO IN (' . substr(str_repeat('?, ', count($items)), 0, -2) . ')';
            array_unshift($items, $newStatus);
            $this->minder->execSQL($sql, $items);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $this->getDatasetAction();
    }

    protected function _getNamespaceMap()
    {
        return array(
            static::SCREEN_MODEL => static::SCREEN_NAMESPACE,
        );
    }
}