<?php

class Services_ChartController extends Minder_Controller_Action {
    public function init()
    {
        $this->view->minder = $this->minder = Minder::getInstance();
        $this->initView();
        $this->view->shortcuts = array();
        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index'), '', true);
        $this->view->leftPannelState = 'hide';
    }

    public function getChartAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $chartName = $this->getRequest()->getParam('chart-name');

        if (empty($chartName))
            return;

        Minder_ChartRenderer::render($chartName);
    }
}