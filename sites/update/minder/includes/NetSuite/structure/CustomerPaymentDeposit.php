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
 * CustomerPaymentDeposit map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerPaymentDeposit {
    public $apply;
    public $doc;
    public $date;
    public $refNum;
    public $total;
    public $remaining;
    public $currency;
    public $amount;

    public function __construct(  $apply, $doc, $date, $refNum, $total, $remaining, $currency, $amount) {
        $this->apply = $apply;
        $this->doc = $doc;
        $this->date = $date;
        $this->refNum = $refNum;
        $this->total = $total;
        $this->remaining = $remaining;
        $this->currency = $currency;
        $this->amount = $amount;
    }
}?>