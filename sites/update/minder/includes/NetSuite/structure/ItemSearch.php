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
 * ItemSearch map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemSearch {
    public $basic; //NetSuite_ItemSearchBasic
    public $preferredVendorJoin; //NetSuite_VendorSearchBasic
    public $shopperJoin; //NetSuite_CustomerSearchBasic
    public $vendorJoin; //NetSuite_VendorSearchBasic

    public function __construct(  NetSuite_ItemSearchBasic $basic, NetSuite_VendorSearchBasic $preferredVendorJoin, NetSuite_CustomerSearchBasic $shopperJoin, NetSuite_VendorSearchBasic $vendorJoin) {
        $this->basic = $basic;
        $this->preferredVendorJoin = $preferredVendorJoin;
        $this->shopperJoin = $shopperJoin;
        $this->vendorJoin = $vendorJoin;
    }
}?>