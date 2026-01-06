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
 * TaxGroupTaxItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_TaxGroupTaxItem {
    public $taxName; //NetSuite_RecordRef
    public $rate;
    public $basis;
    public $taxType;

    public function __construct(  NetSuite_RecordRef $taxName, $rate, $basis, $taxType) {
        $this->taxName = $taxName;
        $this->rate = $rate;
        $this->basis = $basis;
        $this->taxType = $taxType;
    }
}?>