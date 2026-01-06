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
 * LegacyAdjustment
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
class LegacyAdjustment extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['RECORD_ID']           = '';
            $this->items['LA_PROD_ID']          = '';
            $this->items['LA_SSN_ID']           = '';
            $this->items['LA_WH_ID']            = '';
            $this->items['LA_LOCN_ID']          = '';
            $this->items['LA_ADJUST_BY']        = '';
            $this->items['LA_UOM']              = '';
            $this->items['LA_UNIT_COST']        = '';
            $this->items['LA_TOTAL_VALUE']      = '';
            $this->items['LA_TOTAL_ESTIMATE']   = '';
            $this->items['LA_DESCRIPTION']      = '';
            $this->items['LA_HEADER_NOTES']     = '';
            $this->items['LA_LINE_NOTES']       = '';
            $this->items['LA_ACCOUNT_CODE']     = '';
            $this->items['LA_ADJ_LOCATION']     = '';
            $this->items['LA_ADJ_CLASS']        = '';
            $this->items['LA_CUSTOMER']         = '';
            $this->items['LA_DEPARTMENT']       = '';
            $this->items['LA_EXTERNAL_ID']      = '';
            $this->items['LA_INTERNAL_ID']      = '';
            $this->items['LA_POSTING_PERIOD']   = '';
            $this->items['LA_INVENTORY_LIST']   = '';
            $this->items['LA_CREATE_DATE']      = '';
            $this->items['LA_COMPANY_ID']       = '';
            $this->items['LA_STATUS']           = '';
            $this->items['LA_LEGACY_UPDATED']   = '';
            $this->items['LA_USER_ID']          = '';
            $this->items['LA_DEVICE_ID']        = '';
        }
        if (!$this->validate($this->items)) {
            throw new Exception('Invalid PK.');
        }
    }

    public function validate($data)
    {
        if (!array_key_exists('RECORD_ID', $data)) {
            return false;
        }
        $this->id = $data['RECORD_ID'];
        foreach ($this->items as &$value) {
            if (gettype($value) == 'string') {
                $value = trim($value);
            }
        }
        return true;
    }
}