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
 * SSN
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
class SsnLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['SSN_ID'] = '';
            $this->items['SUPPLIER_ID'] = '';
            $this->items['SSN_DESCRIPTION'] = '';
            $this->items['GRN'] = '';
            $this->items['PO_ORDER'] = '';
            $this->items['PO_LINE'] = '';
            $this->items['PURCHASE_PRICE'] = '';
            $this->items['SSN_TYPE'] = '';
            $this->items['OTHER1'] = '';
            $this->items['OTHER2'] = '';
            $this->items['OTHER3'] = '';
            $this->items['OTHER4'] = '';
            $this->items['OTHER5'] = '';
            $this->items['OTHER6'] = '';
            $this->items['OTHER7'] = '';
            $this->items['OTHER8'] = '';
            $this->items['OTHER9'] = '';
            $this->items['OTHER10'] = '';
            $this->items['OTHER11'] = '';
            $this->items['OTHER12'] = '';
            $this->items['OTHER13'] = '';
            $this->items['OTHER14'] = '';
            $this->items['OTHER15_DATE'] = '';
            $this->items['OTHER16_DATE'] = '';
            $this->items['OTHER17_QTY'] = '';
            $this->items['OTHER18_QTY'] = '';
            $this->items['OTHER19'] = '';
            $this->items['OTHER20'] = '';
            $this->items['HOME_LOCN_ID'] = '';
            $this->items['LABEL_LOCN'] = '';
            $this->items['LEASED'] = '';
            $this->items['LEASOR'] = '';
            $this->items['LEASE_EXPIRY_DATE'] = '';
            $this->items['LOAN_STATUS'] = '';
            $this->items['LOAN_PERIOD_NO'] = '';
            $this->items['LOAN_PERIOD'] = '';
            $this->items['LOAN_SAFETY_CHECK'] = '';
            $this->items['LOAN_LAST_SAFETY_CHECK_DATE'] = '';
            $this->items['LOAN_SAFETY_PERIOD_NO'] = '';
            $this->items['LOAN_SAFETY_PERIOD'] = '';
            $this->items['LOAN_CALIBRATE_CHECK'] = '';
            $this->items['LOAN_LAST_CALIBRATE_CHECK_DATE'] = '';
            $this->items['LOAN_CALIBRATE_PERIOD_NO'] = '';
            $this->items['LOAN_CALIBRATE_PERIOD'] = '';
            $this->items['LOAN_COST_BASE'] = '';
            $this->items['LICENCE_NUMBER'] = '';
            $this->items['VERSION'] = '';
            $this->items['IP_ADDRESS'] = '';
            $this->items['WARRANTY_EXPIRY_DATE'] = '';
            $this->items['DISPOSED'] = '';
            $this->items['DISPOSAL_COST'] = '';
            $this->items['DISPOSAL_PRICE'] = '';
            $this->items['DISPOSAL_DATE'] = '';
            $this->items['DISPOSAL_NOTES'] = '';
            $this->items['SSN_SUB_TYPE'] = '';
            $this->items['GENERIC'] = '';
            $this->items['BRAND'] = '';
            $this->items['MODEL'] = '';
            $this->items['ALT_NAME'] = '';
            $this->items['STATUS_CODE'] = '';
            $this->items['PARENT_SSN_ID'] = '';
            $this->items['PROD_ID'] = '';
            $this->items['NET_WEIGHT'] = '';
            $this->items['NOTES'] = '';
            $this->items['LAST_UPDATE_BY'] = '';
            $this->items['LAST_UPDATE_DATE'] = '';
            $this->items['CREATED_BY'] = '';
            $this->items['CREATE_DATE'] = '';
            $this->items['LOCN_ID'] = '';
            $this->items['WH_ID'] = '';
            $this->items['PREV_LOCN_ID'] = '';
            $this->items['PREV_WH_ID'] = '';
            $this->items['PRODUCT'] = '';
            $this->items['RETICULATION'] = '';
            $this->items['OLD_SSN_ID'] = '';
            $this->items['LEGACY_ID'] = '';
            $this->items['SERIAL_NUMBER'] = '';
            $this->items['ORIGINAL_QTY'] = '';
            $this->items['PURCHASE_QTY'] = '';
            $this->items['DEPARTMENT_ID'] = '';
            $this->items['DIVISION_ID'] = '';
            $this->items['COMPANY_ID'] = '';
            $this->items['COST_CENTER'] = '';
            $this->items['STATUS_SSN'] = '';
            $this->items['CURRENT_QTY'] = '';
            $this->items['HAZARD_WARNING'] = '';
            $this->items['HAZARD_STATUS'] = '';
            $this->items['LOAN_SAFETY_TAG_COLOUR'] = '';
            $this->items['HAZARD_IMAGE1'] = '';
            $this->items['HAZARD_IMAGE2'] = '';
            $this->items['HAZARD_IMAGE3'] = '';
            $this->items['DIMENSION_X'] = '';
            $this->items['DIMENSION_Z'] = '';
            $this->items['DIMENSION_Y'] = '';
            $this->items['DIMENSION_UOM'] = '';
            $this->items['NET_WEIGHT_UOM'] = '';
            $this->items['NET_WEIGHT'] = '';
            $this->items['OBJECT_NUMBER'] = '';
            $this->items['FILE_NUMBER'] = '';
            $this->items['OBJECT_NAME'] = '';
            $this->items['ACCESSION'] = '';
            $this->items['COLLECTION_NAME'] = '';
            $this->items['COLLECTION_TYPE'] = '';
            $this->items['COLLECTION_TITLE'] = '';
            $this->items['SSN_PURCHASE_PRICE'] = '';
            $this->items['LOAN_INSPECT_CHECK'] = '';
            $this->items['LOAN_LAST_INSPECT_CHECK_DATE'] = '';
            $this->items['LOAN_INSPECT_PERIOD_NO'] = '';
            $this->items['LOAN_INSPECT_PERIOD'] = '';
            $this->items['LOAN_INSPECT_NOTES'] = '';
            $this->items['BEST_BEFORE_DATE'] = '';
            $this->items['LOT_BATCH_NO'] = '';
            $this->items['USE_BY_DATE'] = '';

        }
    }


}