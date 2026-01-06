<?php

/**
 * @property string COMPANY_ID
 * @property string DEFAULT_CARRIER_ID
 * @property string DESPATCH_CHECK_WEIGHT
 * @property string DESPATCH_CHECK_VOLUME
 */
class Minder2_Model_Company extends Minder2_Model {
    /**
     * @return string
     */
    function getName()
    {
        return $this->COMPANY_ID;
    }

    /**
     * @return int
     */
    function getOrder()
    {
        return 0;
    }

    /**
     * @return string
     */
    function getStateId()
    {
        return 'COMPANY-' . $this->getName();
    }

    function isDespatchVolumeRequired() {
        return $this->DESPATCH_CHECK_VOLUME == 'T';
    }

    function isDespatchWeightRequired() {
        return $this->DESPATCH_CHECK_WEIGHT == 'T';
    }
}