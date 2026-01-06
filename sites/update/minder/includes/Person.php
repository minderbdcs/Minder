<?php
/**
 * Minder
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Order
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Person extends Model
{
    public $personId;
    public $personType;
    public $firstName;
    public $lastName;
    public $mailAddress1;
    public $mailAddress2;
    public $mailCity;
    public $mailState;
    public $mailPostcode;
    public $mailCountry;
    public $contactAddress1;
    public $contactAddress2;
    public $contactCity;
    public $contactState;
    public $contactPostcode;
    public $contactCountry;
    public $telephone1;
    public $addressLine1;
    public $addressLine2;
    public $addressLine3;
    public $addressLine4;
    public $addressLine5;
    public $city;
    public $state;
    public $postcode;
    public $country;
    public $telephone;
    public $status;

    public function __construct() {
        $this->personId = '';
        $this->personType = '';
        $this->firstName = '';
        $this->lastName = '';
        $this->mailAddress1 = '';
        $this->mailAddress2 = '';
        $this->mailCity = '';
        $this->mailState = '';
        $this->mailPostcode = '';
        $this->mailCountry = '';
        $this->telephone1 = '';
        $this->contactAddress1 = '';
        $this->contactAddress2 = '';
        $this->contactCity = '';
        $this->contactState = '';
        $this->contactPostcode = '';
        $this->contactCountry = '';
        $this->addressLine1 = '';
        $this->addressLine2 = '';
        $this->addressLine3 = '';
        $this->addressLine4 = '';
        $this->addressLine5 = '';
        $this->city = '';
        $this->state = '';
        $this->postcode = '';
        $this->country = '';
        $this->telephone = '';
        $this->status = '';
    }
}
