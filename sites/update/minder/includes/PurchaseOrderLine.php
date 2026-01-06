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
 * PurchaseOrderLine
 *
 * All access to the Minder database is through the Minder class.
 * Source table is a PURCHASE_ORDER_LINE
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class PurchaseOrderLine extends ModelCollection
{
    protected $_mandatory = array('PO_LEGACY_LINE', 'PROD_ID', 'PO_LINE_QTY');

    public function __construct()
    {
        if (func_num_args() > 0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['PURCHASE_ORDER']   = '';
            $this->items['PO_LINE']          = '';
            $this->items['REQUISITION_NO']   = '';
            $this->items['PROD_ID']          = '';
            $this->items['PO_LINE_QTY']      = '';
            $this->items['UNIT_PRICE']       = '';
            $this->items['UOM_ORDER']        = '';
            $this->items['PO_LINE_DUE_DATE'] = '';
            $this->items['PO_LINE_STATUS']   = '';
            $this->items['COMMENTS']         = '';
            $this->items['GST_VALUE']        = '';
            $this->items['GST_RATE']         = '';
            $this->items['GST_CODE']         = '';
            $this->items['ORIGINAL_QTY']     = '';
            $this->items['PO_LINE_DESCRIPTION']   = '';
            $this->items['PO_LINE_OPTIONS']       = '';
            $this->items['PO_LINE_QTY_F']         = '';
            $this->items['PO_LINE_LOTNO_LIST']    = '';
            $this->items['PO_LINE_STATUS_TF']     = '';
            $this->items['PO_LINE_CUSTOMER_ID']   = '';
            $this->items['PO_LINE_CUSTOMER_NAME'] = '';
            $this->items['PO_CURRENCY']           = '';
            $this->items['LAST_UPDATE_DATE']      = '';
            $this->items['PO_LEGACY_RECV_ID']     = '';
            $this->items['PO_REVISION_STATUS']    = '';
            $this->items['PO_LEGACY_LINE']        = '';
        }
        if (!$this->validate($this->items)) {
            throw new Exception('Invalid PK. ' . __CLASS__);
        }
        $this->id = $this->items['PURCHASE_ORDER'] . $this->items['PO_LINE'];
    }

    public function validate($data)
    {
        if (!array_key_exists('PURCHASE_ORDER', $data) || !array_key_exists('PO_LINE', $data)) {
            return false;
        }
        $this->id = $this->items['PURCHASE_ORDER'] . $this->items['PO_LINE'];
        foreach ($this->items as &$value) {
            $value = trim($value);
        }
        return true;
    }

}
