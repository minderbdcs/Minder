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
 * Parser
 *
 * Provide parsing and mapping between data formats NetSuite <-> Minder
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class NetSuite_Parser
{
    private $_xpath;
    private $_dom;
    public $lastError;

    /**
     * Parse result of search action to array of objects
     *
     * @param string $xml
     * @return array
     */
    public function parseSearch($xml)
    {
        $data = array();

        $this->__initXPath($xml);
        $entries = $this->_xpath->query('//soapenv:Body/m:searchResponse/c:searchResult/c:recordList/*');
        if ($entries->length == 0) {
            $entries  = $this->_xpath->query('//soapenv:Body/m:searchResponse/c:searchResult/*');
            if ($entries->length == 0) {
                return false;
            }
        }

        $data = $this->__xml2Obj($entries);
        return $data;
    }

    /**
     * Parse result of searchNext action to array of objects
     *
     * @param string $xml
     * @return array
     */
    public function parseNextSearch($xml)
    {
        $data = array();

        $this->__initXPath($xml);
        $entries = $this->_xpath->query('//soapenv:Body/m:searchNextResponse/c:searchResult/c:recordList/*');
        if ($entries->length == 0) {
            $entries  = $this->_xpath->query('//soapenv:Body/m:searchNextResponse/c:searchResult/*');
            if ($entries->length == 0) {
                return false;
            }
        }
        $data = $this->__xml2Obj($entries);
        return $data;

    }

    /**
     * init namespaces for XPath
     * create new DOMDocument and load xml
     *
     * @param string $xml
     * @return void
     */
    private function __initXPath($xml)
    {
        $this->_dom = new DOMDocument();
        $this->_dom->loadXML($xml);
        $this->_xpath = new DOMXPath($this->_dom);
        $this->_xpath->registerNamespace('m', 'urn:messages_2008_1.platform.webservices.netsuite.com');
        $this->_xpath->registerNamespace('c', 'urn:core_2008_1.platform.webservices.netsuite.com');
        $this->_xpath->registerNamespace('p', 'urn:purchases_2008_1.transactions.webservices.netsuite.com');
        $this->_xpath->registerNamespace('s', 'urn:sales_2008_1.transactions.webservices.netsuite.com');
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $entries
     * @return unknown
     */
    private function __xml2Obj($entries) {
        $data = array();
        foreach ($entries as $node) {
            if (($att = $node->getAttribute('xsi:type'))) {
                @list(,$type) = explode(':', $att);
            }
            if (!isset($type)) {
                $type = false;
            }
            switch ($type) {
                case 'SalesOrder':
                case 'PurchaseOrder':
                case 'Vendor':
                case 'LotNumberedInventoryItem':
                case 'ServiceResaleItem':
                case 'ItemFulfillment':
                case 'ItemReceipt':
                    $rec = $this->__buildObjectTree($node);
                    $data[$rec->internalId] = $rec;
                break;
                default:
                    // $att . PHP_EOL;
                break;
            }
        }
        return $data;


    }

    private function __buildObjectTree(DOMNode $node) {
        $obj = new stdClass();
        if ($node->hasAttributes()) {
            foreach($node->attributes as $attr) {
                $propertyName = explode(':', $attr->name);
                $propertyName = (count($propertyName) > 1 ? $propertyName[1]:$propertyName[0]);
                $value = explode(':', $attr->value);
                $value = count($value) > 1 ? $value[1]:$value[0];
                //-- check if property with same name already exist - create array
                if (isset($obj->$propertyName)) {
                    if (is_array($obj->$propertyName)) {
                        $list = $obj->$propertyName;
                    } else {
                        $list = array($obj->$propertyName);
                    }
                    $list[] = $value;
                    $obj->$propertyName = $list;
                } else {
                    $obj->$propertyName = $value;
                }
            }
        }
        if ($node->hasChildNodes()) {
            $list = array();
            foreach ($node->childNodes as $child) {
                if ($child instanceof DOMText) {
                    $list['text'] = $child->wholeText;
                } else {
                    $list[$child->tagName][] =$this->__buildObjectTree($child);
                }
            }
            foreach ($list as $key => $item) {
                $propertyName = explode(':', $key);
                $propertyName = (count($propertyName) > 1 ? $propertyName[1]:$propertyName[0]);
                if (is_array($item)) {
                    if (count($item) == 1) {
                        $value = $item[0];
                    } else {
                        $value = $item;
                    }
                    //-- check if property with same name already exist - create array
                    if (isset($obj->$propertyName)) {
                        if (is_array($obj->$propertyName)) {
                            $list = $obj->$propertyName;
                        } else {
                            $list = array($obj->$propertyName);
                        }
                        $list[] = $value;
                        $obj->$propertyName = $list;
                    } else {
                        $obj->$propertyName = $value;
                    }
                } else {
                    $obj = $item;
                }
            }
        }

        return $obj;
    }


    public function parseSalesOrder($xml) {
        $data = array();

        $p = new DOMDocument();
        $p->loadXML($xml);
        $this->_xpath = new DOMXPath($p);
        $this->_xpath->registerNamespace('m', 'urn:messages_2008_1.platform.webservices.netsuite.com');
        $this->_xpath->registerNamespace('c', 'urn:core_2008_1.platform.webservices.netsuite.com');
        $this->_xpath->registerNamespace('s', 'urn:sales_2008_1.transactions.webservices.netsuite.com');

        $entries  = $this->_xpath->query('//soapenv:Body/m:getListResponse/m:readResponseList/m:readResponse');
        if ($entries->length == 0) {
            $entries  = $this->_xpath->query('//soapenv:Body/m:getResponse/m:readResponse');
            if ($entries->length == 0) {
                return false;
            }
        }
        $entries  = $this->__NodeListToArrayOfNodes($entries);

        foreach ($entries as $node) {
            $status = $this->_xpath->query('c:status', $node);
            $record = $this->_xpath->query('m:record', $node);
            $status = $this->__parseStatus($status->item(0));

            if ($status->isSuccess) {
                $rec = $this->__buildObjectTree($record->item(0));
                $data[$rec->internalId] = $this->__mapSalesOrder($rec);
            }
        }
        return $data;
    }

    /**
     * Map response to PickOrder Model
     *
     * @param object $obj
     * @return PickOrder
     */
    private function __mapSalesOrder($obj)
    {
        $pickOrder = new PickOrder();

        $mapped = array();

        $mapped['PERSON_ID']                = $this->__map(array($obj, 'entity'), 'internalId');
        $mapped['SO_LEGACY_PROJECT_NAME']   = $this->__map(array($obj, 'entity'), 'name');

        /*
        $mapped['COMPANY_ID']               = $this->__map(array($obj, 'entity'), 'internalId');
        if (false == $mapped['COMPANY_ID']) {
            $mapped['COMPANY_ID']   = '';
        }
        */
        // delivery date
        $mapped['PICK_DUE_DATE']            = $this->__dateConvert($this->__map($obj, 'shipDate'));

        $mapped['SO_LEGACY_INTERNAL_ID']    = $this->__map($obj, 'internalId');
        $mapped['SO_LEGACY_LAST_MODIFIED']  = $this->__dateConvert($this->__map($obj, 'lastModifiedDate'));
        $mapped['SO_LEGACY_PICK_WH_ID']     = $this->__map(array($obj, 'location'), 'internalId');
        $mapped['SO_LEGACY_PICK_WH_NAME']   = $this->__map(array($obj, 'location'), 'name');

        if (!$mapped['SO_LEGACY_PICK_WH_ID']) {
            $mapped['SO_LEGACY_PICK_WH_ID']     = $this->__map(array(array(array($obj, 'itemList'), 'item'), 'location'), 'internalId');
            $mapped['SO_LEGACY_PICK_WH_NAME']   = $this->__map(array(array(array($obj, 'itemList'), 'item'), 'location'), 'name');
        }

        $mapped['SO_LEGACY_MEMO']           = $this->__map($obj, 'memo');
        $mapped['SO_LEGACY_STATUS']         = $this->__map($obj, 'status');

        $mapped['D_PHONE']                  = false;
        $mapped['D_ADDRESS_LINE1']          = false;
        $mapped['D_ADDRESS_LINE2']          = false;
        $mapped['D_CITY']                   = false;
        $mapped['D_STATE']                  = false;
        $mapped['D_POST_CODE']              = false;
        $mapped['D_COUNTRY']                = false;
        $mapped['D_FIRST_NAME']             = false;
        $mapped['CONTACT_NAME']             = false;

        //-- deliver to addresses (netsuite transactionShipAddress)
        $mapped['D_FIRST_NAME']     = $this->__map(array($obj, 'transactionShipAddress'), 'shipAttention');
        $mapped['D_ADDRESS_LINE1']  = $this->__map(array($obj, 'transactionShipAddress'), 'shipAddr1');
        $mapped['D_ADDRESS_LINE2']  = $this->__map(array($obj, 'transactionShipAddress'), 'shipAddr2');
        $mapped['D_CITY']           = $this->__map(array($obj, 'transactionShipAddress'), 'shipCity');
        $mapped['D_COUNTRY']        = $this->__map(array($obj, 'transactionShipAddress'), 'shipState');
        $mapped['D_POST_CODE']      = $this->__map(array($obj, 'transactionShipAddress'), 'shipZip');
        $wk_underscores = array("_", "-");
        //$mapped['D_COUNTRY']        = $this->__map(array($obj, 'transactionShipAddress'), 'shipCountry');
        $mapped['D_COUNTRY']        = ucwords( str_replace($wk_underscores, "", $this->__map(array($obj, 'transactionShipAddress'), 'shipCountry')));

        /*
        $t = $this->__map($obj, 'shipAddress');

        if ($t) {
            $map = explode('<br>', $t);
            switch (count($map)) {
                case 5:
                    $mapped['D_COUNTRY'] = $map[4];
                case 4:
                    $add  = trim($map[3]);
                    $map2 = explode(' ', $add);
                    switch (count($map2)) {
                        case 3:
                            $mapped['D_POST_CODE']  = $map2[2];
                        case 2:
                            $mapped['D_STATE']      = $map2[1];
                        case 1:
                            $mapped['D_CITY']       = $map2[0];
                        break;
                    }
                case 3:
                    //$mapped['D_ADDRESS_LINE1']   = $map[2];
                    $mapped['D_COUNTRY']    = $map[2];
                case 2:
                    //$mapped['CONTACT_NAME'] = $map[1];
                    $mapped['D_ADDRESS_LINE1']   = $map[1];
                case 1:
                    $mapped['D_FIRST_NAME'] = $map[0];
                break;
                case 0:
                    $mapped['D_FIRST_NAME'] = $t;
                break;
            }
        }
        */
        $mapped['PARTIAL_PICK_ALLOWED']     = $this->__map($obj, 'shipComplete');
        $mapped['SHIP_VIA']                 = $this->__map(array($obj, 'shipMethod'), 'internalId');
        $mapped['SHIP_VIA_NAME']            = $this->__map(array($obj, 'shipMethod'), 'name');

        $mapped['SO_LEGACY_CREATE_DATE']    = $this->__dateConvert($this->__map($obj, 'tranDate'));
        $mapped['PICK_ORDER']               = $this->__map($obj, 'tranId');
        $mapped['SO_LEGACY_CONSIGNMENT']    = false;
        $mapped['S_PERSON_ID']              = false;

        $customFields = $this->__map(array($obj, 'customFieldList'), 'customField');
        if (is_array($customFields)) {
            foreach ($customFields as $field) {
                if (isset($field->internalId)) {
                    switch ($field->internalId) {
                        case 'custbody2':
                            $mapped['SO_LEGACY_CONSIGNMENT'] = $field->value;
                            break;
                        case 'custbody7':
                            //$result['COMPANY_ID'] = $field->value;
                            break;
                        case 'custbody8':
                            $mapped['S_PERSON_ID'] = $field->value->internalId;
                        default:
                        break;
                    }
                }
            }
        }

        if (false == $mapped['SO_LEGACY_CONSIGNMENT']) {
            $mapped['SO_LEGACY_CONSIGNMENT'] = 'OUT' . substr($mapped['PICK_ORDER'], 2);
        } else {
            $mapped['SO_LEGACY_CONSIGNMENT']->localValue = 'OUT' . substr($mapped['PICK_ORDER'], 2);
        }


        $items = array();

        if (!is_array($obj->itemList->item)) {
            $item = $obj->itemList->item;
            $obj->itemList->item = array($item);
        }
        foreach($obj->itemList->item as $item) {
            //$item       = $this->__mapSalesOrderItem($item, $mapped['PICK_ORDER']);
            //$items[]    = new PickItem($item);
            $itemList       = $this->__mapSalesOrderItem($item, $mapped['PICK_ORDER']);
            foreach($itemList as $item) {
                $items[]    = new PickItem($item);
            }
        }
        if (count($items) == 0) {
            return false;
        }
        $pickOrder->items = $items;
        if (false !== $mapped) {
            $mapped = array_change_key_case($mapped, CASE_LOWER);
            $pickOrder->save ( $mapped );
            $pickOrder->pickOrderType = 'SO';
            $pickOrder->whId          = 'FX';

            $pickOrder->imported = 'N';
            $pickOrder->importErrors = 0;


            $pickOrder->sSameAsSoldFrom = ! empty ( $mapped ['s_same_as_sold_from'] ) ? 'T' : 'F';
            $pickOrder->pSameAsInvoiceTo = ! empty ( $mapped ['p_same_as_invoice_to'] ) ? 'T' : 'F';
            $pickOrder->supplierList = ! empty ( $mapped ['supplier_list'] ) ? 'T' : 'F';
            $pickOrder->invWithGoods = ! empty ( $mapped ['inv_with_goods'] ) ? 'T' : 'F';
            $pickOrder->partialPickAllowed = ! empty ( $mapped ['partial_pick_allowed'] ) ? 'T' : 'F';
            $pickOrder->overSized = ! empty ( $mapped ['over_sized'] ) ? 'T' : 'F';
        }
        return $pickOrder;
    }

    /**
     * Map parsed Object to array
     *
     * @param stdClass $obj
     * @param string $orderId
     * @return array
     */
    private function __mapSalesOrderItem($obj, $orderId) {
        $mapped = array();
        $mappedList = array();

        $mapped['PICK_ORDER']               = $orderId;
        $mapped['PI_LEGACY_ITEM_DESCR']     = $this->__map($obj, 'description');
        $mapped['PI_LEGACY_CLOSED']         = $this->__map($obj, 'isClosed');
        $mapped['PROD_ID']                  = $this->__map(array($obj, 'item'), 'internalId');
        $mapped['PICK_ORDER_LINE_NO']       = $this->__map($obj, 'line');
        $mapped['PI_LEGACY_LINENO']         = $this->__map($obj, 'line');
        $mapped['WH_ID']                    = $this->__map(array($obj, 'location'), 'internalId');
        $mapped['PI_LEGACY_WH_ID']          = $this->__map(array($obj, 'location'), 'internalId');
        $mapped['PI_LEGACY_WH_NAME']        = $this->__map(array($obj, 'location'), 'name');
        $mapped['OTHER1']                   = $this->__map(array($obj, 'options'), 'internalId');
        $mapped['OTHER2']                   = $this->__map(array($obj, 'options'), 'name');
        $mapped['OTHER3']                   = '';
        $mapped['PICK_ORDER_QTY']           = $this->__map($obj, 'quantity');
        $mapped['PICK_ALLOCATED_QTY']       = $this->__map($obj, 'quantityCommited');
        $mapped['PI_LEGACY_FULFILLED_QTY']  = $this->__map($obj, 'qunatityFulfilled');
        $mapped['PI_LEGACY_PACKED_QTY']     = $this->__map($obj, 'quantityPacked');
        $mapped['PI_LEGACY_PICKED_QTY']     = $this->__map($obj, 'quantityPicked');
        $mapped['PI_LEGACY_RATE']           = $this->__map($obj, 'rate');
        $mapped['PI_LEGACY_PICK_UOM']       = $this->__map(array($obj, 'units'), 'internalId');
        $mapped['SSN_ID']                   = $this->__map($obj, 'serialNumbers');
        $mapped['ORIG_SSN_ID']              = $this->__map($obj, 'serialNumbers');
        $mapped['PICK_LINE_STATUS']         = 'OP';

        if ($this->__map($obj, 'serialNumbers') != '') {
            $serialList                         = explode(' ',$this->__map($obj, 'serialNumbers'));
            foreach ($serialList as $serialItem) {
                list($serialNumber, $serialQtyPart) = explode('(',$serialItem . "(");
                list($serialQty, $serialQtyRest) = explode(')',$serialQtyPart . ")");
                $mapped['SSN_ID']                = $serialNumber;
                $mapped['PICK_ORDER_QTY']        = ($serialQty != '') ? $serialQty : $this->__map($obj, 'quantity');
                $mapped['PICK_LINE_STATUS']         = 'UC';
                $mappedList[] = $mapped;
            }
        } else {
            $mappedList[] = $mapped;
        }
        /*
        $mapped['PICK_LABEL_NO']            = '';
        $mapped['PICK_ORDER']               = '';
        $mapped['CONTACT_NAME']             = '';
        $mapped['WARRANTY_TERM']            = '';
        $mapped['PICK_LABEL_DATE']          = '';
        $mapped['SPECIAL_INSTRUCTIONS1']    = '';
        $mapped['SPECIAL_INSTRUCTIONS2']    = '';
        $mapped['SHIP_VIA']                 = '';
        $mapped['PICKED_QTY']               = '';
        $mapped['PICK_LINE_DUE_DATE']       = '';
        $mapped['PICK_STARTED']             = '';
        $mapped['DESPATCH_TS']              = '';
        $mapped['DESPATCH_LOCATION']        = '';
        $mapped['CREATE_DATE']              = '';
        $mapped['USER_ID']                  = '';
        $mapped['DEVICE_ID']                = '';
        $mapped['CHECKIN_START']            = '';
        $mapped['CHECKIN_FINISH']           = '';
        $mapped['CHECKIN_USER_ID']          = '';
        $mapped['PARTIAL_PICK_ALLOWED'] = '';
        $mapped['DESPATCH_PALLET_NO'] = '';
        $mapped['PICK_LOCATION'] = '';
        $mapped['REASON'] = '';
        $mapped['SALE_PRICE'] = '';
        $mapped['DISCOUNT'] = '';
        $mapped['SSN_CONFIRM'] = '';
        $mapped['WIP_PRELOCN_ORDERING'] = '';
        $mapped['WIP_POSTLOCN_ORDERING'] = '';
        $mapped['ALLOW_SUBSTITUTE'] = '';
        $mapped['ORIGINAL_SSN_ID'] = '';
        $mapped['RETURN_DATE'] = '';
        $mapped['PICK_RETRIEVE_STATUS'] = '';
        $mapped['DESPATCH_LOCATION_GROUP'] = '';
        $mapped['PICK_QTY_DIFFERENCE'] = '';
        $mapped['PICK_QTY_DIFFERENCE2'] = '';
        $mapped['LAST_UPDATE_DATE'] = '';
        $mapped['OTHER4'] = '';
        $mapped['OTHER5'] = '';
        $mapped['OTHER6'] = '';
        $mapped['OTHER7'] = '';
        $mapped['OTHER8'] = '';
        $mapped['OTHER9'] = '';
        $mapped['LINE_TOTAL'] = '';
        $mapped['TAX_AMOUNT'] = '';
        $mapped['TAX_RATE'] = '';
        $mapped['BATCH_LINE'] = '';
        $mapped['OVER_SIZED'] = '';
        $mapped['OTHER_QTY1'] = '';
        $mapped['OTHER_QTY2'] = '';
        $mapped['SPECIAL_INSTRUCTIONS3'] = '';
        $mapped['ALT_DESPATCH_WH_ID'] = '';
        $mapped['ALT_DESPATCH_LOCN_ID'] = '';
        $mapped['PICK_LINE_PRIORITY'] = '';
        $mapped['PICK_PICK_FINISH'] = '';
        $mapped['PICK_LOCN_SEQ'] = '';
        $mapped['CANCEL_METHOD'] = '';
        */

         //$mappedList[] = $mapped;
        //return $mapped; //new PickItem($mapped);
        return $mappedList; //new PickItem($mapped);
    }

    /**
     * Parse xml response and build PurchaseOrder object with PurchaseOrderLines
     *
     * @param string $xml
     * @return array
     */
    public function parsePurchaseOrder($xml) {
        $p = new DOMDocument();
        $p->loadXML($xml);
        $this->_xpath = new DOMXPath($p);
        $this->_xpath->registerNamespace('m', 'urn:messages_2008_1.platform.webservices.netsuite.com');
        $this->_xpath->registerNamespace('c', 'urn:core_2008_1.platform.webservices.netsuite.com');
        $this->_xpath->registerNamespace('p', 'urn:purchases_2008_1.transactions.webservices.netsuite.com');

        $entries  = $this->_xpath->query('//soapenv:Body/m:getListResponse/m:readResponseList/m:readResponse');
        if ($entries->length == 0) {
            $entries  = $this->_xpath->query('//soapenv:Body/m:getResponse/m:readResponse');

            if ($entries->length == 0) {
                return false;
            }
        }
        $entries  = $this->__NodeListToArrayOfNodes($entries);

        foreach ($entries as $node) {
            $status = $this->_xpath->query('c:status', $node);
            $record = $this->_xpath->query('m:record', $node);
            $status = $this->__parseStatus($status->item(0));
            if ($status->isSuccess) {
                $rec = $this->__buildObjectTree($record->item(0));
                $data[$rec->internalId] = $this->__mapPurchaseOrder($rec);
            }
        }
        return $data;
    }

    /**
     * Parse itemList into array of items
     *
     * @param array $list
     * @param string $purchaseOrder
     * @return array
     */
    private function __parsePurchaseOrderItemList($list, $purchaseOrder) {
        $result = array();

        if (!is_array($list)) {
            $list = array($list);
        }

        foreach ($list as $val) {
            $mappedArray = $this->__mapPurchaseOrderItem($val);
            $mappedArray['PURCHASE_ORDER'] = $purchaseOrder;
            $result[]  = new PurchaseOrderLine($mappedArray);
        }
        return $result;
    }

    /**
     * Maps object properties to array elements
     *
     * @param stdClass $obj
     * @return array
     */
    private function __mapPurchaseOrderItem($obj) {

        $result = array();
        $result['PO_LINE_CUSTOMER_ID']     = $this->__map(array($obj, 'customer'), 'internalId');
        $result['PO_LINE_CUSTOMER_NAME']   = $this->__map(array($obj, 'customer'), 'name');
        $result['PO_LINE_DESCRIPTION']     = $this->__map($obj, 'description');
        $tmp = $this->__map($obj, 'isClosed');
        if ($tmp) {
            $tmp = ($tmp == 'false' ? 'F':'T');
        }
        $result['PO_LINE_STATUS_TF']       = $tmp;

        // we require internalId of Product to later using for reference in Receipt
        $result['PROD_ID']                 = $this->__map(array($obj, 'item'), 'internalId');
        $result['PO_LINE']                 = $this->__map($obj, 'line');
        $result['PO_LEGACY_LINE']          = $this->__map($obj, 'line');
        $result['PO_LINE_OPTIONS']         = $this->__map($obj, 'options');
        $result['PO_LINE_QTY']             = $this->__map($obj, 'quantity');
        $result['PO_LINE_LOTNO_LIST']      = $this->__map($obj, 'serialNumbers');
        $result['UOM_ORDER']               = substr($this->__map(array($obj, 'units'), 'name'), 0, 2);

        $result = $this->__stripNonExistent($result);
        return $result;
    }


    private function __mapPurchaseOrder($obj)
    {
        $result = array();
        $result['PO_LEGACY_DATE']        = $this->__dateConvert($this->__map($obj, 'createdDate'));
        $result['PO_CURRENCY']           = $this->__map($obj, 'currencyName');
        $result['PO_DUE_DATE']           = $this->__dateConvert($this->__map($obj, 'dueDate'));
        $result['PO_CREATED_BY_NAME']    = $this->__map(array($obj, 'employee'), 'name');
        $result['PERSON_ID']             = $this->__map(array($obj, 'entity'), 'internalId');;
        $result['PO_LEGACY_INTERNAL_ID'] = $this->__map($obj, 'internalId');

        $items = $this->__map(array($obj, 'itemList'), 'item');
        if (!is_array($obj->itemList->item)) {
            $items = array($items);
        }

/*
        $result['PO_RECEIVE_WH_ID'] = $this->__map(array($obj, 'location'), 'internalId');
        $result['PO_RECEIVE_WH_NAME'] = $this->__map(array($obj, 'location'), 'name');

        if (false === $result['PO_RECEIVE_WH_ID']) {
            $result['PO_RECEIVE_WH_ID']     = $this->__map(array(current($items), 'location'), 'internalId');
            if (false === $result['PO_RECEIVE_WH_ID']) {
                $result['PO_RECEIVE_WH_ID'] = '';
            }
        }
        if (false === $result['PO_RECEIVE_WH_NAME']) {
            $result['PO_RECEIVE_WH_NAME']   = $this->__map(array(current($items), 'location'), 'name');
            if (false === $result['PO_RECEIVE_WH_NAME']) {
                $result['PO_RECEIVE_WH_NAME'] = '';
            }
        }
*/
        $result['PO_LEGACY_RECEIVE_WH_ID'] = $this->__map(array($obj, 'location'), 'internalId');
        $result['PO_LEGACY_RECEIVE_WH_NAME'] = $this->__map(array($obj, 'location'), 'name');

        if (false === $result['PO_LEGACY_RECEIVE_WH_ID']) {
            $result['PO_LEGACY_RECEIVE_WH_ID']     = $this->__map(array(current($items), 'location'), 'internalId');
            if (false === $result['PO_LEGACY_RECEIVE_WH_ID']) {
                $result['PO_LEGACY_RECEIVE_WH_ID'] = '';
            }
        }
        if (false === $result['PO_LEGACY_RECEIVE_WH_NAME']) {
            $result['PO_LEGACY_RECEIVE_WH_NAME']   = $this->__map(array(current($items), 'location'), 'name');
            if (false === $result['PO_LEGACY_RECEIVE_WH_NAME']) {
                $result['PO_LEGACY_RECEIVE_WH_NAME'] = '';
            }
        }
        $result['PO_LEGACY_MEMO']       = $this->__map($obj, 'memo');
        $result['PO_LEGACY_STATUS']     = $this->__map($obj, 'status');
        $result['PO_LEGACY_STATUS_ID']  = '';
        $result['PO_LEGACY_RECVD_DATE'] = $this->__dateConvert($this->__map($obj, 'tranDate'));

        $result['PURCHASE_ORDER']          = $this->__map($obj, 'tranId');
        $result['PO_LEGACY_CONSIGNMENT']   = false;
        $result['COMPANY_ID']              = false;
        $result['PO_LEGACY_OWNER_ID']      = false;

        $customFields = $this->__map(array($obj, 'customFieldList'), 'customField');

        if (is_array($customFields)) {
            foreach ($customFields as $field) {
                if (isset($field->internalId)) {
                    switch ($field->internalId) {
                        case 'custbody3':
                            $result['PO_LEGACY_CONSIGNMENT'] = $field->value;
                            break;
                        case 'custbody7':
                            //$result['COMPANY_ID'] = $field->value;
                            $result['PO_LEGACY_OWNER_ID'] = $field->value;
                        default:
                        break;
                    }
                }
            }
        }
        if (false == $result['PO_LEGACY_CONSIGNMENT']) {
            $result['PO_LEGACY_CONSIGNMENT'] = 'IN' . substr($result['PURCHASE_ORDER'], 2);
        } else {
            $result['PO_LEGACY_CONSIGNMENT']->localValue = 'IN' . substr($result['PURCHASE_ORDER'], 2);
        }

        if(false == $result['PO_LEGACY_OWNER_ID']) {
            if(false == $this->__map(array($obj, 'department'), 'internalId')) {

                $this->lastError = 'Field internalId is empty';
                $result['PO_LEGACY_OWNER_ID'] = '';

            } else {
                $result['PO_LEGACY_OWNER_ID'] = $obj->department;
            }
        }

        if (false == $result['COMPANY_ID']) {
            $result['COMPANY_ID']   = ' ';
        }

        $result['PO_DATE']                 = date('Y-m-d H:i:s');
        $result['PO_STATUS']               = 'OP';
        $result['ORDER_TYPE']              = 'PO';

        $result = $this->__stripNonExistent($result);
        $result = new PurchaseOrder($result);

        $result->items['itemList'] = $this->__parsePurchaseOrderItemList($items, $result->id);
        return $result;
    }

    /**
     * Get node by XPath and index
     *
     * @param string $xpathQuery
     * @param DOMNode $node
     * @param integer $index
     * @return DOMNode|false
     */
    private function __getNode($xpathQuery, DOMNode $node = null, $index = 0) {

        if ($node === null) {
            $nodeList = $this->_xpath->query($xpathQuery);
        } else {
            $nodeList = $this->_xpath->query($xpathQuery, $node);
        }
        if ($nodeList instanceof DOMNodeList) {
            if ($nodeList->length > 0) {
                $value = $nodeList->item($index);
            } else {
                $value = false;
            }
        } else {
            $value = false;
        }
        return $value;
    }

    /**
     * Get attribute from Node
     *
     * @param string $xpathQuery
     * @param string $attributeName
     * @param DOMNode $node
     * @return string|false
     */
    private function __getAttributeValue($xpathQuery, $attributeName, DOMNode $node = null) {
        $resultNode = $this->__getNode($xpathQuery, $node);
        if ($resultNode !== false) {
            if ($resultNode->hasAttributes()) {
                return $resultNode->getAttribute($attributeName);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get Node value by XPath
     *
     * @param string $xpathQuery
     * @param DOMNode $node
     * @return string|false
     */
    private function __getNodeValue($xpathQuery, DOMNode $node = null) {
        $resultNode = $this->__getNode($xpathQuery, $node);

        if ($resultNode !== false) {
            $value = $resultNode->nodeValue;
        } else {
            $value = false;
        }
        return $value;
    }

    /**
     * Translate DOMNodeList to array of DOMNodes
     *
     * @param DOMNodeList $list
     * @return array
     */
    private function __nodeListToArrayOfNodes(DOMNodeList $list) {
        $result = array();
        foreach ($list as $node) {
            $result[] = $node;
        }
        return $result;
    }

    /**
     * Parse response status
     *
     * @param DOMElement $n
     * @return object
     */
    private function __parseStatus(DOMElement $n) {
        $result = new stdClass;
        $result->isSuccess = $n->getAttribute('isSuccess') == 'true' ? true: false;
        return $result;
    }

    /**
     * Unset array items which strict comparison
     *
     * @param array $list
     * @return array
     */
    private function __stripNonExistent(array $list, $compareTo = false) {
        $result = $list;
        foreach ($result as $key => $val) {
            if ($compareTo === $val) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    private function __map($obj, $prp, $default = false) {
        if (is_array($obj)) {
            if (isset($obj[0]->$obj[1])) {
                return $this->__map($obj[0]->$obj[1], $prp, $default);
            } else {
                return false;
            }
        } else {
            $result = (isset($obj->$prp) ? $obj->$prp : false);
            if ($result == 'false') {
                $result = 'F';
            } elseif ($result == 'true') {
                $result = 'T';
            }
            return $result;
        }
    }


    /**
     * convert date from Netsuite to Minder
     *
     * @param string $date
     * @return string|false
     */
    private function __dateConvert($date, $format = 'Y-m-d H:i:s') {
        return ($date ? date($format, strtotime($date)) : false);
    }


    public function isSuccess($xml)
    {
        $result = strpos($xml, 'isSuccess="false"');

        if ($result !== false) {
            $result  = false;
            $matches = array();
            preg_match_all('|<[^>]+>(.*)</[^>]+>|U', $xml, $matches);
            $this->lastError = $matches[1][3];
        } else {
            $result = strpos($xml, 'isSuccess');
            if ($result !== false) {
                $result = true;
            } else {
                $result = false;
            }
        }
        return $result;
    }

    public function parseResponse($xml)
    {
        $rec = false;
        $this->__initXPath($xml);
        $entries  = $this->_xpath->query('//soapenv:Body');
        if ($entries->length == 0) {
            $entries  = $this->_xpath->query('//soapenv:Body/*');
            if ($entries->length == 0) {
                return false;
            }
        }
        $entries  = $this->__NodeListToArrayOfNodes($entries);

        foreach ($entries as $node) {
            $rec = $this->__buildObjectTree($node);
        }

        return $rec;
    }

    public function parsePagesCount($xml) {
        $totalPages = 0;
        $this->__initXPath($xml);
        $entries  = $this->_xpath->query('//soapenv:Body/*');
        if ($entries->length == 0) {
            return $totalPages;
        }
        $entries  = $this->__NodeListToArrayOfNodes($entries);
        foreach ($entries as $node) {
            $rec = $this->__buildObjectTree($node);
        }

        $totalPages = (int)$this->__map(array($rec, 'searchResult'), 'totalPages');

            return $totalPages;
    }
}
