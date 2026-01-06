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
 * SoapWrapper
 *
 * Provide direct access to NetSuite Soap methods.
 * Therefore for most functions should use NetSuite_Synchronizer
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class NetSuite_SoapWrapper
{
    private $xmlnsEnv    = 'xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"';
    private $urnMessages = 'urn:messages_2008_1.platform.webservices.netsuite.com';
    private $urnCore     = 'urn:core_2008_1.platform.webservices.netsuite.com';
    private $urnCommon   = 'urn:common_2008_1.platform.webservices.netsuite.com';
    private $urnCommunication = 'urn:communication_2008_1.platform.webservices.netsuite.com';

    public static $cookie;
    public static $client;
    public static $locn;

    public $Passport;
    public $lastError;
    public $lastErrorCode;

    public $lastRequest;
    public $lastResponse;
    public $techResponse;

    private $_parser;

    // temporary added for storing info from whm
    private $_minder;

    private $_mode;

    public function __construct($style = SOAP_RPC, $location = 'https://webservices.netsuite.com/services/NetSuitePort_2008_1', $silentmode = false)
    {
        $this->_mode = $silentmode;

        self::$locn = $location;
        self::$client = new SoapClient(null, array('location' => self::$locn,
                                 'uri'      => '' . $this->urnCore . '',
                                 'style'    => $style,
                                 'use'      => SOAP_LITERAL,
                                 'connection_timeout' => 10,
                                 'trace' => 1));

        // fix for correct include from WHM code
        $path = rtrim(dirname(__FILE__), '\\/');
        include_once $path . DIRECTORY_SEPARATOR . 'Parser.php';

        // move dir up
        $path = rtrim(dirname($path), '\\/');
        include_once $path . DIRECTORY_SEPARATOR . 'Minder.php';
        include_once $path . DIRECTORY_SEPARATOR . 'Minder' . DIRECTORY_SEPARATOR . 'DAO.php';

        $this->_parser = new NetSuite_Parser();
        $this->_minder = Minder::getInstance();
        $this->_minder->silent = $silentmode;
    }

    private function msg($output)
    {
        if (!$this->_mode) {
            echo date('Y-m-d H:i:s', time()) . ': ' . $output . PHP_EOL;
        }
    }

    private function delVendor($id)
    {
        $xml ='<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:ns1="' . $this->urnCore . '"
            xmlns:ns2="' . $this->urnMessages . '"
            xmlns:ns3="urn:relationships_2008_1.lists.webservices.netsuite.com">
            <SOAP-ENV:Body>
                <ns2:delete>
                    <ns2:record xsi:type="ns3:Vendor">
                        <ns3:internalId>' . $id . '</ns3:internalId>
                    </ns2:record>
                </ns2:delete>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'delete');
    }

    private function addVendor($vnd)
    {
        $xml ='<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:ns1="' . $this->urnCore . '"
            xmlns:ns2="' . $this->urnMessages . '"
            xmlns:ns3="urn:relationships_2008_1.lists.webservices.netsuite.com">
            <SOAP-ENV:Body>
                <ns2:add>
                    <ns2:record xsi:type="ns3:Vendor">
                        <ns3:isPerson>false</ns3:isPerson>
                        <ns3:companyName>' . $vnd . '</ns3:companyName>
                        <ns3:unsubscribe>false</ns3:unsubscribe>
                    </ns2:record>
                </ns2:add>
            </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>';

        return $this->doRequest($xml, 'add');
    }

    /**
     * Example
     *
     * @param string $id
     * @param integer $qty
     * @return unknown
     */
    public function updatePO($id, $qty) {
$xml ='<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:ns1="' . $this->urnCore . '"
xmlns:ns2="' . $this->urnMessages . '"
xmlns:ns3="urn:purchases_2008_1.transactions.webservices.netsuite.com">
<SOAP-ENV:Body>
<ns2:update>
<ns2:record internalId="' . $id . '" xsi:type="ns3:PurchaseOrder">
<ns3:entity internalId="244">some test 2</ns3:entity>
<ns3:itemList xsi:type="ns3:PurchaseOrderItemList">
<ns3:item xsi:type="ns3:PurchaseOrderItem">
<item internalId="50"/>
<quantity>' . $qty . '</quantity>
<description>some descr here</description>
</ns3:item>
</ns3:itemList>
</ns2:record>
</ns2:update>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'update');
    }

    /**
     * Example of new PO
     *
     * @param string $po
     * @return string
     */
    public function addPO($po) {
$xml ='<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:ns1="' . $this->urnCore . '"
xmlns:ns2="' . $this->urnMessages . '"
xmlns:ns3="urn:purchases_2008_1.transactions.webservices.netsuite.com">
<SOAP-ENV:Body>
<ns2:add>
<ns2:record xsi:type="ns3:PurchaseOrder">
<ns3:entity internalId="244">some test 3</ns3:entity>
<ns3:itemList xsi:type="ns3:PurchaseOrderItemList">
<ns3:item xsi:type="ns3:PurchaseOrderItem">
<item internalId="50"/>
<quantity>' . $po . '</quantity>
<description>some descr here for 3</description>
</ns3:item>
</ns3:itemList>
</ns2:record>
</ns2:add>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'add');
    }

    /**
     * Sample search of Vendor
     *
     * @return string
     */
    public function srch()
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:VendorSearchBasic">
                            <ns4:type operator="anyOf" xsi:type="ns4:SearchEnumMultiSelectField" xmlns:ns4="' . $this->urnCore . '">
                                <ns4:searchValue xsi:type="ns4:RecordRef"></ns4:searchValue>
                            </ns4:type>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        $this->lastRequest = $xml;
        $response = self::$client->__doRequest($xml, self::$locn, 'search', 1);
        $this->lastResponse = $response;
        return $response;
    }

    public function login($ppo = null) {
        if (null === $ppo) {
            if (null == $this->Passport) {
                throw new Exception('no Passport received');
            } else {
                $ppo = $this->Passport;
            }
        } else {
            $this->Passport = $ppo;
        }

        $var = new SoapVar(
                        $ppo,
                        SOAP_ENC_OBJECT,
                        'passport',
                        'https://webservices.netsuite.com/xsd/platform/v2008_1_0/core.xsd',
                        'passport',
                        '' . $this->urnMessages . ''
                        );
        $wrapper = new SoapParam($var, "passport");
        try {
            $inp = new SoapHeader('' . $this->urnMessages . '', 'loginResponse');
            self::$client->__SoapCall('login',
                                      array($wrapper),
                                      array('soapaction' => 'login'),
                                      null,
                                      $inp);
        } catch (SoapFault $f) {
            $this->lastError     = $f->faultstring;
            $this->lastErrorCode = 0;
            return false;
        }
        $hdr  = self::$client->__getLastResponseHeaders();
        $list = explode(PHP_EOL,$hdr);
        foreach($list as $line) {
            if (false !== strpos($line, 'Set-Cookie:')) {
                $subline         = substr($line, 12);
                list($key, $val) = explode('=', $subline, 2);
                list($val,)      = explode(';', $val);
                $cookie[$key]    = $val;
            }
        }
        $this->setCookie($cookie);
        return self::$client->__getLastResponse();
    }

    public function setCookie($cookie) {
        self::$cookie = $cookie;
        self::$client->__setCookie('JSESSIONID', $cookie['JSESSIONID']);
    }

    /**
     * Interface for NetSuite webservice getList() function
     *
     * @param string $type
     * @param array  $listId
     * @return array
     */
    public function getList($type, $listId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <SOAP-ENV:Envelope ' . $this->xmlnsEnv . ' xmlns:xs2="' . $this->urnMessages . '" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns1="' . $this->urnCore . '" xmlns:ns2="' . $this->urnMessages . '">
                <SOAP-ENV:Body>
                <ns2:getList>';
        foreach ($listId as $internalId) {
            $xml .= '<ns2:baseRef internalId="' . $internalId . '" type="' . $type  . '" xsi:type="ns1:RecordRef"/>';
        }
        $xml .= '</ns2:getList>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';

        $response = $this->doRequest($xml, 'getList');
        if ($this->isSuccess()) {
            switch ($type) {
                case 'salesOrder':
                    $result = $this->_parser->parseSalesOrder($response);
                    return $result;
                break;
                case 'purchaseOrder':
                    $result = $this->_parser->parsePurchaseOrder($response);
                    return $result;
                break;
                default:
                return $response;
                break;
            }
        } else {
            return false;
        }
    }

    /**
     * Interface for NetSuite webservice get() function
     *
     * @param string  $type
     * @param integer $internalId
     * @return array
     */
    public function get($type, $internalId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <SOAP-ENV:Envelope ' . $this->xmlnsEnv . ' xmlns:xs2="' . $this->urnMessages . '" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns1="' . $this->urnCore . '" xmlns:ns2="' . $this->urnMessages . '">
                        <SOAP-ENV:Body>
                            <ns2:get>
                                <ns2:baseRef internalId="' . $internalId . '" type="' . $type  . '" xsi:type="ns1:RecordRef">
                                    <ns1:name/>
                                </ns2:baseRef>
                            </ns2:get>
                        </SOAP-ENV:Body>
                    </SOAP-ENV:Envelope>';
        $response = $this->doRequest($xml, 'get');
        //file_put_contents('responseget' . $internalId . '.xml', $response);
        $msg = 'trying to get type="' . $type . '" internalId="' . $internalId . '"' . PHP_EOL;
        $this->msg($msg);
        $this->msg('---XML---');
        $this->msg($response);
        $this->msg('---XML---');
        if ($this->isSuccess()) {
            switch (strtolower($type)) {
                case 'salesorder':
                    $result = $this->_parser->parseSalesOrder($response);

                    $r = current($result);
                    //-- check location
                    if (!array_key_exists($r->soLegacyPickWhId, $this->_minder->getAllowedLegacyLocations())) {
                        if ('' == $r->soLegacyPickWhId) {
                            $this->lastError = $internalId . ' - ' . $r->pickOrder . ' order doesn\'t have location';
                        } else {
                            $this->lastError = $internalId . ' - ' . $r->pickOrder . ' order not in allowed location ' . $r->soLegacyPickWhId . ' - ' . $r->soLegacyPickWhName;
                        }
                        $this->lastErrorCode = -101; // not proceed order;
                        //$msg = $this->lastError . PHP_EOL;
                        //$this->mailToCustomer('FAILED ' . $r['PURCHASE_ORDER'], $msg);
                        return false;
                    }

                    if ($r->soLegacyConsignment instanceof stdClass) {
                        $id = $r->soLegacyConsignment->internalId;
                        $typeId = $r->soLegacyConsignment->typeId;
                        $consignment = $this->_parser->parseResponse($this->searchCustomRecord($id, $typeId));

                        if ('true' == $consignment->searchResponse->searchResult->status->isSuccess) {
                            if ($consignment->searchResponse->searchResult->totalRecords > 0) {
                                $consignment = $consignment->searchResponse->searchResult->recordList->record->customRecordId;
                            }
                        } else {
                            $consignment = $r->soLegacyConsignment->localValue;
                        }
                        $r->soLegacyConsignment = $consignment;
                    }
                    foreach ($r->items as $item) {
                        $res = $this->searchItemById($item->prodId);
                        $it = $this->_parser->parseSearch($res);
                        if ($it) {
                            if ('LotNumberedInventoryItem' != $it[$item->prodId]->type) {
                                $this->msg($it[$item->prodId]->internalId . ' - ' . $it[$item->prodId]->type . ' != LotNumberedInventoryItem - rejected');
                                $this->lastError = 'Order has item(' . $it[$item->prodId]->type . ') not LotNumberedInventoryItem';
                                return false;
                            } else {
                                $this->msg($it[$item->prodId]->internalId . ' - ' . $it[$item->prodId]->type . ' - accepted');
                            }
                            $item->prodId = $it[$item->prodId]->itemId;
                        }
                    }
                    if ($r->companyId != '') {
                        $vendor = $this->get('customer', $r->companyId);
                        if (isset($vendor->getResponse->readResponse->status->isSuccess) &&
                                  $vendor->getResponse->readResponse->status->isSuccess == 'true') {
                            $record = $vendor->getResponse->readResponse->record;
                            $r->companyId       = $record->entityId;
                            $r->dCountry        = isset($record->addressbookList->addressbook->country) ? $this->_countryCapitalize($record->addressbookList->addressbook->country) : 'Australia';
                            $r->contactName     = isset($record->addressbookList->addressbook->addressee) ? $record->addressbookList->addressbook->addressee : false;
                            $r->dAddressLine1   = isset($record->addressbookList->addressbook->addr1) ? $record->addressbookList->addressbook->addr1 : false;
                            $r->dAddressLine2   = isset($record->addressbookList->addressbook->addr2) ? $record->addressbookList->addressbook->addr2 : false;
                            $r->dCity           = isset($record->addressbookList->addressbook->city) ? $record->addressbookList->addressbook->city : false;
                            $r->dState          = isset($record->addressbookList->addressbook->state) ? $record->addressbookList->addressbook->state : false;
                            $r->dPostCode       = isset($record->addressbookList->addressbook->zip) ? $record->addressbookList->addressbook->zip : false;
                        }
                    }
                    if ($r->personId != '') {
                        if ($r->personId == $r->companyId) {
                            $entity = $vendor;
                        } else {
                            $entity = $this->get('customer', $r->personId);
                        }
                        if (isset($entity->getResponse->readResponse->status->isSuccess) &&
                                $entity->getResponse->readResponse->status->isSuccess == 'true') {
                            $record = $entity->getResponse->readResponse->record;
                            $r->personId       = $record->entityId;
                            $r->pCountry        = isset($record->addressbookList->addressbook->country) ? $this->_countryCapitalize($record->addressbookList->addressbook->country) : 'Australia';
                            $r->pFirstName      = isset($record->addressbookList->addressbook->addressee) ? $record->addressbookList->addressbook->addressee : false;
                            $r->pAddressLine1   = isset($record->addressbookList->addressbook->addr1) ? $record->addressbookList->addressbook->addr1 : false;
                            $r->pAddressLine2   = isset($record->addressbookList->addressbook->addr2) ? $record->addressbookList->addressbook->addr2 : false;
                            $r->pCity           = isset($record->addressbookList->addressbook->city) ? $record->addressbookList->addressbook->city : false;
                            $r->pState          = isset($record->addressbookList->addressbook->state) ? $record->addressbookList->addressbook->state : false;
                            $r->pPostCode       = isset($record->addressbookList->addressbook->zip) ? $record->addressbookList->addressbook->zip : false;
                        }
                    }
                    /*
                    if ($r->sPersonId != '') {
                        if ($r->sPersonId == $r->companyId) {
                            $person = $vendor;
                        } elseif ($r->sPersonId == $r->personId) {
                            $person = $entity;
                        } else {
                            $person = $this->get('customer', $r->supplierId);
                        }
                        if (isset($person->getResponse->readResponse->status->isSuccess) &&
                                $person->getResponse->readResponse->status->isSuccess == 'true') {
                            $record = $person->getResponse->readResponse->record;
                            $r->sPersonId       = $record->entityId;
                            $r->sCountry        = isset($record->addressbookList->addressbook->country) ? $record->addressbookList->addressbook->country : 'Australia';
                            $r->sFirstName      = isset($record->addressbookList->addressbook->addressee) ? $record->addressbookList->addressbook->addressee : false;
                            $r->sAddressLine1   = isset($record->addressbookList->addressbook->addr1) ? $record->addressbookList->addressbook->addr1 : false;
                            $r->sAddressLine2   = isset($record->addressbookList->addressbook->addr2) ? $record->addressbookList->addressbook->addr2 : false;
                            $r->sCity           = isset($record->addressbookList->addressbook->city) ? $record->addressbookList->addressbook->city : false;
                            $r->sState          = isset($record->addressbookList->addressbook->state) ? $record->addressbookList->addressbook->state : false;
                            $r->sPostCode       = isset($record->addressbookList->addressbook->zip) ? $record->addressbookList->addressbook->zip : false;
                        }
                    }
                    */
                    $legacyCompanyId  = $this->_minder->getSoapCache('DepartmentId');
                    if ($legacyCompanyId != false and $legacyCompanyId != "")
                    {
                        $r->companyId = $this->_minder->getCompanyIdforLegacyCompany( $legacyCompanyId);
                    }
                    return $result;
                break;
                case 'purchaseorder':
                    $result = $this->_parser->parsePurchaseOrder($response);
                    $r = current($result);

                    //-- check consignment
                    if ($r->items['PO_LEGACY_CONSIGNMENT'] instanceof stdClass) {
                        $id = $r->items['PO_LEGACY_CONSIGNMENT']->internalId;
                        $typeId = $r->items['PO_LEGACY_CONSIGNMENT']->typeId;
                        $consignment = $this->_parser->parseResponse($this->searchCustomRecord($id, $typeId));
                        if ('true' == $consignment->searchResponse->searchResult->status->isSuccess) {
                            if ($consignment->searchResponse->searchResult->totalRecords > 0) {
                                $consignment = $consignment->searchResponse->searchResult->recordList->record->customRecordId;
                            }
                        } else {
                            $consignment = $r->items['PO_LEGACY_CONSIGNMENT']->localValue;
                        }
                        $r->items['PO_LEGACY_CONSIGNMENT'] = $consignment;
                    }
                    //-- check Owner code
                    if($r->items['PO_LEGACY_OWNER_ID'] instanceof stdClass && $r->items['PO_LEGACY_OWNER_ID']!='' ) {
                        // want the company_id_prefix for the department id
                        $id = $r->items['PO_LEGACY_OWNER_ID']->internalId;
                        $r->items['PO_LEGACY_OWNER_ID'] = $this->_minder->getCompanyPrefixforLegacyCompany( $id);
/*
                        $optionsList = $this->_minder->getOptionsList('DEP_INT_ID');
                        if(array_key_exists($id, $optionsList)) {
                            $r->items['PO_LEGACY_OWNER_ID'] = $optionsList[$id];
                        } else {
                            $this->lastError     = 'In a base these records are not present';
                            $this->lastErrorCode = 0;
                            $r->items['PO_LEGACY_OWNER_ID'] = '';
                        }
*/
                        if($r->items['PO_LEGACY_OWNER_ID'] == '') {
                            $this->lastError = 'No Company Prefix for Department';
                            $this->lastErrorCode = 0; 
                            $msg = $this->lastError . PHP_EOL;
                        }
                    }
                    if($r->items['PO_LEGACY_OWNER_ID'] == '') {
                        $this->lastError = 'Order rejected ' . $r['PURCHASE_ORDER'] . ' no Owner code or Department' . PHP_EOL;
                        $this->lastErrorCode = -101; // not proceed order;
                        $msg = $this->lastError . PHP_EOL;
                        $this->mailToCustomer('FAILED ' . $r['PURCHASE_ORDER'], $msg);
                        return false;
                    }

                    //-- check location
                    //if (!array_key_exists($r['PO_RECEIVE_WH_ID'], $this->_minder->getAllowedLegacyLocations())) {
                    if (!array_key_exists($r['PO_LEGACY_RECEIVE_WH_ID'], $this->_minder->getAllowedLegacyLocations())) {
                        //if ('' == $r['PO_RECEIVE_WH_ID']) {
                        if ('' == $r['PO_LEGACY_RECEIVE_WH_ID']) {
                            $this->lastError = $internalId . ' - ' . $r['PURCHASE_ORDER'] . ' order doesn\'t have location';
                        } else {
                            //$this->lastError = $internalId . ' - ' . $r['PURCHASE_ORDER'] . ' order not in allowed location ' . $r['PO_RECEIVE_WH_ID'] . ' - ' . $r['PO_RECEIVE_WH_NAME'];
                            $this->lastError = $internalId . ' - ' . $r['PURCHASE_ORDER'] . ' order not in allowed location ' . $r['PO_LEGACY_RECEIVE_WH_ID'] . ' - ' . $r['PO_LEGACY_RECEIVE_WH_NAME'];
                        }
                        $this->lastErrorCode = -101; // not proceed order;
                        $msg = $this->lastError . PHP_EOL;
                        //$this->mailToCustomer('FAILED ' . $r['PURCHASE_ORDER'], $msg);
                        return false;
                    }

                    $legacyCompanyId  = $this->_minder->getSoapCache('DepartmentId');
                    if ($legacyCompanyId != false and $legacyCompanyId != "")
                    {
                        $r->items['COMPANY_ID'] = $this->_minder->getCompanyIdforLegacyCompany( $legacyCompanyId);
                    }
                    if ('' == $r->items['COMPANY_ID'] || ' ' == $r->items['COMPANY_ID'] ) {
                        $r->items['COMPANY_ID'] = current($this->_minder->getListByField('CONTROL.COMPANY_ID'));
                    }
                    foreach ($r->items['itemList'] as $item) {
                        $res = $this->searchItemById($item->items['PROD_ID']);
                        $it = $this->_parser->parseSearch($res);
                        if ($it) {
                            if ('LotNumberedInventoryItem' != $it[$item->items['PROD_ID']]->type) {
                                $this->msg($it[$item->items['PROD_ID']]->internalId . ' - ' . $it[$item->items['PROD_ID']]->type . ' != LotNumberedInventoryItem - rejected');
                                $this->lastError = 'Order has item(' . $it[$item->items['PROD_ID']]->type . ') not LotNumberedInventoryItem';
                                return false;
                            } else {
                                $this->msg($it[$item->items['PROD_ID']]->internalId . ' - ' . $it[$item->items['PROD_ID']]->type . ' - accepted');
                            }
                            $item->items['PROD_ID'] = $it[$item->items['PROD_ID']]->itemId;
                            $item->items['PROD_ID'] = trim($item->items['PROD_ID'], '*');
                        }
                    }
                    $vendor = $this->get('vendor', $r->items['PERSON_ID']);
                    if (isset($vendor->getResponse->readResponse->status->isSuccess) &&
                              $vendor->getResponse->readResponse->status->isSuccess == 'true') {
                        $record = $vendor->getResponse->readResponse->record;
                        $r->items['PERSON_ID'] = $record->entityId;
                        $r->items['PO_SHIP_TO_COUNTRY']      = isset($record->addressbookList->addressbook->country) ? $this->_countryCapitalize($record->addressbookList->addressbook->country) : 'Australia';
                        $r->items['PO_SHIP_TO_ATTENSION']    = isset($record->addressbookList->addressbook->attention) ? $record->addressbookList->addressbook->attention : false;
                        $r->items['PO_SHIP_TO_ADDRESSEE']    = isset($record->addressbookList->addressbook->addressee) ? $record->addressbookList->addressbook->addressee : false;
                        $r->items['PO_SHIP_TO_ADDRESS1']     = isset($record->addressbookList->addressbook->addr1) ? $record->addressbookList->addressbook->addr1 : false;
                        $r->items['PO_SHIP_TO_ADDRESS2']     = isset($record->addressbookList->addressbook->addr2) ? $record->addressbookList->addressbook->addr2 : false;
                        $r->items['PO_SHIP_TO_SUBURB']       = isset($record->addressbookList->addressbook->city) ? $record->addressbookList->addressbook->city : false;
                        $r->items['PO_SHIP_TO_STATE']        = isset($record->addressbookList->addressbook->state) ? $record->addressbookList->addressbook->state : false;
                        $r->items['PO_SHIP_TO_POSTCODE']     = isset($record->addressbookList->addressbook->zip) ? $record->addressbookList->addressbook->zip : false;
                    }
                    // calculate the minder wh
                    $r->items['PO_RECEIVE_WH_ID'] = current($this->_minder->getListByField('CONTROL.DEFAULT_WH_ID'));
                    return $result;
                break;
                case 'vendor':
                    $result = $this->_parser->parseResponse($response);
                    return $result;
                    break;
                case 'customer':
                    $result = $this->_parser->parseResponse($response);
                    return $result;
                    break;
                default:
                    //throw new Exception('Unrecognized Type. (' . $type . ')');
                    return $response;
                break;
            }
        } else {
            $this->msg($this->lastError . PHP_EOL . $this->lastErrorCode);
            return false;
        }
    }

    public function searchTransactionByTranId($tranId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <SOAP-ENV:Envelope
                        ' . $this->xmlnsEnv . '
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:ns2="' . $this->urnMessages . '"
                        xmlns:ns3="' . $this->urnCommon . '"
                        xmlns:ns4="' . $this->urnCore . '">
                    <SOAP-ENV:Body>
                        <search xmlns="' . $this->urnMessages . '">
                            <searchRecord xsi:type="ns3:TransactionSearchBasic" xmlns:ns1="urn:relationships_2008_1.lists.webservices.netsuite.com">
                                <ns3:tranId operator="contains" xsi:type="ns4:SearchStringField">
                                    <ns4:searchValue xsi:type="ns4:RecordRef">' . $tranId . '</ns4:searchValue>
                                </ns3:tranId>
                            </searchRecord>
                        </search>
                    </SOAP-ENV:Body>
                    </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'search');
    }

    /**
     * Search transaction at Netsuite
     *
     * @param array $typeList
     * @param string $lastModifiedDate
     */
    public function searchTransaction($typeList = array(''), $lastModifiedDate = null)
    {
        if (!is_array($typeList)) {
            $typeList = array($typeList);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <SOAP-ENV:Envelope
                        ' . $this->xmlnsEnv . '
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:ns2="' . $this->urnMessages . '"
                        xmlns:ns3="' . $this->urnCommon . '"
                        xmlns:ns4="' . $this->urnCore . '">
                    <SOAP-ENV:Body>
                        <search xmlns="' . $this->urnMessages . '">
                            <searchRecord xsi:type="ns3:TransactionSearchBasic" xmlns:ns1="urn:relationships_2008_1.lists.webservices.netsuite.com">
                                <ns3:type operator="anyOf" xsi:type="ns4:SearchEnumMultiSelectField">';
        if (count($typeList) > 0) {
            foreach ($typeList as $type) {
                $xml .= '<ns4:searchValue xsi:type="ns4:RecordRef">' . $type . '</ns4:searchValue>';
            }
        } else {
            $xml .= '<ns4:searchValue xsi:type="ns4:RecordRef"/>';
        }
        $xml .= '                 </ns3:type>';
        if ($lastModifiedDate !== null) {
            $xml .= '<ns3:lastModifiedDate operator="after" xsi:type="ns4:SearchDateField">
                 <ns4:searchValue xsi:type="ns4:RecordRef">' . $lastModifiedDate . '</ns4:searchValue>
                 </ns3:lastModifiedDate>';
        }
/*
        $xml .= ' <ns3:status operator="anyOf" xsi:type="ns4:SearchEnumMultiSelectField">';
        if (count($statusList) > 0) {
            foreach ($statusList as $status) {
                $xml .= '<ns4:searchValue xsi:type="ns4:RecordRef">' . $status . '</ns4:searchValue>';
            }
        } else {
            $xml .= '<ns4:searchValue xsi:type="ns4:RecordRef"/>';
        }
        $xml .= '                 </ns3:status>';
*/
/*
            $xml .= ' <ns3:status operator="anyOf" xsi:type="ns4:SearchEnumMultiSelectField">
                                    <ns4:searchValue xsi:type="ns4:RecordRef">pending receipt</ns4:searchValue>
                                    <ns4:searchValue xsi:type="ns4:RecordRef">partially receipted</ns4:searchValue>
                                    <ns4:searchValue xsi:type="ns4:RecordRef">approved by supervisor/pending receipt</ns4:searchValue>
                                    <ns4:searchValue xsi:type="ns4:RecordRef">pending fulfillment</ns4:searchValue>
                                    <ns4:searchValue xsi:type="ns4:RecordRef">partially fulfilled</ns4:searchValue>
                                </ns3:status>';
*/
/*
            $xml .= ' <ns3:location operator="anyOf" xsi:type="ns4:SearchMultiSelectField" xmlns:ns4="' . $this->urnCore . '">
                    ';
            $id = 1; 
            $xml .= '                        <ns4:searchValue internalId="' . $id . '" xsi:type="ns4:RecordRef"></ns4:searchValue>
                    ';
            $id = 7; 
            $xml .= '                        <ns4:searchValue internalId="' . $id . '" xsi:type="ns4:RecordRef"></ns4:searchValue>
                    ';
            $id = 10; 
            $xml .= '                        <ns4:searchValue internalId="' . $id . '" xsi:type="ns4:RecordRef"></ns4:searchValue>
                    ';
            $id = 45; 
            $xml .= '                        <ns4:searchValue internalId="' . $id . '" xsi:type="ns4:RecordRef"></ns4:searchValue>
                    ';
            $id = 48; 
            $xml .= '                        <ns4:searchValue internalId="' . $id . '" xsi:type="ns4:RecordRef"></ns4:searchValue>
                    ';
            $xml .= '           </ns3:location>
                    ';
*/
        $xml .= '            </searchRecord>
                        </search>
                    </SOAP-ENV:Body>
                    </SOAP-ENV:Envelope>';
        $this->msg('---SEARCH XML---');
        $this->msg($xml);
        $this->msg('---SEARCH XML---');
        return $this->doRequest($xml, 'search');
    }

    public function searchVendor($id = null)
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:VendorSearchBasic">
                            <ns3:entityId ';
        if (null == $id) {
            $xml .= 'operator="notEmpty" xsi:type="ns1:SearchStringField">';
        } else {
            $xml .= 'operator="contains" xsi:type="ns1:SearchStringField">
                    <';
        }
        $xml .= '            </ns3:entityId>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'search');
    }

    public function doRequest($xml, $action) {
        $this->lastRequest = $xml;
        try {
            $this->techResponse = $response = self::$client->__doRequest($xml, self::$locn, $action, 1);
            //file_put_contents($action . 'do-response.xml', $response);
            //file_put_contents($action . 'do-request.xml', $xml);
            $success = $this->_parser->isSuccess($response);
            if (!$success) {
                $obj = $this->_parser->parseResponse($response);
                if (false === $success && isset($obj->Fault)) {
                    if (isset($obj->Fault->detail->invalidSessionFault)) {
                        if ('SESSION_TIMED_OUT' == $obj->Fault->detail->invalidSessionFault->code) {
                            if ($this->login()) {
                                $this->techResponse = $response = self::$client->__doRequest($xml, self::$locn, $action, 1);
                            }
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
                } elseif (false === $success) {
                    if (isset($obj->getResponse->readResponse->status->statusDetail)) {
                        $this->lastError = $obj->getResponse->readResponse->status->statusDetail->message;
                        $this->lastErrorCode = 0;
                    }
                    //$response = false;
                } else {
                    $this->lastError = 'Unknown error';
                    $response = false;
                }
            }
        } catch (SoapFault $f) {
            $this->lastError     = $f->getMessage();
            $this->lastErrorCode = $f->getCode();
            $this->techResponse  = $f->__toString();
            $response = false;
        }

        $this->lastResponse = $response;
        return $response;
    }

    public function getAllowedTypeList() {
        return array('salesOrder', 'purchaseOrder', 'SalesOrder', 'PurchaseOrder');
    }

    private function isSuccess() {
        if (false !== strpos($this->lastResponse, 'status isSuccess="true"')) {
            return true;
        } else {
            return false;
        }

    }

    public function updateItemFulfillment($id, $state = '_picked')
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:sales_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>
                        <ns2:update xmlns="' . $this->urnMessages . '">
                            <ns2:record internalId="' . $id . '" xsi:type="ns3:ItemFulfillment">
                                <ns3:shipStatus>' . $state . '</ns3:shipStatus>
                            </ns2:record>
                        </ns2:update>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        $this->msg(' Update ItemFulfillment status ' . $id . ' - ' . $state . PHP_EOL);
        $response   = $this->doRequest($xml, 'update');
        $result     = $this->_parser->isSuccess($response);
        if ($result == false) {
            $this->msg($this->lastError = $this->_parser->lastError);
            return false;
        } else {
            $this->msg('Successfully.');
            return true;
        }
    }

    public function updateSalesOrder()
    {
        $result = false;

        $serialNumber = '';
        $this->lastError = '';

        $details = $this->_minder->getPickItemDetail();

        $this->msg('Trying to update SO with : ');
        $this->msg(serialize($details));

        if (count($details) > 0) {
            foreach ($details as $pickItemDetail) {
                foreach ($pickItemDetail as $key => $line) {
                    $serialNumber = '';
                    $qty = 0;
                    $qty                     = $line['QTY_PICKED'];
                    $serialNumber         = $key;

                    $line['WH_ID']           = trim($line['WH_ID']);
                    $line['PI_LEGACY_WH_ID'] = trim($line['PI_LEGACY_WH_ID']);
                    $serialNumber2 = $key . '(' . $qty . ')';
                    //$serialNumber[$key] = $key . '(' . $line['QTY_PICKED'] . ')';
                    //$serialNumber[$key] = $key . '(' . $qty . ')';
                    //$serialNumber[$key] = $key . '(' . $qty . ')';

                    if (isset($line->items['PROD_ID'])) {
                        $response = $this->searchItemByName($line->items['PROD_ID']);
                        $product = $this->_parser->parseSearch($response);
                        $product = current($product);
                        if (!isset($product->internalId)) {
                            $this->lastError = $product->internalId . ' Product ' . $line->items['PROD_ID'] . ' not found in NetSuite.';
                            $this->lastErrorCode = 0;
                            $msg = $this->lastError . PHP_EOL;
                            $this->msg($msg);
                            $product = false;
                        }
                    } else {
                       $product = false;
                    } 
              

        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:sales_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>
                        <ns2:add xmlns="' . $this->urnMessages . '">
                            <ns2:record xsi:type="ns3:ItemFulfillment">
                                <ns3:createdFrom internalId="' . $line['SO_LEGACY_INTERNAL_ID'] . '"/>
                                <ns3:tranDate>' . date(DATE_W3C, time()) . '</ns3:tranDate>
                                <ns3:itemList replaceAll="false" xsi:type="ns3:ItemFulfillmentItemList">
                                    <ns3:item xsi:type="ns3:ItemFulfillmentItem">
                                    <ns3:itemReceive>true</ns3:itemReceive>';
/*
        if (false != $product) {
                //$xml .= '               <ns3:item type="inventoryItem" internalId="' . $product . '" />';
		// if product is a string or an array ?
                $xml .= '               <ns3:item type="inventoryItem" internalId="' . $product->internalId . '" />';
        }
*/

        if ('' != $line['PI_LEGACY_WH_ID']) {
            $xml .= PHP_EOL;
            $xml .= '                    <ns3:location internalId="' . ((int)trim($line['PI_LEGACY_WH_ID'])) . '"/>';
        } else {
            if ('' != $line['WH_ID']) {
                if (is_numeric($line['WH_ID'])) {
                    $xml .= PHP_EOL;
                    $xml .= '                    <ns3:location internalId="' . ((int)$line['WH_ID']) . '"/>';
                } else {
                    $this->msg('PI_LEGACY_WH_ID is empty. WH_ID is not numeric (' . $line['WH_ID'] . ')');
                }
            } else {
                $this->msg('PI_LEGACY_WH_ID is empty. WH_ID is empty');
            }
        }

        if ('' != $qty) {
            $xml .= PHP_EOL;
            $xml .= '                    <ns3:quantity>' . ((int)$qty) . '</ns3:quantity>';
        }
        if ('' != $line['PI_LEGACY_LINENO']) {
            $xml .= PHP_EOL;
            $xml .= '                    <ns3:orderLine>' . $line['PI_LEGACY_LINENO'] . '</ns3:orderLine>';
        }
        if ('' != $serialNumber) {
            //$xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
            // if serial number is an array ?
            $xml .= PHP_EOL;
            $xml .='                     <ns3:serialNumbers>' . $serialNumber2 . '</ns3:serialNumbers>';

        } else {
            //$prod
        }
        if (false != $product) {
                //$xml .= '               <ns3:item type="inventoryItem" internalId="' . $product . '" />';
		// if product is a string or an array ?
            if (isset($product->internalId)) {
                $xml .= PHP_EOL;
                $xml .= '               <ns3:item type="inventoryItem" internalId="' . $product->internalId . '" />';
            }
        }
        $xml .= PHP_EOL;
        $xml .='                    </ns3:item>
                                </ns3:itemList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        $this->msg('--- xml add itemFulfillment ---');
        $this->msg($xml);
        $this->msg('--- end xml ---');

                    $output =
                          'Request to update = ' . print_r($line['SO_LEGACY_INTERNAL_ID'], true) . PHP_EOL .
                          //'Location          = ' . print_r($line['PI_LEGACY_WH_NAME'], true) . PHP_EOL .
                          'Location Id       = ' . print_r($line['WH_ID'], true) . PHP_EOL .
                          'PICK_ORDER        = ' . print_r($line['PICK_ORDER'], true) . PHP_EOL .
                          'PICK_DETAIL_ID    = ' . print_r($line['PICK_DETAIL_ID'], true) . PHP_EOL .
                          'PI_LEGACY_LINE_NO = ' . print_r($line['PI_LEGACY_LINENO'], true) . PHP_EOL .
                          'PROD_ID           = ' . print_r($line['PROD_ID'], true) . PHP_EOL .
                          'SSN.PROD_ID       = ' . print_r($line['SSN.PROD_ID'], true) . PHP_EOL .
                          'QTY_PICKED        = ' . print_r($line['QTY_PICKED'], true) . PHP_EOL .
                          'serialNumbers     = ' . $serialNumber;
                    $this->msg($output);
                    $response = $this->doRequest($xml, 'add');
                    if (false !== $response) {
                        $result = $this->_parser->isSuccess($response);
                        if ($result == false) {
                            $this->lastError = $this->_parser->lastError;
                            $this->lastErrorCode = 0;
                            //$this->msg($this->lastError);
                            $this->msg('Failed in Fulfillment : ' . $this->lastError);
                            $msg = $output . PHP_EOL;
                            $this->mailToCustomer('FAILED ' . $line['PICK_ORDER'] . ' - ' . $this->lastError, $msg);
                            //$this->mailToTechnical('Trying to update SO', serialize($details));
                            $this->_minder->updatePickItemDespatchExit($line['PICK_DETAIL_ID']);
                        } else {
                            $obj = $this->_parser->parseResponse($response);
                            if (isset($obj->addResponse->writeResponse->baseRef->internalId)) {
                                $id = $obj->addResponse->writeResponse->baseRef->internalId;
                                if ($id != '') {
                                    $this->msg('Created fulfillment with internalId = ' . $id);
                                    $this->mailToCustomer($line['PICK_ORDER'] . ' - created itemFulfillment' , $msg);

                                    if (!$this->updateItemFulfillment($id, '_picked')) {
                                        //$this->mailToTechnical('Trying to update SO', serialize($details));
                                    } elseif (!$this->updateItemFulfillment($id, '_packed')) {
                                        //$this->mailToTechnical('Trying to update SO', serialize($details));
                                    } elseif (!$this->updateItemFulfillment($id, '_shipped')) {
                                        //$this->mailToTechnical('Trying to update SO', serialize($details));
                                    }
                                    $this->_minder->updatePickItemDespatched($line['PICK_DETAIL_ID']);
                                } else {
                                    $this->msg('Have some troubles with ' . serialize($obj));
                                    //$this->mailToTechnical('Trying to update SO', serialize($details));
                                }
                            } else {
                                $msg = $this->lastError . PHP_EOL . $output . PHP_EOL;
                                $this->msg('An error #- ' . $this->lastError . PHP_EOL . $output . PHP_EOL);
                                $this->mailToCustomer('FAILED ' . $line['PICK_ORDER'] . ' - failed itemFulfillment' , $msg);
                                $this->mailToSoap('FAILED ' . $line['PICK_ORDER'] . ' - failed itemFulfillment' , $msg);
                                //$this->mailToTechnical('Trying to update SO', serialize($details));
                                $this->_minder->updatePickItemDespatchExit($pickItemDetail[$key]['PICK_DETAIL_ID']);
                            }
                        }
                    } else {
                        //$this->msg('Trying to update SO. ' . PHP_EOL . serialize($details));
                        $this->msg('An error - ' . $this->lastError . PHP_EOL . $this->techResponse);
                        //$this->mailToTechnical('Trying to update SO', serialize($details));
                        return false;
                    }
                }
            }
        } else {
            $this->msg('Nothing to update' . PHP_EOL);
        }
        return $result;
    }

    public function sendSalesOrdersByOrder()
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $result = false;

        $serialNumber = array();
        $qty = array();
        $this->lastError = '';
	$lastOrder = '';
	$lastLine = 'none';
	$lastSsn = '';
        $xml = '';
        $xmls = array();
        $despatches = array();
        $despatchId = array();

        $details = $this->_minder->getPickItemDetail();
        // have fixed this to be ordered by pick order
        // by pick line
        // by ssn's ssn_id

        // must put all the lines for an order together in one soap message
        // at a change in order  produce details for order
        // at a change in line no produce details for line
        // at a change in original ssn put in
        //   serial numbers "FInnnnn(qty), FInnnnm(qty)"
        // then must update all for packed etc
        // and update the pick item details

        $this->msg('Trying to update SO with : ');
        $this->msg(serialize($details));

        if (count($details) > 0) {
            foreach ($details as $pickItemDetail) {
                foreach ($pickItemDetail as $key => $line) {
                    //$serialNumber = '';
                    //$qty = 0;

        //if (($line['PICK_LABEL_NO'] != $lastLine) or
        if (($line['PI_LEGACY_LINENO'] != $lastLine) or
            ($line['PICK_ORDER'] != $lastOrder)) {
            // line changed - 
            // do item ending
            if ($xml != '') {
                if (count($qty) > 0) {
                    $xml .= PHP_EOL;
                    $xml .= '                                    <ns3:quantity>' . ((int)array_sum($qty)) . '</ns3:quantity>';
                }
                if (count($serialNumber) > 0) {
                    //$xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
                    $xml .= PHP_EOL;
                    $xml .='                                     <ns3:serialNumbers>' ;
                    $serialNumber2 = '';
                    foreach ($serialNumber as $serialKey => $serialId) {
                        $serialNumber2 .= $serialId . ", " ;
                    }
                    $serialNumber2 = substr($serialNumber2, 0, -2);
                    $xml .= $serialNumber2 . '</ns3:serialNumbers>';
                } else {
                    //$prod
                }
                // add  line  ending
                $xml .= PHP_EOL;
                $xml .='                                  </ns3:item>';
            }
            // reset serial no and qty vars
            unset($serialNumber);
            unset($qty);
            $serialNumber = array();
            $qty = array();
        }
        if ($line['PICK_ORDER'] != $lastOrder) {
            // order changed
            $lastLine = 'none';
            if ($xml != '') {
                // add  order ending
                $xml .= PHP_EOL;
                $xml .= '                                </ns3:itemList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
                // save this xml
                $xmls[$lastOrder] = $xml;
                $this->msg('--- created xml itemFulfillment ---');
                //$this->msg($xml);
                //$this->msg('--- end xml ---');
                $despatches[$lastOrder] = $despatchId;
                unset($despatchId);
                $despatchId = array();
            }
            $lastOrder = $line['PICK_ORDER'];
            // start new xml
            $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:sales_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>
                        <ns2:add xmlns="' . $this->urnMessages . '">
                            <ns2:record xsi:type="ns3:ItemFulfillment">
                                <ns3:createdFrom internalId="' . $line['SO_LEGACY_INTERNAL_ID'] . '"/>
                                <ns3:tranDate>' . date(DATE_W3C, time()) . '</ns3:tranDate>
                                <ns3:itemList replaceAll="false" xsi:type="ns3:ItemFulfillmentItemList">';
        }

        //if ($line['PICK_LABEL_NO'] != $lastLine)  {
        //    $lastLine = $line['PICK_LABEL_NO'];
        if ($line['PI_LEGACY_LINENO'] != $lastLine)  {
            $lastLine = $line['PI_LEGACY_LINENO'];
            // start new xml
            $xml .= PHP_EOL ; 
            $xml .= '                                   <ns3:item xsi:type="ns3:ItemFulfillmentItem">
                                     <ns3:itemReceive>true</ns3:itemReceive>';
            $line['WH_ID']           = trim($line['WH_ID']);
            $line['PI_LEGACY_WH_ID'] = trim($line['PI_LEGACY_WH_ID']);
            if ('' != $line['PI_LEGACY_WH_ID']) {
                $xml .= PHP_EOL;
                $xml .= '                                    <ns3:location internalId="' . ((int)trim($line['PI_LEGACY_WH_ID'])) . '"/>';
            } else {
                // need companyId and whId of order
                $wkPickOrder = $this->_minder->getPickOrder($lastOrder );
                $wkLegacyLocationId = $this->_minder->getLegacyLocationIdforCompany( $wkPickOrder->whId, $wkPickOrder->companyId);
                if (is_numeric($wkLegacyLocationId)) {
                    $xml .= PHP_EOL;
                    $xml .= '                                    <ns3:location internalId="' . ((int)$wkLegacyLocationId) . '"/>';
                } else {
                    $this->msg('PI_LEGACY_WH_ID is empty.  and Legacy Company Location is empty');
                }

/*
                if ('' != $line['WH_ID']) {
                    if (is_numeric($line['WH_ID'])) {
                        $xml .= PHP_EOL;
                        $xml .= '                                    <ns3:location internalId="' . ((int)$line['WH_ID']) . '"/>';
                    } else {
                        $this->msg('PI_LEGACY_WH_ID is empty. WH_ID is not numeric (' . $line['WH_ID'] . ')');
                    }
                } else {
                    $this->msg('PI_LEGACY_WH_ID is empty. WH_ID is empty');
                }
*/
            }
            if ('' != $line['PI_LEGACY_LINENO']) {
                $xml .= PHP_EOL;
                $xml .= '                                    <ns3:orderLine>' . $line['PI_LEGACY_LINENO'] . '</ns3:orderLine>';
            }
        }
                    //$qty                     = $line['QTY_PICKED'];
                    //$serialNumber         = $key;

                    $line['WH_ID']           = trim($line['WH_ID']);
                    $line['PI_LEGACY_WH_ID'] = trim($line['PI_LEGACY_WH_ID']);
                    //$serialNumber[$key] = $key . '(' . $line['QTY_PICKED'] . ')';
                    //$serialNumber[$key] = $key . '(' . $qty . ')';
                    $serialNumber[$key] = $key . '(' . $line['QTY_PICKED'] . ')';
                    $qty[$key] = $line['QTY_PICKED'];
                    $despatchId[] = $line['PICK_DETAIL_ID'];

/*
        if (false != $product) {
                //$xml .= '               <ns3:item type="inventoryItem" internalId="' . $product . '" />';
		// if product is a string or an array ?
            if (isset($product->internalId)) {
                $xml .= PHP_EOL;
                $xml .= '               <ns3:item type="inventoryItem" internalId="' . $product->internalId . '" />';
            }
        }
*/
        $this->msg('--- detail add itemFulfillment ---');

                    $output =
                          'Request to update = ' . print_r($line['SO_LEGACY_INTERNAL_ID'], true) . PHP_EOL .
                          //'Location          = ' . print_r($line['PI_LEGACY_WH_NAME'], true) . PHP_EOL .
                          'Location Id       = ' . print_r($line['WH_ID'], true) . PHP_EOL .
                          'PICK_ORDER        = ' . print_r($line['PICK_ORDER'], true) . PHP_EOL .
                          'PICK_DETAIL_ID    = ' . print_r($line['PICK_DETAIL_ID'], true) . PHP_EOL .
                          'PI_LEGACY_LINE_NO = ' . print_r($line['PI_LEGACY_LINENO'], true) . PHP_EOL .
                          'PROD_ID           = ' . print_r($line['PROD_ID'], true) . PHP_EOL .
                          'SSN.PROD_ID       = ' . print_r($line['SSN.PROD_ID'], true) . PHP_EOL .
                          'QTY_PICKED        = ' . print_r($line['QTY_PICKED'], true) . PHP_EOL .
                          'serialNumbers     = ' . print_r($serialNumber, true);
                    $this->msg($output);
                } // foreach pick item detail
            } // foreach detail
            if ($xml != '') {
                // do item ending
                if (count($qty) > 0) {
                    $xml .= PHP_EOL;
                    $xml .= '                                    <ns3:quantity>' . ((int)array_sum($qty)) . '</ns3:quantity>';
                }
                if (count($serialNumber) > 0) {
                    //$xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
                   $xml .= PHP_EOL;
                    $xml .='                                     <ns3:serialNumbers>' ;
                    $serialNumber2 = '';
                    foreach ($serialNumber as $serialKey => $serialId) {
                        $serialNumber2 .= $serialId . ", " ;
                    }
                    $serialNumber2 = substr($serialNumber2, 0, -2);
                    $xml .= $serialNumber2 . '</ns3:serialNumbers>';
                } else {
                    //$prod
                }
                // add  line  ending
                $xml .= PHP_EOL;
                $xml .='                                 </ns3:item>';
            }
            if ($xml != '') {
                // add ending
                $xml .= PHP_EOL;
                $xml .= '                                </ns3:itemList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
                // save this xml
                $xmls[$lastOrder] = $xml;
                $this->msg('--- created last xml itemFulfillment ---');
                //$this->msg($xml);
                //$this->msg('--- end xml ---');
                $despatches[$lastOrder] = $despatchId;
            }
            // now must process all these xmls
            foreach ($xmls as $key => $order) {
                $line = array();
                $line['PICK_ORDER'] = $key;
                $despatchId = $despatches[$key];
                $output = print_r($despatchId, true);
                // process the $line
                $this->msg('Process xml ' . $key);
                //$this->msg('with despatches  ' . $output);
                $this->msg('--- xml add itemFulfillment ---');
                $this->msg($order);
                $this->msg('--- end xml ---');

                $response = $this->doRequest($order, 'add');
                $this->msg("response:" . print_r($response, true));
                if (false !== $response) {
                    $result = $this->_parser->isSuccess($response);
                    $this->msg("isSuccess:" . print_r($result, true));
                    if ($result == false) {
                        $this->msg("isSuccess: is false" );
                        $this->lastError = $this->_parser->lastError;
                        $this->msg("lasterror:" . $this->_parser->lastError );
                        $this->lastErrorCode = 0;
                        $this->msg('Failed in Fulfillment : ' . $this->lastError);
                        $msg = $line['PICK_ORDER']  . PHP_EOL . $order . PHP_EOL;
                        $this->mailToCustomer('FAILED ' . $line['PICK_ORDER'] . ' - ' . $this->lastError, $msg);
                        foreach($despatchId as $despatchkey => $despatchLine) {
                            $this->msg('fail despatch ' . $despatchLine);
                            $this->_minder->updatePickItemDespatchExit($despatchLine);
                        }
                    } else {
                        $obj = $this->_parser->parseResponse($response);
                        if (isset($obj->addResponse->writeResponse->baseRef->internalId)) {
                            $id = $obj->addResponse->writeResponse->baseRef->internalId;
                            if ($id != '') {
                                $this->msg('Created fulfillment with internalId = ' . $id);
                                //$msg = 'Created Fulfillment with internalId = ' . $id . PHP_EOL . $line['PICK_ORDER'] . PHP_EOL . $order . PHP_EOL ;
                                //$this->mailToCustomer($line['PICK_ORDER'] . ' - created itemFulfillment' , $msg);
                                if (!$this->updateItemFulfillment($id, '_picked')) {
                                } elseif (!$this->updateItemFulfillment($id, '_packed')) {
                                } elseif (!$this->updateItemFulfillment($id, '_shipped')) {
                                }
                                foreach($despatchId as $despatchkey => $despatchLine) {
                                    $this->msg('succeed ' . $despatchLine);
                                    $this->_minder->updatePickItemDespatched($despatchLine);
                                }
                            } else {
                                $this->msg('Have some troubles with ' . serialize($obj));
                            }
                        } else {
                            $msg = $this->lastError . PHP_EOL . $line['PICK_ORDER'] . PHP_EOL . $order . PHP_EOL ;
                            $this->msg('An error #- ' . $this->lastError . PHP_EOL . $line['PICK_ORDER'] . PHP_EOL);
                            $this->mailToCustomer('FAILED ' . $line['PICK_ORDER'] . ' - failed itemFulfillment' , $msg);
                            $this->mailToSoap('FAILED ' . $line['PICK_ORDER'] . ' - failed itemFulfillment' , $msg);
                            foreach($despatchId as $despatchkey => $despatchLine) {
                                $this->msg('fail despatch ' . $depatchLine);
                                $this->_minder->updatePickItemDespatchExit($despatchLine);
                            }
                        }
                    }
                } else {
                    $this->msg('An error - ' . $this->lastError . PHP_EOL . $this->techResponse);
                    return false;
                }
            }
        } else {
            // count is zero
            $this->msg('Nothing to update' . PHP_EOL);
        }
        return $result;
    }

    public function updatePurchaseOrder($data)
    {
        $xml = $this->_parser->translatePurchaseOrder($data);
        if (false !== $xml) {
            $this->__update($xml, 'update');
        }
        return false;
    }


    public function updateItemReceipt($internalId, $orderLine, $qty)
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:purchases_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>
                        <ns2:update>
                            <ns2:record xsi:type="ns3:ItemReceipt" internalId="' . $internalId . '">
                                <ns3:landedCostMethod>_quantity</ns3:landedCostMethod>
                                <ns3:tranDate>' . date(DATE_W3C, time()) . '</ns3:tranDate>
                                <ns3:itemList xsi:type="ns3:ItemReceiptItemList">
                                    <ns3:item xsi:type="ns3:ItemReceiptItem">
                                        <ns3:quantity>' . $qty . '</ns3:quantity>
                                        <ns3:orderLine>' . $orderLine . '</ns3:orderLine>';
/*
        if ('' != $serialNumber) {
            $xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
        }
*/
        $xml .='                    </ns3:item>
                                </ns3:itemList>
                            </ns2:record>
                        </ns2:update>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        $this->msg('--- xml update itemReceipt ---');
        $this->msg($xml);
        $this->msg('--- end xml ---');
        $response   = $this->doRequest($xml, 'update');
        $result     = $this->_parser->isSuccess($response);
        if ($result == false) {
            $msg = $this->lastError = $this->_parser->lastError;
            $this->lastErrorCode = 0;
            $this->msg($msg);
            //$this->mailToCustomer('FAILED ' . $order['PURCHASE_ORDER'] . ' - ' . $this->lastError, $msg);
        }
        return $result;
    }

//=======Start Item Receipt===================================================================
    /**
     * Update NetSuite with PurchaseOrder Receipt
     *
     * @param PurchaseOrder $order
     * @param PurchaseOrderLine $orderLine
     * @param array $serialNumbers
     * @return boolean
     */
    public function addItemReceipt(PurchaseOrder $order, PurchaseOrderLine $orderLine, array $serialNumbers)
    {
        $serialNumber = '';

        foreach ($serialNumbers as $number) {
            if (count($number) != 2) {
                throw new Exception('Not enought params.');
            }
            $serial = $number[0];
            $qty    = $number[1];
            $serialNumber .= $serial . '(' . $qty . '),';
        }
        $serialNumber = substr($serialNumber, 0, -1);

        $response = $this->searchItemByName($orderLine->items['PROD_ID']);
        $product = $this->_parser->parseSearch($response);
        $product = current($product);
        if (!isset($product->internalId)) {
            $this->lastError = $product->internalId . ' Product ' . $orderLine->items['PROD_ID'] . ' not found in NetSuite.';
            $this->lastErrorCode = 0;
            $msg = $this->lastError . PHP_EOL;
            //$this->mailToCustomer('FAILED ' . $order['PURCHASE_ORDER'] . ' - ' . $this->lastError, $msg);
            return false;
        }
        //$log = Zend_Registry::get('logger');
        //$log->info(print_r($orderLine, true));
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:purchases_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>
                        <ns2:add>
                            <ns2:record xsi:type="ns3:ItemReceipt">
                                <ns3:createdFrom internalId="' . $order->items['PO_LEGACY_INTERNAL_ID'] . '"/>
                                <ns3:landedCostMethod>_quantity</ns3:landedCostMethod>
                                <ns3:tranDate>' . date(DATE_W3C, time()) . '</ns3:tranDate>
                                <ns3:itemList xsi:type="ns3:ItemReceiptItemList">
                                    <ns3:item xsi:type="ns3:ItemReceiptItem">
                                        <item internalId="' . ($product->internalId) . '"/>
                                        <ns3:quantity>' . $qty . '</ns3:quantity>
                                        <ns3:orderLine>' . $orderLine->items['PO_LEGACY_LINE'] . '</ns3:orderLine>';
        if ('' != $serialNumber) {
            $xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
        }
        $xml .='                    </ns3:item>
                                </ns3:itemList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        $this->msg('--- xml add itemReceipt ---');
        $this->msg($xml);
        $this->msg('--- end xml ---');
        $response   = $this->doRequest($xml, 'add');
        $this->msg("response:" . print_r($response, true));

        $result     = $this->_parser->isSuccess($response);
        if ($result == false) {
            $msg = $this->lastError = $this->_parser->lastError;
            $this->lastErrorCode = 0;
            $this->msg($msg);
            //$this->mailToCustomer('FAILED ' . $order['PURCHASE_ORDER'] . ' - ' . $this->lastError, $msg);
        } else {
            $obj    = $this->_parser->parseResponse($response);
            $result = $obj->addResponse->writeResponse->baseRef->internalId;
        }
        return $result;
    }


    /**
     * Update NetSuite with PurchaseOrder Receipt
     *
     * @return boolean
     */
    public function sendItemReceiptsByIssn($issnId)
    {
        $log = Zend_Registry::get('logger');

        $SOAPorderNo       = $this->session->params[$this->_controller]['purchase_order'];
        $orderLineNo       = $this->session->params[$this->_controller]['po_line'];

        /*
        $log->info('start SOAP using ' . PHP_EOL
                   . $SOAPorderNo . PHP_EOL
                   . $orderLineNo . PHP_EOL
                   . $issnId . PHP_EOL);
        */

        $purchaseOrder     = $this->minder->getPurchaseOrderById($SOAPorderNo);
        //$log->info('with order : ' . serialize($purchaseOrder) . PHP_EOL);
        $line              = $this->minder->getPurchaseOrderLinesByPurchaseOrder($purchaseOrder->id, $orderLineNo, true);
        //$log->info('with lines : ' . serialize($line) . PHP_EOL);
        $purchaseOrderLine = new PurchaseOrderLine($line[0]);

        //if ($purchaseOrderLine['PO_LINE_STATUS'] == 'CL') {
            $ssnOriginal = $this->minder->getOriginalSsn($issnId);
            //$log->info('with issn and ssn : ' . $issnId . ',' . $ssnOriginal . PHP_EOL);

            //-- form data for SOAP
            $serialNumbers = array(
                array($ssnOriginal,
                      $purchaseOrderLine['RECVD']
                )
            );

            $SoapPassport = Zend_Registry::get('SoapPassport');

            $soap = new NetSuite_SoapWrapper();
            $soap->Passport = $SoapPassport;
            $soap->login();
            if ('' == $purchaseOrderLine['PO_LEGACY_RECV_ID']) {
                $response = $soap->addItemReceipt($purchaseOrder, $purchaseOrderLine, $serialNumbers);
                $log->info('---xml request---');
                $log->info($soap->lastRequest);
                $log->info('---xml request---');
                $log->info('---xml response---');
                $log->info($soap->lastResponse);
                $log->info('---xml tech response---');
                $log->info($soap->techResponse);
                $log->info('---xml response---');
                if ($response) {
                    $clause = array('PURCHASE_ORDER = ? AND ' => $purchaseOrderLine['PURCHASE_ORDER'],
                                    'PO_LINE = ? AND ' => $purchaseOrderLine['PO_LINE']);
                    $this->addMessage('Receipt added successfully by SOAP. New internalId = ' . $response);
                    $log->info('Receipt added successfully by SOAP. New internalId = ' . $response);
                    if (!$this->minder->updatePurchaseOrderLine($clause, 'PO_LEGACY_RECV_ID', $response)) {
                        $this->addError($this->minder->lastError);
                    }
                } else {
                    $this->addError($soap->lastError);
                    $log->info('failed - ' . $soap->lastError);
                    $log->info('Mail send with error - ' . $purchaseOrderLine['PURCHASE_ORDER'] . ' ' . $soap->lastError);
                    $soap->mailToSoap('Error - ', 'failed - ' . $purchaseOrderLine['PURCHASE_ORDER'] . ' ' . $soap->lastError);

                }
            } else {
                $response = $soap->updateItemReceipt($purchaseOrderLine['PO_LEGACY_RECV_ID'],
                                                     $purchaseOrderLine['PO_LEGACY_LINE'],
                                                     $purchaseOrderLine['RECVD']);
                $log->info('---xml request---');
                $log->info($soap->lastRequest);
                $log->info('---xml request---');
                $log->info('---xml response---');
                $log->info($soap->lastResponse);
                $log->info('---xml tech response---');
                $log->info($soap->techResponse);
                $log->info('---xml response---');
                if ($response) {
                    $this->addMessage('Receipt updated successfully by SOAP. InternalId =' . $purchaseOrderLine['PO_LEGACY_RECV_ID']);
                    $log->info('Receipt updated successfully by SOAP. InternalId =' . $purchaseOrderLine['PO_LEGACY_RECV_ID']);
                } else {
                    $this->addError('failed - ' . $soap->lastError);
                    $log->info('failed - ' . $soap->lastError);
                    $log->info('Mail send with error - ' . $purchaseOrderLine['PURCHASE_ORDER'] . ' ' . $soap->lastError);
                    $soap->mailToSoap('Error - ', 'failed - ' . $purchaseOrderLine['PURCHASE_ORDER'] . ' ' . $soap->lastError);
                }
            }

        //}
    }
    /**
     * Update NetSuite with PurchaseOrder Receipt
     *
     * @return boolean
     */
    public function sendItemReceiptsByOrder()
    {
        $result = false;

        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $serialNumber = array();
        $qty = array();
        $this->lastError = '';
	$lastOrder = '';
	$lastLine = '';
	$lastRecv = '';
	$lastSsn = '';
        $xml = '';
        $xmls = array();
        $receives = array();
        $receiveId = array();
        $recvId = array();
        $Recvs = array();
        $product = array();

        $details = $this->_minder->getPurchaseLineDetail();
        // have fixed this to be ordered by pick order
        // by purchase line
        // by ssn's ssn_id

        // must put all the lines for an order together in one soap message
        // at a change in order  produce details for order
        // at a change in line no produce details for line
        // at a change in original ssn put in
        //   serial numbers "FInnnnn(qty), FInnnnm(qty)"
        // and update the purchase line details

        $this->msg('Trying to update PO with : ');
        $this->msg(serialize($details));
        $log->info('details :');
        $log->info(print_r($details));
/* need 
	need prods internalid
*/

        if (count($details) > 0) {
            foreach ($details as $purchaseLineDetailKey => $purchaseLineDetail) {
                //$log->info('purchasedetailKey:' . $purchaseLineDetailKey);
                //$log->info(print_r($purchaseLineDetail));
                $this->msg('purchaseDetailLineKey:' . $purchaseLineDetailKey);
                $this->msg(serialize($purchaseLineDetail));
                foreach ($purchaseLineDetail as $key => $line) {
                    //$log->info('key:' . $key);
                    //$log->info(print_r($line));
                    $this->msg('key:' . $key);
                    $this->msg(serialize($line));
                    //$serialNumber = '';
                    //$qty = 0;

        if (($line['PO_LINE'] != $lastLine) or
            ($line['PURCHASE_ORDER'] != $lastOrder)) {
            // line changed - 
            // do item ending
            if ($xml != '') {
                if (count($qty) > 0) {
                    $xml .= PHP_EOL;
                    $xml .= '                                    <ns3:quantity>' . ((int)array_sum($qty)) . '</ns3:quantity>';
                }
                if ($lastRecv == '') {
                    // an add - so add serialnumbers
                    if (count($serialNumber) > 0) {
                        //$xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
                        $xml .= PHP_EOL;
                        $xml .='                                    <ns3:serialNumbers>' ;
                        $serialNumber2 = '';
                        foreach ($serialNumber as $serialKey => $serialId) {
                            $serialNumber2 .= $serialId . ", " ;
                        }
                        $serialNumber2 = substr($serialNumber2, 0, -2);
                        $xml .= $serialNumber2 . '</ns3:serialNumbers>';
                    } else {
                        //$prod
                    }
                }
                // add  line  ending
                $xml .= PHP_EOL;
                $xml .='                                  </ns3:item>';
            }
            // reset serial no and qty vars
            unset($serialNumber);
            unset($qty);
            $serialNumber = array();
            $qty = array();
        }
        if ($line['PURCHASE_ORDER'] != $lastOrder) {
            // order changed
            if ($xml != '') {
                // add  order ending
                $xml .= PHP_EOL;
                if ($lastRecv == '') {
                    // an add
                    $xml .='                                        </ns3:itemList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
                } else {
                    // an update
                    $xml .='                                        </ns3:itemList>
                            </ns2:record>
                        </ns2:update>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
                }
                // save this xml
                $xmls[$lastOrder] = $xml;
                $this->msg('--- created xml itemReceive ---');
                //$this->msg($xml);
                //$this->msg('--- end xml ---');
                $receives[$lastOrder] = $receiveId;
                $Recvs[$lastOrder] = $lastRecv;
                unset($receiveId);
                $receiveId = array();
            }
            $lastOrder = $line['PURCHASE_ORDER'];
            // force a change in line
            $lastLine = '';
            // start new xml
            $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:purchases_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>';
            $lastRecv = $line['PO_LEGACY_RECV_ID'];
            if ($line['PO_LEGACY_RECV_ID'] == '') {
                // no recv id so an add 
                $xml .= PHP_EOL ; 
                //$xml .=    '    <ns2:add xmlns="' . $this->urnMessages . '">';
                $xml .=    '                        <ns2:add>
                            <ns2:record xsi:type="ns3:ItemReceipt">
                                <ns3:createdFrom internalId="' . $line['PO_LEGACY_INTERNAL_ID'] . '"/>';
            } else {
                $xml .= PHP_EOL ; 
                $xml .=    '                        <ns2:update>
                            <ns2:record xsi:type="ns3:ItemReceipt" internalId="' . $line['PO_LEGACY_RECV_ID'] . '">';
            }
            $xml .= PHP_EOL ; 
            $xml .= '                                <ns3:landedCostMethod>_quantity</ns3:landedCostMethod>
                                <ns3:tranDate>' . date(DATE_W3C, time()) . '</ns3:tranDate>
                                <ns3:itemList xsi:type="ns3:ItemReceiptItemList">';
             $recvId[$lastOrder] = $line['PO_LEGACY_RECV_ID'];
        }

        if ($line['PO_LINE'] != $lastLine)  {
            $lastLine = $line['PO_LINE'];
            $line['PO_LEGACY_LINE'] = trim($line['PO_LEGACY_LINE']);
            // start new xml
            $xml .= PHP_EOL ; 
            $xml .= '                                 <ns3:item xsi:type="ns3:ItemReceiptItem">';
            //$line['PO_RECEIVE_WH_ID'] = trim($line['PO_RECEIVE_WH_ID']);
            //if ('' != $line['PO_LEGACY_WH_ID']) {
            //    $xml .= PHP_EOL;
            //    $xml .= '                                    <ns3:location internalId="' . ((int)trim($line['PO_LEGACY_WH_ID'])) . '"/>';
            //} else {
            //        $this->msg('PI_LEGACY_WH_ID is empty. ');
            //    }
            //}
            // need companyId and whId of order
            $wkPurchaseOrder = $this->_minder->getPurchaseOrderById($lastOrder );
            // need wh and company for receive
            // company is in the purchase order
            $wkDefaultWh = current($this->_minder->getListByField('CONTROL.DEFAULT_WH_ID'));
            $wkLegacyLocationId = $this->_minder->getLegacyLocationIdforCompany( $wkDefaultWh, $wkPurchaseOrder->companyId);
            if (is_numeric($wkLegacyLocationId)) {
                $xml .= PHP_EOL;
                $xml .= '                                    <ns3:location internalId="' . ((int)$wkLegacyLocationId) . '"/>';
            } else {
                //$this->msg('Default WH  and Legacy Company Location is empty');
                $this->msg('Default WH  and Legacy Company Location is empty for company ' . $wkPurchaseOrder->companyId);
            }
//=============================================================================================================
// this uses the wh_id from the control table
// it should be using the wh_id from the issns
// but I dont have this only the ssn
//=============================================================================================================
            // get the internal if for the product
            $response = $this->searchItemByName($line['PROD_ID']);
            $product = $this->_parser->parseSearch($response);
            $product = current($product);
            if ($lastRecv == '') {
                // an add
                if (!isset($product->internalId)) {
                    $this->msg('Error  Product ' . $line['PROD_ID'] . ' not found in NetSuite.');
                } else {
                    $xml .= PHP_EOL;
                    $xml .= '                                    <item internalId="' . ($product->internalId) . '"/>';
                }
            }
            if ('' != $line['PO_LEGACY_LINE']) {
                $xml .= PHP_EOL;
                $xml .= '                                    <ns3:orderLine>' . $line['PO_LEGACY_LINE'] . '</ns3:orderLine>';
            }
        }
                    //$qty                     = $line['QTY_PICKED'];
                    //$serialNumber         = $key;

                    //$line['PO_RECEIVE_WH_ID'] = trim($line['PO_RECEIVE_WH_ID']);
                    $line['PO_LEGACY_RECEIVE_WH_ID'] = trim($line['PO_LEGACY_RECEIVE_WH_ID']);
                    $serialNumber[$key] = $key . '(' . $line['ORIGINAL_QTY'] . ')';
                    //$qty[$key] = $line['QTY_RECEIVED'];
                    $qty[$key] = $line['ORIGINAL_QTY'];
                    $receiveId[] = $line['PURCHASE_DETAIL_ID'];

        $this->msg('--- detail add itemReceive ---');

                    $output =
                          'Request to update = ' . print_r($line['PO_LEGACY_INTERNAL_ID'], true) . PHP_EOL .
                          'PURCHASE_ORDER    = ' . print_r($line['PURCHASE_ORDER'], true) . PHP_EOL .
                          'PURCHASE_DETAIL_ID = ' . print_r($line['PURCHASE_DETAIL_ID'], true) . PHP_EOL .
                          'PO_LEGACY_LINE    = ' . print_r($line['PO_LEGACY_LINE'], true) . PHP_EOL .
                          'PROD_ID           = ' . print_r($line['PROD_ID'], true) . PHP_EOL .
                          'SSN.PROD_ID       = ' . print_r($line['SSN.PROD_ID'], true) . PHP_EOL .
                          'SSN.ORIGINAL_QTY  = ' . print_r($line['ORIGINAL_QTY'], true) . PHP_EOL .
                          'QTY_RECEIVED      = ' . print_r($line['QTY_RECEIVED'], true) . PHP_EOL .
                          'PO_LEGACY_RECV_ID = ' . print_r($line['PO_LEGACY_RECV_ID'], true) . PHP_EOL .
                          'serialNumbers     = ' . print_r($serialNumber, true);
                    $this->msg($output);
                } // foreach pick item detail
            } // foreach detail
            if ($xml != '') {
                // do item ending
                if (count($qty) > 0) {
                    $xml .= PHP_EOL;
                    $xml .= '                                    <ns3:quantity>' . ((int)array_sum($qty)) . '</ns3:quantity>';
                }
                if ($lastRecv == '') {
                    // an add - so add serialnumbers
                    if (count($serialNumber) > 0) {
                        //$xml .='                     <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>';
                        $xml .= PHP_EOL;
                        $xml .='                                    <ns3:serialNumbers>' ;
                        $serialNumber2 = '';
                        foreach ($serialNumber as $serialKey => $serialId) {
                            $serialNumber2 .= $serialId . ", " ;
                        }
                        $serialNumber2 = substr($serialNumber2, 0, -2);
                        $xml .= $serialNumber2 . '</ns3:serialNumbers>';
                    } else {
                        //$prod
                    }
                }
                // add  line  ending
                $xml .= PHP_EOL;
                $xml .='                                 </ns3:item>';
            }
            if ($xml != '') {
                // add ending
                $xml .= PHP_EOL;
                if ($lastRecv == '') {
                    // an add
                    $xml .='                                </ns3:itemList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
                } else {
                    // an update
                    $xml .='                                </ns3:itemList>
                            </ns2:record>
                        </ns2:update>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
                }
                // save this xml
                $xmls[$lastOrder] = $xml;
                $this->msg('--- created last xml itemReceive ---');
                //$this->msg($xml);
                //$this->msg('--- end xml ---');
                $receives[$lastOrder] = $receiveId;
                $Recvs[$lastOrder] = $lastRecv;
            }
            // now must process all these xmls

            foreach ($xmls as $key => $order) {
                $line = array();
                $line['PURCHASE_ORDER'] = $key;
                $receiveId = $receives[$key];
                $output = print_r($receiveId, true);
                $lastRecv = $Recvs[$key];
                // process the $line
                $this->msg('Process xml ' . $key);
                //$this->msg('with despatches  ' . $output);
                $this->msg('--- xml add itemReceipt ---');
                $this->msg($order);
                $this->msg('--- end xml ---');

                //$response = false;
                if ($lastRecv == '') {
                    // an add
                    $response = $this->doRequest($order, 'add'); 
                } else {
                     // an update 
                    $response = $this->doRequest($order, 'update'); 
                }
                if (false !== $response) {
                    $result = $this->_parser->isSuccess($response);
                    if ($result == false) {
                        $this->lastError = $this->_parser->lastError;
                        $this->lastErrorCode = 0;
                        $this->msg('Failed in Receive : ' . $this->lastError);
                        $msg = $line['PURCHASE_ORDER']  . PHP_EOL . $order . PHP_EOL;
                        $this->mailToCustomer('FAILED ' . $line['PURCHASE_ORDER'] . ' - ' . $this->lastError, $msg);
                        foreach($receiveId as $receivekey => $receiveLine) {
                            $this->msg('fail receive ' . $receiveLine);
                            $this->_minder->updatePurchaseLineDetailStatus($receiveLine,'Dl');
                        }
                    } else {
                        if ($lastRecv == '') {
                            // an add
                            $obj = $this->_parser->parseResponse($response);
                            if (isset($obj->addResponse->writeResponse->baseRef->internalId)) {
                                $id = $obj->addResponse->writeResponse->baseRef->internalId;
                                if ($id != '') {
                                    $this->msg('Created Receive by Soap with internalId = ' . $id);
                                    $clause = array('PURCHASE_ORDER = ? ' => $line['PURCHASE_ORDER']);
                                    if (!$this->_minder->updatePurchaseOrderField($clause, 'PO_LEGACY_RECV_ID', $id)) {
                                        $this->msg('fail to update po_legacy_recv_id');
                                    }
                                    foreach($receiveId as $receivekey => $receiveLine) {
                                        $this->msg('succeed ' . $receiveLine);
                                        $this->_minder->updatePurchaseLineDetailStatus($receiveLine,'PA');
                                    }
                                } else {
                                    $this->msg('Have some troubles with ' . serialize($obj));
                                }
                            } else {
                                $msg = $this->lastError . PHP_EOL . $line['PURCHASE_ORDER'] . PHP_EOL . $order . PHP_EOL ;
                                $this->msg('An error #- ' . $this->lastError . PHP_EOL . $line['PURCHASE_ORDER'] . PHP_EOL);
                                $this->mailToCustomer('FAILED ' . $line['PURCHASE_ORDER'] . ' - failed itemReceipt' , $msg);
                                $this->mailToSoap('FAILED ' . $line['PURCHASE_ORDER'] . ' - failed itemReceipt' , $msg);
                                foreach($receiveId as $receivekey => $receiveLine) {
                                    $this->msg('fail receive ' . $receiveLine);
                                    $this->_minder->updatePurchaseLineDetailStatus($receiveLine,'Dl');
                                }
                            }
                        } else { 
                            // an update
                            foreach($receiveId as $receivekey => $receiveLine) {
                                $this->msg('succeed ' . $receiveLine);
                                $this->_minder->updatePurchaseLineDetailStatus($receiveLine,'PA');
                            }
                        } // end of if add
                    }
                } else {
                    $this->msg('An error - ' . $this->lastError . PHP_EOL . $this->techResponse);
                    return false;
                }
            }
        } else {
            // count is zero
            $this->msg('Nothing to update' . PHP_EOL);
        }
        return $result;

    }
//=========End of Receipt====================================================================

    public function searchItem()
    {

        /*
         *  @param string $number
         *
            <ns3:serialNumber operator="contains" xsi:type="ns1:SearchStringField">
            <ns1:searchValue>' . $number . '</ns1:searchValue>
            </ns3:serialNumber>
        */

        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:ItemSearchBasic">
                            <ns3:itemId operator="notEmpty" xsi:type="ns4:SearchStringField" xmlns:ns4="' . $this->urnCore . '">
                                <ns4:searchValue xsi:type="ns4:RecordRef"></ns4:searchValue>
                            </ns3:itemId>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';

        return $this->doRequest($xml, 'search');
    }

    public function searchItemByName($name)
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '"
                    xmlns:ns5="urn:accounting_2008_1.lists.webservices.netsuite.com">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:ItemSearchBasic">
                            <ns3:itemId operator="contains" xsi:type="ns4:SearchStringField" xmlns:ns4="' . $this->urnCore . '">
                                <ns4:searchValue xsi:type="ns4:RecordRef">' . $name . '</ns4:searchValue>
                            </ns3:itemId>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'search');
    }

    /**
     * Do search next SOAP request
     *
     * @return string An response XML
     */
    public function searchNext()
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '"
                    xmlns:ns5="urn:accounting_2008_1.lists.webservices.netsuite.com">
                <SOAP-ENV:Body>
                    <searchNext xmlns="' . $this->urnMessages . '"/>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'searchNext');
    }

    public function searchItemById($id)
    {

        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '"
                    xmlns:ns5="urn:accounting_2008_1.lists.webservices.netsuite.com">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:ItemSearchBasic">
                            <ns3:internalId operator="anyOf" xsi:type="ns4:SearchMultiSelectField" xmlns:ns4="' . $this->urnCore . '">
                                <ns4:searchValue internalId="' . $id . '" xsi:type="ns4:RecordRef"></ns4:searchValue>
                            </ns3:internalId>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';

        return $this->doRequest($xml, 'search');
    }

    public function searchCustomRecord($id, $typeId)
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:CustomRecordSearchBasic">
                            <ns3:internalId operator="anyOf" xsi:type="ns1:SearchMultiSelectField">
                                <ns1:searchValue internalId="' . $id . '" xsi:type="ns1:RecordRef"/>
                            </ns3:internalId>
                            <ns3:recType internalId="' . $typeId . '"/>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'search');
    }

    /**
     * seek account by $id at Netsuite
     *
     * @param string $id If null then all account will be returned
     * @return mixed
     */
    public function searchAccount($id = null)
    {
        $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="' . $this->urnCommon . '"
                    xmlns:ns4="' . $this->urnCommunication . '">
                <SOAP-ENV:Body>
                    <search xmlns="' . $this->urnMessages . '">
                        <searchRecord xsi:type="ns3:AccountSearchBasic">
                            <ns3:number operator="anyOf" xsi:type="ns1:SearchMultiSelectField">';
        if ($id !== null) {
            $xml .= '           <ns1:searchValue internalId="' . $id . '"></ns1:searchValue>';
        } else {
            $xml .= '           <ns1:searchValue xsi:type="ns1:RecordRef"/>';
        }
        $xml .= '            </ns3:number>
                        </searchRecord>
                    </search>
                </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';
        return $this->doRequest($xml, 'search');
    }

    public function inventoryAdjustments()
    {
        $result = array();

        $clause = array('LA_STATUS IN (\'PU\', \'PV\') AND ' => '');
        $lines = $this->_minder->getLegacyAdjustments($clause);
        $acc = '70701';
        $accInternalId = '341';
        //-- count net total for adjustment
        foreach ($lines['data'] as $line) {
            $issns                                = $this->_minder->getIssns(array('SSN_ID = ? ' => $line['LA_SSN_ID']));
            $issn                                 = current($issns);

            $serialNumber                         = $issn['ORIGINAL_SSN'];
            $result[$serialNumber]['RECORD_ID'][] = $line['RECORD_ID'];

            if (isset($result[$serialNumber]['QTY'])) {
                $result[$serialNumber]['QTY']  += $line['LA_ADJUST_BY'];
            } else {
                $result[$serialNumber]['QTY']   = $line['LA_ADJUST_BY'];
            }
            $result[$serialNumber]['PROD_ID']   = $issn['PROD_ID'];
            $result[$serialNumber]['LA_STATUS'] = $line['LA_STATUS'];
        }
        $this->msg('Start inventoryAdjustment with ' . serialize($result));
        foreach ($result as $serialNumber => $line) {
            $flag         = true;
            $searchResult = $this->searchItemByName($line['PROD_ID']);
            if (!$searchResult) {
                $this->msg($this->lastError);
                $flag = false;
            } else {
                $listItems = $this->_parser->parseSearch($searchResult);
                if ($listItems != null) {
                    $item = current($listItems);
                    if (isset($item->internalId)) {
                        $itemId = $item->internalId;
                    } else {
                        $this->msg('Item not found for ' . $line['PROD_ID']);
                        $result[$serialNumber]['status'] = false;
                        $flag = false;
                    }
                } else {
                    $flag = false;
                    $this->msg('Item list empty for ' . $line['PROD_ID']);
                    $result[$serialNumber]['status'] = false;
                }
            }
            if ($flag) {
                $xml = '<SOAP-ENV:Envelope ' . $this->xmlnsEnv . '
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:ns1="' . $this->urnCore . '"
                    xmlns:ns2="' . $this->urnMessages . '"
                    xmlns:ns3="urn:inventory_2008_1.transactions.webservices.netsuite.com">
                    <SOAP-ENV:Body>
                        <ns2:add>
                            <ns2:record xsi:type="ns3:InventoryAdjustment">
                                <ns3:account internalId="' . $accInternalId . '">' . $acc . '</ns3:account>
                                <ns3:inventoryList xsi:type="ns3:InventoryAdjustmentInventoryList">
                                    <ns3:inventory xsi:type="ns3:InventoryAdjustmentInventory">
                                        <ns3:location internalId="1"/>
                                        <ns3:item internalId="' . $itemId . '"/>
                                        <ns3:serialNumbers>' . $serialNumber . '</ns3:serialNumbers>
                                        <ns3:adjustQtyBy>' . $line['QTY'] . '</ns3:adjustQtyBy>
                                    </ns3:inventory>
                                </ns3:inventoryList>
                            </ns2:record>
                        </ns2:add>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';

                $out  = 'ADJUST' . PHP_EOL;
                $out .= 'account id = ' . $accInternalId . ' value = ' . $acc . PHP_EOL;
                $out .= 'item    id = ' . $itemId . PHP_EOL;
                $out .= 'serial     = ' . $serialNumber . PHP_EOL;
                $out .= 'qty        = ' . $line['QTY'] . PHP_EOL;
                $this->msg($out);

                $response = $this->doRequest($xml, 'add');
                if (false !== $response) {
                    $obj = $this->_parser->parseResponse($response);
                    if (isset($obj->addResponse->writeResponse->status)) {
                        if ('true' == $obj->addResponse->writeResponse->status->isSuccess) {
                            $result[$serialNumber]['status']     = true;
                            $result[$serialNumber]['internalId'] = $obj->addResponse->writeResponse->baseRef->internalId;
                        } else {
                            $result[$serialNumber]['status']     = false;
                            $result[$serialNumber]['internalId'] = false;
                            $this->msg('SOAP error ' . $obj->addResponse->writeResponse->status->statusDetail->message);
                        }
                    } else {
                        $this->msg('Error in response. ' . serialize($obj));
                    }
                } else {
                    $this->msg('Failed Request.');
                    $result[$serialNumber]['status'] = false;
                }
            } else {
                $result[$serialNumber]['status'] = false;
            }
            foreach ($result[$serialNumber]['RECORD_ID'] as $record) {
                $this->msg('Set status for rec# ' . $record .
                           ' to ' . print_r($result[$serialNumber]['status'], true) .
                           'as internalId = ' . $result[$serialNumber]['internalId']);
                $this->_minder->updateAdjustmentStatus($record, $result[$serialNumber]['status']);
                /*
                if ($result[$serialNumber]['internalId']) {
                    $updateResult = $this->_minder->updateAdjustmentInternalId($record,  $result[$serialNumber]['internalId']);
                }
                */
            }
        }
        return $result;
    }

    public function mailToCustomer($subj, $body)
    {
        $mailList = $this->_minder->getCustomerMailList();
        if (false != $mailList) {
            $mail = new Zend_Mail();
            foreach ($mailList as $userId => $email) {
                $mail->addTo($email, $userId);
            }
            $mail->setFrom('soap@minder.barcoding.com.au');
            $mail->setSubject($subj);
            $mail->setBodyText($body);

            try {
                $mail->send();
            } catch (Zend_Exception $e) {
                $this->msg($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        } else {
            //$msg = 'Customer mail list is empty ';
            //$this->msg($msg);
        }
    }

    public function mailToSoap($subj, $body)
    {
        $mailList = $this->_minder->getCustomerMailList('EMAIL_SOAP');

        if (false != $mailList) {
            $mail = new Zend_Mail();
            foreach ($mailList as $userId => $email) {
                $mail->addTo($email, $userId);
            }
            $mail->setFrom('netsuite@freshproduce.net.au');
            $mail->setSubject($subj);
            $mail->setBodyText($body);

            try {
                $mail->send();
            } catch (Zend_Exception $e) {
                $this->msg($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        } else {
            //$msg = 'Customer mail list is empty ';
            //$this->msg($msg);
        }
    }

    /**
     * Send technical e-mail
     *
     * @param string $subj
     * @param string $body
     */
    public function mailToTechnical($subj = null, $body = null)
    {
        $timestamp = date('Ymd-His', time());
        if ($subj === null) {
            $subj = 'Netsuite SOAP log ' . $timestamp;
        } else {
            $subj .= ' ' . $timestamp;
        }
        if ($body === null) {
            $body = '';
        } else {
            $body .= PHP_EOL;
        }

        $attachment = array('request' . $timestamp . '.xml' => $this->lastRequest,
                            'response' . $timestamp . '.xml' => $this->techResponse);

        $mailList = $this->_minder->getTechnicalMailList();
        if (false != $mailList) {
            $mail = new Zend_Mail();

            foreach ($attachment as $filename => $content) {
                $body .= PHP_EOL . PHP_EOL
                      . $filename . PHP_EOL
                      . PHP_EOL . PHP_EOL
                      . $content
                      . PHP_EOL . PHP_EOL;
                /*
                $at           = new Zend_Mime_Part($content);
                $at->type     = 'text/plain';
                $at->filename = $filename;
                $mail->addAttachment($at);
                */
            }

            foreach ($mailList as $userId => $email) {
                $mail->addTo($email, $userId);
            }

            $mail->setFrom('soap@minder.barcoding.com.au');
            $mail->setSubject($subj);
            $mail->setBodyText($body);

            try {
                $mail->send();
            } catch (Zend_Exception $e) {
                $this->msg($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        } else {
            //$msg = 'Technical mail list is empty ';
            //$this->msg($msg);
        }
    }

    /**
     * lock a soap transaction if it was not blocked
     * if transaction locked longer then MAX_LOCK_TIME - treat it as unlocked
     *
     * @param void
     * @return boolean
     */
    public function lockSoapTransaction()
    {
        $result = $this->_minder->getSoapCache('SoapTransaction_Lock');
        if($result === 'lock') {
            $date = $this->_minder->getSoapCacheDate('SoapTransaction_Lock');
            if ($date) {
                $config = Zend_Registry::get('config');
                if ($config->soap->max_lock_time > 0) {
                    if (time() - (strtotime($date) + ($config->soap->max_lock_time * 60)) > 0) {
                        $this->msg('Autounlock transaction after ' . (time() - strtotime($date)) . ' minutes');
                        return true;
                    }
                }
            }
            return false;
        }
        if(empty($result)) {
            $this->_minder->updateSoapCache('SoapTransaction_Lock', 'lock');
            return true;
        }
        if($result === 'unlock') {
            $this->_minder->updateSoapCache('SoapTransaction_Lock', 'lock');
            return true;
        }
        return false;
    }

    /**
     * @desc to unlock a soap transaction if it was locked
     *
     * @return boolean
     */
    public function unlockSoapTransaction()
    {

       $result = $this->getLockSoapTransactionStatus();

       if($result === 'lock') {
            $this->_minder->updateSoapCache('SoapTransaction_Lock', 'unlock');
            return true;
       }

       return false;
    }

    /**
     * get Status of lock SOAP Transactions
     *
     * @return mixed
     */
    public function getLockSoapTransactionStatus()
    {
        return $this->_minder->getSoapCache('SoapTransaction_Lock');
    }

    /**
     * get date of lock SOAP Transaction
     *
     * @return string
     */
    public function getLockSoapTransactionDate()
    {
        return $this->_minder->getSoapCacheDate('SoapTransaction_Lock');
    }
    /**
     * Get current status of silence
     *
     * @return boolean
     */
    public function getSilentMode()
    {
        return $this->_mode;
    }

    /**
     * set status of silence
     *
     * @param boolean $mode
     */
    public function setSilentMode($mode)
    {
        $this->_minder->silent = (bool)$mode;
        $this->_mode = (bool)$mode;
    }

    /**
     * add data to cache
     *
     * @param string $id unique ID
     * @param mixed $obj data
     *
     * @return void
     */
    public function addToCache($id, $obj)
    {
        if ($this->_minder->getSoapCache($id)) {
            $this->msg('Cache hit ' . $id);
        }
        $this->_minder->updateSoapCache($id, $obj);
        return;
    }

    /**
     * get data from cache
     *
     * @param string $id
     * @return mixed
     */
    public function getFromCache($id)
    {
        return $this->_minder->getSoapCache($id);
    }

    /**
     * removes data from cache
     *
     * @param array $idList List of unique ID's to be removed
     * @return void
     */
    public function deleteFromCache(array $idList)
    {
        return $this->_minder->deleteSoapCache($idList);
    }

    public function clearCache()
    {
        $this->_minder->updateSoapCache(array());
    }

    private function _countryCapitalize($string)
    {
        if (substr($string, 0, 1) == '_') {
            return ucfirst(substr($string, 1));
        } else {
            return $string;
        }
    }

}
