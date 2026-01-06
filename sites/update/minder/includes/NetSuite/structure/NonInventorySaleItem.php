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
 * NonInventorySaleItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_NonInventorySaleItem {
    public $createdDate;
    public $lastModifiedDate;
    public $salesDescription;
    public $incomeAccount; //NetSuite_RecordRef
    public $isTaxable;
    public $shippingCost;
    public $shippingCostUnits;
    public $handlingCost;
    public $handlingCostUnits;
    public $weight;
    public $weightUnit;
    public $weightUnits;
    public $unitsType; //NetSuite_RecordRef
    public $saleUnit; //NetSuite_RecordRef
    public $billingSchedule; //NetSuite_RecordRef
    public $deferredRevenueAccount; //NetSuite_RecordRef
    public $revRecSchedule; //NetSuite_RecordRef
    public $stockDescription;
    public $producer;
    public $manufacturer;
    public $mpn;
    public $manufacturerAddr1;
    public $manufacturerCity;
    public $manufacturerState;
    public $manufacturerZip;
    public $countryOfManufacture;
    public $manufacturerTaxId;
    public $scheduleBNumber;
    public $scheduleBQuantity;
    public $scheduleBCode;
    public $manufacturerTariff;
    public $preferenceCriterion;
    public $minimumQuantity;
    public $enforceMinQtyInternally;
    public $quantityPricingSchedule; //NetSuite_RecordRef
    public $useMarginalRates;
    public $overallQuantityPricingType;
    public $pricingGroup; //NetSuite_RecordRef
    public $minimumQuantityUnits;
    public $storeDisplayName;
    public $storeDisplayThumbnail; //NetSuite_RecordRef
    public $storeDisplayImage; //NetSuite_RecordRef
    public $storeDescription;
    public $storeDetailedDescription;
    public $storeItemTemplate; //NetSuite_RecordRef
    public $pageTitle;
    public $metaTagHtml;
    public $searchKeywords;
    public $isDonationItem;
    public $showDefaultDonationAmount;
    public $maxDonationAmount;
    public $onSpecial;
    public $relatedItemsDescription;
    public $specialsDescription;
    public $isFeatured;
    public $featuredDescription;
    public $shoppingDotComCategory;
    public $shopzillaCategoryId;
    public $nexTagCategory;
    public $productFeed;
    public $customForm; //NetSuite_RecordRef
    public $itemId;
    public $nameIsUPC;
    public $displayName;
    public $parent; //NetSuite_RecordRef
    public $isOnline;
    public $offerSupport;
    public $isInactive;
    public $availableToPartners;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $itemOptionsList; //NetSuite_ItemOptionsList
    public $pricingMatrix; //NetSuite_PricingMatrix
    public $purchaseTaxCode; //NetSuite_RecordRef
    public $rate;
    public $salesTaxCode; //NetSuite_RecordRef
    public $siteCategoryList; //NetSuite_SiteCategoryList
    public $translation; //NetSuite_TranslationList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, $salesDescription, NetSuite_RecordRef $incomeAccount, $isTaxable, $shippingCost, $shippingCostUnits, $handlingCost, $handlingCostUnits, $weight, $weightUnit, $weightUnits, NetSuite_RecordRef $unitsType, NetSuite_RecordRef $saleUnit, NetSuite_RecordRef $billingSchedule, NetSuite_RecordRef $deferredRevenueAccount, NetSuite_RecordRef $revRecSchedule, $stockDescription, $producer, $manufacturer, $mpn, $manufacturerAddr1, $manufacturerCity, $manufacturerState, $manufacturerZip, $countryOfManufacture, $manufacturerTaxId, $scheduleBNumber, $scheduleBQuantity, $scheduleBCode, $manufacturerTariff, $preferenceCriterion, $minimumQuantity, $enforceMinQtyInternally, NetSuite_RecordRef $quantityPricingSchedule, $useMarginalRates, $overallQuantityPricingType, NetSuite_RecordRef $pricingGroup, $minimumQuantityUnits, $storeDisplayName, NetSuite_RecordRef $storeDisplayThumbnail, NetSuite_RecordRef $storeDisplayImage, $storeDescription, $storeDetailedDescription, NetSuite_RecordRef $storeItemTemplate, $pageTitle, $metaTagHtml, $searchKeywords, $isDonationItem, $showDefaultDonationAmount, $maxDonationAmount, $onSpecial, $relatedItemsDescription, $specialsDescription, $isFeatured, $featuredDescription, $shoppingDotComCategory, $shopzillaCategoryId, $nexTagCategory, $productFeed, NetSuite_RecordRef $customForm, $itemId, $nameIsUPC, $displayName, NetSuite_RecordRef $parent, $isOnline, $offerSupport, $isInactive, $availableToPartners, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_ItemOptionsList $itemOptionsList, NetSuite_PricingMatrix $pricingMatrix, NetSuite_RecordRef $purchaseTaxCode, $rate, NetSuite_RecordRef $salesTaxCode, NetSuite_SiteCategoryList $siteCategoryList, NetSuite_TranslationList $translation, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->salesDescription = $salesDescription;
        $this->incomeAccount = $incomeAccount;
        $this->isTaxable = $isTaxable;
        $this->shippingCost = $shippingCost;
        $this->shippingCostUnits = $shippingCostUnits;
        $this->handlingCost = $handlingCost;
        $this->handlingCostUnits = $handlingCostUnits;
        $this->weight = $weight;
        $this->weightUnit = $weightUnit;
        $this->weightUnits = $weightUnits;
        $this->unitsType = $unitsType;
        $this->saleUnit = $saleUnit;
        $this->billingSchedule = $billingSchedule;
        $this->deferredRevenueAccount = $deferredRevenueAccount;
        $this->revRecSchedule = $revRecSchedule;
        $this->stockDescription = $stockDescription;
        $this->producer = $producer;
        $this->manufacturer = $manufacturer;
        $this->mpn = $mpn;
        $this->manufacturerAddr1 = $manufacturerAddr1;
        $this->manufacturerCity = $manufacturerCity;
        $this->manufacturerState = $manufacturerState;
        $this->manufacturerZip = $manufacturerZip;
        $this->countryOfManufacture = $countryOfManufacture;
        $this->manufacturerTaxId = $manufacturerTaxId;
        $this->scheduleBNumber = $scheduleBNumber;
        $this->scheduleBQuantity = $scheduleBQuantity;
        $this->scheduleBCode = $scheduleBCode;
        $this->manufacturerTariff = $manufacturerTariff;
        $this->preferenceCriterion = $preferenceCriterion;
        $this->minimumQuantity = $minimumQuantity;
        $this->enforceMinQtyInternally = $enforceMinQtyInternally;
        $this->quantityPricingSchedule = $quantityPricingSchedule;
        $this->useMarginalRates = $useMarginalRates;
        $this->overallQuantityPricingType = $overallQuantityPricingType;
        $this->pricingGroup = $pricingGroup;
        $this->minimumQuantityUnits = $minimumQuantityUnits;
        $this->storeDisplayName = $storeDisplayName;
        $this->storeDisplayThumbnail = $storeDisplayThumbnail;
        $this->storeDisplayImage = $storeDisplayImage;
        $this->storeDescription = $storeDescription;
        $this->storeDetailedDescription = $storeDetailedDescription;
        $this->storeItemTemplate = $storeItemTemplate;
        $this->pageTitle = $pageTitle;
        $this->metaTagHtml = $metaTagHtml;
        $this->searchKeywords = $searchKeywords;
        $this->isDonationItem = $isDonationItem;
        $this->showDefaultDonationAmount = $showDefaultDonationAmount;
        $this->maxDonationAmount = $maxDonationAmount;
        $this->onSpecial = $onSpecial;
        $this->relatedItemsDescription = $relatedItemsDescription;
        $this->specialsDescription = $specialsDescription;
        $this->isFeatured = $isFeatured;
        $this->featuredDescription = $featuredDescription;
        $this->shoppingDotComCategory = $shoppingDotComCategory;
        $this->shopzillaCategoryId = $shopzillaCategoryId;
        $this->nexTagCategory = $nexTagCategory;
        $this->productFeed = $productFeed;
        $this->customForm = $customForm;
        $this->itemId = $itemId;
        $this->nameIsUPC = $nameIsUPC;
        $this->displayName = $displayName;
        $this->parent = $parent;
        $this->isOnline = $isOnline;
        $this->offerSupport = $offerSupport;
        $this->isInactive = $isInactive;
        $this->availableToPartners = $availableToPartners;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->itemOptionsList = $itemOptionsList;
        $this->pricingMatrix = $pricingMatrix;
        $this->purchaseTaxCode = $purchaseTaxCode;
        $this->rate = $rate;
        $this->salesTaxCode = $salesTaxCode;
        $this->siteCategoryList = $siteCategoryList;
        $this->translation = $translation;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>