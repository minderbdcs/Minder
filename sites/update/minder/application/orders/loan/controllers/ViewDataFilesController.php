<?php

class Orders_ViewDataFilesController extends Minder_Controller_Action
{
    const MODEL_NAME                = 'VIEW_DATA_FILES';
    const MODEL_NAMESPACE           = 'VIEW_DATA_FILES_NAMESPACE';

    const CONTENT_MODEL             = 'CONTENTS';
    const CONTENT_MODEL_NAMESPACE   = 'CONTENTS_NAMESPACE';

    public function indexAction()
    {
        $this->view->title                      = 'VIEW DATA FILES';

        try {
            $this->view->searchStringForm       = $this->_buildSearchStringForm();
            $this->view->filesSsName            = self::MODEL_NAME;
            $this->view->filesSsNamespace       = self::MODEL_NAMESPACE;
            $this->view->contentsSsName         = self::CONTENT_MODEL;
            $this->view->contentsSsNamespace    = self::CONTENT_MODEL_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper  = $this->_helper->searchKeeper;

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            $fileListModel = $screenBuilder->buildSysScreenModelFileSystem(self::MODEL_NAME,
                new Minder_SysScreen_Model_FileSystem());

            $fileListModel->setConditions($fileListModel->makeConditionsFromSearch($searchFields));

            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            $rowSelector->setRowSelection('select_complete',
                'init',
                null,
                null,
                $fileListModel,
                true,
                self::MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);

            $rowSelector->setDefaultSelectionMode('one',
                self::MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens', array(self::MODEL_NAMESPACE => array()));

            $this->getDatasetAction();

            $this->view->filesJsSearchResults = $this->view->jsSearchResult(
                self::MODEL_NAME,
                self::MODEL_NAMESPACE,
                array('sysScreenCaption' => 'FILES LIST', 'usePagination' => true)
            );

            $this->view->filesJsSearchResultsDataset = $this->view->sysScreens[self::MODEL_NAMESPACE];
            $this->view->filesJsSearchResultsDataset['paginator']['selectionMode'] = 'one';

        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        return;
    }

    public function searchFilesAction()
    {
        $path       = $this->getRequest()->getParam('path');
        $extension  = $this->getRequest()->getParam('ext');

        $this->session->selectedFolder  = $path;
        $this->session->selectedType    = $extension;

        $screenBuilder = new Minder_SysScreen_Builder();
        /**
         * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
         */
        $searchKeeper  = $this->_helper->searchKeeper;

        list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MODEL_NAME);

        $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
        $searchFields = $searchKeeper->makeSearch($searchFields, self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->rowSelector;

        /**
         * @var Minder_SysScreen_Model_FileSystem $rowsModel
         */
        $rowsModel = $rowSelector->getModel(self::MODEL_NAMESPACE,
            self::$defaultSelectionAction,
            self::$defaultSelectionController);

        $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

        $rowSelector->setRowSelection('select_complete',
            'init',
            null,
            null,
            $rowsModel,
            true,
            self::MODEL_NAMESPACE,
            self::$defaultSelectionAction,
            self::$defaultSelectionController);

        $this->getRequest()->setParam('sysScreens', array(self::MODEL_NAMESPACE => array()));

        $this->getDatasetAction();

        $this->render('get-dataset');
    }

    public function getDatasetAction() {
        $datasets = array(
            self::MODEL_NAMESPACE   => self::MODEL_NAME,
            self::CONTENT_MODEL_NAMESPACE => self::CONTENT_MODEL
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        // set dataSource for file contents
        if (isset($sysScreens[self::CONTENT_MODEL_NAMESPACE])) {
            if(!isset($this->view->dataSource)) {
                $this->view->dataSource = 'file';
            }
        }

        foreach ($sysScreens as $namespace => $sysScreenPagination) {
            if (!isset($datasets[$namespace]))
                continue;

            $pagination = $this->restorePagination($namespace);
            if (isset($sysScreenPagination['paginator'])) {
                $pagination['selectedPage'] = (isset($sysScreenPagination['paginator']['selectedPage'])) ?
                    $sysScreenPagination['paginator']['selectedPage'] : $pagination['selectedPage'];

                $pagination['showBy']       = (isset($sysScreenPagination['paginator']['showBy']))       ?
                    $sysScreenPagination['paginator']['showBy']       : $pagination['showBy'];
            }

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy']);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($datasets[$namespace],
                $this->view->dataset,
                $this->view->selectedRows,
                $this->view->paginator);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new stdClass();
        $result->errors   = array();
        $result->warnings = array();
        $result->messages = array();
        $result->sysScreens = array();
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try{
            foreach ($this->getRequest()->getParam('sysScreens', array()) as $namespace => $sysScreen) {
                $tmpSysScreen = new stdClass();
                $tmpSysScreen->selectedRows = array();
                $tmpSysScreen->selectedRowsTotal = 0;
                $tmpSysScreen->selectedRowsOnPage = 0;

                $pagination = $this->restorePagination($namespace);

                if (isset($sysScreen['paginator'])) {
                    $pagination['selectedPage']  = (isset($sysScreen['paginator']['selectedPage']))  ? $sysScreen['paginator']['selectedPage']  : $pagination['selectedPage'];
                    $pagination['showBy']        = (isset($sysScreen['paginator']['showBy']))        ? $sysScreen['paginator']['showBy']        : $pagination['showBy'];
                    $pagination['selectionMode'] = (isset($sysScreen['paginator']['selectionMode'])) ? $sysScreen['paginator']['selectionMode'] : $pagination['selectionMode'];
                }

                if (isset($sysScreen['rowId']) && isset($sysScreen['state'])) {
                    $rowSelector->setSelectionMode($pagination['selectionMode'], $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                    $rowSelector->setRowSelection($sysScreen['rowId'], $sysScreen['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                }

                $tmpSysScreen->selectedRowsTotal = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($tmpSysScreen->selectedRowsTotal > 0) {
                    $tmpSysScreen->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
                    $tmpSysScreen->selectedRowsOnPage = count($tmpSysScreen->selectedRows);

                }


                $result->sysScreens[$namespace] = $tmpSysScreen;
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function reportAction() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $selectedCount = $rowSelector->getSelectedCount(self::MODEL_NAMESPACE,
            self::$defaultSelectionAction,
            self::$defaultSelectionController);

        if ($selectedCount > 0) {
            /**
             * @var Minder_SysScreen_Model_FileSystem $rowsModel
             */
            $rowsModel    = $rowSelector->getModel(self::MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);

            $rowsModel->addConditions($rowSelector->getSelectConditions(self::MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController));

            $fullFileNames = $rowsModel->getFullFileNames(0, $selectedCount);

            $data = array();

            foreach ($fullFileNames as $fileName) {
                $fileHandle = fopen($fileName, 'r');
                if ($fileHandle) {
                    while (($tmpdata = fgetcsv($fileHandle, 10240, ','))) {
                        $data[] = $tmpdata;
                    }
                    fclose($fileHandle);
                }
            }

            $this->view->data = $data;
        }

        $this->getResponse()->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
        $this->render('report-csv');
    }

    public function reportContentAction() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $selectedRowsCount = $rowSelector->getSelectedCount(self::CONTENT_MODEL_NAMESPACE,
            self::$defaultSelectionAction,
            self::$defaultSelectionController);
        $this->view->data = array();

        if ($selectedRowsCount > 0) {
            /**
             * @var Minder_SysScreen_Model_FileContent $rowsModel
             */
            $rowsModel    = $rowSelector->getModel(self::CONTENT_MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);
            $rowsModel->addConditions($rowSelector->getSelectConditions(self::CONTENT_MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController));
            $this->view->data = $rowsModel->getReportItems(0, $selectedRowsCount);
        }

        $this->getResponse()->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
        $this->render('report-csv');
    }

    public function showFileContentsAction() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $selectedRowsCount = $rowSelector->getSelectedCount(self::MODEL_NAMESPACE,
            self::$defaultSelectionAction,
            self::$defaultSelectionController);

        if ($selectedRowsCount > 0) {
            /**
             * @var Minder_SysScreen_Model_FileSystem $filesModel
             */
            $filesModel    = $rowSelector->getModel(self::MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);
            $filesModel->addConditions($rowSelector->getSelectConditions(self::MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController));

            $selectedFiles = $filesModel->getFullFileNames(0, $selectedRowsCount);

            $firstFile = current($selectedFiles);
            $screenBuilder = new Minder_SysScreen_FileContentsBuilder();

            try {
                $fileContentsModel = $screenBuilder->buildSysScreenModelFileContents(self::CONTENT_MODEL,
                    new Minder_SysScreen_Model_FileContent(),
                    $firstFile);

                $conditions = array('FULLNAME' => $selectedFiles);

                $fileContentsModel->addConditions($conditions);

                /**
                 * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
                 */
                $rowSelector   = $this->_helper->getHelper('RowSelector');

                $rowSelector->setRowSelection('select_complete',
                    'init',
                    null,
                    null,
                    $fileContentsModel,
                    true,
                    self::CONTENT_MODEL_NAMESPACE,
                    self::$defaultSelectionAction,
                    self::$defaultSelectionController);

                $this->getRequest()->setParam('sysScreens', array(self::CONTENT_MODEL_NAMESPACE => array()));

                // flag for render method
                $this->view->dataSource = 'file';

                $this->view->contentsJsSearchResults = $this->view->jsSearchResult(
                    self::CONTENT_MODEL,
                    self::CONTENT_MODEL_NAMESPACE,
                    array('sysScreenCaption' => 'CONTENTS',
                        'usePagination'    => true,
                        'filename'         => $firstFile
                    )
                );

                $this->view->contentsJsSearchResultsDataset = $this->view->sysScreens[self::CONTENT_MODEL_NAMESPACE];

            } catch(Exception $e) {
                $this->view->errors = array($e->getMessage());
            }
        }

        $this->getDatasetAction();
        $this->render('get-dataset');

        return;
    }

    public function _buildSearchStringForm() {
        return new Minder_Form_SearchString();
    }

    public function searchLineAction() {
        $search_line = $this->getRequest()->getParam('line');

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $selectedRows = $rowSelector->getSelectedRowsNoDbModel(self::MODEL_NAMESPACE,
            self::$defaultSelectionAction,
            self::$defaultSelectionController);
        if (!empty($selectedRows)) {
            /**
             * @var Minder_SysScreen_Model_FileContent $contentModel
             */
            $contentModel = $rowSelector->getModel(self::CONTENT_MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);

            $contentModel->addConditions(array('search-line' => $search_line));

            $rowSelector->setRowSelection('select_complete',
                'init',
                null,
                null,
                $contentModel,
                true,
                self::CONTENT_MODEL_NAMESPACE,
                self::$defaultSelectionAction,
                self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens', array(self::CONTENT_MODEL_NAMESPACE => array()));

            $this->view->dataSource = 'file';

            $this->getDatasetAction();

            $this->render('get-dataset');
        }
    }
}
