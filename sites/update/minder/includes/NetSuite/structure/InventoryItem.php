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
 * InventoryItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InventoryItem {
    public $createdDate;
    public $lastModifiedDate;
    public $purchaseDescription;
    public $copyDescription;
    public $cogsAccount; //NetSuite_RecordRef
    public $salesDescription;
    public $incomeAccount; //NetSuite_RecordRef
    public $isTaxable;
    public $assetAccount; //NetSuite_RecordRef
    public $shippingCost;
    public $shippingCostUnits;
    public $handlingCost;
    public $handlingCostUnits;
    public $weight;
    public $weightUnit;
    public $weightUnits;
    public $costingMethodDisplay;
    public $unitsType; //NetSuite_RecordRef
    public $stockUnit; //NetSuite_RecordRef
    public $purchaseUnit; //NetSuite_RecordRef
    public $saleUnit; //NetSuite_RecordRef
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
    public $totalValue;
    public $useBins;
    public $quantityReorderUnits;
    public $leadTime;
    public $autoLeadTime;
    public $autoPreferredStockLevel;
    public $preferredStockLevelDays;
    public $safetyStockLevel;
    public $safetyStockLevelDays;
    public $seasonalDemand;
    public $demandModifier;
    public $autoReorderPoint;
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
    public $vendorName;
    public $parent; //NetSuite_RecordRef
    public $isOnline;
    public $offerSupport;
    public $isInactive;
    public $availableToPartners;
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $costingMethod;
    public $currency;
    public $itemOptionsList; //NetSuite_ItemOptionsList
    public $itemVendorList; //NetSuite_ItemVendorList
    public $preferredStockLevel;
    public $pricingMatrix; //NetSuite_PricingMatrix
    public $purchaseTaxCode; //NetSuite_RecordRef
    public $quantityBackOrdered;
    public $quantityCommitted;
    public $quantityOnHand;
    public $quantityOnOrder;
    public $rate;
    public $reorderPoint;
    public $salesTaxCode; //NetSuite_RecordRef
    public $siteCategoryList; //NetSuite_SiteCategoryList
    public $translation; //NetSuite_TranslationList
    public $vendor; //NetSuite_RecordRef
    public $binNumberList; //NetSuite_InventoryItemBinNumberList
    public $locationsList; //NetSuite_InventoryItemLocationsList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, $purchaseDescription, $copyDescription, NetSuite_RecordRef $cogsAccount, $salesDescription, NetSuite_RecordRef $incomeAccount, $isTaxable, NetSuite_RecordRef $assetAccount, $shippingCost, $shippingCostUnits, $handlingCost, $handlingCostUnits, $weight, $weightUnit, $weightUnits, $costingMethodDisplay, NetSuite_RecordRef $unitsType, NetSuite_RecordRef $stockUnit, NetSuite_RecordRef $purchaseUnit, NetSuite_RecordRef $saleUnit, NetSuite_RecordRef $billingSchedule, $trackLandedCost, $isDropShipItem, $isSpecialOrderItem, $stockDescription, NetSuite_RecordRef $deferredRevenueAccount, $producer, $manufacturer, NetSuite_RecordRef $revRecSchedule, $mpn, $manufacturerAddr1, $manufacturerCity, $manufacturerState, $manufacturerZip, $countryOfManufacture, $manufacturerTaxId, $scheduleBNumber, $scheduleBQuantity, $scheduleBCode, $manufacturerTariff, $preferenceCriterion, $minimumQuantity, $enforceMinQtyInternally, NetSuite_RecordRef $quantityPricingSchedule, $useMarginalRates, $overallQuantityPricingType, NetSuite_RecordRef $pricingGroup, NetSuite_RecordRef $preferredLocation, $cost, $costUnits, $totalValue, $useBins, $quantityReorderUnits, $leadTime, $autoLeadTime, $autoPreferredStockLevel, $preferredStockLevelDays, $safetyStockLevel, $safetyStockLevelDays, $seasonalDemand, $demandModifier, $autoReorderPoint, $storeDisplayName, NetSuite_RecordRef $storeDisplayThumbnail, NetSuite_RecordRef $storeDisplayImage, $storeDescription, $storeDetailedDescription, NetSuite_RecordRef $storeItemTemplate, $pageTitle, $metaTagHtml, $searchKeywords, $isDonationItem, $showDefaultDonationAmount, $maxDonationAmount, $onSpecial, $relatedItemsDescription, $specialsDescription, $isFeatured, $featuredDescription, $shoppingDotComCategory, $shopzillaCategoryId, $nexTagCategory, $productFeed, NetSuite_RecordRef $customForm, $itemId, $nameIsUPC, $displayName, $vendorName, NetSuite_RecordRef $parent, $isOnline, $offerSupport, $isInactive, $availableToPartners, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $costingMethod, $currency, NetSuite_ItemOptionsList $itemOptionsList, NetSuite_ItemVendorList $itemVendorList, $preferredStockLevel, NetSuite_PricingMatrix $pricingMatrix, NetSuite_RecordRef $purchaseTaxCode, $quantityBackOrdered, $quantityCommitted, $quantityOnHand, $quantityOnOrder, $rate, $reorderPoint, NetSuite_RecordRef $salesTaxCode, NetSuite_SiteCategoryList $siteCategoryList, NetSuite_TranslationList $translation, NetSuite_RecordRef $vendor, NetSuite_InventoryItemBinNumberList $binNumberList, NetSuite_InventoryItemLocationsList $locationsList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->purchaseDescription = $purchaseDescription;
        $this->copyDescription = $copyDescription;
        $this->cogsAccount = $cogsAccount;
        $this->salesDescription = $salesDescription;
        $this->incomeAccount = $incomeAccount;
        $this->isTaxable = $isTaxable;
        $this->assetAccount = $assetAccount;
        $this->shippingCost = $shippingCost;
        $this->shippingCostUnits = $shippingCostUnits;
        $this->handlingCost = $handlingCost;
        $this->handlingCostUnits = $handlingCostUnits;
        $this->weight = $weight;
        $this->weightUnit = $weightUnit;
        $this->weightUnits = $weightUnits;
        $this->costingMethodDisplay = $costingMethodDisplay;
        $this->unitsType = $unitsType;
        $this->stockUnit = $stockUnit;
        $this->purchaseUnit = $purchaseUnit;
        $this->saleUnit = $saleUnit;
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
        $this->totalValue = $totalValue;
        $this->useBins = $useBins;
        $this->quantityReorderUnits = $quantityReorderUnits;
        $this->leadTime = $leadTime;
        $this->autoLeadTime = $autoLeadTime;
        $this->autoPreferredStockLevel = $autoPreferredStockLevel;
        $this->preferredStockLevelDays = $preferredStockLevelDays;
        $this->safetyStockLevel = $safetyStockLevel;
        $this->safetyStockLevelDays = $safetyStockLevelDays;
        $this->seasonalDemand = $seasonalDemand;
        $this->demandModifier = $demandModifier;
        $this->autoReorderPoint = $autoReorderPoint;
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
        $this->vendorName = $vendorName;
        $this->parent = $parent;
        $this->isOnline = $isOnline;
        $this->offerSupport = $offerSupport;
        $this->isInactive = $isInactive;
        $this->availableToPartners = $availableToPartners;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->costingMethod = $costingMethod;
        $this->currency = $currency;
        $this->itemOptionsList = $itemOptionsList;
        $this->itemVendorList = $itemVendorList;
        $this->preferredStockLevel = $preferredStockLevel;
        $this->pricingMatrix = $pricingMatrix;
        $this->purchaseTaxCode = $purchaseTaxCode;
        $this->quantityBackOrdered = $quantityBackOrdered;
        $this->quantityCommitted = $quantityCommitted;
        $this->quantityOnHand = $quantityOnHand;
        $this->quantityOnOrder = $quantityOnOrder;
        $this->rate = $rate;
        $this->reorderPoint = $reorderPoint;
        $this->salesTaxCode = $salesTaxCode;
        $this->siteCategoryList = $siteCategoryList;
        $this->translation = $translation;
        $this->vendor = $vendor;
        $this->binNumberList = $binNumberList;
        $this->locationsList = $locationsList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>