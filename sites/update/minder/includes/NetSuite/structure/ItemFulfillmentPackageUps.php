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
 * ItemFulfillmentPackageUps map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemFulfillmentPackageUps {
    public $packageWeightUps;
    public $packageDescrUps;
    public $packageTrackingNumberUps;
    public $packagingUps;
    public $insuredValueUps;
    public $reference1Ups;
    public $reference2Ups;
    public $packageLengthUps;
    public $packageWidthUps;
    public $packageHeightUps;
    public $additionalHandlingUps;
    public $useCodUps;
    public $codAmountUps;
    public $codMethodUps;
    public $deliveryConfUps;

    public function __construct(  $packageWeightUps, $packageDescrUps, $packageTrackingNumberUps, $packagingUps, $insuredValueUps, $reference1Ups, $reference2Ups, $packageLengthUps, $packageWidthUps, $packageHeightUps, $additionalHandlingUps, $useCodUps, $codAmountUps, $codMethodUps, $deliveryConfUps) {
        $this->packageWeightUps = $packageWeightUps;
        $this->packageDescrUps = $packageDescrUps;
        $this->packageTrackingNumberUps = $packageTrackingNumberUps;
        $this->packagingUps = $packagingUps;
        $this->insuredValueUps = $insuredValueUps;
        $this->reference1Ups = $reference1Ups;
        $this->reference2Ups = $reference2Ups;
        $this->packageLengthUps = $packageLengthUps;
        $this->packageWidthUps = $packageWidthUps;
        $this->packageHeightUps = $packageHeightUps;
        $this->additionalHandlingUps = $additionalHandlingUps;
        $this->useCodUps = $useCodUps;
        $this->codAmountUps = $codAmountUps;
        $this->codMethodUps = $codMethodUps;
        $this->deliveryConfUps = $deliveryConfUps;
    }
}?>