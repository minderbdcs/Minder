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
 * ContactAddressbook map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ContactAddressbook {
    public $defaultShipping;
    public $defaultBilling;
    public $label;
    public $attention;
    public $addressee;
    public $phone;
    public $addr1;
    public $addr2;
    public $city;
    public $zip;
    public $country;
    public $addrText;
    public $override;
    public $internalId;
    public $state;

    public function __construct(  $defaultShipping, $defaultBilling, $label, $attention, $addressee, $phone, $addr1, $addr2, $city, $zip, $country, $addrText, $override, $internalId, $state) {
        $this->defaultShipping = $defaultShipping;
        $this->defaultBilling = $defaultBilling;
        $this->label = $label;
        $this->attention = $attention;
        $this->addressee = $addressee;
        $this->phone = $phone;
        $this->addr1 = $addr1;
        $this->addr2 = $addr2;
        $this->city = $city;
        $this->zip = $zip;
        $this->country = $country;
        $this->addrText = $addrText;
        $this->override = $override;
        $this->internalId = $internalId;
        $this->state = $state;
    }
}?>