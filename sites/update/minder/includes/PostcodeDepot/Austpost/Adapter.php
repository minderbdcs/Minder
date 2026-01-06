<?php

class PostcodeDepot_Austpost_Adapter extends PostcodeDepot_Adapter_Array {
    protected $_fieldsMap = array(
        'pcode' => 'postCode',
        'locality' => 'locality',
        'state' => 'state',
        'comments' => 'comments',
        'deliveryoffice' => 'deliveryOffice',
        'presortindicator' => 'preSortIndicator',
        'parcelzone' => 'parcelZone',
        'bspnumber' => 'bspNumber',
        'bspname' => 'bspName',
        'category' => 'category',
        'record_id' => 'recordId'
    );

    /**
     * @param array $source
     * @return PostcodeDepot
     */
    public function convert($source) {
        $source    = array_values($source);
        $minLength = (count($source) > count($this->_fields)) ? count($this->_fields) : count($source);
        $result    = new PostcodeDepot();

        for ($i =0; $i < $minLength; $i++) {
            $propertyName = $this->_fieldsMap[$this->_fields[$i]];
            $result->$propertyName = trim($source[$i]);
        }

        $tmpCountry = $result->country;
        if (empty($tmpCountry))
            $result->country = 'AU';

        return $result;
    }
}