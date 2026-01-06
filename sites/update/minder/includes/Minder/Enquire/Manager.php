<?php

class Minder_Enquire_Manager {
    const PROD_ID = 'PROD_ID';

    /**
     * @return Minder_Enquire_List|Minder_Enquire_Abstract[]
     */
    public function getAll() {
        $result = new Minder_Enquire_List();

        foreach ($this->_getMinderOptions()->getEnquireTypes() as $option) {
            $newType = $this->_newType($option->CODE);
            $newType->dataType = $option->CODE;
            $newType->description = $option->DESCRIPTION;
            $newType->screens = $option->DESCRIPTION2;

            $result->add($newType);
        }

        return $result;
    }

    /**
     * @param $type
     * @return Minder_Enquire_Abstract|null
     */
    public function getByType($type) {
        foreach ($this->getAll() as $enquire) {
            if ($enquire->dataType == $type) {
                return $enquire;
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @return Minder_Enquire_Abstract
     */
    protected function _newType($type) {
        switch (strtoupper($type)) {
            case (static::PROD_ID):
                return new Minder_Enquire_Product();
            default:
                return new Minder_Enquire_Abstract();
        }
    }

    protected function _getMinderOptions() {
        return new Minder2_Options();
    }
}