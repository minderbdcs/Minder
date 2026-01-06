<?php

class DashboardController extends Minder_Controller_Action {
    public function init()
    {
        $this->view->minder = $this->minder = Minder::getInstance();
        $this->initView();
        $this->view->shortcuts = array();
        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index'), '', true);
        $this->view->leftPannelState = 'hide';
    }

    public function indexAction() {
        $this->view->orderChart = new Zend_Form_Element_Image(array(
                'name' => 'order-chart',
                'src'  => $this->view->url(array(
                                               'module'      => 'services',
                                               'controller'  => 'chart',
                                               'action'      => 'get-chart',
                                               'chart-name' => Minder_ChartRenderer::ORDER_STATISTICS
                                           ))
                                                              ));
    }
}