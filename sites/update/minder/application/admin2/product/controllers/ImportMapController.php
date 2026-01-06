<?php

class Admin2_ImportMapController extends Minder_Controller_Action {

    const IMPORT_MAP_TABLE = "IMPORT_MAP";

    /**
     * @return null|Minder_Paginator_ItemAddress
     */
    protected function _getCurrentItemAddress() {
        if (isset($this->session->currentItem)) {
            return $this->session->currentItem;
        }

        return null;
    }

    /**
     * @param Minder_Paginator_ItemAddress $itemAddress
     * @return void
     */
    protected function _saveCurrentItemAddress($itemAddress) {
        $this->session->currentItem = $itemAddress;
    }

    /**
     * @return array
     */
    protected function _getMapFormState() {
        if (isset($this->session->mapForm)) {
            return $this->session->mapForm;
        }

        return array();
    }

    /**
     * @param array $state
     * @return void
     */
    protected function _saveMapFormState($state) {
        $this->session->mapForm = $state;
    }

    /**
     * @return Minder_Paginator_State
     */
    protected function _getMapPaginatorState() {
        if (isset($this->session->mapPaginator))
            return $this->session->mapPaginator;

        return new Minder_Paginator_State();
    }

    /**
     * @param Minder_Paginator_State $state
     * @return void
     */
    protected function _saveMapPaginatorState($state) {
        $this->session->mapPaginator = $state;
    }

    /**
     * @return array
     */
    protected function _getCrudFormState() {
        if (isset($this->session->crudForm))
            return $this->session->crudForm;

        return array();
    }

    /**
     * @param array $state
     * @return void
     */
    protected function _saveCrudFormState($state) {
        $this->session->crudForm = $state;
    }

    protected function _getSearchClause() {
        if (isset($this->session->searchClause))
            return $this->session->searchClause;

        return array();
    }

    protected function _saveSearchClause($clause) {
        $this->session->searchClause = $clause;
    }

    public function indexAction() {

        $mapForm = new Minder_Form_ImportMap();

        $mapForm->populate($this->_getMapFormState());

        $paginator = $this->_getImportMapPaginator();

        $this->view->importMapHeaders = array(
            'RECORD_ID' => '#',
            'MAP_TYPE' => 'Type',
            'MAP_IMPORT_FILENAME' => 'Name',
            'MAP_IMPORT_COL' => 'Column',
            'MAP_IMS_TABLE' => 'Tablename',
            'MAP_IMS_FIELDNAME' => 'Fieldname',
            'MAP_IMS_FIELDTYPE' => 'Field Format'
        );

        $this->view->paginator       = $paginator;
        $this->view->mapForm         = $mapForm;
        $this->view->currentItemAddr = $this->_getCurrentItemAddress();

        $crudFormState = $this->_getCrudFormState();
        if (!empty($crudFormState)) {
            if (isset($crudFormState['RECORD_ID'])) {
                $crudForm = $this->_getCrudForm(array(), Minder_Form_Crud::MODE_UPDATE, $this->view->currentItemAddr);
            } else {
                $crudForm = $this->_getCrudForm(array(), Minder_Form_Crud::MODE_ADD);
            }

            $crudForm->populate($crudFormState);
            /** @noinspection PhpUndefinedMethodInspection */
            $crudForm->setAction($this->view->url(array('action' => 'save-crud-form')));

            $this->view->crudForm = $crudForm;
            $this->_saveCrudFormState(array());
        }
    }

    /**
     * @return Minder_Paginator
     */
    protected function _getImportMapPaginator()
    {
        $paginator = new Minder_Paginator(new Minder_Paginator_Adapter_MinderTable('IMPORT_MAP', $this->_getSearchClause()));
        $paginatorState = $this->_getMapPaginatorState();
        $paginator->setItemCountPerPage($paginatorState->itemsCountPerPage);
        $paginator->setCurrentPageNumber($paginatorState->currentPage);
        return $paginator;
    }

    public function changePaginatorStateAction() {
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');

        $paginatorState = $this->_getMapPaginatorState();

        $request = $this->getRequest();
        $paginatorState->currentPage = $request->getParam('pageselector', $paginatorState->currentPage);
        $paginatorState->itemsCountPerPage = $request->getParam('show_by', $paginatorState->itemsCountPerPage);

        $this->_saveMapPaginatorState($paginatorState);

        return $redirector->gotoSimple('index');
    }

    public function searchMapAction() {
        $mapForm = new Minder_Form_ImportMap();

        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');

        if (!$mapForm->isValid($this->getRequest()->getParams())) {
            $this->_saveMapFormState($mapForm->getValues());
            $this->addError($this->collectErrorMessages($mapForm));

            return $redirector->gotoSimple('index');
        }

        $formValues = $mapForm->getValues();
        $this->_saveMapFormState($formValues);

        $searchClause = array();

        foreach ($formValues as $field => $value) {
            if (empty($value)) continue;

            switch (strtolower($field)) {
                case 'import_type':
                    $searchClause[] = array('MAP_TYPE = ?' => array($value));
                    break;
                case 'import_filename':
                    $searchClause[] = array('MAP_IMPORT_FILENAME LIKE ?' => array($value));
                    break;
                case 'import_worksheet':
                    $searchClause[] = array('MAP_IMPORT_SHEET LIKE ?' => array($value));
                    break;
                case 'import_table':
                    $searchClause[] = array('MAP_IMS_TABLE = ?' => array($value));
                    break;
            }
        }

        $this->_saveSearchClause($searchClause);

        return $redirector->gotoSimple('index');
    }

    public function clearMapSearchAction() {
        $this->_saveMapFormState(array());
        $this->_saveSearchClause(array());
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');
        return $redirector->gotoSimple('index');
    }

    public function getMapFieldsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            $mapForm = new Minder_Form_ImportMap();

            if (!$mapForm->isValid($this->getRequest()->getParams())) {
                $this->_saveMapFormState($mapForm->getValues());
                $result->errors = $this->collectErrorMessages($mapForm);
                echo json_encode($result);
                return;
            }

            $this->_saveMapFormState($mapForm->getValues());

            $formValues = $mapForm->getValues();
            $importTable = $formValues['import_table'];

            $result->mapFields = Minder::getInstance()->getFieldList($importTable);

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function saveMapAction() {
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');

        try {
            $mapForm = new Minder_Form_ImportMap(Minder_Form_ImportMap::MODE_ADD);

            if (!$mapForm->isValid($this->getRequest()->getParams())) {
                $this->_saveMapFormState($mapForm->getValues());
                $this->addError($this->collectErrorMessages($mapForm));

                return $redirector->gotoSimple('index');
            }

            $this->_saveMapFormState($mapParams = $mapForm->getValues());

            $fieldsMap = $this->getRequest()->getParam('fieldMap', array());

            if (!is_array($fieldsMap) || empty($fieldsMap)) {
                $this->addError('No fields selected for Map. Please select one.');
                return $redirector->gotoSimple('index');
            }

            Minder::getInstance()->insertMapRecord(
                $mapParams['import_type'],
                $mapParams['import_filename'],
                isset($mapParams['import_worksheet']) ? $mapParams['import_worksheet'] : '',
                $fieldsMap,
                $mapParams['import_table'],
                array_combine(array_keys($fieldsMap), array_keys($fieldsMap))
            );

            $this->addMessage('IMPORT_MAP records added successfully.');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            return $redirector->gotoSimple('index');
        }

        return $redirector->gotoSimple('index');
    }

    public function getCrudFormAction() {
        $recordId = $this->getRequest()->getParam('rowId');
        $recordId = (empty($recordId)) ? 'new' : $recordId;

        $importMapDataset = Minder::getInstance()->getMasterTableDataSet(self::IMPORT_MAP_TABLE);
        if (strtolower($recordId) == 'new') {
            $crudForm = $this->_getCrudForm(array(), Minder_Form_Crud::MODE_ADD);
            $crudForm->populate($importMapDataset->getNewRecord()->getRawData());
        } else {
            $rowNo       = $this->getRequest()->getParam('rowNo');
            $pageNo      = $this->getRequest()->getParam('pageNo');
            $itemAddress = null;

            if (!is_null($rowNo)) {
                $itemAddress = new Minder_Paginator_ItemAddress($rowNo, $pageNo);
            }
            $crudForm = $this->_getCrudForm(array(), Minder_Form_Crud::MODE_UPDATE, $itemAddress);
            $crudForm->populate($importMapDataset->getRecord($recordId)->getRawData());
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $crudForm->setAction($this->view->url(array('action' => 'save-crud-form')));

        $this->view->crudForm = $crudForm;
    }

    protected function _getCrudForm($options = array(), $mode = Minder_Form_Crud::MODE_UPDATE, $itemAddress = null)
    {
        $crudForm = new Minder_Form_Crud(self::IMPORT_MAP_TABLE, $options, $mode);

        if (is_null($itemAddress)) {
            $crudForm->getElement('last_btn')->setAttrib('disabled', 'disabled');
            $crudForm->getElement('next_btn')->setAttrib('disabled', 'disabled');
            $crudForm->getElement('first_btn')->setAttrib('disabled', 'disabled');
            $crudForm->getElement('prev_btn')->setAttrib('disabled', 'disabled');
        } else {
            $paginator = $this->_getImportMapPaginator();
            $itemAddress = $paginator->normalizeItemAddress($itemAddress);
            $nextItemAddr = $paginator->getNextItemAddress($itemAddress);
            $prevItemAddr = $paginator->getPrevioseItemAddress($itemAddress);

            $crudForm->populate(array('itemNo' => $itemAddress->itemNo, 'pageNo' => $itemAddress->pageNo));

            if (is_null($nextItemAddr)) {
                $crudForm->getElement('last_btn')->setAttrib('disabled', 'disabled');
                $crudForm->getElement('next_btn')->setAttrib('disabled', 'disabled');
            }

            if (is_null($prevItemAddr)) {
                $crudForm->getElement('first_btn')->setAttrib('disabled', 'disabled');
                $crudForm->getElement('prev_btn')->setAttrib('disabled', 'disabled');
            }
        }

        return $crudForm;
    }

    public function saveCrudFormAction() {
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');

        try {
            $crudForm = new Minder_Form_Crud(self::IMPORT_MAP_TABLE, array(), Minder_Form_Crud::MODE_UPDATE);

            if (!$crudForm->isValid($this->getRequest()->getParams())) {
                $this->_saveCrudFormState($crudForm->getValues());
                $this->addError($this->collectErrorMessages($crudForm));
                return $redirector->gotoSimple('index');
            }

            $formData = $crudForm->getValues();
            $this->_saveCrudFormState($formData);
            $minder = Minder::getInstance();
            $importMapDataset = $minder->getMasterTableDataSet(self::IMPORT_MAP_TABLE);
            if (!isset($formData['RECORD_ID']) || empty($formData['RECORD_ID'])) {
                $record = $importMapDataset->getNewRecord();
            } else {
                $record = $importMapDataset->getRecord($formData['RECORD_ID']);
            }

            if (!isset($formData['MAP_IMPORT_SHEET']) || empty($formData['MAP_IMPORT_SHEET'])) {
                $formData['MAP_IMPORT_SHEET'] = 'NOT_USED';
            }

            if (false === $record->save($formData)) {
                $this->addError($record->getValidationErrorList());
                return $redirector->gotoSimple('index');
            }

            $importMapDataset->setRecord($record);
            if (false === $minder->updateMasterTableDataSet($importMapDataset)) {
                $this->addError($minder->lastError);
                return $redirector->gotoSimple('index');
            }

            $this->_saveCrudFormState(array());

            if (!isset($formData['RECORD_ID']) || empty($formData['RECORD_ID'])) {
                $this->addMessage('Record added.');
            } else {
                $this->addMessage('Record #' . $formData['RECORD_ID'] . ' updated.');
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        return $redirector->gotoSimple('index');
    }

    public function crudFormNavigateAction() {
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');

        $params = $this->getRequest()->getParams();

        $itemAddr = null;

        if (isset($params['RECORD_ID'])) {
            if (isset($params['itemNo']) && !empty($params['itemNo'])) {
                $itemAddr = new Minder_Paginator_ItemAddress($params['itemNo'], (isset($params['pageNo'])) ? $params['pageNo'] : null);
            }

            $crudForm = $this->_getCrudForm(array(), Minder_Form_Crud::MODE_UPDATE, $itemAddr);
        } else {
            $crudForm = $this->_getCrudForm(array(), Minder_Form_Crud::MODE_ADD, null);
        }

        $crudForm->populate($params);
        $this->_saveCrudFormState($crudForm->getValues());

        if (is_null($itemAddr))
            return $redirector->gotoSimple('index');

        $this->_saveCurrentItemAddress($itemAddr);

        $newItemAddr = null;
        $paginator = $this->_getImportMapPaginator();

        switch ($this->getRequest()->getParam('navigate')) {
            case 'FIRST':
                $newItemAddr = $paginator->getFirstItemAddress();
                break;
            case 'LAST':
                $newItemAddr = $paginator->getLastItemAddress();
                break;
            case 'PREV':
                $newItemAddr = $paginator->getPrevioseItemAddress($itemAddr);
                break;
            case 'NEXT':
                $newItemAddr = $paginator->getNextItemAddress($itemAddr);
                break;
        }

        if (is_null($newItemAddr))
            return $redirector->gotoSimple('index');

        $newItem = $paginator->getItem($newItemAddr->itemNo, $newItemAddr->pageNo);
        $crudForm->populate($newItem);
        $crudForm->populate(array('itemNo' => $newItemAddr->itemNo, 'pageNo' => $newItemAddr->pageNo));
        $this->_saveCrudFormState($crudForm->getValues());

        $paginator->setCurrentPageNumber($newItemAddr->pageNo);
        $this->_saveMapPaginatorState($paginator->getState());

        $this->_saveCurrentItemAddress($newItemAddr);

        return $redirector->gotoSimple('index');
    }

    public function deleteMapRecordsAction() {
        /**
         * @var Zend_Controller_Action_Helper_Redirector $redirector
         */
        $redirector = $this->_helper->getHelper('Redirector');

        try {
            $rowsToDelete = $this->getRequest()->getParam('delete-rows', array());

            if (empty($rowsToDelete)) {
                $this->addError('No rows selected. Please select one.');
                return $redirector->gotoSimple('index');
            }

            $minder = Minder::getInstance();
            if (false === $minder->deleteMapRecords($rowsToDelete)) {
                $this->addError($minder->lastError);
                return $redirector->gotoSimple('index');
            }

            $this->addMessage(count($rowsToDelete) . ' records were deleted.');

        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        return $redirector->gotoSimple('index');
    }

    /**
     * @param Zend_Form $form
     * @return array
     */
    protected function collectErrorMessages($form) {
        $result = array();
        /**
         * @var Zend_Form_Element $formElement
         */
        foreach ($form->getElements() as $formElement) {
            if ($formElement->hasErrors()) {
                foreach ($formElement->getMessages() as $error)
                    $result[] = '"' . $formElement->getLabel() . '": ' . $error;
            }
        }

        return $result;
    }

    protected function _getMenuId()
    {
        return 'ADMIN';
    }

    public function reportAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $type = $this->getRequest()->getParam('type');
        $ids  = $this->getRequest()->getParam('selected');

        $selectedRecords = explode(',', $ids);

        $mapRecords = array();
        $mapRecords = $this->minder->getMapRecords();

        $reportData = array();

        foreach($mapRecords as $record) {
            if (in_array($record['RECORD_ID'], $selectedRecords)) {
                array_push($reportData, $record);
            }
        }

        $this->view->data    = $reportData;
        $this->view->headers = array(
            'RECORD_ID' => '#',
            'MAP_TYPE' => 'Type',
            'MAP_IMPORT_FILENAME' => 'Name',
            'MAP_IMPORT_COL' => 'Column',
            'MAP_IMS_TABLE' => 'Tablename',
            'MAP_IMS_FIELDNAME' => 'Fieldname',
            'MAP_IMS_FIELDTYPE' => 'Field Format'
        );

        if ($type == 'CSV') {
            $this->getResponse()->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
            $this->render('report-csv');

            return true;
        }

        if ($type == 'XLS') {
            $xls = new Spreadsheet_Excel_Writer();
            $xls->send('report.xls');
            $this->view->xls = $xls;
            $this->render('report-xls');

            return true;
        }
        return false;
    }
}
