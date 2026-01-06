<?php
/**
 * Minder
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * PickOrder
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class PickOrder extends Model
{
    public $addressLabelDate;
    public $adminFeeAmount;
    public $adminFeeRate;
    public $amountPaid;
    public $approvedBy;
    public $approvedDate;
    public $approvedDespBy;
    public $approvedDespDate;
    public $assemblyStarted;
    public $companyId;
    public $contactName;
    public $costCenter;
    public $createDate;
    public $createdBy;
    public $customerPoWo;
    public $dAddressLine1;
    public $dAddressLine2;
    public $dAddressLine3;
    public $dAddressLine4;
    public $dAddressLine5;
    public $dAddrRecordId;
    public $dCity;
    public $dCountry;
    public $dFirstName;
    public $dLastName;
    public $dPersonId;
    public $dPhone;
    public $dPostCode;
    public $dState;
    public $dTitle;
    public $defaultSalePrice;
    public $deliveryRunNo;
    public $despatchLocation;
    public $divisionId;
    public $dueAmount;
    public $exportCategory;
    public $footer1;
    public $footer2;
    public $footer3;
    public $footer4;
    public $footer5;
    public $freight;
    public $freightTaxAmount;
    public $importErrors;
    public $imported;
    public $invWithGoods;
    public $labelPrintedDate;
    public $lastLineNo;
    public $lastUpdateDate;
    public $netWeight;
    public $orderCancelled;
    public $other1;
    public $other2;
    public $other3;
    public $other4;
    public $other5;
    public $other6;
    public $other7;
    public $other8;
    public $other9;
    public $otherNum1;
    public $otherNum2;
    public $overSized;
    public $overSizedReason;
    public $pAddressLine1;
    public $pAddressLine2;
    public $pAddressLine3;
    public $pAddressLine4;
    public $pAddressLine5;
    public $pAddrRecordId;
    public $pAustPost4stateId;
    public $pCity;
    public $pCountry;
    public $pFirstName;
    public $pLastName;
    public $pPersonId;
    public $pPersonType;
    public $pPhone;
    public $pPostCode;
    public $pSameAsInvoiceTo;
    public $pState;
    public $pTitle;
    public $palletBase;
    public $partialPickAllowed;
    public $partialDespatchAllowed;
    public $paymentMethod;
    public $permanentTransfer;
    public $personId;
    public $pickDueDate;
    public $pickOrder;
    public $pickOrderStarted;
    public $pickOrderType;
    public $pickOrderSubType;
    public $pickPriority;
    public $pickRetrieveStatus;
    public $pickStarted;
    public $pickStatus;
    public $printedDate;
    public $printedStatus;
    public $remarks1;
    public $remarks2;
    public $remarks3;
    public $remarks4;
    public $remarks5;
    public $remarks6;
    public $returnDate;
    public $sAddressLine1;
    public $sAddressLine2;
    public $sAddressLine3;
    public $sAddressLine4;
    public $sAddressLine5;
    public $sCity;
    public $sCountry;
    public $sFirstName;
    public $sLastName;
    public $sPersonId;
    public $sPersonType;
    public $sPhone;
    public $sPostCode;
    public $sSameAsSoldFrom;
    public $sState;
    public $sTitle;
    public $shippingMethod;
    public $shipService;
    public $shipVia;
    public $specialInstructions;
    public $specialInstructions1;
    public $specialInstructions2;
    public $subTotalAmount;
    public $subTotalAmountSsnId;
    public $subTotalAmountProdId;
    public $subTotalTax;
    public $sumSaleAmount;
    public $sumStdAmount;
    public $supplierId;
    public $supplierList;
    public $taxAmount;
    public $taxRate;
    public $terms;
    public $updateId;
    public $whId;
    public $wipOrdering;
    public $soLegacyConsignment;
    public $soLegacyInternalId;
    public $soLegacyLastModified;
    public $soLegacyPickWhId;
    public $soLegacyPickWhName;
    public $soLegacyMemo;
    public $soLegacyStatusId;
    public $shipViaName;
    public $soLegacyCreateDate;
    public $poMaterialSafetyData;
    public $legacyLedgerAdminFeeCode;
    public $legacyLedgerFreightCode;
    public $legacyLedgerDepositCode;
    public $legacyLedgerSaleCode;
    public $legacyLedgerSaleSsnIdCode;
   
    // This is not a column of the PICK_ORDER table
    /**
     * @deprecated PICK_ORDER.SUBTOTAL_TAX field is added use it instead
     * @var int $taxSubTotal
     */
    public $taxSubTotal;

    public function __construct() {
        $this->addressLabelDate = '';
        $this->adminFeeAmount = 0;
        $this->adminFeeRate = 0;
        $this->amountPaid = 0;
        $this->approvedBy = '';
        $this->approvedDate = '';
        $this->approvedDespBy = '';
        $this->approvedDespDate = '';
        $this->assemblyStarted = '';
        $this->companyId = '';
        $this->contactName = '';
        $this->costCenter = '';
        $this->createDate = '';
        $this->createdBy = '';
        $this->customerPoWo = '';
        $this->dAddressLine1 = '';
        $this->dAddressLine2 = '';
        $this->dAddressLine3 = '';
        $this->dAddressLine4 = '';
        $this->dAddressLine5 = '';
        $this->dCity = '';
        $this->dCountry = '';
        $this->dFirstName = '';
        $this->dLastName = '';
        $this->dPhone = '';
        $this->dPostCode = '';
        $this->dState = '';
        $this->dTitle = '';
        $this->defaultSalePrice = '';
        $this->deliveryRunNo = '';
        $this->despatchLocation = '';
        $this->divisionId = '';
        $this->dueAmount = 0;
        $this->exportCategory = '';
        $this->footer1 = '';
        $this->footer2 = '';
        $this->footer3 = '';
        $this->footer4 = '';
        $this->footer5 = '';
        $this->freight = 0;
        $this->freightTaxAmount = 0;
        $this->taxSubTotal = 0;
        $this->importErrors = 0;
        $this->imported = 'N';
        $this->invWithGoods = 'F';
        $this->labelPrintedDate = '';
        $this->lastLineNo = 0;
        $this->lastUpdateDate = '';
        $this->netWeight = '';
        $this->orderCancelled = '';
        $this->other1 = '';
        $this->other2 = '';
        $this->other3 = '';
        $this->other4 = '';
        $this->other5 = '';
        $this->other6 = '';
        $this->other7 = '';
        $this->other8 = '';
        $this->other9 = '';
        $this->otherNum1 = 0;
        $this->otherNum2 = 0;
        $this->overSized = 'F';
        $this->overSizedReason = '';
        $this->pAddressLine1 = '';
        $this->pAddressLine2 = '';
        $this->pAddressLine3 = '';
        $this->pAddressLine4 = '';
        $this->pAddressLine5 = '';
        $this->pAustPost4stateId = '';
        $this->pCity = '';
        $this->pCountry = '';
        $this->pFirstName = '';
        $this->pLastName = '';
        $this->pPersonId = '';
        $this->pPersonType = '';
        $this->pPhone = '';
        $this->pPostCode = '';
        $this->pSameAsInvoiceTo = 'F';
        $this->pState = '';
        $this->pTitle = '';
        $this->palletBase = '';
        $this->partialPickAllowed = 'F';
        $this->partialDespatchAllowed = 'F';
        $this->paymentMethod = '';
        $this->permanentTransfer = '';
        $this->personId = '';
        $this->pickDueDate = date('Y-m-d');
        $this->pickOrder = '';
        $this->pickOrderStarted = '';
        $this->pickOrderType = '';
        $this->pickOrderSubType = '';
        $this->pickPriority = 0;
        $this->pickRetrieveStatus = 'F';
        $this->pickStarted = '';
        $this->pickStatus = 'UC';
        $this->printedDate = '';
        $this->printedStatus = 'F';
        $this->remarks1 = '';
        $this->remarks2 = '';
        $this->remarks3 = '';
        $this->remarks4 = '';
        $this->remarks5 = '';
        $this->remarks6 = '';
        $this->returnDate = '';
        $this->sAddressLine1 = '';
        $this->sAddressLine2 = '';
        $this->sAddressLine3 = '';
        $this->sAddressLine4 = '';
        $this->sAddressLine5 = '';
        $this->sCity = '';
        $this->sCountry = '';
        $this->sFirstName = '';
        $this->sLastName = '';
        $this->sPersonId = '';
        $this->sPersonType = '';
        $this->sPhone = '';
        $this->sPostCode = '';
        $this->sSameAsSoldFrom = 'T';
        $this->sState = '';
        $this->sTitle = '';
        $this->shippingMethod = 'FIS';
        $this->shipService = '';
        $this->shipVia = '';
        $this->specialInstructions = 'F';
        $this->specialInstructions1 = '';
        $this->specialInstructions2 = '';
        $this->subTotalAmount = 0;
        $this->subTotalAmountProdId = 0;
        $this->subTotalAmountSsnId = 0;
        $this->sumSaleAmount = '';
        $this->sumStdAmount = '';
        $this->supplierId = '';
        $this->supplierList = 'F';
        $this->taxAmount = 0;
        $this->taxRate = 0;
        $this->terms = '';
        $this->updateId = '';
        $this->whId = '';
        $this->wipOrdering = '';
        $this->soLegacyConsignment = '';
        $this->soLegacyInternalId = '';
        $this->soLegacyLastModified = '';
        $this->soLegacyPickWhId = '';
        $this->soLegacyPickWhName = '';
        $this->soLegacyMemo = '';
        $this->soLegacyStatusId = '';
        $this->shipViaName = '';
        $this->soLegacyCreateDate = '';
        $this->poMaterialSafetyData = 'F';
        $this->legacyLedgerAdminFeeCode = '';
        $this->legacyLedgerFreightCode = '';
        $this->legacyLedgerDepositCode = '';
        $this->legacyLedgerSaleCode = '';
        $this->legacyLedgerSaleSsnIdCode = '';
}

    public function canAllocatePicks()
    {
        return false;
    }

    public function canApproveForDespatch()
    {
        if (in_array($this->pickStatus, array('CF', 'OP'))) {
            return true;
        }
        return false;
    }

    public function canApproveForPicking()
    {
        if (in_array($this->pickStatus, array('CF'))) {
            return true;
        }
        return false;
    }

    public function canCancel()
    {
        if (in_array($this->pickStatus, array('CF', 'OP', 'DA', 'HD'))) {
            return true;
        }
        return false;
    }

    public function canConfirm()
    {
        if ($this->personId == 'ANONYMOUS') {
            return false;
        }
        if (in_array($this->pickStatus, array('UC', 'UP', 'HD'))) {
            return true;
        }
        return false;
    }

    public function canHold()
    {
        if (in_array($this->pickStatus, array('CF', 'OP', 'DA', 'UC' ))) {
            return true;
        }
        return false;
    }

    public function canUnHold()
    {
        if (in_array($this->pickStatus, array('HD', 'CF', 'OP', 'DA', 'UC'))) {
            return true;
        }
        return false;
    }

    public function canReserve()
    {
        return false;
    }

    /**
     * Replace PICK_ORDER fields values vith provided
     *
     * @param  array $updateFields - new fields values
     * @return void
     */
    public function update($updateFields) {
        if (isset($updateFields['PICK_ORDER']))
            unset($updateFields['PICK_ORDER']);

        foreach ($updateFields as $fieldName => $fieldValue) {
            $propertyName = transformToObjectProp($fieldName);
            if (property_exists($this, $propertyName))
                $this->$propertyName = $fieldValue;
        }
    }

    /**
     * @return boolean
     */
    protected function isGstApplicable() {
        if (empty($this->pCountry) || strtoupper($this->pCountry) == 'AU' || strtoupper($this->pCountry) == 'AUSTRALIA')
            return true;
        return false;
    }

    protected function getDefaultGstRate() {
        if ($this->isGstApplicable())
            return Minder::getInstance()->defaultControlValues['DEFAULT_GST_RATE'];
        return 0;
    }

    protected function getGstRate() {
        if ($this->isGstApplicable())
            return Minder::getInstance()->defaultControlValues['DEFAULT_GST_RATE'];
        return 0;
    }

    public function recalculateBillingInformation() {
        $this->freight          = (is_numeric($this->freight))          ? $this->freight            : 0;
        $this->taxRate          = (is_numeric($this->taxRate))          ? $this->taxRate            : $this->getGstRate();
        $this->subTotalAmount   = (is_numeric($this->subTotalAmount))   ? $this->subTotalAmount     : 0;
        $this->adminFeeRate     = (is_numeric($this->adminFeeRate))     ? $this->adminFeeRate       : 0;
        $this->adminFeeAmount   = (is_numeric($this->adminFeeAmount))   ? $this->adminFeeAmount     : 0;
        $this->otherNum1        = (is_numeric($this->otherNum1))        ? $this->otherNum1          : 0;
        $this->otherNum2        = (is_numeric($this->otherNum2))        ? $this->otherNum2          : 0;
        $this->subTotalTax      = (is_numeric($this->subTotalTax))      ? $this->subTotalTax        : 0;
        $this->amountPaid       = (is_numeric($this->amountPaid))       ? $this->amountPaid         : 0;
        $this->freightTaxAmount = round($this->freight * $this->taxRate / 100, 2);
        $tmpTaxableAmount       = $this->freight + ($this->subTotalAmount + $this->freight) * $this->adminFeeRate / 100 + $this->adminFeeAmount + $this->otherNum1 + $this->otherNum2;
        $this->taxAmount        = round($this->subTotalTax + $tmpTaxableAmount * $this->taxRate / 100, 2);
        $this->dueAmount        = round($this->subTotalAmount + $tmpTaxableAmount + $this->taxAmount - $this->amountPaid, 2);
    }
}
