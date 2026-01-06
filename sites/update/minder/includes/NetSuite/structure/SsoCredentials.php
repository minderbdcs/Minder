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
 * SsoCredentials map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SsoCredentials {
    public $email;
    public $password;
    public $account;
    public $role; //NetSuite_RecordRef
    public $authenticationToken;
    public $partnerId;

    public function __construct(  $email, $password, $account, NetSuite_RecordRef $role, $authenticationToken, $partnerId) {
        $this->email = $email;
        $this->password = $password;
        $this->account = $account;
        $this->role = $role;
        $this->authenticationToken = $authenticationToken;
        $this->partnerId = $partnerId;
    }
}?>