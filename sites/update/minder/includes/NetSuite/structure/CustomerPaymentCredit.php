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
 * CustomerPaymentCredit map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerPaymentCredit {
    public $apply;
    public $doc;
    public $date;
    public $type;
    public $refNum;
    public $total;
    public $due;
    public $currency;
    public $amount;

    public function __construct(  $apply, $doc, $date, $type, $refNum, $total, $due, $currency, $amount) {
        $this->apply = $apply;
        $this->doc = $doc;
        $this->date = $date;
        $this->type = $type;
        $this->refNum = $refNum;
        $this->total = $total;
        $this->due = $due;
        $this->currency = $currency;
        $this->amount = $amount;
    }
}?>