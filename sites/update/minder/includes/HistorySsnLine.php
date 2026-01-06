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
 * History SSN Line
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
class HistorySsnLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this['RECORD_ID']       = '';
            $this['WH_ID']           = '';
            $this['LOCN_ID']         = '';
            $this['SSN_ID']          = '';
            $this['TRN_DATE']        = '';
            $this['TRN_TYPE']        = '';
            $this['TRN_CODE']        = '';
            $this['ERROR_TEXT']      = '';
            $this['REFERENCE']       = '';
            $this['QTY']             = '';
            $this['SUB_LOCN_ID']     = '';
            $this['DEVICE_ID']       = '';
            $this['PERSON_ID']       = '';
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