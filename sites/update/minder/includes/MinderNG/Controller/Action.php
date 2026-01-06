<?php

use MinderNG\PageMicrocode\Microcode;

class MinderNG_Controller_Action extends Zend_Controller_Action {
    const DICE = 'dice';

    /**
     * @var \MinderNG\PageMicrocode\JsonRpc\Api
     */
    private $_microcodeApi;

    public function init()
    {
        Zend_Layout::startMvc(array('layout' => 'layout-ng'));
    }

    /**
     * @return \MinderNG\PageMicrocode\JsonRpc\Api|object
     * @throws Exception
     */
    protected function _microcodeApi() {
        if (empty($this->_microcodeApi)) {
            $this->_microcodeApi = $this->dice()->create(\MinderNG\PageMicrocode\JsonRpc\Api::CLASS_NAME);
        }

        return $this->_microcodeApi;
    }


    /**
     * @return \MinderNG\Di\DiContainer
     * @throws Zend_Exception
     */
    protected function dice() {
        return Zend_Registry::get(static::DICE);
    }

    /**
     * @return Zend_Controller_Action_Helper_ContextSwitch
     */
    protected function _contextSwitch()
    {
        return $this->_helper->getHelper('contextSwitch');
    }

    /**
     * @return \MinderNG\PageMicrocode\Component\PageCollection
     * @throws Exception
     */
    protected function _getPages()
    {
        /**
         * @var MinderNG\PageMicrocode\Component\PageManager $pageManager
         */
        $pageManager = $this->dice()->create(\MinderNG\PageMicrocode\Component\PageManager::CLASS_NAME, array(''));
        $pages = $pageManager->getCurrentUserAndDevicePages(Minder2_Environment::getInstance());
        return $pages;
    }

    /**
     * @param $page
     * @return Microcode
     * @throws Exception
     */
    protected function _loadPageMicrocode($page)
    {
        $microcode = $this->_getMicrocodeCache()->getPageMicrocode($page, $this->_getEnvironment(), $this->dice());
        return $microcode;
    }

    protected function _savePageMicrocode($page, Microcode $microcode) {
        $this->_getMicrocodeCache()->savePageMicrocode($microcode, $this->_getEnvironment());
    }

    /**
     * @return \MinderNG\PageMicrocode\MicrocodeCache
     * @throws Exception
     */
    protected function _getMicrocodeCache()
    {
        return $this->dice()->create(\MinderNG\PageMicrocode\MicrocodeCache::CLASS_NAME);
    }

    /**
     * @return Minder2_Environment
     */
    protected function _getEnvironment()
    {
        return Minder2_Environment::getInstance();
    }
}