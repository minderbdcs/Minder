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
 * ExpenseCategory map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ExpenseCategory {
    public $name;
    public $description;
    public $expenseAcct; //NetSuite_RecordRef
    public $isInactive;
    public $internalId;
    public $externalId;

    public function __construct(  $name, $description, NetSuite_RecordRef $expenseAcct, $isInactive, $internalId, $externalId) {
        $this->name = $name;
        $this->description = $description;
        $this->expenseAcct = $expenseAcct;
        $this->isInactive = $isInactive;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>