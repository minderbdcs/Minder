<?php

use \MinderNG\PageMicrocode\Event;

class Pages_IndexController extends MinderNG_Controller_Action {
    public function init()
    {
        parent::init();

        Zend_Db_Table::setDefaultAdapter(Minder::getDefaultDbAdapter());
    }

    public function indexAction() {
        $pageId = $this->getRequest()->getParam('menuId');

        if (empty($pageId)) {
            $this->_redirect('/', array('exit' => false));
            return;
        }

        $this->view->pageComponents = array();
        $this->view->pages = $this->_getPages();

        try {
            $this->view->pageComponents = $this->_microcodeApi()->loadPage($pageId);
        } catch (Exception $e) {
            //todo
        }
    }

    public function editRowAction() {
        $pageId = $this->getRequest()->getParam('menuId');
        $screenName = $this->getRequest()->getParam('screen');
        $formName = $this->getRequest()->getParam('form');
        $params = array_diff_key(
            $this->getRequest()->getParams(),
            array('menuId'=> '', 'screen' => '', 'form' => '', 'module' => '', 'controller' => '', 'action' => '')
        );

        if (empty($pageId)) {
            $this->_redirect('/', array('exit' => false));
            return;
        }

        $this->view->pageComponents = array();
        $this->view->pages = $this->_getPages();

        try {
            $this->view->pageComponents = $this->_microcodeApi()->getPageComponents($pageId);
            $this->view->pageComponents = $this->_microcodeApi()->editRow($pageId, $screenName, $formName, $params);
        } catch (Exception $e) {

        }

        $this->render('index');
    }

    public function clearSessionCacheAction() {
        $this->_viewRenderer()->setNoRender(true);
        $this->_microcodeApi()->clearSessionCache();

        echo 'Done!';
    }

    /**
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    private function _viewRenderer()
    {
        return $this->getHelper('viewRenderer');
    }
}
