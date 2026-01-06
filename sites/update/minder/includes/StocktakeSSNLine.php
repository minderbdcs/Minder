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
 * StocktakeSSNLine
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
class StocktakeSSNLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this['PROD_ID']         = '';
            $this['SSN_DESCRIPTION'] = '';
            $this['ORIGINAL_SSN']    = '';
            $this['ST_COUNT']        = '';
            $this['ST_VARIANCE']     = '';
            $this['WH_ID']        = '';
        }
        if (!$this->validate($this->items)) {
            throw new Exception('Invalid PK.');
        }
    }

    public function validate($data)
    {
        if (!array_key_exists('ORIGINAL_SSN', $data)) {
            return false;
        }
        $this->id = $data['ORIGINAL_SSN'];
        foreach ($this->items as &$value) {
            if (gettype($value) == 'string') {
                $value = trim($value);
            }
        }
        return true;
    }
}