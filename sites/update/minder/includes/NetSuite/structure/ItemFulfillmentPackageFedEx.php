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
 * ItemFulfillmentPackageFedEx map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemFulfillmentPackageFedEx {
    public $packageWeightFedEx;
    public $packageTrackingNumberFedEx;
    public $packagingFedEx;
    public $admPackageTypeFedEx;
    public $isNonStandardContainerFedEx;
    public $insuredValueFedEx;
    public $reference1FedEx;
    public $packageLengthFedEx;
    public $packageWidthFedEx;
    public $packageHeightFedEx;
    public $useCodFedEx;
    public $codAmountFedEx;
    public $codMethodFedEx;
    public $codFreightTypeFedEx;
    public $deliveryConfFedEx;
    public $signatureOptionsFedEx;
    public $signatureReleaseFedEx;
    public $authorizationNumberFedEx;

    public function __construct(  $packageWeightFedEx, $packageTrackingNumberFedEx, $packagingFedEx, $admPackageTypeFedEx, $isNonStandardContainerFedEx, $insuredValueFedEx, $reference1FedEx, $packageLengthFedEx, $packageWidthFedEx, $packageHeightFedEx, $useCodFedEx, $codAmountFedEx, $codMethodFedEx, $codFreightTypeFedEx, $deliveryConfFedEx, $signatureOptionsFedEx, $signatureReleaseFedEx, $authorizationNumberFedEx) {
        $this->packageWeightFedEx = $packageWeightFedEx;
        $this->packageTrackingNumberFedEx = $packageTrackingNumberFedEx;
        $this->packagingFedEx = $packagingFedEx;
        $this->admPackageTypeFedEx = $admPackageTypeFedEx;
        $this->isNonStandardContainerFedEx = $isNonStandardContainerFedEx;
        $this->insuredValueFedEx = $insuredValueFedEx;
        $this->reference1FedEx = $reference1FedEx;
        $this->packageLengthFedEx = $packageLengthFedEx;
        $this->packageWidthFedEx = $packageWidthFedEx;
        $this->packageHeightFedEx = $packageHeightFedEx;
        $this->useCodFedEx = $useCodFedEx;
        $this->codAmountFedEx = $codAmountFedEx;
        $this->codMethodFedEx = $codMethodFedEx;
        $this->codFreightTypeFedEx = $codFreightTypeFedEx;
        $this->deliveryConfFedEx = $deliveryConfFedEx;
        $this->signatureOptionsFedEx = $signatureOptionsFedEx;
        $this->signatureReleaseFedEx = $signatureReleaseFedEx;
        $this->authorizationNumberFedEx = $authorizationNumberFedEx;
    }
}?>