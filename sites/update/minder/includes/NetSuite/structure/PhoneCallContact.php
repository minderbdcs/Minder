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
 * PhoneCallContact map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PhoneCallContact {
    public $company; //NetSuite_RecordRef
    public $contact; //NetSuite_RecordRef
    public $phone;
    public $email;

    public function __construct(  NetSuite_RecordRef $company, NetSuite_RecordRef $contact, $phone, $email) {
        $this->company = $company;
        $this->contact = $contact;
        $this->phone = $phone;
        $this->email = $email;
    }
}?>