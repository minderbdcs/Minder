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
 * LotNumberedInventoryItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_LotNumberedInventoryItem {
    public $createdDate;
    public $lastModifiedDate;
    public $customForm; //NetSuite_RecordRef
    public $itemId;
    public $nameIsUPC;
    public $displayName;
    public $vendorName;
    public $parent; //NetSuite_RecordRef
    public $isOnline;
    public $offerSupport;
    public $isInactive;
    public $availableToPartners;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $purchaseDescription;
    public $copyDescription;
    public $currency;
    public $cogsAccount; //NetSuite_RecordRef
    public $vendor; //NetSuite_RecordRef
    public $salesDescription;
    public $incomeAccount; //NetSuite_RecordRef
    public $isTaxable;
    public $assetAccount; //NetSuite_RecordRef
    public $shippingCost;
    public $handlingCost;
    public $weight;
    public $costingMethodDisplay;
    public $billingSchedule; //NetSuite_RecordRef
    public $trackLandedCost;
    public $isDropShipItem;
    public $isSpecialOrderItem;
    public $stockDescription;
    public $deferredRevenueAccount; //NetSuite_RecordRef
    public $producer;
    public $manufacturer;
    public $revRecSchedule; //NetSuite_RecordRef
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
    public $preferredLocation; //NetSuite_RecordRef
    public $cost;
    public $costUnits;
    public $quantityReorderUnits;
    public $totalValue;
    public $useBins;
    public $leadTime;
    public $autoLeadTime;
    public $autoReorderPoint;
    public $autoPreferredStockLevel;
    public $preferredStockLevelDays;
    public $safetyStockLevel;
    public $safetyStockLevelDays;
    public $seasonalDemand;
    public $demandModifier;
    public $storeDisplayName;
    public $storeDisplayThumbnail; //NetSuite_RecordRef
    public $weightUnit;
    public $weightUnits;
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
    public $shoppingDotComCategory;
    public $shopzillaCategoryId;
    public $nexTagCategory;
    public $quantityOnHand;
    public $expirationDate;
    public $onHandValueMli;
    public $serialNumbers;
    public $reorderPoint;
    public $preferredStockLevel;
    public $quantityOnOrder;
    public $quantityCommitted;
    public $quantityAvailable;
    public $quantityBackOrdered;
    public $purchaseTaxCode; //NetSuite_RecordRef
    public $rate;
    public $salesTaxCode; //NetSuite_RecordRef
    public $translation; //NetSuite_TranslationList
    public $onSpecial;
    public $specialsDescription;
    public $isFeatured;
    public $relatedItemsDescription;
    public $featuredDescription;
    public $productFeed;
    public $itemOptionsList; //NetSuite_ItemOptionsList
    public $itemVendorList; //NetSuite_ItemVendorList
    public $pricingMatrix; //NetSuite_PricingMatrix
    public $numbersList; //NetSuite_LotNumberedInventoryItemNumbersList
    public $binNumberList; //NetSuite_InventoryItemBinNumberList
    public $siteCategoryList; //NetSuite_SiteCategoryList
    public $locationsList; //NetSuite_LotNumberedInventoryItemLocationsList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $customForm, $itemId, $nameIsUPC, $displayName, $vendorName, NetSuite_RecordRef $parent, $isOnline, $offerSupport, $isInactive, $availableToPartners, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $purchaseDescription, $copyDescription, $currency, NetSuite_RecordRef $cogsAccount, NetSuite_RecordRef $vendor, $salesDescription, NetSuite_RecordRef $incomeAccount, $isTaxable, NetSuite_RecordRef $assetAccount, $shippingCost, $handlingCost, $weight, $costingMethodDisplay, NetSuite_RecordRef $billingSchedule, $trackLandedCost, $isDropShipItem, $isSpecialOrderItem, $stockDescription, NetSuite_RecordRef $deferredRevenueAccount, $producer, $manufacturer, NetSuite_RecordRef $revRecSchedule, $mpn, $manufacturerAddr1, $manufacturerCity, $manufacturerState, $manufacturerZip, $countryOfManufacture, $manufacturerTaxId, $scheduleBNumber, $scheduleBQuantity, $scheduleBCode, $manufacturerTariff, $preferenceCriterion, $minimumQuantity, $enforceMinQtyInternally, NetSuite_RecordRef $quantityPricingSchedule, $useMarginalRates, $overallQuantityPricingType, NetSuite_RecordRef $pricingGroup, NetSuite_RecordRef $preferredLocation, $cost, $costUnits, $quantityReorderUnits, $totalValue, $useBins, $leadTime, $autoLeadTime, $autoReorderPoint, $autoPreferredStockLevel, $preferredStockLevelDays, $safetyStockLevel, $safetyStockLevelDays, $seasonalDemand, $demandModifier, $storeDisplayName, NetSuite_RecordRef $storeDisplayThumbnail, $weightUnit, $weightUnits, NetSuite_RecordRef $storeDisplayImage, $storeDescription, $storeDetailedDescription, NetSuite_RecordRef $storeItemTemplate, $pageTitle, $metaTagHtml, $searchKeywords, $isDonationItem, $showDefaultDonationAmount, $maxDonationAmount, $shoppingDotComCategory, $shopzillaCategoryId, $nexTagCategory, $quantityOnHand, $expirationDate, $onHandValueMli, $serialNumbers, $reorderPoint, $preferredStockLevel, $quantityOnOrder, $quantityCommitted, $quantityAvailable, $quantityBackOrdered, NetSuite_RecordRef $purchaseTaxCode, $rate, NetSuite_RecordRef $salesTaxCode, NetSuite_TranslationList $translation, $onSpecial, $specialsDescription, $isFeatured, $relatedItemsDescription, $featuredDescription, $productFeed, NetSuite_ItemOptionsList $itemOptionsList, NetSuite_ItemVendorList $itemVendorList, NetSuite_PricingMatrix $pricingMatrix, NetSuite_LotNumberedInventoryItemNumbersList $numbersList, NetSuite_InventoryItemBinNumberList $binNumberList, NetSuite_SiteCategoryList $siteCategoryList, NetSuite_LotNumberedInventoryItemLocationsList $locationsList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->customForm = $customForm;
        $this->itemId = $itemId;
        $this->nameIsUPC = $nameIsUPC;
        $this->displayName = $displayName;
        $this->vendorName = $vendorName;
        $this->parent = $parent;
        $this->isOnline = $isOnline;
        $this->offerSupport = $offerSupport;
        $this->isInactive = $isInactive;
        $this->availableToPartners = $availableToPartners;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->purchaseDescription = $purchaseDescription;
        $this->copyDescription = $copyDescription;
        $this->currency = $currency;
        $this->cogsAccount = $cogsAccount;
        $this->vendor = $vendor;
        $this->salesDescription = $salesDescription;
        $this->incomeAccount = $incomeAccount;
        $this->isTaxable = $isTaxable;
        $this->assetAccount = $assetAccount;
        $this->shippingCost = $shippingCost;
        $this->handlingCost = $handlingCost;
        $this->weight = $weight;
        $this->costingMethodDisplay = $costingMethodDisplay;
        $this->billingSchedule = $billingSchedule;
        $this->trackLandedCost = $trackLandedCost;
        $this->isDropShipItem = $isDropShipItem;
        $this->isSpecialOrderItem = $isSpecialOrderItem;
        $this->stockDescription = $stockDescription;
        $this->deferredRevenueAccount = $deferredRevenueAccount;
        $this->producer = $producer;
        $this->manufacturer = $manufacturer;
        $this->revRecSchedule = $revRecSchedule;
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
        $this->preferredLocation = $preferredLocation;
        $this->cost = $cost;
        $this->costUnits = $costUnits;
        $this->quantityReorderUnits = $quantityReorderUnits;
        $this->totalValue = $totalValue;
        $this->useBins = $useBins;
        $this->leadTime = $leadTime;
        $this->autoLeadTime = $autoLeadTime;
        $this->autoReorderPoint = $autoReorderPoint;
        $this->autoPreferredStockLevel = $autoPreferredStockLevel;
        $this->preferredStockLevelDays = $preferredStockLevelDays;
        $this->safetyStockLevel = $safetyStockLevel;
        $this->safetyStockLevelDays = $safetyStockLevelDays;
        $this->seasonalDemand = $seasonalDemand;
        $this->demandModifier = $demandModifier;
        $this->storeDisplayName = $storeDisplayName;
        $this->storeDisplayThumbnail = $storeDisplayThumbnail;
        $this->weightUnit = $weightUnit;
        $this->weightUnits = $weightUnits;
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
        $this->shoppingDotComCategory = $shoppingDotComCategory;
        $this->shopzillaCategoryId = $shopzillaCategoryId;
        $this->nexTagCategory = $nexTagCategory;
        $this->quantityOnHand = $quantityOnHand;
        $this->expirationDate = $expirationDate;
        $this->onHandValueMli = $onHandValueMli;
        $this->serialNumbers = $serialNumbers;
        $this->reorderPoint = $reorderPoint;
        $this->preferredStockLevel = $preferredStockLevel;
        $this->quantityOnOrder = $quantityOnOrder;
        $this->quantityCommitted = $quantityCommitted;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityBackOrdered = $quantityBackOrdered;
        $this->purchaseTaxCode = $purchaseTaxCode;
        $this->rate = $rate;
        $this->salesTaxCode = $salesTaxCode;
        $this->translation = $translation;
        $this->onSpecial = $onSpecial;
        $this->specialsDescription = $specialsDescription;
        $this->isFeatured = $isFeatured;
        $this->relatedItemsDescription = $relatedItemsDescription;
        $this->featuredDescription = $featuredDescription;
        $this->productFeed = $productFeed;
        $this->itemOptionsList = $itemOptionsList;
        $this->itemVendorList = $itemVendorList;
        $this->pricingMatrix = $pricingMatrix;
        $this->numbersList = $numbersList;
        $this->binNumberList = $binNumberList;
        $this->siteCategoryList = $siteCategoryList;
        $this->locationsList = $locationsList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>