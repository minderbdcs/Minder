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
 * ReportEmail 
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
class ReportEmail extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items =  array();
            $this->items['RECORD_ID'] = '';

            $this->items['SET_GROUP_NAME'] = '';
            $this->items['SET_EMAIL_NAME'] = '';
            $this->items['SET_STATUS'] = '';
            $this->items['SET_CREATED_DATE'] = '';
            $this->items['SET_CREATED_BY'] = '';
            $this->items['SET_LAST_UPDATED'] = '';
            $this->items['SET_LAST_UPDATED_BY'] = '';

        }
        $this->id = $this->items['SET_GROUP_NAME'] . ":" . $this->items['SET_EMAIL_NAME'] ;
    }

    public function validate($data)
    {
                return true;
    }
}
