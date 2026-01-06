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
 * Location
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
class Location extends ModelCollection
{
    public $hadCheckDigit = false;

    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['WH_ID']                = '';
            $this->items['LOCN_ID']                = '';
            $this->items['LOCN_NAME']            = '';
            $this->items['LOCN_TYPE']            = '';
            $this->items['LOCN_METRIC']            = '';
            $this->items['LOCN_HGHT']            = '';
            $this->items['PARENT_LOCN_ID']        = '';
            $this->items['ZONE_C']                = '';
            $this->items['CC_C']                = '';
            $this->items['TOG_C']                = 'A';
            $this->items['LOCN_STAT']            = '';
            $this->items['MOVE_STAT']            = '';
            $this->items['REPLENISH']            = '';
            $this->items['PACK_T']                = '';
            $this->items['STORE_TYPE']            = 'ST';
            $this->items['STORE_AREA']            = 'ST';
            $this->items['STORE_METH']            = '';
            $this->items['PERM_LEVEL']            = '';
            $this->items['LABEL_DATE']            = '';
            $this->items['LAST_AUDITED_DATE']    = '';
            $this->items['PROD_ID']                = '';
            $this->items['INSTANCE_ID']            = '';
            $this->items['MAX_QTY']                = '';
            $this->items['MIN_QTY']                = '';
            $this->items['REORDER_QTY']            = '';
            $this->items['AISLE_SEQ']            = '';
            $this->items['BAY_SEQ']                = '';
            $this->items['SHELF_SEQ']            = '';
            $this->items['COMPARTMENT_SEQ']        = '';
            $this->items['LAST_UPDATE_DATE']    = '';
            $this->items['LAST_UPDATE_BY']        = '';
            $this->items['PUTAWAY_QTY']            = '';
            $this->items['LOCN_OWNER']            = '';
            $this->items['CURRENT_WH_ID']        = '';
            $this->items['MOVEABLE_LOCN']        = '';
            $this->items['SSN_TRACK']            = '';
            $this->items['LOCN_SEQ']            = '';
            $this->items['TEMPERATURE_ZONE']    = '';
        }
    }

    public function hasCheckDigit() {
        return is_numeric($this->items['LOCN_CHECK_DIGITS']);
    }
}
