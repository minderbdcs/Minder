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
 * TaxGroup map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TaxGroup {
    public $itemId;
    public $description;
    public $state;
    public $taxitem1; //NetSuite_RecordRef
    public $unitprice1;
    public $taxitem2; //NetSuite_RecordRef
    public $unitprice2;
    public $piggyback;
    public $isInactive;
    public $rate;
    public $taxType; //NetSuite_RecordRef
    public $county;
    public $city;
    public $zip;
    public $isDefault;
    public $taxItemList; //NetSuite_TaxGroupTaxItemList
    public $internalId;
    public $externalId;

    public function __construct(  $itemId, $description, $state, NetSuite_RecordRef $taxitem1, $unitprice1, NetSuite_RecordRef $taxitem2, $unitprice2, $piggyback, $isInactive, $rate, NetSuite_RecordRef $taxType, $county, $city, $zip, $isDefault, NetSuite_TaxGroupTaxItemList $taxItemList, $internalId, $externalId) {
        $this->itemId = $itemId;
        $this->description = $description;
        $this->state = $state;
        $this->taxitem1 = $taxitem1;
        $this->unitprice1 = $unitprice1;
        $this->taxitem2 = $taxitem2;
        $this->unitprice2 = $unitprice2;
        $this->piggyback = $piggyback;
        $this->isInactive = $isInactive;
        $this->rate = $rate;
        $this->taxType = $taxType;
        $this->county = $county;
        $this->city = $city;
        $this->zip = $zip;
        $this->isDefault = $isDefault;
        $this->taxItemList = $taxItemList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>