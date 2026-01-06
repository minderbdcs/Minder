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
 * AddressLine
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
class AddressLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['RECORD_ID'] = '';
            $this->items['PERSON_ID'] = '';
            $this->items['ADDR_TYPE'] = '';
            $this->items['COMPANY_ID'] = '';
            $this->items['ADDR_LINE1'] = '';
            $this->items['ADDR_LINE2'] = '';
            $this->items['ADDR_SUBURB'] = '';
            $this->items['ADDR_CITY'] = '';
            $this->items['ADDR_STATE'] = '';
            $this->items['ADDR_POST_CODE'] = '';
            $this->items['ADDR_COUNTRY'] = '';
            $this->items['ADDR_POST_4STATE_ID'] = '';
            $this->items['ADDR_MOBILE_NO'] = '';
            $this->items['ADDR_PHONE_NO'] = '';
            $this->items['ADDR_FAX_NO'] = '';
            $this->items['ADDR_EMAIL'] = '';
            $this->items['ADDR_TITLE'] = '';
            $this->items['ADDR_FIRST_NAME'] = '';
            $this->items['ADDR_LAST_NAME'] = '';
            $this->items['ADDR_STATUS'] = '';
            $this->items['ADDR_CREATED_DATE'] = '';
            $this->items['ADDR_CREATED_BY'] = '';
        }
    }
}
?>