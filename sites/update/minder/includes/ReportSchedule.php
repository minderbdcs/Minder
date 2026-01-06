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
class ReportSchedule extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items =  array();
            $this->items['RECORD_ID'] = '';
            $this->items['REPS_WH_ID'] = '';

            $this->items['REPS_RUN_TIME'] = '';
            $this->items['REPS_RUN_HOUR'] = '';
            $this->items['REPS_RUN_DAY'] = '';
            $this->items['REPS_LAST_RUN_TIME'] = '';

            $this->items['REPS_NAME'] = '';
            $this->items['REPS_EMAIL_GROUP'] = '';
            $this->items['REPS_SUBJECT'] = '';
            $this->items['REPS_OUT_FOLDER'] = '';
            $this->items['REPS_OUT_FILE_NAME'] = '';
            $this->items['REPS_OUT_FILE_EXT'] = '';
            $this->items['REPS_PARAMETER_2'] = '';
            $this->items['REPS_PARAMETER_3'] = '';
            $this->items['REPS_PARAMETER_4'] = '';
            $this->items['REPS_PARAMETER_5'] = '';
            $this->items['REPS_PARAMETER_6'] = '';
            $this->items['REPS_PARAMETER_1_QUERY_FIELD'] = '';
            $this->items['REPS_PARAMETER_2_QUERY_FIELD'] = '';
            $this->items['REPS_PARAMETER_3_QUERY_FIELD'] = '';
            $this->items['REPS_PARAMETER_4_QUERY_FIELD'] = '';
            $this->items['REPS_PARAMETER_5_QUERY_FIELD'] = '';
            $this->items['REPS_PARAMETER_6_QUERY_FIELD'] = '';
            $this->items['REPS_STATUS'] = '';
            $this->items['REPS_CREATED_DATE'] = '';
            $this->items['REPS_CREATED_BY'] = '';
            $this->items['REPS_LAST_UPDATED'] = '';
            $this->items['REPS_LAST_UPDATED_BY'] = '';
            $this->items['REPS_NOTES'] = '';

        }
        if (!$this->validate($this->items)) {
            throw new Exception('Invalid PK. ' . __CLASS__);
        }
        $this->id = $this->items['RECORD_ID'] ;
    }

    public function validate($data)
    {
/*
            if (false == is_numeric($this->items['RECORD_ID'])) {
                return false;
            }
*/
                return true;
    }
}
