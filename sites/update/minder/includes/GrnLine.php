<?php
/**
 * Minder
 *
 * PHP version 5.2.3
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
 * GRN
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class GrnLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args() > 0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['GRN']                 = '';
            $this->items['GRN_TYPE']            = '';
            $this->items['ORDER_NO']            = '';
            $this->items['ORDER_LINE_NO']       = '';
            $this->items['CARRIER']             = '';
            $this->items['RETURN_ID']           = '';
            $this->items['GRN_DATE']            = '';
            $this->items['AWB_CONSIGNMENT_NO']  = '';
            $this->items['TOTAL_QTY_PACKS']     = '';
            $this->items['SHIPPED_DATE']        = '';
            $this->items['OWNER_ID']            = '';
            $this->items['CONTAINER_NO']        = '';
            $this->items['SHIP_CONTAINER_TYPE'] = '';
            $this->items['PALLETS_YN']          = '';
            $this->items['GRN_PALLET_QTY']      = '';
            $this->items['PACK_CRATE_OWNER']    = '';
            $this->items['PACK_CRATE_TYPE']     = '';
            $this->items['PACK_CRATE_QTY']      = '';
            $this->items['LAST_LINE_NO']        = '';
            $this->items['LAST_PALLET_NO']      = '';
            $this->items['USER_ID']             = '';
            $this->items['DEVICE_ID']           = '';
            $this->items['COMMENTS']            = '';
            $this->items['RECEIPT_FLAG']        = '';
            $this->items['GRN_PRINTED']         = '';
            $this->items['LAST_UPDATE_DATE']    = '';
            $this->items['RECEIPT_FLAG_01']     = '';
            $this->items['COMMENTS_01']         = '';
            $this->items['PACK_CRATE_OWNER']    = '';
            $this->items['SHIPPING_CRATE_OWNER']= '';
        }
    }
}
