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
 * ReportDetail
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
class ReportDetail extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items =  array();
            $this->items['REPORT_ID'] = '';
            $this->items['REPORT_DETAIL_ID'] = '';
            $this->items['SEQUENCE'] = '';
            $this->items['QUERY_FIELD'] = '';
            $this->items['QUERY_PROMPT'] = '';
            $this->items['QUERY_DB_FIELD'] = '';
            $this->items['QUERY_PROMPT_TYPE'] = '';
            $this->items['QUERY_COPY_FIELD'] = '';
        }
    }

    public function validate($data)
    {
            if (false == is_numeric($this->items['REPORT_ID'])) {
                return false;
            }
            if (false == is_numeric($this->items['REPORT_DETAIL_ID'])) {
                $this->items['REPORT_DETAIL_ID'] = 0;
            }
            if (false == is_numeric($this->items['SEQUENCE'])) {
                $this->items['SEQUENCE'] = 0;
            }
            if (strlen($this->items['QUERY_FIELD']) > 30) {
                $this->items['QUERY_FIELD'] = substr($this->items['QUERY_FIELD'], 0, 30);
            }
            if (strlen($this->items['QUERY_PROMPT']) > 80) {
                $this->items['QUERY_PROMPT'] = substr($this->items['QUERY_PROMPT'], 0, 80);
            }
            if (strlen($this->items['QUERY_DB_FIELD']) > 80) {
                $this->items['QUERY_DB_FIELD'] = substr($this->items['QUERY_DB_FIELD'], 0, 80);
            }
            if (strlen($this->items['QUERY_PROMPT_TYPE']) > 1) {
                $this->items['QUERY_PROMPT_TYPE'] = strtoupper(substr($this->items['QUERY_PROMPT_TYPE'], 0, 1));
            }
            if (strlen($this->items['QUERY_COPY_FIELD']) > 1) {
                $this->items['QUERY_COPY_FIELD'] = substr($this->items['QUERY_COPY_FIELD'], 0, 40);
            }
    }
}
