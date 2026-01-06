<?php
/**
 * Minder
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * PickItem
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class PickItem extends ModelCollection
{

    public function __construct()
    {
        if (func_num_args() > 0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['PICK_LABEL_NO'] = '';
            $this->items['PICK_ORDER'] = '';
            $this->items['PICK_ORDER_LINE_NO'] = '';
            $this->items['CONTACT_NAME'] = '';
            $this->items['PROD_ID'] = '';
            $this->items['SSN_ID'] = '';
            $this->items['WARRANTY_TERM'] = '';
            $this->items['PICK_LABEL_DATE'] = '';
            $this->items['SPECIAL_INSTRUCTIONS1'] = '';
            $this->items['SPECIAL_INSTRUCTIONS2'] = '';
            $this->items['SHIP_VIA'] = '';
            $this->items['PICK_ORDER_QTY'] = '';
            $this->items['PICK_ALLOCATED_QTY'] = '';
            $this->items['PICKED_QTY'] = '';
            $this->items['PICK_LINE_DUE_DATE'] = '';
            $this->items['PICK_STARTED'] = '';
            $this->items['DESPATCH_TS'] = '';
            $this->items['DESPATCH_LOCATION'] = '';
            $this->items['CREATE_DATE'] = '';
            $this->items['USER_ID'] = '';
            $this->items['DEVICE_ID'] = '';
            $this->items['CHECKIN_START'] = '';
            $this->items['CHECKIN_FINISH'] = '';
            $this->items['CHECKIN_USER_ID'] = '';
            $this->items['PICK_LINE_STATUS'] = '';
            $this->items['PARTIAL_PICK_ALLOWED'] = '';
            $this->items['DESPATCH_PALLET_NO'] = '';
            $this->items['WH_ID'] = '';
            $this->items['PICK_LOCATION'] = '';
            $this->items['REASON'] = '';
            $this->items['SALE_PRICE'] = '';
            $this->items['DISCOUNT'] = '';
            $this->items['SSN_CONFIRM'] = '';
            $this->items['WIP_PRELOCN_ORDERING'] = '';
            $this->items['WIP_POSTLOCN_ORDERING'] = '';
            $this->items['ALLOW_SUBSTITUTE'] = '';
            $this->items['ORIGINAL_SSN_ID'] = '';
            $this->items['RETURN_DATE'] = '';
            $this->items['PICK_RETRIEVE_STATUS'] = '';
            $this->items['DESPATCH_LOCATION_GROUP'] = '';
            $this->items['PICK_QTY_DIFFERENCE'] = '';
            $this->items['PICK_QTY_DIFFERENCE2'] = '';
            $this->items['LAST_UPDATE_DATE'] = '';
            $this->items['OTHER1'] = '';
            $this->items['OTHER2'] = '';
            $this->items['OTHER3'] = '';
            $this->items['OTHER4'] = '';
            $this->items['OTHER5'] = '';
            $this->items['OTHER6'] = '';
            $this->items['OTHER7'] = '';
            $this->items['OTHER8'] = '';
            $this->items['OTHER9'] = '';
            $this->items['LINE_TOTAL'] = '';
            $this->items['TAX_AMOUNT'] = '';
            $this->items['TAX_RATE'] = '';
            $this->items['BATCH_LINE'] = '';
            $this->items['OVER_SIZED'] = '';
            $this->items['OTHER_QTY1'] = '';
            $this->items['OTHER_QTY2'] = '';
            $this->items['SPECIAL_INSTRUCTIONS3'] = '';
            $this->items['ALT_DESPATCH_WH_ID'] = '';
            $this->items['ALT_DESPATCH_LOCN_ID'] = '';
            $this->items['PICK_LINE_PRIORITY'] = '';
            $this->items['PICK_PICK_FINISH'] = '';
            $this->items['PICK_LOCN_SEQ'] = '';
            $this->items['CANCEL_METHOD'] = '';
            $this->items['PI_LEGACY_ITEM_DESCR'] = '';
            $this->items['PI_LEGACY_CLOSED'] = '';
            $this->items['PI_LEGACY_LINENO'] = '';
            $this->items['PI_LEGACY_WH_NAME'] = '';
            $this->items['PI_LEGACY_FULFILLED_QTY'] = '';
            $this->items['PI_LEGACY_PACKED_QTY'] = '';
            $this->items['PI_LEGACY_PICKED_QTY'] = '';
            $this->items['PI_LEGACY_RATE'] = '';
            $this->items['PI_LEGACY_PICK_UOM'] = '';
            $this->items['PI_LEGACY_WH_ID'] = '';
            $this->items['SO_REVISION_STATUS'] = '';
        }
    }
}
