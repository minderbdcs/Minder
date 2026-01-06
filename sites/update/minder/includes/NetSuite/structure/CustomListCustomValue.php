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
 * CustomListCustomValue map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomListCustomValue {
    public $value;
    public $abbreviation;
    public $isInactive;
    public $valueId; //NetSuite_RecordRef

    public function __construct(  $value, $abbreviation, $isInactive, NetSuite_RecordRef $valueId) {
        $this->value = $value;
        $this->abbreviation = $abbreviation;
        $this->isInactive = $isInactive;
        $this->valueId = $valueId;
    }
}?>