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
class ReportLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['REPORT_ID'] = '';
            $this->items['NAME'] = '';
            $this->items['DESCRIPTION'] = '';
            $this->items['QUERY'] = '';
            $this->items['COMPANY_ID'] = '';
            $this->items['CREATE_DATE'] = '';
            $this->items['CREATED_BY'] = '';
            $this->items['LAST_UPDATE_DATE'] = '';
            $this->items['LAST_UPDATE_BY'] = '';
            $this->items['REPORT_FREQUENCY'] = '';
            $this->items['REPORT_OUTPUT_FORMAT'] = '';
            $this->items['REPORT_HEADER'] = '';
            $this->items['REPORT_FOOTER'] = '';
            $this->items['REPORT_TYPE'] = '';
            $this->items['REPORT_URI'] = '';
            $this->items['REPORT_FORMAT'] = '';
            $this->items['REPORT_MENU_TYPE'] = '';
            $this->items['REPORT_ACTIVITY'] = '';
        }
    }
}
