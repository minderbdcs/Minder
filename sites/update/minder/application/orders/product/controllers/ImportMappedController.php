<?php

class Orders_ImportMappedController extends Minder_Controller_StandardPage {

    const FILES_MODEL = 'MAPPED_FILES';
    const FILES_NAMESPACE = 'IMPORT_MAPPED-MAPPED_FILES';

    protected function _getImportForm() {
        $form = new Minder_Form(new Zend_Config_Ini(APPLICATION_CONFIG_DIR . '/forms/import-mapped-orders.ini', 'form'));

        $form->getElement('import_type')->setValue($this->_getMapType());
        $form->setAction($this->view->url(array('action' => 'do-import')));

        return $form;
    }

    public function indexAction() {
        $mapType = $this->_getMapType();

        switch (strtoupper($mapType)) {
            case 'S':
                $this->view->pageTitle = 'Import Mapped Sales Orders';
                break;
            case 'P':
                $this->view->pageTitle = 'Import Mapped Purchase Orders';
                break;
            case 'T':
                $this->view->pageTitle = 'Import Mapped Transfer Orders';
                break;
            default:
                $this->addError('Wrong Map Type: "' . $mapType . '".');
                return;
        }

        $this->view->form = $this->_getImportForm();

        $this->getRequest()->setParam('sysScreens', array(
            $this->_getFilesNamespace() => array(),
        ));

        parent::indexAction();
    }

    public function doImportAction() {
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');
        $importForm = $this->_getImportForm();

        try {
            $importTypeElement = $importForm->getElement('import_type');

            if (!$importForm->isValid($this->getRequest()->getParams())) {
                $this->addError($importForm->getElementsErrorMessages());
                return $redirector->gotoSimple('index', null, null, array('type' => $importTypeElement->getValue()));
            }

            /**
             * @var Minder_Form_Element_File $importFileElement
             */
            $importFileElement = $importForm->getElement('import_filename');
            if (false === $importFileElement->receive())
                throw new Exception('Error uploading file.');

            $importRoutine = new Minder_Process_ImportMappedOrders($importTypeElement->getValue(), $importFileElement->getFileName(), true);
            $importStatus = $importRoutine->doImport();
            $this->addMessage($importStatus->getProceed() . ' rows were proceed. ' . $importStatus->getSkipped() . ' rows were skipped.');
            if ($importStatus->isError()) {
                $this->addError($importStatus->getErrorMessage());
            }

        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        return $redirector->gotoSimple('index', null, null, array('type' => $importForm->getElement('import_type')->getValue()));
    }

    public function importSelectedAction() {
        $this->_viewRenderer()->setNoRender();
        $result = new Minder_JSResponse();
        $namespaceMap = $this->_getNamespaceMap();

        $pathField = strtoupper($this->getRequest()->getParam('pathField', ''));
        $sysScreen = $this->getRequest()->getParam('screenName');

        if (empty($pathField)) {
            $result->addErrors('No Path Field specified.');
        }

        if (empty($sysScreen)) {
            $result->addErrors('No Sys Screen name given.');
        } else {
            if (!isset($namespaceMap[$sysScreen])) {
                $result->addErrors('Unknown Sys Screen "' . $sysScreen . '".');
            }
        }

        if (!$result->hasErrors()) {
            try {
                $model = $this->_rowSelector()->getModel($namespaceMap[$sysScreen], static::$defaultSelectionAction, static::$defaultSelectionController);
                $conditions = $this->_rowSelector()->getSelectConditions($namespaceMap[$sysScreen], static::$defaultSelectionAction, static::$defaultSelectionController);
                $originalConditions = $model->getConditions();
                $model->addConditions($conditions);

                $items = $model->getItems(0, count($model));
                $model->setConditions($originalConditions);

                if (count($items) < 1) {
                    $result->addWarnings('No rows selected');
                } else {
                    foreach (Minder_ArrayUtils::mapField($items, $pathField) as $filePath) {
                        $importRoutine = new Minder_Process_ImportMappedOrders($this->_getMapType(), $filePath);
                        $importStatus = $importRoutine->doImport();
                        $result->addMessages($importStatus->getProceed() . ' rows were proceed. ' . $importStatus->getSkipped() . ' rows were skipped.');
                        if ($importStatus->isError()) {
                            $result->addErrors($importStatus->getErrorMessage());
                        }
                    }
                }


            } catch (Exception $e) {
                $result->addErrors($e->getMessage());
            }
        }

        $result->sysScreens  = $this->_buildDatatset($this->_getNamespaceMap());
        echo json_encode($result);
    }

    /**
     * @return string
     */
    protected function _getMapType()
    {
        return $this->getRequest()->getParam('type');
    }

    protected function _getFilesNamespace() {
        return $this->_getMapType() . '-' . static::FILES_NAMESPACE;
    }

    protected function _getNamespaceMap()
    {
        return array(
            static::FILES_MODEL => $this->_getFilesNamespace(),
        );
    }

    protected function _getModelBuilder()
    {
        if (empty($this->_modelBuilder)) {
            $this->_modelBuilder = new Minder_SysScreen_View_ImportMappedBuilder($this->_getMapType());
        }
        return $this->_modelBuilder;
    }


}