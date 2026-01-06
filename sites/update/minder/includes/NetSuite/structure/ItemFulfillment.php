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
 * ItemFulfillment map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemFulfillment {
    public $createdDate;
    public $lastModifiedDate;
    public $postingPeriod; //NetSuite_RecordRef
    public $entity; //NetSuite_RecordRef
    public $createdFrom; //NetSuite_RecordRef
    public $shipAddressList; //NetSuite_RecordRef
    public $shipAddress;
    public $shipOverride;
    public $shipAttention;
    public $shipCompany;
    public $shipAddr1;
    public $shipAddr2;
    public $shipCity;
    public $shipState;
    public $shipZip;
    public $shipCountry;
    public $shipPhone;
    public $shipIsResidential;
    public $shipStatus;
    public $saturdayDeliveryUps;
    public $sendShipNotifyEmailUps;
    public $sendBackupEmailUps;
    public $shipNotifyEmailAddressUps;
    public $shipNotifyEmailAddress2Ups;
    public $backupEmailAddressUps;
    public $shipNotifyEmailMessageUps;
    public $thirdPartyAcctUps;
    public $thirdPartyZipcodeUps;
    public $thirdPartyCountryUps;
    public $thirdPartyTypeUps;
    public $partiesToTransactionUps;
    public $exportTypeUps;
    public $methodOfTransportUps;
    public $carrierIdUps;
    public $entryNumberUps;
    public $inbondCodeUps;
    public $isRoutedExportTransactionUps;
    public $licenseNumberUps;
    public $licenseDateUps;
    public $licenseExceptionUps;
    public $eccNumberUps;
    public $recipientTaxIdUps;
    public $blanketStartDateUps;
    public $blanketEndDateUps;
    public $shipmentWeightUps;
    public $saturdayDeliveryFedEx;
    public $saturdayPickupFedex;
    public $sendShipNotifyEmailFedEx;
    public $sendBackupEmailFedEx;
    public $signatureHomeDeliveryFedEx;
    public $shipNotifyEmailAddressFedEx;
    public $backupEmailAddressFedEx;
    public $shipDateFedEx;
    public $homeDeliveryTypeFedEx;
    public $homeDeliveryDateFedEx;
    public $bookingConfirmationNumFedEx;
    public $intlExemptionNumFedEx;
    public $b13aFilingOptionFedEx;
    public $b13aStatementDataFedEx;
    public $thirdPartyAcctFedEx;
    public $thirdPartyCountryFedEx;
    public $thirdPartyTypeFedEx;
    public $shipmentWeightFedEx;
    public $termsOfSaleFedEx;
    public $termsFreightChargeFedEx;
    public $termsInsuranceChargeFedEx;
    public $tranDate;
    public $tranId;
    public $shipMethod; //NetSuite_RecordRef
    public $generateIntegratedShipperLabel;
    public $shippingCost;
    public $shippingCostInForeignCurrency;
    public $handlingCost;
    public $handlingCostInForeignCurrency;
    public $packageList; //NetSuite_ItemFulfillmentPackageList
    public $packageUpsList; //NetSuite_ItemFulfillmentPackageUpsList
    public $packageFedExList; //NetSuite_ItemFulfillmentPackageFedExList
    public $itemList; //NetSuite_ItemFulfillmentItemList
    public $customFieldList; //NetSuite_CustomFieldList
    public $internalId;
    public $externalId;

    public function __construct(  $createdDate, $lastModifiedDate, NetSuite_RecordRef $postingPeriod, NetSuite_RecordRef $entity, NetSuite_RecordRef $createdFrom, NetSuite_RecordRef $shipAddressList, $shipAddress, $shipOverride, $shipAttention, $shipCompany, $shipAddr1, $shipAddr2, $shipCity, $shipState, $shipZip, $shipCountry, $shipPhone, $shipIsResidential, $shipStatus, $saturdayDeliveryUps, $sendShipNotifyEmailUps, $sendBackupEmailUps, $shipNotifyEmailAddressUps, $shipNotifyEmailAddress2Ups, $backupEmailAddressUps, $shipNotifyEmailMessageUps, $thirdPartyAcctUps, $thirdPartyZipcodeUps, $thirdPartyCountryUps, $thirdPartyTypeUps, $partiesToTransactionUps, $exportTypeUps, $methodOfTransportUps, $carrierIdUps, $entryNumberUps, $inbondCodeUps, $isRoutedExportTransactionUps, $licenseNumberUps, $licenseDateUps, $licenseExceptionUps, $eccNumberUps, $recipientTaxIdUps, $blanketStartDateUps, $blanketEndDateUps, $shipmentWeightUps, $saturdayDeliveryFedEx, $saturdayPickupFedex, $sendShipNotifyEmailFedEx, $sendBackupEmailFedEx, $signatureHomeDeliveryFedEx, $shipNotifyEmailAddressFedEx, $backupEmailAddressFedEx, $shipDateFedEx, $homeDeliveryTypeFedEx, $homeDeliveryDateFedEx, $bookingConfirmationNumFedEx, $intlExemptionNumFedEx, $b13aFilingOptionFedEx, $b13aStatementDataFedEx, $thirdPartyAcctFedEx, $thirdPartyCountryFedEx, $thirdPartyTypeFedEx, $shipmentWeightFedEx, $termsOfSaleFedEx, $termsFreightChargeFedEx, $termsInsuranceChargeFedEx, $tranDate, $tranId, NetSuite_RecordRef $shipMethod, $generateIntegratedShipperLabel, $shippingCost, $shippingCostInForeignCurrency, $handlingCost, $handlingCostInForeignCurrency, NetSuite_ItemFulfillmentPackageList $packageList, NetSuite_ItemFulfillmentPackageUpsList $packageUpsList, NetSuite_ItemFulfillmentPackageFedExList $packageFedExList, NetSuite_ItemFulfillmentItemList $itemList, NetSuite_CustomFieldList $customFieldList, $internalId, $externalId) {
        $this->createdDate = $createdDate;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->postingPeriod = $postingPeriod;
        $this->entity = $entity;
        $this->createdFrom = $createdFrom;
        $this->shipAddressList = $shipAddressList;
        $this->shipAddress = $shipAddress;
        $this->shipOverride = $shipOverride;
        $this->shipAttention = $shipAttention;
        $this->shipCompany = $shipCompany;
        $this->shipAddr1 = $shipAddr1;
        $this->shipAddr2 = $shipAddr2;
        $this->shipCity = $shipCity;
        $this->shipState = $shipState;
        $this->shipZip = $shipZip;
        $this->shipCountry = $shipCountry;
        $this->shipPhone = $shipPhone;
        $this->shipIsResidential = $shipIsResidential;
        $this->shipStatus = $shipStatus;
        $this->saturdayDeliveryUps = $saturdayDeliveryUps;
        $this->sendShipNotifyEmailUps = $sendShipNotifyEmailUps;
        $this->sendBackupEmailUps = $sendBackupEmailUps;
        $this->shipNotifyEmailAddressUps = $shipNotifyEmailAddressUps;
        $this->shipNotifyEmailAddress2Ups = $shipNotifyEmailAddress2Ups;
        $this->backupEmailAddressUps = $backupEmailAddressUps;
        $this->shipNotifyEmailMessageUps = $shipNotifyEmailMessageUps;
        $this->thirdPartyAcctUps = $thirdPartyAcctUps;
        $this->thirdPartyZipcodeUps = $thirdPartyZipcodeUps;
        $this->thirdPartyCountryUps = $thirdPartyCountryUps;
        $this->thirdPartyTypeUps = $thirdPartyTypeUps;
        $this->partiesToTransactionUps = $partiesToTransactionUps;
        $this->exportTypeUps = $exportTypeUps;
        $this->methodOfTransportUps = $methodOfTransportUps;
        $this->carrierIdUps = $carrierIdUps;
        $this->entryNumberUps = $entryNumberUps;
        $this->inbondCodeUps = $inbondCodeUps;
        $this->isRoutedExportTransactionUps = $isRoutedExportTransactionUps;
        $this->licenseNumberUps = $licenseNumberUps;
        $this->licenseDateUps = $licenseDateUps;
        $this->licenseExceptionUps = $licenseExceptionUps;
        $this->eccNumberUps = $eccNumberUps;
        $this->recipientTaxIdUps = $recipientTaxIdUps;
        $this->blanketStartDateUps = $blanketStartDateUps;
        $this->blanketEndDateUps = $blanketEndDateUps;
        $this->shipmentWeightUps = $shipmentWeightUps;
        $this->saturdayDeliveryFedEx = $saturdayDeliveryFedEx;
        $this->saturdayPickupFedex = $saturdayPickupFedex;
        $this->sendShipNotifyEmailFedEx = $sendShipNotifyEmailFedEx;
        $this->sendBackupEmailFedEx = $sendBackupEmailFedEx;
        $this->signatureHomeDeliveryFedEx = $signatureHomeDeliveryFedEx;
        $this->shipNotifyEmailAddressFedEx = $shipNotifyEmailAddressFedEx;
        $this->backupEmailAddressFedEx = $backupEmailAddressFedEx;
        $this->shipDateFedEx = $shipDateFedEx;
        $this->homeDeliveryTypeFedEx = $homeDeliveryTypeFedEx;
        $this->homeDeliveryDateFedEx = $homeDeliveryDateFedEx;
        $this->bookingConfirmationNumFedEx = $bookingConfirmationNumFedEx;
        $this->intlExemptionNumFedEx = $intlExemptionNumFedEx;
        $this->b13aFilingOptionFedEx = $b13aFilingOptionFedEx;
        $this->b13aStatementDataFedEx = $b13aStatementDataFedEx;
        $this->thirdPartyAcctFedEx = $thirdPartyAcctFedEx;
        $this->thirdPartyCountryFedEx = $thirdPartyCountryFedEx;
        $this->thirdPartyTypeFedEx = $thirdPartyTypeFedEx;
        $this->shipmentWeightFedEx = $shipmentWeightFedEx;
        $this->termsOfSaleFedEx = $termsOfSaleFedEx;
        $this->termsFreightChargeFedEx = $termsFreightChargeFedEx;
        $this->termsInsuranceChargeFedEx = $termsInsuranceChargeFedEx;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->shipMethod = $shipMethod;
        $this->generateIntegratedShipperLabel = $generateIntegratedShipperLabel;
        $this->shippingCost = $shippingCost;
        $this->shippingCostInForeignCurrency = $shippingCostInForeignCurrency;
        $this->handlingCost = $handlingCost;
        $this->handlingCostInForeignCurrency = $handlingCostInForeignCurrency;
        $this->packageList = $packageList;
        $this->packageUpsList = $packageUpsList;
        $this->packageFedExList = $packageFedExList;
        $this->itemList = $itemList;
        $this->customFieldList = $customFieldList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>