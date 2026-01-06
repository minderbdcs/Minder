<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\Param;

class DataIdentifierManager {
    /**
     * @var Param
     */
    private $_paramProvider;

    function __construct(Param $paramProvider)
    {
        $this->_paramProvider = $paramProvider;
    }

    public function getDataIdentifierCollection(\Minder2_Environment $environment) {
        $result = new DataIdentifierCollection();
        $result->init($this->_paramProvider->getDeviceIdentifiers($environment->getCurrentDevice()), new AddOptions(true, true));

        return $result;
    }
}