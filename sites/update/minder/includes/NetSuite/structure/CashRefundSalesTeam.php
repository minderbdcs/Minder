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
 * CashRefundSalesTeam map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CashRefundSalesTeam {
    public $employee; //NetSuite_RecordRef
    public $salesRole; //NetSuite_RecordRef
    public $isPrimary;
    public $contribution;

    public function __construct(  NetSuite_RecordRef $employee, NetSuite_RecordRef $salesRole, $isPrimary, $contribution) {
        $this->employee = $employee;
        $this->salesRole = $salesRole;
        $this->isPrimary = $isPrimary;
        $this->contribution = $contribution;
    }
}?>