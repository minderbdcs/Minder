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
 * Currency map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Currency {
    public $name;
    public $symbol;
    public $isInactive;
    public $isBaseCurrency;
    public $locale;
    public $formatSample;
    public $exchangeRate;
    public $currencyPrecision;
    public $internalId;
    public $externalId;

    public function __construct(  $name, $symbol, $isInactive, $isBaseCurrency, $locale, $formatSample, $exchangeRate, $currencyPrecision, $internalId, $externalId) {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->isInactive = $isInactive;
        $this->isBaseCurrency = $isBaseCurrency;
        $this->locale = $locale;
        $this->formatSample = $formatSample;
        $this->exchangeRate = $exchangeRate;
        $this->currencyPrecision = $currencyPrecision;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>