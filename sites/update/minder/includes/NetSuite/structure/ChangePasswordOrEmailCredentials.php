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
 * ChangePasswordOrEmailCredentials map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ChangePasswordOrEmailCredentials {
    public $currentPassword;
    public $newEmail;
    public $newEmail2;
    public $newPassword;
    public $newPassword2;
    public $justThisAccount;

    public function __construct(  $currentPassword, $newEmail, $newEmail2, $newPassword, $newPassword2, $justThisAccount) {
        $this->currentPassword = $currentPassword;
        $this->newEmail = $newEmail;
        $this->newEmail2 = $newEmail2;
        $this->newPassword = $newPassword;
        $this->newPassword2 = $newPassword2;
        $this->justThisAccount = $justThisAccount;
    }
}?>