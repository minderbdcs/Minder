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
 * Pricing map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Pricing {
    public $currency; //NetSuite_RecordRef
    public $priceLevel; //NetSuite_RecordRef
    public $discount;
    public $priceList; //NetSuite_PriceList

    public function __construct(  NetSuite_RecordRef $currency, NetSuite_RecordRef $priceLevel, $discount, NetSuite_PriceList $priceList) {
        $this->currency = $currency;
        $this->priceLevel = $priceLevel;
        $this->discount = $discount;
        $this->priceList = $priceList;
    }
}?>