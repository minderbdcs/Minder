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
 * Report
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
class ReportType extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['CODE'] = '';
            $this->items['DESCRIPTION'] = '';
            $this->items['RT_NOTES'] = '';
            $this->items['RT_USER_ID'] = '';
            $this->items['RT_PASS_WORD'] = '';
            $this->items['RT_URL'] = '';
            $this->items['LAST_UPDATE_DATE'] = '';
            $this->items['LAST_UPDATE_BY'] = '';
            $this->items['RT_STATUS'] = '';
        }
    }
}
