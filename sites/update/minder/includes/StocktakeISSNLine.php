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
 * StocktakeISSNLine
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
class StocktakeISSNLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this['RECORD_ID']       = '';
            $this['ST_SSN_ID']       = '';
            $this['SSN_DESCRIPTION'] = '';
            $this['PROD_ID']         = '';
            $this['ST_COUNT']        = '';
            $this['ST_VARIANCE']     = '';
            $this['ST_STATUS']       = '';
            $this['ST_ACTION']       = '';
            $this['ST_AUDIT_DATE']   = '';
            $this['WH_ID']           = '';
            $this['ST_LOCN_ID']      = '';
            $this['ST_AUDIT_BY']     = '';
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