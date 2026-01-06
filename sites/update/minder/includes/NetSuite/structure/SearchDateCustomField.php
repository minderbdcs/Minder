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
 * SearchDateCustomField map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SearchDateCustomField {
    public $predefinedSearchValue;
    public $searchValue;
    public $searchValue2;
    public $internalId;
    public $operator;

    public function __construct(  $predefinedSearchValue, $searchValue, $searchValue2, $internalId, $operator) {
        $this->predefinedSearchValue = $predefinedSearchValue;
        $this->searchValue = $searchValue;
        $this->searchValue2 = $searchValue2;
        $this->internalId = $internalId;
        $this->operator = $operator;
    }
}?>