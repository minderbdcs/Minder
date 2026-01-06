<?php

class ImagesController extends Minder_Controller_Action {
    public function init()
    {
        $this->_forward('not-found');
    }

    public function notFoundAction() {
        $this->_viewRenderer()->setNoRender();
        $this->getResponse()->setHttpResponseCode(404);
    }
}