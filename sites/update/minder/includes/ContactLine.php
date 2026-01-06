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
 * Contacts
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
class ContactLine extends ModelCollection
{
    public function __construct()
    {
        if (func_num_args()>0) {
            $this->items = func_get_arg(0);
        } else {
            $this->items['PERSON_ID'] = '';
            $this->items['PERSON_TYPE'] = '';
            $this->items['ADDRESS_LINE2'] = '';
            $this->items['CITY'] = '';
            $this->items['STATE'] = '';
            $this->items['POST_CODE'] = '';
            $this->items['COUNTRY'] = '';
            $this->items['MOBILE_NO'] = '';
            $this->items['EMAIL'] = '';
            $this->items['CONTACT_FIRST_NAME'] = '';
            $this->items['CONTACT_LAST_NAME'] = '';
            $this->items['CREATE_DATE'] = '';
            $this->items['STATUS'] = '';
            $this->items['MAIL_ADDRESS1'] = '';
            $this->items['MAIL_ADDRESS2'] = '';
            $this->items['MAIL_CITY'] = '';
            $this->items['MAIL_STATE'] = '';
            $this->items['MAIL_POST_CODE'] = '';
            $this->items['MAIL_COUNTRY'] = '';
            $this->items['SECOND_CONTACT_FIRST_NAME'] = '';
            $this->items['SECOND_CONTACT_LAST_NAME'] = '';
            $this->items['SECOND_MOBILE_NO'] = '';
            $this->items['SECOND_EMAIL'] = '';
            $this->items['LAST_NAME'] = '';
            $this->items['SECOND_FAX_NO'] = '';
            $this->items['SECOND_PHONE_NO'] = '';
            $this->items['FAX_NO'] = '';
            $this->items['PHONE_NO'] = '';
            $this->items['TITLE'] = '';
            $this->items['MAIL_PHONE'] = '';
            $this->items['CONTACT_ADDRESS1'] = '';
            $this->items['CONTACT_ADDRESS2'] = '';
            $this->items['CONTACT_CITY'] = '';
            $this->items['CONTACT_STATE'] = '';
            $this->items['CONTACT_POST_CODE'] = '';
            $this->items['CONTACT_PHONE'] = '';
            $this->items['CREATE_BY'] = '';
            $this->items['CONTACT_COUNTRY'] = '';
            $this->items['FIRST_NAME'] = '';
            $this->items['ADDRESS_LINE1'] = '';
            $this->items['ADDRESS_LINE3'] = '';
            $this->items['ADDRESS_LINE4'] = '';
            $this->items['AUST_POST_4STATE_ID'] = '';
            $this->items['ADDRESS_LINE5'] = '';
            $this->items['WEB_SITE'] = '';
            $this->items['COMPANY_ID'] = '';
        }
    }

}
