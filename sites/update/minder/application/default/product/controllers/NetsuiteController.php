<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Function ( minder_array_merge() );
 */
include "functions.php";

/**
 * NetSuiteController
 *
 * Provide functionality for SOAP request to NetSuite webservice
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class NetSuiteController extends Minder_Controller_Action
{
    public function init()
    {
        $this->minder = Minder::getInstance();

        if ($this->minder->userId == null) {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('login', 'user', '', array());
            return;
        }
        if (false == $this->minder->isAdmin) {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('index', 'index', '', array());
            return;
        }

        $this->initView();
        $this->view->addHelperPath(ROOT_DIR . '/includes/helpers/', 'Minder_View_Helper');
        $this->view->minder         = $this->minder;
        $this->view->flashMessenger = $this->_helper->getHelper('flashMessenger');

        $this->_initSession('home');
        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index'), '', true);

        $this->_setupShortcuts();
        $this->_controller = $this->getRequest()->getControllerName();
        $this->_action = $this->getRequest()->getActionName();

        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index'), '', true);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->pageTitle = 'Administration';
    }

    public function addAction()
    {
        set_time_limit(0);
        $SoapPassport = new stdClass();
        $SoapPassport->email    = 'artyom.goncharov@binary-studio.com'; //'fpgminder@barcoding.com.au'; //'glenn@barcoding.com.au' ; //'artyom.goncharov@binary-studio.com';
        $SoapPassport->password = 'netsuite1';
        $SoapPassport->role     = 'Administrator';
        $SoapPassport->account  = '823303';//'32147';

        $p = new NetSuite_Parser();
        $s = new NetSuite_SoapWrapper();

        $s->Passport = $SoapPassport;

        try {
            if ($s->login()) {
                $this->session->netSuiteCookie = NetSuite_SoapWrapper::$cookie;
            } else {
                $this->addMessage('Can\'t login');
                return;
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
        //$xml = $s->inventoryAdjustments();
        //$xml = $s->searchVendor();
        $xml = $s->searchItemById(1177);
        //$xml = $s->searchTransaction('inventoryAdjustment');

        $objs = $p->parseResponse($xml);
        //var_dump($objs);
        echo '<pre>';
        foreach ($objs->searchResponse->searchResult->recordList as $obj) {
            //if ($obj->account->internalId == '147') {
                var_dump($obj);
            //}
        }
        echo '</pre>';
        //file_put_contents('vendor.xml', $xml);
        file_put_contents('response.xml', $xml);
        //var_dump($xml);
        die();
        /*
        if ($this->getRequest()->isPost()) {
            $po = (int)$this->getRequest()->getPost('qty');
            if ($po == 0) {
                $po = 1;
            }
            $s = new NetSuite_SoapWrapper();
            $s->login();
            $response = $s->addPO($po);
            $this->view->added = true;
            $this->view->flash = 'Added order with ' . $po;
        } else {

        }*/
    }

    public function refreshAction()
    {
        $sw = new NetSuite_SoapWrapper();

        $s = new NetSuite_Synchronizer();
        set_time_limit(1400);
        if ($s->refresh()) {
            $this->view->flash = 'Local cache was successfully updated.';
        } else {
            $this->view->flash = 'Refresh failed.';
        }
        set_time_limit(30);
    }

    public function updateAction()
    {
        set_time_limit(0);
        echo 'Update started.' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        $p = new NetSuite_Parser();
        $SoapPassport = new stdClass();
        $SoapPassport->email    = 'artyom.goncharov@binary-studio.com'; //'fpgminder@barcoding.com.au'; //'glenn@barcoding.com.au' ; //'artyom.goncharov@binary-studio.com';
        $SoapPassport->password = 'netsuite1';
        $SoapPassport->role     = 'Administrator';
        $SoapPassport->account  = '823303';//'32147';

        $s = new NetSuite_SoapWrapper();
        $s->Passport = $SoapPassport;

        try {
            $syn = new NetSuite_Synchronizer($s, 'TEST', 'XY');
        } catch (Exception $e) {
            var_dump($e);
        }

        $syn->update();
        die();
        /*
        try {
            $s->Passport = $SoapPassport;
            $s->login($SoapPassport);
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
        $response = $s->searchTransactionByTranId('PO00537');
        file_put_contents('responseTranId.xml', $response);
        */
        //$response = $s->searchItem('31410200');
        //31410200
        //array('salesOrder', 'purchaseOrder')
        //$response = $s->searchTransaction('lotNumberedItem');
        if ($p->isSuccess($response)) {
            //file_put_contents('tran1.xml', $response);
            //file_put_contents('stti1page.xml', $response);
//            $response = $s->searchNext();
//            file_put_contents('st2page.xml', $response);
            //file_put_contents('tran2.xml', $response);
//            $response = $s->searchNext();
//            file_put_contents('st3page.xml', $response);
            //file_put_contents('tran3.xml', $response);
            //file_put_contents('items2page.xml', $response);
        }

        //$response = $s->searchCustomRecord(null, null);
        //file_put_contents('customRecord.xml', $response);
        die();
        $xml = file_get_contents('D:\Shared\response.4765.xml');
        //var_dump($xml);
        var_dump($p->isSuccess($xml));
        $obj = $p->parseResponse($xml);
        echo '<pre>';
        var_dump($obj->addResponse->writeResponse->baseRef->internalId);
        echo '</pre>';
        die();
                if (isset($obj->Fault)) {
                    if (isset($obj->Fault->detail->invalidSessionFault)) {
                        if ('SESSION_TIMED_OUT' == $obj->Fault->detail->invalidSessionFault->code) {
                            echo 'NEW LOGIN';
                        } else {
                            $this->lastError = $obj->Fault->faultstring;
                            $this->lastErrorCode = $obj->Fault->faultcode;
                            $response = false;
                        }
                    } else {
                        $this->lastError = $obj->Fault->faultstring;
                        $this->lastErrorCode = $obj->Fault->faultcode;
                        $response = false;
                    }
                } else {
                    $response = false;
                }

        die();
        /*
        if ($this->getRequest()->isPost()) {
            $po = (int)$this->getRequest()->getPost('qty');
            if ($po == 0) {
                $po = 1;
            }
            $s = new NetSuite_SoapWrapper();
            $s->login();
            $response = $s->updatePO(906, $po);
            $this->view->updated = true;
            $this->view->flash = 'Order quantity updated with ' . $po;
        } else {

        }
        */
    }

    public function localCacheAction()
    {
        $s = new NetSuite_Synchronizer();
        if ($this->getRequest()->getPost('type') != '') {
            $st = $this->getRequest()->getPost('type');
        } else {
            $st = 'S';
        }

        if ($this->getRequest()->getPost('type') != '') {
            $this->view->page = $this->getRequest()->getPost('pageselector');
        } else {
            $this->view->page = 0;
        }
        $temp = $s->getCache($st);
        //$st = 'salesOrder';
        $counter = array('S' => 0, 'P' => 0);
        $counter[$st] = count($temp);

        $this->view->pages = range(1, ceil($counter[$st]/10), 1);
        if ((int)$this->view->page > ceil($counter[$st]/10)) {
            $this->view->page = 0;
        }

        $this->view->selectedType = $st;
        $this->view->data = array_slice($temp, $this->view->page*10, 10);
    }


    public function getAction()
    {
        $st = 'salesOrder';
        $this->view->selectedType = $st;
        $this->view->internalId = '';
        if ($this->getRequest()->isPost()) {
            $flash = array();
            if ($this->getRequest()->getPost('type') != '') {
                $st = $this->getRequest()->getPost('type');
            }
            $this->view->selectedType = $st;
            switch (strtoupper($this->getRequest()->getPost('action'))) {
                case 'GET INFO':
                    // setup internal ID if specified
                    if (isset($_POST['internalId'])) {
                        if ('' == $this->getRequest()->getPost('internalId')) {
                            $this->view->flash = 'No ID specified';
                            return;
                        } else {
                            $internalId = $this->getRequest()->getPost('internalId');
                        }
                        $this->view->internalId = $internalId;
                        set_time_limit(0);

                        $SoapPassport = new stdClass();
                        $SoapPassport->email    = 'artyom.goncharov@binary-studio.com'; //'fpgminder@barcoding.com.au'; //'glenn@barcoding.com.au' ; //'artyom.goncharov@binary-studio.com';
                        $SoapPassport->password = 'netsuite1';//'fpgminder1';
                        $SoapPassport->role     = 'Administrator';
                        $SoapPassport->account  = '823303';//'32147';

                        // create SoapWrapper;
                        $s = new NetSuite_SoapWrapper();
                        $s->Passport = $SoapPassport;
                        // login
                        if ($s->login()) {
                            $this->session->netSuiteCookie = NetSuite_SoapWrapper::$cookie;
                        } else {
                            $this->view->flash = 'Can\'t login';
                            return;
                        }

                        try {
                        // do request
                            $res = $s->get($st, $internalId);
                        } catch (Exception $e) {
                            echo '<pre>';
                            var_dump($e->getMessage());
                            var_dump($e->getTrace());
                            echo '</pre>';
                        }
                        if ($res) {
                            $this->session->Order = current($res);
                            $this->view->data = current($res);
                            $this->view->headers = $this->_setupHeaders($st);
                        } else {
                            $flash = $st . ' with internalId = ' . $internalId . ' doesn\'t exist . ' . $s->lastError;

                            $this->view->internalId = '';
                            $this->view->flash = $flash;
                        }
                    }
                    break;
                case 'ADD TO MINDER':
                    /*set_time_limit(0);
                    // create SoapWrapper;
                    $s = new NetSuite_SoapWrapper();
                    // login
                    if ($s->login()) {
                        $this->session->netSuiteCookie = NetSuite_SoapWrapper::$cookie;
                    } else {
                        $this->view->flash = 'Can\'t login';
                        return;
                    }
                    // do request
                    $res = $s->get($st, $internalId);
                    */
                    if ($st == 'salesOrder') {
                        $res = $this->session->Order;
                        if ($res) {
                            $pickOrder = $res;
                            if (!$this->minder->pickOrderInsert($pickOrder)) {
                                $flash = $this->view->flash;
                                $flash[] = 'Error occured - INSERT failed';
                                $this->view->flash = $flash;
                            } else {
                                $flash = $this->view->flash;
                                $flash[] = 'New Sales Order inserted';
                                $this->view->flash = $flash;
                            }
                        } else {
                            $this->view->flash = 'Can\'t retrieve order info';
                        }
                    } else {
                         $res = $this->session->Order;
                         if ($res) {
                                $purchaseOrder = $res;
                                //unset($purchaseOrder->items['itemList']);
                                if (!$this->minder->addPurchaseOrder($purchaseOrder)) {
                                    $flash = $this->view->flash;
                                    $flash[] = 'Error occured - INSERT failed';
                                    $this->view->flash = $flash;
                                } else {

                                    $flash = $this->view->flash;
                                    $flash[] = 'New Purchase Order inserted';
                                    $this->view->flash = $flash;
                                }
                            } else {
                                $this->view->flash = 'Can\'t retrieve order info';
                            }
                       }
                   break;
            }
        }
    }

    protected function _setupHeaders($type) {
        $headers = array(
                    'purchaseOrder' =>
                    array ( 'PURCHASE_ORDER'        => 'Purchase Order',
                            'PERSON_ID'             => 'Vendor',
                            'REQUISITION_NO'        => 'Requisition No',
                            'PO_DATE'               => 'Created Date',
                            'PO_REVISION_NO'        => 'Revision No',
                            'COMPANY_ID'            => 'Company ID',
                            'DIVISION_ID'           => 'Division ID',
                            'PO_STATUS'             => 'Order Status',
                            'COMMENTS'              => 'Comments',
                            'PO_PRINTED'            => 'Printed date',
                            'USER_ID'               => 'Created By ID',
                            'ORDER_TYPE'            => 'Order Type',
                            'PO_LEGACY_DATE'        => 'Legacy Created Date',
                            'PO_CURRENCY'           => 'Currency',
                            'PO_DUE_DATE'           => 'Due Date',
                            'PO_CREATED_BY_NAME'    => 'Created by Name',
                            'PO_LEGACY_INTERNAL_ID' => 'Internal ID',
                            'PO_RECEIVE_WH_ID'  => 'Receive WH.ID',
                            'PO_RECEIVE_WH_NAME' => 'Receive Warehouse',
                            'PO_LEGACY_MEMO'        => 'Order Notes',
                            'PO_LEGACY_STATUS'      => 'Legacy Status',
                            'PO_LEGACY_STATUS_ID'   => 'Legacy Status ID',
                            'PO_LEGACY_RECVD_DATE'  => 'Legacy Received Date',
                            'PO_LEGACY_CONSIGNMENT' => 'Legacy Consignment No',
                            'PO_SHIP_TO_ATTENTION'  => 'Ship To Attention',
                            'PO_SHIP_TO_ADDRESSEE'  => 'Ship To Addressee',
                            'PO_SHIP_TO_PHONE'      => 'Ship To Phone',
                            'PO_SHIP_TO_SUBURB'     => 'Ship To Suburb',
                            'PO_SHIP_TO_STATE'      => 'Ship To State',
                            'PO_SHIP_TO_POSTCODE'   => 'Ship To Post Code',
                            'PO_SHIP_TO_COUNTRY'    => 'Ship To Country',
                            'PO_SHIP_TO_ADDRESS1'   => 'Ship To Address 1',
                            'PO_SHIP_TO_ADDRESS2'   => 'Ship To Address 2',
                            'itemList'              => 'ItemList'),
                    'salesOrder' => array('Order #', 'Status', 'Priority', 'Due Date', 'Customer ID', 'Delivery To', 'Customer Ref.', 'Ship Via', 'WH', 'Company ID', 'Reason')
                    /*array ( 'tranId'        => 'tranId',
                            'location'      => 'location',
                            'entity'        => 'entity',
                            'billAddress'   => 'billAddress',
                            'subTotal'      => 'subTotal',
                            'taxTotal'      => 'taxTotal',
                            'total'         => 'total',
                            'balance'       => 'balance',
                            'exchangeRate'  => 'exchangeRate',
                            'currencyName'  => 'currencyName',
                            'email'         => 'email',
                            'terms'         => 'terms',
                            'createdDate'   => 'createdDate',
                            'tranDate'      => 'tranDate',
                            'status'        => 'status',
                            'itemList'      => 'itemList')*/
                        );

        return $headers[$type];
    }

    protected function _setupShortcuts()
    {
        $this->view->shortcuts = array(
            'Get Order by ID'      => $this->view->url(array('controller' => 'netsuite', 'action' => 'get'), '', true),
            'Add function Demo'    => $this->view->url(array('controller' => 'netsuite', 'action' => 'add'), '', true),
            'Update function Demo' => $this->view->url(array('controller' => 'netsuite', 'action' => 'update'), '', true),
            'Refresh Local Cache'  => $this->view->url(array('controller' => 'netsuite', 'action' => 'refresh'), '', true),
            'Do Request'           => $this->view->url(array('controller' => 'netsuite', 'action' => 'do-request'), '', true),
            'View Local Cache'     => $this->view->url(array('controller' => 'netsuite', 'action' => 'local-cache'), '', true)
        );
    }

    public function doRequestAction()
    {
        set_time_limit(0);
        if ($this->getRequest()->isPost()) {
            $xml = $this->getRequest()->getPost('xml');
            $soapAction = $this->getRequest()->getPost('soap_action');

            $p = new NetSuite_Parser();
            $SoapPassport = new stdClass();
            $SoapPassport->email    = 'fpgminder@barcoding.com.au'; //'glenn@barcoding.com.au' ; //'artyom.goncharov@binary-studio.com';
            $SoapPassport->password = 'fpgminder1';
            $SoapPassport->role     = 'Administrator';
            $SoapPassport->account  = '823303';//'32147';

            $s = new NetSuite_SoapWrapper();
            $s->login($SoapPassport);

            $response = $s->doRequest($xml, $soapAction);
            file_put_contents('response.xml', $response);
            //$obj = $p->parseResponse($response);

            //var_dump($obj);
            //if ($obj instanceof stdClass) {
            //    if ($obj->addResponse->writeResponse->status->isSuccess == 'true') {
            //        $id = $obj->addResponse->writeResponse->baseRef->internalId;
            //    }
            //}
            $this->view->request    = $s->lastRequest;
            $this->view->response   = $obj;
            $this->view->xml        = $xml;
            $this->view->soapAction = $soapAction;
        }

    }

    public function msg($output)
    {
        var_dump($output);
    }
}