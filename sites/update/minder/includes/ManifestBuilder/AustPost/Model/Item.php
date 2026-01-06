<?php

/**
 * @property string QTY_PICKED
 * @property string PICK_DETAIL_STATUS
 * @property string SHORT_DESC
 */
class ManifestBuilder_AustPost_Model_Item extends ArrayObject {

    const PROD_DESCRIPTION_LEN = 45;

    public function __construct($input = null, $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct(empty($input) ? array() : $input, static::ARRAY_AS_PROPS, $iterator_class);
    }

    public function isValid() {
        return (trim($this->QTY_PICKED) != "" )
            and (trim($this->QTY_PICKED)  != "0")
            and (trim($this->PICK_DETAIL_STATUS )  != "CN")
            and (trim($this->PICK_DETAIL_STATUS)  != "XX");
    }

    protected function getProdDescription() {
        return $this->SHORT_DESC;
    }

    public function getGoodsDescription() {
        return substr($this->getProdDescription(), 0, static::PROD_DESCRIPTION_LEN);
    }

}
