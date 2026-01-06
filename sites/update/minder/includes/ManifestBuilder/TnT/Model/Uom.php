<?php

/**
 * Class ManifestBuilder_TnT_Model_Uom
 * @property string TO_STANDARD_CONV
 */
class ManifestBuilder_TnT_Model_Uom extends Zend_Db_Table_Row {
    protected $_tableClass = 'ManifestBuilder_TnT_Table_Uom';


    public function getPackLength($length) {
//var_dump($length);
        $_to_standard_conv = is_null($this->TO_STANDARD_CONV) ? "1" : $this->TO_STANDARD_CONV;
//var_dump($_to_standard_conv);
        return  (float) $length / (float) $_to_standard_conv;
    }

}
