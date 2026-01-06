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
 * CustomerPaymentApply map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerPaymentApply {
    public $apply;
    public $doc;
    public $date;
    public $job;
    public $type;
    public $refNum;
    public $total;
    public $due;
    public $currency;
    public $discDate;
    public $discAmt;
    public $disc;
    public $amount;

    public function __construct(  $apply, $doc, $date, $job, $type, $refNum, $total, $due, $currency, $discDate, $discAmt, $disc, $amount) {
        $this->apply = $apply;
        $this->doc = $doc;
        $this->date = $date;
        $this->job = $job;
        $this->type = $type;
        $this->refNum = $refNum;
        $this->total = $total;
        $this->due = $due;
        $this->currency = $currency;
        $this->discDate = $discDate;
        $this->discAmt = $discAmt;
        $this->disc = $disc;
        $this->amount = $amount;
    }
}?>