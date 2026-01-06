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
 * UnitsTypeUom map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_UnitsTypeUom {
    public $name;
    public $pluralName;
    public $abbreviation;
    public $pluralAbbreviation;
    public $conversionRate;
    public $baseUnit;

    public function __construct(  $name, $pluralName, $abbreviation, $pluralAbbreviation, $conversionRate, $baseUnit) {
        $this->name = $name;
        $this->pluralName = $pluralName;
        $this->abbreviation = $abbreviation;
        $this->pluralAbbreviation = $pluralAbbreviation;
        $this->conversionRate = $conversionRate;
        $this->baseUnit = $baseUnit;
    }
}?>