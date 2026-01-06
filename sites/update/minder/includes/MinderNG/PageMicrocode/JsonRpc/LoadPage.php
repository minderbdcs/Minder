<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Event\PageLoad;
use MinderNG\PageMicrocode\Microcode;

class LoadPage {
    /**
     * @var \Minder2_Environment
     */
    private $_environment;

    function __construct(\Minder2_Environment $environment)
    {
        $this->_environment = $environment;
    }


    public function execute(Microcode $microcode) {
        $microcode->getPublisher()->trigger(new PageLoad($this->_getEnvironment()));

        return $microcode->getPageComponents();
    }

    /**
     * @return \Minder2_Environment
     */
    private function _getEnvironment()
    {
        return $this->_environment;
    }

}