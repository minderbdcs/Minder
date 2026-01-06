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
 * CustomerCreditCards map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerCreditCards {
    public $internalId;
    public $ccNumber;
    public $ccExpireDate;
    public $ccName;
    public $paymentMethod; //NetSuite_RecordRef
    public $ccDefault;

    public function __construct(  $internalId, $ccNumber, $ccExpireDate, $ccName, NetSuite_RecordRef $paymentMethod, $ccDefault) {
        $this->internalId = $internalId;
        $this->ccNumber = $ccNumber;
        $this->ccExpireDate = $ccExpireDate;
        $this->ccName = $ccName;
        $this->paymentMethod = $paymentMethod;
        $this->ccDefault = $ccDefault;
    }
}?>