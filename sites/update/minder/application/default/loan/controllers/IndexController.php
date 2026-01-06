<?php

class IndexController extends Minder_Controller_Action
{
    public function init() {
        
        parent::init();
        $this->minder = Minder::getInstance();

        if ($this->minder->userId == null) {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)->goto('login', 'user', '', array());
            return;
        }

        $this->initView();
        $this->view->minder     = $this->minder;
        $this->view->baseUrl    = $this->view->url(array('action' => 'index', 'controller' => 'index', 'module' => 'default'), '', true);
        $this->licensee         = Zend_Registry::get('licensee');
        
    }

    public function indexAction(){}
    
    public function aboutAction() {
        
        $pathToManual  =   '../whm/manuals/' .  $this->licensee . '/' ;
        $manualArr     =   array();
        
        if (is_readable($pathToManual) && ($handle = opendir($pathToManual))) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $manualArr[]    =   $file;
                }
            }
            closedir($handle);
        }
        
        $this->view->manuals    =   $manualArr;
        $this->view->manualDir  =   $pathToManual;
   
    }

    public function errorAction() {
        
        $suffix = $this->_helper->viewRenderer->getViewSuffix();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->getResponse()->clearBody();

        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                echo $this->view->render('index/error_404.' . $suffix);
                break;
            
            default:
                echo $this->view->render('index/error_500.' .$suffix);
                break;
        }
    }

    protected function _getMenuId()
    {
        return 'HOME';
    }


}
