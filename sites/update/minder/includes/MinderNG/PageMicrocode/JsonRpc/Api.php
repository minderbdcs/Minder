<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\Di\DiContainer;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception\Exception;
use MinderNG\PageMicrocode\Microcode;
use MinderNG\PageMicrocode\MicrocodeCache;
use MinderNG\PageMicrocode\PageCache;

class Api {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\JsonRpc\\Api';

    /**
     * @var PageCache
     */
    private $_pageCache;

    /**
     * @var MicrocodeCache
     */
    private $_microcodeCache;

    /**
     * @var DiContainer
     */
    private $_dice;

    function __construct(PageCache $pageCache, MicrocodeCache $microcodeCache, DiContainer $dice)
    {
        $this->_pageCache = $pageCache;
        $this->_microcodeCache = $microcodeCache;
        $this->_dice = $dice;
    }

    public function clearSessionCache() {
        $this->_microcodeCache->cleanSessionCache();
    }

    public function touch($page) {
        return $this->_microcodeCache->touch($this->_findPage($page), $this->_getEnvironment());
    }

    public function loadPage($page) {
        return $this->_executeHandler($page, 'LoadPage');
    }

    public function getPageComponents($page) {
        return $this->_executeHandler($page, 'GetPageComponents');
    }

    public function executeSearch($page, $searchForm) {
        return $this->_executeHandler($page, 'ExecuteSearch', $searchForm);
    }

    public function saveChanges($page, $form) {
        return $this->_executeHandler($page, 'SaveChanges', $form);
    }

    public function flushCache($page) {
        $foundPage = $this->_findPage($page);
        $this->_microcodeCache->cleanCache($foundPage, $this->_getEnvironment());

        return true;
    }

    public function setCompanyLimit($page, $companyLimit) {
        return $this->_executeHandler($page, 'SetCompanyLimit', $companyLimit);
    }

    public function setWarehouseLimit($page, $warehouseLimit) {
        return $this->_executeHandler($page, 'SetWarehouseLimit', $warehouseLimit);
    }

    public function selectPrinter($page, $printer) {
        return $this->_executeHandler($page, 'SelectPrinter', $printer);
    }

    public function rowEditStarted($page, $form, $dataSet, $dataSetRow) {
        return $this->_executeHandler($page, 'RowEditStarted', $form, $dataSet, $dataSetRow);
    }

    public function editRow($pageId, $screenName, $formName, $searchSpecification) {
        return $this->_executeHandler($pageId, 'EditRow', $screenName, $formName, $searchSpecification);
    }

    public function clickFormButton($page, $form, $button) {
        return $this->_executeHandler($page, 'ClickFormButton', $form, $button);
    }

    private function _doExecute($name, Microcode $microcode) {
        $args = func_get_args();
        $instance = $this->_dice->create(__NAMESPACE__ . "\\" . array_shift($args));

        return call_user_func_array(array($instance, 'execute'), $args);
    }

    private function _executeHandler($page, $name) {
        $args = func_get_args();
        $microcode = $this->_microcodeCache->getPageMicrocode($this->_findPage(array_shift($args)), $this->_getEnvironment(), $this->_dice);

        try {
            $result = call_user_func_array(array($this, '_doExecute'), array_merge(array(array_shift($args), $microcode), $args));
        } catch (Exception $e) {
            $this->_microcodeCache->savePageMicrocode($microcode, $this->_getEnvironment());
            throw $e;
        }

        $this->_microcodeCache->savePageMicrocode($microcode, $this->_getEnvironment());

        return $result;
    }
    /**
     * @return \Minder2_Environment
     */
    protected function _getEnvironment()
    {
        return \Minder2_Environment::getInstance();
    }

    /**
     * @param $page
     * @return Component\Page
     * @throws \Exception
     */
    protected function _findPage($page)
    {
        $foundPage = $this->_pageCache->getCurrentUserAndDevicePages($this->_getEnvironment())->getPage($page);

        if (empty($foundPage)) {
            throw new \Exception('Page not found.'); //todo: change to typed exception
        }
        return $foundPage;
    }
}