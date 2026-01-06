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
 * PurchaseLineDetail
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
class PurchaseLineDetail extends ModelCollection
{
    protected $_mandatory = array();

    public function __construct()
    {
        if (func_num_args() > 0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['PURCHASE_DETAIL_ID']   = '';
            $this->items['PURCHASE_ORDER']   = '';
            $this->items['PO_LINE']          = '';
            $this->items['SSN_ID']           = '';
            $this->items['QTY_RECEIVED']     = '';
            $this->items['PURCHASE_DETAIL_STATUS']   = '';
            $this->items['LAST_UPDATE_DATE'] = '';
            $this->items['CREATE_DATE']      = '';
            $this->items['USER_ID']          = '';
            $this->items['DEVICE_ID']        = '';
        }
        if (!$this->validate($this->items)) {
            throw new Exception('Invalid PK. ' . __CLASS__);
        }
        $this->id = $this->items['PURCHASE_ORDER'] . $this->items['PO_LINE'] . $this->items['SSN_ID'];
    }

    public function validate($data)
    {
        if (!array_key_exists('PURCHASE_ORDER', $data) || !array_key_exists('PO_LINE', $data) || !array_key_exists('SSN_ID', $data)) {
            return false;
        }
        $this->id = $this->items['PURCHASE_ORDER'] . $this->items['PO_LINE'] . $this->items['SSN_ID'];
        foreach ($this->items as &$value) {
            $value = trim($value);
        }
        return true;
    }

}
