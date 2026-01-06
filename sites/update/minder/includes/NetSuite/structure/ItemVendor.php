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
 * ItemVendor map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemVendor {
    public $vendor; //NetSuite_RecordRef
    public $vendorCode;
    public $purchasePrice;
    public $preferredVendor;

    public function __construct(  NetSuite_RecordRef $vendor, $vendorCode, $purchasePrice, $preferredVendor) {
        $this->vendor = $vendor;
        $this->vendorCode = $vendorCode;
        $this->purchasePrice = $purchasePrice;
        $this->preferredVendor = $preferredVendor;
    }
}?>