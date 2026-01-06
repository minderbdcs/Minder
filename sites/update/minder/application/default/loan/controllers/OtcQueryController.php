<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Denis Obuhov
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 *
 */


class OtcQueryController extends Minder_Controller_Action
{
    const RESULT_ACTION         = 'result';
    const SEARCH_REQUEST        = 'SEARCH_REQUEST';
    const QUERY_TYPE            = 'QUERY_TYPE';
    const QUERY_ID              = 'QUERY_ID';

    const TYPE_DESCRIPTION      = 'DESCRIPTION';
    const TYPE_TOOL             = 'TOOL';
    const TYPE_LOCATION         = 'LOCATION';
    const TYPE_BORROWER         = 'BORROWER';
    const TYPE_BORROWER_PARTIAL = 'BORROWER_PARTIAL';
    const TYPE_PRODUCT          = 'PROD_ID';

    public function searchAction() {
        $this->_storeSearchRequest($this->_otcQueryHelper()->getSearchRequest());
        $this->_forward('result');
    }

    public function resultAction() {

        $searchRequest = $this->_getStoredSearchRequest();

        $conditions = $this->_getConditions(static::RESULT_ACTION);

        $this->_preProcessNavigation();
        $navigation = $this->_getNavigationState(static::RESULT_ACTION);

        $limit = $navigation[static::SHOW_BY];
        $offset = $limit * $navigation[static::PAGE_SELECTOR];

        $this->view->data       = $this->_otcQueryHelper()->getResult($searchRequest, $offset, $limit);
        $this->view->headers    = $this->_otcQueryHelper()->getResultHeaders($searchRequest);

        $this->_postProcessNavigation($this->view->data);

        $this->view->data       = $this->view->data[static::DATA];
        $this->view->shownFrom  = ($this->view->numRecords) ? $offset + 1 : 0;
        $this->view->shownTill  = $offset + count($this->view->data);

        $this->view->conditions = $conditions;
        unset($this->view->conditions[static::QUERY_TYPE]);
        unset($this->view->conditions[static::QUERY_ID]);
        $this->view->totalSelect = count($this->view->conditions);
    }

    public function selectRowAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $rowId  = $this->getRequest()->getParam('rowId', '');
        $method = $this->getRequest()->getParam('mode' , 'init');

        $searchRequest = $this->_getStoredSearchRequest();

        $this->_preProcessNavigation();
        $navigation = $this->_getNavigationState(static::RESULT_ACTION);

        $limit = $navigation[static::SHOW_BY];
        $offset = $limit * $navigation[static::PAGE_SELECTOR];

        $data = $this->_otcQueryHelper()->getResult($searchRequest, $offset, $limit);

        $data = $data[static::DATA];
        $this->_markSelected2($data, $rowId, null, $method, static::RESULT_ACTION);

        $conditions = $this->_getConditions(static::RESULT_ACTION);
        unset($conditions[static::QUERY_TYPE]);
        unset($conditions[static::QUERY_ID]);

        echo json_encode(array(
            'totalSelected' => count($conditions),
            'selectedRows'  => $conditions
        ));
    }

    public function reportAction() {
        $reportType  = $this->getRequest()->getParam('report');
        $searchRequest = $this->_getStoredSearchRequest();

        $data = $this->_otcQueryHelper()->getResult($searchRequest, 0, 1);
        $data = $this->_otcQueryHelper()->getResult($searchRequest, 0, $data[static::TOTAL]);

        $this->view->data = $data[static::DATA];
        $this->view->headers = $this->_otcQueryHelper()->getResultHeaders($searchRequest);
        $this->_processReportTo($reportType);
    }

    /**
     * @return Minder_Controller_Action_Helper_OtcQuery
     */
    protected function _otcQueryHelper() {
        return $this->getHelper('OtcQuery');
    }

    /**
     * @param Minder_OtcSearchRequest $request
     */
    protected function _storeSearchRequest($request) {
        $conditions = $this->_getConditions(static::RESULT_ACTION);
        $conditions[static::SEARCH_REQUEST] = $request;
        $this->_setConditions($conditions, static::RESULT_ACTION);
    }

    /**
     * @return Minder_OtcSearchRequest
     */
    protected function _getStoredSearchRequest() {
        $conditions = $this->_getConditions(static::RESULT_ACTION);
        return isset($conditions[static::SEARCH_REQUEST]) ? $conditions[static::SEARCH_REQUEST] : new Minder_OtcSearchRequest();
    }
}