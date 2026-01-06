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
 * SiteCategory map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_SiteCategory {
    public $itemId;
    public $parentCategory; //NetSuite_RecordRef
    public $categoryListLayout; //NetSuite_RecordRef
    public $itemListLayout; //NetSuite_RecordRef
    public $relatedItemsListLayout; //NetSuite_RecordRef
    public $isOnline;
    public $isInactive;
    public $description;
    public $storeDetailedDescription;
    public $storeDisplayThumbnail; //NetSuite_RecordRef
    public $storeDisplayImage; //NetSuite_RecordRef
    public $pageTitle;
    public $metaTagHtml;
    public $searchKeywords;
    public $presentationItemList; //NetSuite_SiteCategoryPresentationItemList
    public $internalId;
    public $externalId;

    public function __construct(  $itemId, NetSuite_RecordRef $parentCategory, NetSuite_RecordRef $categoryListLayout, NetSuite_RecordRef $itemListLayout, NetSuite_RecordRef $relatedItemsListLayout, $isOnline, $isInactive, $description, $storeDetailedDescription, NetSuite_RecordRef $storeDisplayThumbnail, NetSuite_RecordRef $storeDisplayImage, $pageTitle, $metaTagHtml, $searchKeywords, NetSuite_SiteCategoryPresentationItemList $presentationItemList, $internalId, $externalId) {
        $this->itemId = $itemId;
        $this->parentCategory = $parentCategory;
        $this->categoryListLayout = $categoryListLayout;
        $this->itemListLayout = $itemListLayout;
        $this->relatedItemsListLayout = $relatedItemsListLayout;
        $this->isOnline = $isOnline;
        $this->isInactive = $isInactive;
        $this->description = $description;
        $this->storeDetailedDescription = $storeDetailedDescription;
        $this->storeDisplayThumbnail = $storeDisplayThumbnail;
        $this->storeDisplayImage = $storeDisplayImage;
        $this->pageTitle = $pageTitle;
        $this->metaTagHtml = $metaTagHtml;
        $this->searchKeywords = $searchKeywords;
        $this->presentationItemList = $presentationItemList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>