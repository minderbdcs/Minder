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
 * SiteCategorySearchBasic map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SiteCategorySearchBasic {
    public $dateViewed; //NetSuite_SearchDateField
    public $description; //NetSuite_SearchStringField
    public $externalId; //NetSuite_SearchMultiSelectField
    public $internalId; //NetSuite_SearchMultiSelectField
    public $name; //NetSuite_SearchMultiSelectField

    public function __construct(  NetSuite_SearchDateField $dateViewed, NetSuite_SearchStringField $description, NetSuite_SearchMultiSelectField $externalId, NetSuite_SearchMultiSelectField $internalId, NetSuite_SearchMultiSelectField $name) {
        $this->dateViewed = $dateViewed;
        $this->description = $description;
        $this->externalId = $externalId;
        $this->internalId = $internalId;
        $this->name = $name;
    }
}?>