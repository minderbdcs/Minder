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
 * Address
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Address extends Model
{
    public $recordId;
    public $personId;
    public $type;
    public $firstName;
    public $lastName;
    public $line1;
    public $line2;
    public $suburb;
    public $city;
    public $state;
    public $postcode;
    public $country;
    public $phone;
    public $title;

    public function __construct() {
        $this->recordId = null;
        $this->personId = '';
        $this->type = '';
        $this->firstName = '';
        $this->lastName = '';
        $this->line1 = '';
        $this->line2 = '';
        $this->suburb = '';
        $this->city = '';
        $this->state = '';
        $this->postcode = '';
        $this->country = '';
        $this->phone = '';
        $this->title = '';
    }
}
