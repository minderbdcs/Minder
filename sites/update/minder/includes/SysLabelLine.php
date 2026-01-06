<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */
class SysLabelLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['RECORD_ID']        = '';
            $this->items['SL_NAME']          = '';
            $this->items['SL_SEQUENCE']      = '';
            $this->items['SL_LINE']          = '';
            $this->items['SL_IMAGE']         = '';
            $this->items['SL_BRAND']         = '';
            $this->items['SL_MODEL']         = '';
            $this->items['SL_FIRMWARE']      = '';
            $this->items['SL_NOTES']         = '';
            $this->items['LAST_UPDATE_DATE'] = '';
            $this->items['LAST_UPDATE_BY']   = '';
        }
    }
}
