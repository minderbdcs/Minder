<?php

class Minder_View_Helper_StandardPage extends Zend_View_Helper_Abstract {

    protected $_jsNamespace = '';
    protected $_customLoad = false;

    public function standardPage($jsNamespace, $customLoad = false) {
        $this->_setCustomLoad($customLoad);
        $this->setJsNamespace($jsNamespace);
        return $this;
    }

    public function pageTitle($title = null) {
        $title = is_null($title) ? $this->_getTitle() : $title;
        return '<h2>' . $this->_escape($title) . '</h2>';
    }

    public function messages() {
        return $this->_messenger()->all();
    }

    public function searchFieldsContainer($screenName, $namespace, $searchFields = null) {
        $searchFields   = is_null($searchFields)    ? $this->_searchFields()    : $searchFields;

        if (empty($searchFields[$screenName])) {
            return '';
        }

        $xhtml = array();
        $xhtml[] = '<div class="search_form_container" data-namespace="' . $namespace .'">';
        $xhtml[] = $this->view->sysScreenSearchForm2($screenName, array('search_fields' => $searchFields[$screenName]));
        $xhtml[] = '</div>';

        return implode("\n", $xhtml);
    }

    public function searchResultsContainer($screenResult) {
        return '<div class="'. $screenResult['namespace'] . ' hidden"></div>';
    }

    public function screenContainers($searchResults = null, $searchFields = null) {
        $searchResults  = is_null($searchResults)   ? $this->_searchResults()   : $searchResults;

        $xhtml = array();

        foreach($searchResults as $screenName => $screenResult) {
            $xhtml[] = $this->searchFieldsContainer($screenName, $screenResult['namespace'], $searchFields);
            $xhtml[] = $this->searchResultsContainer($screenResult);
            $xhtml[] = '';
        }

        return implode("\n", $xhtml);
    }

    public function jsExtend($baseModel = null) {
        $baseModel = is_null($baseModel) ? 'Minder.AbstractPageController' : $baseModel;
        return '        $.extend(this, ' . $baseModel . ');';
    }

    public function jsControllerOnLoad($searchResults = null, $sysScreens = null) {
        $searchResults  = is_null($searchResults)   ? $this->_searchResults()   : $searchResults;
        $sysScreens     = is_null($sysScreens)      ? $this->_sysScreens()    : $sysScreens;

        $js = array();
        $js[] = '       this.onLoad = function() {';
        $js[] = '           var searchResults = ' . json_encode($searchResults) . ';';
        $js[] = '           this.initScreenVarianceSearchResults(searchResults, true);';
        $js[] = '           this.initSearchForms(searchResults);';
        $js[] = '           this.onDataReady({sysScreens: ' . json_encode($sysScreens) . '});';
        $js[] = '           this.showAll();';
        $js[] = '       }';
        return implode("\n", $js);
    }

    public function jsGetRowSelectUrl() {
        $js = array();
        $js[] = '       this.getRowSelectUrl = function() {';
        $js[] = '           return "' . $this->_url(array('action' => 'select-row')) . '";';
        $js[] = '       }';
        return implode("\n", $js);
    }

    public function jsGetLoadUrl() {
        $js = array();
        $js[] = '       this.getLoadUrl = function() {';
        $js[] = '           return "' . $this->_url(array('action' => 'get-dataset')) . '";';
        $js[] = '       }';
        return implode("\n", $js);
    }

    public function jsGetSearchUrl() {
        $js = array();
        $js[] = '       this.getSearchUrl = function() {';
        $js[] = '           return "' . $this->_url(array('action' => 'search')) . '";';
        $js[] = '       }';
        return implode("\n", $js);
    }

    public function jsGetMasterSlaveChain($masterSlaveChain = null) {
        $masterSlaveChain = is_null($masterSlaveChain) ? $this->_masterSlaveChain() :$masterSlaveChain;
        $js = array();
        $js[] = '       this.getMasterSlaveChain = function() {';
        $js[] = '           return ' . json_encode($masterSlaveChain) . ';';
        $js[] = '       }';
        return implode("\n", $js);
    }

    public function jsPageControllerServiceMethods() {
        $js = array();

        $js[] = $this->jsGetLoadUrl();
        $js[] = $this->jsGetRowSelectUrl();
        $js[] = $this->jsGetSearchUrl();
        $js[] = $this->jsGetMasterSlaveChain();

        return implode("\n", $js);
    }

    public function jsPageController($jsNamespace = null) {
        $jsNamespace = is_null($jsNamespace) ? $this->getJsNamespace() : $jsNamespace;

        $js = array();
        $js[] = '   minderNamespace("' . $jsNamespace . '", function() {';

        $js[] = $this->jsExtend();
        $js[] = $this->jsControllerOnLoad();
        $js[] = $this->jsPageControllerServiceMethods();
        $js[] = '   });';
        return implode("\n", $js);
    }

    public function jsDomLoad($jsNamespace = null) {
        $jsNamespace = is_null($jsNamespace) ? $this->getJsNamespace() : $jsNamespace;

        $js = array();
        $js[] = '   $(function() {';

        $js[] = '       ' . $jsNamespace . '.onLoad();';
        $js[] = '   });';
        return implode("\n", $js);
    }

    public function jsMakeReport() {
        $js = array();
        $js[] = '   function makeReport(format, sysScreen) {';

        $js[] = '       location.href = "' . $this->_url(array('action' => 'report')) . '/report_format/" + escape(format) + "/screen/" + escape(sysScreen);';
        $js[] = '   };';
        return implode("\n", $js);
    }

    public function jsScript($jsNamespace = null) {
        $xhtml = array();
        $xhtml[] = '<script type="text/javascript">';

        $xhtml[] = $this->jsPageController($jsNamespace);
        $xhtml[] = '';

        if (!$this->_isCustomLoad()) {
            $xhtml[] = $this->jsDomLoad($jsNamespace);
            $xhtml[] = '';
        }

        $xhtml[] = $this->jsMakeReport();
        $xhtml[] = '</script>';
        return implode("\n", $xhtml);
    }

    function __toString()
    {
        $xhtml = array();
        $xhtml[] = $this->messages();
        $xhtml[] = $this->pageTitle();

        if ($this->hasErrors()) {
            return implode("\n", $xhtml) ;
        }

        $xhtml[] = $this->screenContainers();
        $xhtml[] = $this->jsScript();

        return implode("\n", $xhtml) ;
    }

    /**
     * @return string
     */
    public function getJsNamespace()
    {
        if (empty($this->_jsNamespace)) {
            trigger_error(__CLASS__ . ': jsTemplate is empty.', E_USER_WARNING);
        }

        return $this->_jsNamespace;
    }

    /**
     * @param string $jsNamespace
     * @return $this
     */
    public function setJsNamespace($jsNamespace)
    {
        $this->_jsNamespace = $jsNamespace;
        return $this;
    }

    /**
     * @return Minder_View_Helper_Messenger
     */
    protected function _messenger() {
        return $this->view->messenger();
    }

    protected function _escape($string) {
        return $this->view->escape($string);
    }

    protected function _getTitle() {
        return $this->view->pageTitle;
    }

    public function hasErrors() {
        return $this->view->hasErrors;
    }

    protected function _searchResults() {
        return $this->view->searchResults;
    }

    protected function _searchFields() {
        return $this->view->searchFields;
    }

    protected function _sysScreens() {
        return $this->view->sysScreens;
    }

    protected function _url(array $urlOptions = array(), $name = null, $reset = false, $encode = true) {
        return $this->view->url($urlOptions, $name, $reset, $encode);
    }

    protected function _masterSlaveChain() {
        return $this->view->masterSlaveChain;
    }

    /**
     * @return boolean
     */
    protected function _isCustomLoad()
    {
        return $this->_customLoad;
    }

    /**
     * @param boolean $customLoad
     * @return $this
     */
    protected function _setCustomLoad($customLoad)
    {
        $this->_customLoad = $customLoad;
        return $this;
    }
}