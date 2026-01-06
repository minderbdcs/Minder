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
 * Term map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Term {
    public $name;
    public $dateDriven;
    public $daysUntilNetDue;
    public $discountPercent;
    public $daysUntilExpiry;
    public $dayOfMonthNetDue;
    public $dueNextMonthIfWithinDays;
    public $discountPercentDateDriven;
    public $dayDiscountExpires;
    public $preferred;
    public $isInactive;
    public $internalId;
    public $externalId;

    public function __construct(  $name, $dateDriven, $daysUntilNetDue, $discountPercent, $daysUntilExpiry, $dayOfMonthNetDue, $dueNextMonthIfWithinDays, $discountPercentDateDriven, $dayDiscountExpires, $preferred, $isInactive, $internalId, $externalId) {
        $this->name = $name;
        $this->dateDriven = $dateDriven;
        $this->daysUntilNetDue = $daysUntilNetDue;
        $this->discountPercent = $discountPercent;
        $this->daysUntilExpiry = $daysUntilExpiry;
        $this->dayOfMonthNetDue = $dayOfMonthNetDue;
        $this->dueNextMonthIfWithinDays = $dueNextMonthIfWithinDays;
        $this->discountPercentDateDriven = $discountPercentDateDriven;
        $this->dayDiscountExpires = $dayDiscountExpires;
        $this->preferred = $preferred;
        $this->isInactive = $isInactive;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>