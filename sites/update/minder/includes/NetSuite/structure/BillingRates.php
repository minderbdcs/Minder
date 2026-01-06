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
 * BillingRates map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_BillingRates {
    public $currency; //NetSuite_RecordRef
    public $billingClass; //NetSuite_RecordRef
    public $rateList; //NetSuite_RateList

    public function __construct(  NetSuite_RecordRef $currency, NetSuite_RecordRef $billingClass, NetSuite_RateList $rateList) {
        $this->currency = $currency;
        $this->billingClass = $billingClass;
        $this->rateList = $rateList;
    }
}?>