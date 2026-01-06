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
 * Location Line
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
class LocationLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['PROD_ID']           = '';
            $this->items['LOCN_ID']           = '';
            $this->items['WH_ID']             = '';
            $this->items['QTY']               = '';
            $this->items['LOCN_STAT']         = '';
            $this->items['MOVE_STAT']         = '';
            $this->items['STORE_AREA']        = '';
            $this->items['STORE_TYPE']        = '';
            $this->items['TEMPERATURE_ZONE']  = '';
            $this->items['LAST_AUDITED_DATE'] = '';
            $this->items['DESCRIPTION']       = '';
            $this->items['COUNT_ISSN']        = '';
            $this->items['LOCN_NAME']         = '';
            $this->items['STORE_METH']        = '';
            $this->items['MOVEABLE_LOCN']     = '';
            $this->items['LOCN_SEQ']          = '';
            $this->items['REPLENISH']         = '';
            $this->items['PERM_LEVEL']        = '';
            $this->items['COUNT_ISSN']        = '';
            $this->items['COMPANY_ID']        = '';
        }
    }
}
