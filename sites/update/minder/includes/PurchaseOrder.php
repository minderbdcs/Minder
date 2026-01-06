<?php
/**
 * Minder
 *
 * PHP version 5.2.6
 *
 * @category  Minder
 * @package   Warehouse
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 */

/**
 * @category  Minder
 * @package   Minder
 * @author    Strelnikov Evgeniy <strelnikov.evgeniy@binary-studio.com@binary-studio.com>
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class PurchaseOrder extends ModelCollection
{
    protected $_mandatory = array('PURCHASE_ORDER', 'PO_LEGACY_CONSIGNMENT');

    public function __construct()
    {
        if (func_num_args() > 0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['PURCHASE_ORDER']          = '';
            $this->items['PERSON_ID']               = '';
            $this->items['REQUISITION_NO']          = '';
            $this->items['PO_DATE']                 = '';
            
            $this->items['LAST_UPDATE_DATE']        = '';
            $this->items['EARLIEST_DATE']          	= '';
            $this->items['PO_RECEIVER']          	= '';
            
            $this->items['PO_RECEIVE_LOCN_ID']     	= '';
            
            $this->items['PO_REVISION_NO']          = '';
            $this->items['COMPANY_ID']              = '';
            $this->items['DIVISION_ID']             = '';
            $this->items['PO_STATUS']               = '';
            $this->items['COMMENTS']                = '';
            $this->items['PO_PRINTED']              = '';
            $this->items['USER_ID']                 = '';
            $this->items['ORDER_TYPE']              = '';
            $this->items['PO_LEGACY_DATE']          = '';
            $this->items['PO_CURRENCY']             = '';
            $this->items['PO_DUE_DATE']             = '';
            $this->items['PO_LEGACY_INTERNAL_ID']   = '';
            $this->items['PO_RECEIVE_WH_ID']        = '';
            $this->items['PO_LEGACY_MEMO']          = '';
            $this->items['PO_LEGACY_STATUS']        = '';
            $this->items['PO_LEGACY_RECVD_DATE']    = '';
            $this->items['PO_SHIP_TO_ADDRESS3']     = '';
            $this->items['PO_SHIP_TO_ADDRESS4']     = '';
            $this->items['PO_SHIP_TO_ADDRESS5']     = '';
            $this->items['PO_CREATED_BY_NAME']      = '';
            $this->items['PO_RECEIVE_WH_NAME']      = '';
            $this->items['PO_LEGACY_STATUS_ID']     = '';
            $this->items['PO_SHIP_TO_ATTENSION']    = '';
            $this->items['PO_SHIP_TO_ADDRESSEE']    = '';
            $this->items['PO_SHIP_TO_PHONE']        = '';
            $this->items['PO_SHIP_TO_ADDRESS1']     = '';
            $this->items['PO_SHIP_TO_ADDRESS2']     = '';
            $this->items['PO_SHIP_TO_SUBURB']       = '';
            $this->items['PO_SHIP_TO_STATE']        = '';
            $this->items['PO_SHIP_TO_POSTCODE']     = '';
            $this->items['PO_SHIP_TO_COUNTRY']      = '';
            $this->items['PO_LEGACY_CONSIGNMENT']   = '';
            $this->items['PO_LEGACY_CREATED_BY_NAME']= '';
            $this->items['PO_LEGACY_OWNER_ID']      = '';
            $this->items['PO_LEGACY_RECEIVE_WH_ID']        = '';
            $this->items['PO_LEGACY_RECEIVE_WH_NAME']      = '';

            $this->items['PO_GRN']     				= '';
            $this->items['PO_VESSEL_NAME']      	= '';
            $this->items['PO_CONTAINER_NO']      	= '';
            $this->items['PO_VOYAGE_NO']     		= '';
            
            $this->items['PO_FREIGHT_COST']      	= '';
            $this->items['PO_CUSTOM_FEES']      	= '';
            $this->items['PO_STORAGE_FEES']      	= '';
            $this->items['PO_INSURANCE']      		= '';
            $this->items['PO_AMOUNT_PAID']      	= '';
            $this->items['PO_CONTAINER_FEES']      	= '';
            $this->items['PO_UNLOADING_FEES']      	= '';
            $this->items['PO_ADMIN_FEES']      		= '';
            $this->items['PO_OTHER_FEES']      		= '';
            $this->items['PO_TAX_AMOUNT']      		= '';
            $this->items['PO_TAX_RATE']      		= '';
            $this->items['PO_AMOUNT_DUE']      		= '';
            
            $this->items['PO_LINE_EXTERNAL_NOTES']  = '';
            $this->items['PO_SHIP_VIA']             = '';
            $this->items['SUPPLIER_CONTACT']        = '';
            
            $this->items['COST_CENTER']             = '';
            $this->items['PO_SHIPPING_METHOD']      = '';
            $this->items['PO_SHIP_VIA_SERVICE']     = '';
            $this->items['PO_SHIPPING_ACCOUNT']     = '';
             
            
        }
        if (!$this->validate($this->items)) {
            throw new Exception('Invalid PK. ' . __CLASS__);
        }
    }

    public function validate($data)
    {
        if (!array_key_exists('PURCHASE_ORDER', $data)) {
            return false;
        }
        $this->id = $data['PURCHASE_ORDER'];
        foreach ($this->items as &$value) {
            if (gettype($value) == 'string') {
                $value = trim($value);
            }
        }
        return true;
    }


}
