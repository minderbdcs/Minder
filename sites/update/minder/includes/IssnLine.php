<?php
/**
 * Minder
 *
 * PHP version 5.2.3
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
 * ISSN
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
class IssnLine extends ModelCollection
{

    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['SSN_ID']              = '';
            $this->items['WH_ID']               = '';
            $this->items['LOCN_ID']             = '';
            $this->items['PROD_ID']             = '';
            $this->items['CURRENT_QTY']         = '';
            $this->items['GRN_CREATE_DATE']     = '';
            $this->items['SSN_CREATE_DATE']     = '';
            $this->items['COMPANY_ID']          = '';
            $this->items['ISSN_STATUS']         = '';
            $this->items['SSN_DESCRIPTION']     = '';
            $this->items['OTHER1']              = '';
            $this->items['OTHER2']              = '';
            $this->items['OTHER3']              = '';
            $this->items['SSN_TYPE']            = '';
            $this->items['SUPPLIER_ID']         = '';
            $this->items['LAST_UPDATE_DATE']    = '';
            $this->items['PREV_PROD_ID']        = '';
            $this->items['PREV_PREV_PROD_ID']   = '';
            $this->items['PROD_ID_UPDATE']      = '';
            $this->items['PREV_PROD_ID_UPDATE'] = '';
            $this->items['PREV_WH_ID']          = '';
            $this->items['PREV_PREV_WH_ID']     = '';
            $this->items['PREV_LOCN_ID']        = '';
            $this->items['PREV_PREV_LOCN_ID']   = '';
            $this->items['PREV_DATE']           = '';
            $this->items['ORIGINAL_QTY']        = '';
            $this->items['PREV_QTY']            = '';
            $this->items['AUDIT_DATE']          = '';
            $this->items['LABEL_DATE']          = '';
            $this->items['USER_ID']             = '';
            $this->items['CREATE_DATE']         = '';
            $this->items['DESPATCHED_DATE']     = '';
            $this->items['DIVISION_ID']         = '';
            $this->items['KITTED']              = '';
            $this->items['INTO_DATE']           = '';
            $this->items['SERIAL_NUMBER']       = '';
            $this->items['ORIGINAL_SSN']        = '';
            $this->items['AUDITED']             = '';
            $this->items['PICK_ORDER']          = '';
            $this->items['STATUS_CODE']         = '';
            $this->items['ISSN_PACKAGE_TYPE']   = '';
            $this->items['PACK_ID']             = '';
            $this->items['DESPATCH_ID']         = '';
            $this->items['ISSN_PREV_PACKAGE_TYPE'] = '';
            $this->items['PO_ORDER']            = '';
            $this->items['PICK_LABEL_NO']       = '';
            $this->items['REASON']              = '';
        }
    }
}
