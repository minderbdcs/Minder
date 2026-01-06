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
 * SiteCategoryPresentationItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SiteCategoryPresentationItem {
    public $item; //NetSuite_RecordRef
    public $itemType;
    public $description;
    public $onlinePrice;
    public $basePrice;

    public function __construct(  NetSuite_RecordRef $item, $itemType, $description, $onlinePrice, $basePrice) {
        $this->item = $item;
        $this->itemType = $itemType;
        $this->description = $description;
        $this->onlinePrice = $onlinePrice;
        $this->basePrice = $basePrice;
    }
}?>