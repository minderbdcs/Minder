<?php

/**
 * @property string SERVICE_LOCATION_ID
 * @property string PACK_SERIAL_NO
 * @property string PACK_SEQUENCE_NO
 * @property string SERVICE_SERVICE_CODE
 * @property string PACK_LAST_SEQUENCE_INDICATOR
 *
 * @property string DESPATCH_LABEL_NO
 *
 * @property string DIMENSION_X
 * @property string DIMENSION_Y
 * @property string DIMENSION_Z
 *
 * @property string PACK_WEIGHT
 *
 * @property string SERVICE_TRANSIT_COVER_REQD
 * @property string SERVICE_TRANSIT_COVER_AMOUNT
 *
 * @property string PACK_ID
 */
class ManifestBuilder_TnT_Model_Article extends ArrayObject {

    public $articleNo;

    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct(empty($input) ? array() : $input, static::ARRAY_AS_PROPS, $iterator_class);
    }

    public function getRawNumber() {
        return trim($this->SERVICE_LOCATION_ID . $this->PACK_SERIAL_NO . $this->PACK_SEQUENCE_NO . $this->SERVICE_SERVICE_CODE . $this->PACK_LAST_SEQUENCE_INDICATOR);
    }


    public function getBarcodeArticleNumber() {
        return trim($this->DESPATCH_LABEL_NO);
    }

    public function getLength() {
        return trim(sprintf("%d", $this->DIMENSION_X));
    }

    public function getWidth() {
        return trim(sprintf("%d", $this->DIMENSION_Y));
    }

    public function getHeight() {
        return trim(sprintf("%d", $this->DIMENSION_Z));
    }

    public function getActualWeight() {
        return round(floatval(trim($this->PACK_WEIGHT)), 2);
    }

    public function getIsTransitCoverRequired() {
        return ($this->SERVICE_TRANSIT_COVER_REQD == 'T') ? 'Y' : 'N';
    }

    public function getTransitCoverAmount() {
        return empty($this->SERVICE_TRANSIT_COVER_AMOUNT) ? 0 : $this->SERVICE_TRANSIT_COVER_AMOUNT;
    }
}
