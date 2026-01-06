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
 * PaymentMethod map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PaymentMethod {
    public $name;
    public $creditCard;
    public $undepFunds;
    public $account; //NetSuite_RecordRef
    public $isInactive;
    public $isOnline;
    public $isDebitCard;
    public $internalId;
    public $externalId;

    public function __construct(  $name, $creditCard, $undepFunds, NetSuite_RecordRef $account, $isInactive, $isOnline, $isDebitCard, $internalId, $externalId) {
        $this->name = $name;
        $this->creditCard = $creditCard;
        $this->undepFunds = $undepFunds;
        $this->account = $account;
        $this->isInactive = $isInactive;
        $this->isOnline = $isOnline;
        $this->isDebitCard = $isDebitCard;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>