<?php

class PageController extends Minder2_Controller_Action {
    function showAction() {

        $menuId = $this->getRequest()->getParam('menuId');

        if (empty($menuId)) {
            $this->_helper->redirector->gotoSimple('index', 'index');
            return;
        }

        $pageBuilder      = Minder2_Page_Builder::getPageBuilder($menuId);
        $page             = $pageBuilder->build($menuId);
        $page->serviceUrl = $this->_helper->url->url(array('menuId' => $page->menuId), 'pageService');

        if (count($page->getScreens()) < 1) {
            $page->addWarnings('Page has no screens.');
        }

        /**
         * @var Minder2_Model_ChartScreen | Minder2_Model_SysScreen $screen
         */
        foreach ($page->getScreens() as $screen) {
            if ($screen instanceof Minder2_Model_ChartScreen)
                $screen->chartUrl = $this->_helper->url->url(array('menuId' => $page->menuId, 'chartId' => $screen->SS_NAME), 'minderChart');

            $screen->serviceUrl = $this->_helper->url->url(array('menuId' => $page->menuId, 'screenId' => $screen->SS_NAME), 'screenService');
            $screen->exportUrl  = $this->_helper->url->url(array('menuId' => $page->menuId, 'screenId' => $screen->SS_NAME), 'pageExport');
        }

        foreach ($this->_masterSlave()->buildMasterSlaveChain($page->getScreenNames()) as $master => $slaves) {
            foreach ($slaves as $slave => $relations) {
                $screen = $page->getScreen($slave);
                if (!empty($screen)) {
                    $screen->initDependentConditions($master);
                }
            }
        }

        $this->view->page = $page;

        return;
    }

    function pageServiceAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $menuId     = $this->getRequest()->getParam('menuId');
        $methodName = $this->getRequest()->getParam('method');

        if (empty($menuId)) {
            echo json_encode(array('error' => array('code' => '-1', 'message' => 'Page not found')));
            return;
        }

        $pageBuilder = new Minder2_Page_Builder();
        $page = $pageBuilder->build($menuId);

        if (!is_callable(array($page, $methodName))) {
            echo json_encode(array('error' => array('code' => '-1', 'message' => 'Method not found')));
            return;
        }

        $args = $this->getRequest()->getParam('params');

        echo json_encode(array('result' => call_user_func_array(array($page, $methodName), $args)));
    }

    function screenServiceAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            $menuId     = $this->getRequest()->getParam('menuId');
            $screenName = $this->getRequest()->getParam('screenId');
            $methodName = $this->getRequest()->getParam('method');

            if (empty($menuId)) {
                echo json_encode(array('error' => array('code' => '-1', 'message' => 'Page not found')));
                return;
            }

            $pageBuilder = new Minder2_Page_Builder();
            $page = $pageBuilder->build($menuId);

            $screenName = $page->getScreen($screenName);

            if (is_null($screenName)) {
                echo json_encode(array('error' => array('code' => '-1', 'message' => 'Screen not found')));
                return;
            }

            if (!is_callable(array($screenName, $methodName))) {
                echo json_encode(array('error' => array('code' => '-1', 'message' => 'Method not found')));
                return;
            }

            $args = $this->getRequest()->getParam('params', array());

            echo json_encode(array('result' => call_user_func_array(array($screenName, $methodName), $args)));
        } catch (Exception $e) {
            echo json_encode(array('error' => array('code' => '-1', 'message' => $e->getMessage())));
        }
    }

    public function getChartAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $menuId  = $this->getRequest()->getParam('menuId');
        $chartId = $this->getRequest()->getParam('chartId');

        if (empty($menuId)) {
            echo 'Page not found';
            return;
        }

        $pageBuilder = Minder2_Page_Builder::getPageBuilder($menuId);
        $page = $pageBuilder->build($menuId);

        /**
         * @var Minder2_Model_ChartScreen $chart
         */
        $chart = $page->getScreen($chartId);

        if (is_null($chart)) {
            echo 'Chart not found';
            return;
        }

        echo $chart->getChart();
    }

    public function exportAction() {
        $this->_helper->layout()->disableLayout();
        $this->view->data = array();

        try {
            $menuId     = $this->getRequest()->getParam('menuId');
            $screenName = $this->getRequest()->getParam('screenId');

            if (empty($menuId)) {
                throw new Exception('No MenuID given.');
            }

            $pageBuilder = new Minder2_Page_Builder();
            $page = $pageBuilder->build($menuId);

            $screen = $page->getScreen($screenName);

            if (is_null($screen)) {
                throw new Exception('No SYS_SCREEN given.');
            }

            $selectedCount = $screen->getSelectedRowsAmount();

            if ($selectedCount > 0) {
                $this->view->data = $screen->getSelectedItems(0, $selectedCount);

                if (reset($this->view->data)) {
                    $this->view->headers = array_keys(current($this->view->data));
                    $this->view->headers = array_combine($this->view->headers, $this->view->headers);
                }
            }

        } catch (Exception $e) {
        }

        switch (strtoupper($this->getRequest()->getParam('reportFormat', 'REPORT: CSV'))) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('/reports/report-csv');
                return;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('/reports/report-xls');
                return;

            case 'XLSX':
            case 'REPORT: XLSX':
                $response = $this->getResponse();
                $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->setHeader('Content-Disposition', 'attachment;filename="report.xlsx"');
                $response->setHeader('Cache-Control', 'max-age=0');
                $this->render('/reports/report-xlsx');
                break;
        }
    }
}
