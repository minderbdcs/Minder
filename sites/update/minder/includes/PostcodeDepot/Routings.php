<?php

class PostcodeDepot_Routings {

    /**
     * Saves collection of POSTCODE_DEPOT records recieved from Austpost.
     * If some POSTCODE_DEPOT record have no RECORD_ID, method searches for existed
     * records with same POSTCODE, LOCALITY, STATE and COUNTRY (siblings). If found one,
     * rewrite it with new record data. As there can be several records with same
     * POSTCODE, LOCALITY, STATE and COUNTRY in both new data and existing data, method
     * owerwrite first record from existed dataset with first from importing dataset, second -
     * with second, and so on.
     * If no siblings found (or all existed is already owerwritten) new recocord is created.
     *
     * @param PostcodeDepot_Collection $postcodeCollection
     * @param PostcodeDepot_Mapper $dataMapper
     * @return void
     */
    public function saveCollectionOfAustpostPostcodes($postcodeCollection, $dataMapper) {
        $siblingsOrder = array();

        /**
         * @var PostcodeDepot $postcode
         */
        foreach ($postcodeCollection as $postcode) {
            if (!$postcode->existedRecord()) {
                $hash = $postcode->postCode . $postcode->locality . $postcode->state . $postcode->country;
                if (!isset($siblingsOrder[$hash]))
                    $siblingsOrder[$hash] = 0;
                $order = $siblingsOrder[$hash]++;
                $postcode->recordId = $dataMapper->findSiblingRecordId($postcode->postCode, $postcode->locality, $postcode->state, $postcode->country, $order);
            }

            $dataMapper->save($postcode);
        }
    }

    /**
     * @param PostcodeDepot_Collection $postcodeCollection
     * @param PostcodeDepot_Mapper $dataMapper
     * @param string $depotIdField
     * @return void
     */
    public function updateCarriersDepots($postcodeCollection, $dataMapper, $depotIdField) {
        $depotIdProperty = transformToObjectProp($depotIdField);
        /**
         * @var PostcodeDepot $postcodeRecord
         */
        foreach ($postcodeCollection as $postcodeRecord) {
            $dataMapper->massDepotIdUpdate($postcodeRecord->postCode, $postcodeRecord->$depotIdProperty, $depotIdField);
        }
    }
}